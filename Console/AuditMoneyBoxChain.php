<?php

namespace Modules\Software\Console;

use Illuminate\Console\Command;
use Modules\Software\Services\MoneyBoxAuditService;

/**
 * Read-only audit of the sw_gym_money_boxes running-balance chain.
 * See Modules\Software\Services\MoneyBoxAuditService for the detection logic
 * (also used by the "Audit Money Box" admin UI page).
 *
 * Run manually:  php artisan moneybox:audit {branch_setting_id=1}
 */
class AuditMoneyBoxChain extends Command
{
    protected $signature = 'moneybox:audit {branch_setting_id=1} {--limit=50}';

    protected $description = 'Read-only: find corrupted amounts / broken links in the money box running-balance chain';

    public function handle(MoneyBoxAuditService $service): int
    {
        $branchId = (int) $this->argument('branch_setting_id');
        $limit    = (int) $this->option('limit');

        $result = $service->scan($branchId, $limit);

        $this->info("Scanned {$result['scanned']} rows for branch {$branchId}.");

        if ($result['amount_mismatches']) {
            $this->error(count($result['amount_mismatches']) . ' row(s) whose amount does not match their linked invoice total:');
            foreach ($result['amount_mismatches'] as $m) {
                $this->line("  id={$m['id']} created_at={$m['created_at']} updated_at={$m['updated_at']} stored_amount={$m['stored_amount']} invoice_total={$m['invoice_total']} diff={$m['diff']} -> suggested fix: set amount = {$m['suggested_amount']}");
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

        if (!$result['amount_mismatches'] && !$result['order_issues'] && !$result['chain_breaks']) {
            $this->info('Chain is consistent — no issues found.');
        }

        return self::SUCCESS;
    }
}
