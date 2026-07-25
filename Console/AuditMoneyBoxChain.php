<?php

namespace Modules\Software\Console;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Modules\Software\Services\MoneyBoxAuditService;

/**
 * Read-only audit of the sw_gym_money_boxes running-balance chain.
 * See Modules\Software\Services\MoneyBoxAuditService for the detection logic
 * (also used by the "Audit Money Box" admin UI page).
 *
 * Defaults to the last week when no --from/--to/--all is given, to keep the
 * default run fast and focused. Pass --all to scan the full history instead
 * (the underlying computation is always full-history either way - see
 * MoneyBoxAuditService - this only changes which issues get reported).
 *
 * Run manually:  php artisan moneybox:audit {branch_setting_id=1}
 */
class AuditMoneyBoxChain extends Command
{
    protected $signature = 'moneybox:audit {branch_setting_id=1} {--limit=50} {--from=} {--to=} {--all}';

    protected $description = 'Read-only: find corrupted amounts / broken links in the money box running-balance chain (defaults to last week, use --all for full history)';

    public function handle(MoneyBoxAuditService $service): int
    {
        $branchId = (int) $this->argument('branch_setting_id');
        $limit    = (int) $this->option('limit');
        $from     = $this->option('from');
        $to       = $this->option('to');

        if (!$from && !$to && !$this->option('all')) {
            $from = Carbon::now()->subWeek()->toDateString();
            $to   = Carbon::now()->toDateString();
        }

        $result = $service->scan($branchId, $limit, $from, $to);

        $period = $from || $to ? "{$from} .. {$to}" : 'full history';
        $this->info("Scanned {$result['scanned']} rows for branch {$branchId} (period: {$period}).");

        if ($result['amount_mismatches']) {
            $this->error(count($result['amount_mismatches']) . ' standalone entry(ies) whose amount does not match their linked invoice amount_paid:');
            foreach ($result['amount_mismatches'] as $m) {
                $this->line("  id={$m['id']} created_at={$m['created_at']} updated_at={$m['updated_at']} stored_amount={$m['stored_amount']} invoice_amount_paid={$m['invoice_amount_paid']} diff={$m['diff']} -> suggested fix: set amount = {$m['suggested_amount']}");
            }
        }

        if ($result['source_mismatches']) {
            $this->error(count($result['source_mismatches']) . ' subscription/order/pt-member whose linked money box entries do not net to its own amount_paid:');
            foreach ($result['source_mismatches'] as $s) {
                $this->line("  {$s['source']} id={$s['source_id']} money_box_net={$s['money_box_net']} source_amount_paid={$s['source_amount_paid']} diff={$s['diff']}");
            }
        }

        if ($result['order_issues']) {
            $this->warn(count($result['order_issues']) . ' id/created_at ordering issue(s) (row created_at was likely edited backward):');
            foreach ($result['order_issues'] as $o) {
                $this->line("  id={$o['id']} created_at={$o['created_at']} appears after a row with higher id ({$o['max_id_seen_before']}) in chronological order");
            }
        }

        if ($result['chain_breaks']) {
            $this->warn(count($result['chain_breaks']) . ' running-balance chain break(s):');
            foreach ($result['chain_breaks'] as $b) {
                $this->line("  id={$b['id']} created_at={$b['created_at']} prev_id={$b['prev_id']} expected_amount_before={$b['expected_amount_before']} stored={$b['stored_amount_before']} diff={$b['diff']}");
            }
        }

        if (!$result['amount_mismatches'] && !$result['source_mismatches'] && !$result['order_issues'] && !$result['chain_breaks']) {
            $this->info('Chain is consistent — no issues found.');
        }

        return self::SUCCESS;
    }
}
