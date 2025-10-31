<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBranchSettingIdToGymAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_activity_subscription', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_banners', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_block_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_member_notification_logs', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_orders', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_subscription_trainer', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_push_notifications', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_push_tokens', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_reservations', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_sms_logs', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_store_order_product', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_training_members', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_training_plans', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_user_attendees', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_user_logs', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_user_notifications', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_wa_logs', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });
        Schema::table('sw_gym_zk_fingerprints', function (Blueprint $table) {
            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable()->after('id');
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sw_gym_activities', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_activity_subscription', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_banners', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_block_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_member_attendees', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_member_notification_logs', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_member_subscription', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_non_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_orders', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_potential_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_classes', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_member_attendees', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_subscriptions', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_subscription_trainer', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_pt_trainers', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_push_notifications', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_push_tokens', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_reservations', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_sms_logs', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_store_orders', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_store_order_product', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_store_products', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_subscriptions', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_training_members', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_training_plans', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_training_tracks', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_user_attendees', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_user_logs', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_user_notifications', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_wa_logs', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });
        Schema::table('sw_gym_zk_fingerprints', function (Blueprint $table) {
            $table->dropColumn('branch_setting_id');
        });

    }
}
