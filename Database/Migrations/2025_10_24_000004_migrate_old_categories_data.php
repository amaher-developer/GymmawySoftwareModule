<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateOldCategoriesData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Migrate store categories
        $storeCategories = DB::table('sw_gym_categories')
            ->where('is_store', 1)
            ->get();

        foreach ($storeCategories as $category) {
            $newId = DB::table('sw_gym_store_categories')->insertGetId([
                'branch_setting_id' => $category->branch_setting_id,
                'user_id' => $category->user_id,
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at,
            ]);

            // Update products with new category_id
            DB::table('sw_gym_store_products')
                ->where('store_category_id', $category->id)
                ->update(['store_category_id' => $newId]);
        }

        // Migrate subscription categories
        $subscriptionCategories = DB::table('sw_gym_categories')
            ->where(function($query) {
                $query->where('is_subscription', 1)
                      ->orWhere('is_pt_subscription', 1)
                      ->orWhere('is_activity', 1)
                      ->orWhere('is_training', 1);
            })
            ->get();

        foreach ($subscriptionCategories as $category) {
            $newId = DB::table('sw_gym_subscription_categories')->insertGetId([
                'branch_setting_id' => $category->branch_setting_id,
                'user_id' => $category->user_id,
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
                'deleted_at' => $category->deleted_at,
            ]);

            // Update subscriptions with new category_id
            if ($category->is_subscription) {
                DB::table('sw_gym_subscriptions')
                    ->where('subscription_category_id', $category->id)
                    ->update(['subscription_category_id' => $newId]);
            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration is not easily reversible
        // Data would need to be migrated back to sw_gym_categories
    }
}

