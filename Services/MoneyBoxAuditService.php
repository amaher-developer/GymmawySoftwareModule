<?php

namespace Modules\Software\Services;

use Illuminate\Support\Facades\DB;
use Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymSwInvoice;

/**
 * Diagnoses and repairs the sw_gym_money_boxes running-balance chain.
 *
 * Four independent signals are checked because a corrupted `amount` on one
 * row does not always show up as a chain break: once a rebuild script runs
 * again after the corruption, it can recompute every amount_before after it
 * consistently (just anchored on the wrong number), leaving the recurrence
 * check clean.
 *
 * - invoice cross-check only applies to standalone manual entries (no
 *   subscription/order/pt-member FK). For those, invoice.amount_paid ==
 *   invoice.total == the row's own amount by construction (a manual add/
 *   withdraw is a single atomic full transaction - see
 *   GymMoneyBoxObserver::handleManualEntry). It is NOT applied to
 *   subscription/order/pt-member linked rows: sw_gym_money_boxes.amount only
 *   ever holds what was actually paid in that one transaction (can be a
 *   partial installment), while the invoice's `total` is the full price
 *   regardless of how much has been paid so far - comparing the two would
 *   flag every installment plan as corrupted.
 * - invoice cross-check also only covers rows created after invoice_id
 *   existed (added 2026-04-30), so it misses everything older.
 * - source reconciliation (this file's SOURCE_RECONCILIATIONS) covers every
 *   row regardless of age or payment plan, by comparing the net sum of money
 *   box entries tied to a subscription/order/pt-member against that
 *   record's own independently-maintained amount_paid field (which is
 *   cumulative, so it correctly matches partial payments too).
 */
class MoneyBoxAuditService
{
    /**
     * fk column on sw_gym_money_boxes => [source table, source amount column, label]
     * Each source's amount_paid is reconciled against the NET sum (adds - subs)
     * of every money box row tied to it, since a single subscription/order can
     * have several partial payments spread across multiple rows.
     * non_member_subscription_id is intentionally excluded: sw_gym_non_members
     * has no reliable cumulative amount_paid column to compare against.
     */
    private const SOURCE_RECONCILIATIONS = [
        'member_subscription_id'    => ['sw_gym_member_subscription', 'amount_paid', 'Member subscription'],
        'member_pt_subscription_id' => ['sw_gym_pt_members', 'amount_paid', 'PT subscription'],
        'store_order_id'            => ['sw_gym_store_orders', 'amount_paid', 'Store order'],
    ];

    /**
     * @param string|null $fromDate 'Y-m-d' - only used to narrow which issues are
     *   reported, never to narrow what is computed. Chain/order checks always
     *   walk the FULL history (a break can only be judged against the row
     *   immediately before it, which may sit outside the requested period),
     *   and source reconciliation always sums a subscription/order's ENTIRE
     *   lifetime (amount_paid is cumulative, so summing a partial window
     *   would itself look like a false mismatch). The date range just filters
     *   which already-correctly-computed issues are worth showing you.
     */
    public function scan(int $branchId, int $limit = 100, ?string $fromDate = null, ?string $toDate = null): array
    {
        $rows = GymMoneyBox::where('branch_setting_id', $branchId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'id', 'amount', 'amount_before', 'operation', 'invoice_id', 'created_at', 'updated_at',
                'member_subscription_id', 'member_pt_subscription_id', 'non_member_subscription_id', 'store_order_id',
            ]);

        $invoiceIds = $rows->pluck('invoice_id')->filter()->unique()->values();
        $invoiceAmountsPaid = GymSwInvoice::whereIn('id', $invoiceIds)->pluck('amount_paid', 'id');

        $from = $fromDate ? $fromDate . ' 00:00:00' : null;
        $to   = $toDate ? $toDate . ' 23:59:59' : null;
        $inRange = function (string $createdAt) use ($from, $to) {
            if ($from && $createdAt < $from) return false;
            if ($to && $createdAt > $to) return false;
            return true;
        };

        $prev = null;
        $maxIdSeen = 0;
        $chainBreaks = [];
        $orderIssues = [];
        $amountMismatches = [];

        foreach ($rows as $row) {
            $createdAt = (string) $row->created_at;

            if ($row->id < $maxIdSeen) {
                if ($inRange($createdAt) && count($orderIssues) < $limit) {
                    $orderIssues[] = [
                        'id' => $row->id,
                        'created_at' => $createdAt,
                        'max_id_seen_before' => $maxIdSeen,
                    ];
                }
            }
            $maxIdSeen = max($maxIdSeen, $row->id);

            $isStandaloneEntry = !$row->member_subscription_id && !$row->member_pt_subscription_id
                && !$row->non_member_subscription_id && !$row->store_order_id;

            if ($isStandaloneEntry && $row->invoice_id && isset($invoiceAmountsPaid[$row->invoice_id]) && $inRange($createdAt)) {
                $rowAmount = round((float) $row->amount, 2);
                $invoicePaid = round((float) $invoiceAmountsPaid[$row->invoice_id], 2);

                if (abs($rowAmount - $invoicePaid) > 0.01 && count($amountMismatches) < $limit) {
                    $amountMismatches[] = [
                        'id' => $row->id,
                        'created_at' => $createdAt,
                        'updated_at' => (string) $row->updated_at,
                        'stored_amount' => $rowAmount,
                        'invoice_id' => $row->invoice_id,
                        'invoice_amount_paid' => $invoicePaid,
                        'diff' => round($rowAmount - $invoicePaid, 2),
                        'suggested_amount' => $invoicePaid,
                    ];
                }
            }

            if ($prev) {
                $expected = GymMoneyBoxFrontController::amountAfter(
                    round((float) $prev->amount, 2),
                    round((float) $prev->amount_before, 2),
                    (int) $prev->operation
                );
                $actual = round((float) $row->amount_before, 2);

                if (abs($expected - $actual) > 0.01 && $inRange($createdAt) && count($chainBreaks) < $limit) {
                    $chainBreaks[] = [
                        'id' => $row->id,
                        'created_at' => $createdAt,
                        'prev_id' => $prev->id,
                        'expected_amount_before' => $expected,
                        'stored_amount_before' => $actual,
                        'diff' => round($expected - $actual, 2),
                    ];
                }
            }

            $prev = $row;
        }

        return [
            'scanned' => $rows->count(),
            'amount_mismatches' => $amountMismatches,
            'source_mismatches' => $this->reconcileSources($branchId, $limit, $from, $to),
            'chain_breaks' => $chainBreaks,
            'order_issues' => $orderIssues,
        ];
    }

    /**
     * Compare the net sum (adds minus refunds/withdrawals) of money box rows
     * tied to each subscription/order/pt-member/transaction against that
     * source record's own amount_paid — an independent field never touched
     * by any money box rebuild script.
     */
    private function reconcileSources(int $branchId, int $limit, ?string $from = null, ?string $to = null): array
    {
        $mismatches = [];

        foreach (self::SOURCE_RECONCILIATIONS as $fkColumn => [$sourceTable, $amountColumn, $label]) {
            // Sum is always over the FULL lifetime of each group - amount_paid on
            // the source is cumulative, so summing only a date window would make
            // every subscription with activity outside that window look broken.
            // has_activity_in_range only decides whether to report the mismatch.
            $fromBound = $from ?? '1970-01-01 00:00:00';
            $toBound   = $to ?? '2099-12-31 23:59:59';

            $grouped = DB::table('sw_gym_money_boxes')
                ->selectRaw("{$fkColumn}, SUM(CASE WHEN operation = 0 THEN amount WHEN operation IN (1, 2) THEN -amount ELSE 0 END) as net_amount")
                ->selectRaw('MAX(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as has_activity_in_range', [$fromBound, $toBound])
                ->where('branch_setting_id', $branchId)
                ->whereNotNull($fkColumn)
                ->whereNull('deleted_at')
                ->groupBy($fkColumn)
                ->get();

            if ($grouped->isEmpty()) {
                continue;
            }

            $sourceAmounts = DB::table($sourceTable)
                ->whereIn('id', $grouped->pluck($fkColumn))
                ->pluck($amountColumn, 'id');

            foreach ($grouped as $group) {
                $fkId = $group->$fkColumn;
                if (!isset($sourceAmounts[$fkId])) {
                    continue; // source record deleted/missing - not an amount corruption
                }

                if (($from || $to) && !$group->has_activity_in_range) {
                    continue; // no activity in the requested period, skip reporting it
                }

                $net = round((float) $group->net_amount, 2);
                $expected = round((float) $sourceAmounts[$fkId], 2);

                if (abs($net - $expected) > 0.05) {
                    $mismatches[] = [
                        'source' => $label,
                        'fk_column' => $fkColumn,
                        'source_id' => $fkId,
                        'money_box_net' => $net,
                        'source_amount_paid' => $expected,
                        'diff' => round($net - $expected, 2),
                    ];

                    if (count($mismatches) >= $limit) {
                        return $mismatches;
                    }
                }
            }
        }

        return $mismatches;
    }

    /**
     * Correct a row's amount and rebuild every subsequent row's amount_before.
     * Never touches any other row's `amount` — only the one being fixed.
     */
    public function fix(int $branchId, int $id, float $correctedAmount): array
    {
        $row = GymMoneyBox::where('branch_setting_id', $branchId)->where('id', $id)->first();

        if (!$row) {
            return ['success' => false, 'message' => 'Record not found'];
        }

        $row->amount = round($correctedAmount, 2);
        $row->save();

        $controller = new GymMoneyBoxFrontController();
        $controller->scriptForRebuildMoneybox($id);

        return ['success' => true];
    }

    /**
     * Repair a chain_breaks entry: $anchorId is the LAST KNOWN-GOOD row (the
     * chain break's prev_id), whose own amount/amount_before/operation are
     * trusted as-is. Every row after it (by created_at, id order) gets its
     * amount_before recomputed from that point forward - this also silently
     * fixes any later breaks caused by the same race, since each row's
     * amount_before is derived from the freshly-recomputed row before it.
     * Never touches any row's `amount`, only `amount_before`.
     */
    public function rebuildChain(int $branchId, int $anchorId): array
    {
        $anchor = GymMoneyBox::where('branch_setting_id', $branchId)->where('id', $anchorId)->first();

        if (!$anchor) {
            return ['success' => false, 'message' => 'Anchor record not found'];
        }

        $controller = new GymMoneyBoxFrontController();
        $controller->scriptForRebuildMoneybox($anchorId);

        return ['success' => true];
    }
}
