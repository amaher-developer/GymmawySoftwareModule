<?php

namespace Modules\Software\Services;

use Modules\Software\Http\Controllers\Front\GymMoneyBoxFrontController;
use Modules\Software\Models\GymMoneyBox;
use Modules\Software\Models\GymSwInvoice;

/**
 * Diagnoses and repairs the sw_gym_money_boxes running-balance chain.
 *
 * Three independent signals are checked because a corrupted `amount` on one
 * row does not always show up as a chain break: once a rebuild script runs
 * again after the corruption, it can recompute every amount_before after it
 * consistently (just anchored on the wrong number), leaving the recurrence
 * check clean. Comparing each row's amount against its own linked invoice
 * (an independent, untouched source of truth) catches that case.
 */
class MoneyBoxAuditService
{
    public function scan(int $branchId, int $limit = 100): array
    {
        $rows = GymMoneyBox::where('branch_setting_id', $branchId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'amount', 'amount_before', 'operation', 'invoice_id', 'created_at', 'updated_at']);

        $invoiceIds = $rows->pluck('invoice_id')->filter()->unique()->values();
        $invoiceTotals = GymSwInvoice::whereIn('id', $invoiceIds)->pluck('total', 'id');

        $prev = null;
        $maxIdSeen = 0;
        $chainBreaks = [];
        $orderIssues = [];
        $amountMismatches = [];

        foreach ($rows as $row) {
            if ($row->id < $maxIdSeen) {
                $orderIssues[] = [
                    'id' => $row->id,
                    'created_at' => (string) $row->created_at,
                    'max_id_seen_before' => $maxIdSeen,
                ];
            }
            $maxIdSeen = max($maxIdSeen, $row->id);

            if ($row->invoice_id && isset($invoiceTotals[$row->invoice_id])) {
                $rowAmount = round((float) $row->amount, 2);
                $invoiceTotal = round((float) $invoiceTotals[$row->invoice_id], 2);

                if (abs($rowAmount - $invoiceTotal) > 0.01) {
                    $amountMismatches[] = [
                        'id' => $row->id,
                        'created_at' => (string) $row->created_at,
                        'updated_at' => (string) $row->updated_at,
                        'stored_amount' => $rowAmount,
                        'invoice_id' => $row->invoice_id,
                        'invoice_total' => $invoiceTotal,
                        'diff' => round($rowAmount - $invoiceTotal, 2),
                        'suggested_amount' => $invoiceTotal,
                    ];

                    if (count($amountMismatches) >= $limit) {
                        // keep scanning for order issues / chain breaks but stop collecting more of this list
                    }
                }
            }

            if ($prev) {
                $expected = GymMoneyBoxFrontController::amountAfter(
                    round((float) $prev->amount, 2),
                    round((float) $prev->amount_before, 2),
                    (int) $prev->operation
                );
                $actual = round((float) $row->amount_before, 2);

                if (abs($expected - $actual) > 0.01 && count($chainBreaks) < $limit) {
                    $chainBreaks[] = [
                        'id' => $row->id,
                        'created_at' => (string) $row->created_at,
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
            'chain_breaks' => $chainBreaks,
            'order_issues' => $orderIssues,
        ];
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
}
