<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'store_active_quantity')) {
                $table->boolean('store_active_quantity')
                    ->default(false)
                    ->after('store_postpaid');
            }

            if (!Schema::hasColumn('settings', 'member_attendees_expire')) {
                $table->boolean('member_attendees_expire')
                    ->default(false)
                    ->after('store_active_quantity');
            }

            if (!Schema::hasColumn('settings', 'app_max_capacity_num')) {
                $table->unsignedInteger('app_max_capacity_num')
                    ->nullable()
                    ->after('member_attendees_expire');
            }
        });

        $storeActiveQuantity = env('STORE_ACTIVE_QUANTITY', false) ? 1 : 0;
        $memberAttendeesExpire = env('MEMBER_ATTENDEES_EXPIRE', false) ? 1 : 0;
        $appMaxCapacityNum = env('APP_MAX_CAPACITY_NUM');
        $storePostpaid = env('STORE_POSTPAID', false) ? 1 : 0;

        if (!is_null($appMaxCapacityNum) && $appMaxCapacityNum !== '') {
            $appMaxCapacityNum = (int) $appMaxCapacityNum;
        } else {
            $appMaxCapacityNum = null;
        }

        DB::table('settings')->update([
            'store_active_quantity' => $storeActiveQuantity,
            'member_attendees_expire' => $memberAttendeesExpire,
            'app_max_capacity_num' => $appMaxCapacityNum,
        ]);

        if (Schema::hasColumn('settings', 'store_postpaid')) {
            $settings = DB::table('settings')->select('id', 'store_postpaid')->get();
            foreach ($settings as $setting) {
                if ($setting->store_postpaid === null || $setting->store_postpaid === '' || $setting->store_postpaid === false) {
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['store_postpaid' => $storePostpaid]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'store_active_quantity')) {
                $table->dropColumn('store_active_quantity');
            }

            if (Schema::hasColumn('settings', 'member_attendees_expire')) {
                $table->dropColumn('member_attendees_expire');
            }

            if (Schema::hasColumn('settings', 'app_max_capacity_num')) {
                $table->dropColumn('app_max_capacity_num');
            }
        });
    }
};

