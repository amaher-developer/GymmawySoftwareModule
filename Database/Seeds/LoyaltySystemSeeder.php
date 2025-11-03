<?php

namespace Modules\Software\Database\Seeds;

use Illuminate\Database\Seeder;
use Modules\Software\Models\LoyaltyPointRule;
use Modules\Software\Models\LoyaltyCampaign;
use Carbon\Carbon;

/**
 * LoyaltySystemSeeder
 * 
 * Seeds initial data for the loyalty points system
 * 
 * Usage: php artisan db:seed --class="Modules\Software\Database\Seeds\LoyaltySystemSeeder"
 */
class LoyaltySystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Loyalty System...');

        // Create default loyalty point rule
        $defaultRule = LoyaltyPointRule::firstOrCreate(
            ['name' => 'Standard Points Rule'],
            [
                'branch_setting_id' => 1,
                'money_to_point_rate' => 10.00,  // 10 EGP = 1 Point
                'point_to_money_rate' => 10.00,  // 1 Point = 10 EGP
                'expires_after_days' => 365,      // Points expire after 1 year
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Default loyalty rule created: ' . $defaultRule->name);

        // Create a sample campaign (optional - can be commented out)
        $campaignExists = LoyaltyCampaign::where('name', 'Welcome Bonus Campaign')->exists();
        
        if (!$campaignExists) {
            $welcomeCampaign = LoyaltyCampaign::create([
                'branch_setting_id' => 1,
                'name' => 'Welcome Bonus Campaign',
                'multiplier' => 2.00,  // Double points
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(1),
                'applies_to' => 'subscription',
                'is_active' => true,
            ]);

            $this->command->info('✓ Sample campaign created: ' . $welcomeCampaign->name);
        } else {
            $this->command->info('• Sample campaign already exists');
        }

        $this->command->info('✓ Loyalty System seeding completed!');
    }
}

