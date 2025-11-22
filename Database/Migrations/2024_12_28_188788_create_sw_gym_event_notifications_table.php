<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Generic\Models\Setting;

class CreateSwGymEventNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_event_notifications', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');


            $table->string('event_code', 191)->nullable();
            $table->text('title_ar');
            $table->text('title_en');
            $table->boolean('status')->default(0);
            $table->mediumText('message')->nullable();


            $table->softDeletes();
            $table->timestamps();
        });

        $setting = Setting::first();
        $events = ['sms_new_member' , 'sms_renew_member', 'sms_before_expired_member', 'sms_expired_member'];
        foreach ($events as $event){
            $messageKey = $event.'_message';
            $message = $setting && isset($setting->$messageKey) ? $setting->$messageKey : '';
            $status = $setting && isset($setting->$event) ? $setting->$event : 0;
            
            \Modules\Software\Models\GymEventNotification::create([
                'event_code' => str_replace('sms_', '', $event),
                'title_ar' => trans('sw.'.$event, [], 'ar'),
                'title_en' => trans('sw.'.$event, [], 'en'),
                'message' => $message,
                'status' => $status
            ]);
        }

        Schema::create('sw_gym_member_credits', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->unsignedInteger('member_id')->index()->nullable();
            $table->foreign('member_id')->references('id')
                ->on('sw_gym_members')
                ->onDelete('cascade');

            $table->float('amount');
            $table->smallInteger('operation')->default(0)->nullable(); // add (+), refund (-), pay (-)
            $table->text('notes')->nullable();
            $table->tinyInteger('payment_type')->default(0)->nullable();


            $table->softDeletes();
            $table->timestamps();

        });

        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
            $table->smallInteger('type')->change();
        });


        Schema::table('settings', function (Blueprint $table) {
            $table->tinyInteger('active_activity')->default(0)->nullable()->after('active_website');
            $table->tinyInteger('active_subscription')->default(0)->nullable()->after('active_website');

            $table->dropColumn('sms_new_member');
            $table->dropColumn('sms_renew_member');
            $table->dropColumn('sms_before_expire_member');
            $table->dropColumn('sms_expire_member');

            $table->dropColumn('sms_new_member_message');
            $table->dropColumn('sms_renew_member_message');
            $table->dropColumn('sms_before_expire_member_message');
            $table->dropColumn('sms_expire_member_message');

        });

        $settings = Setting::all();
        foreach ($settings as $setting){
            $setting->active_subscription = 1;
            $setting->active_activity = 1;
            $setting->save();
        }

        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->dropColumn('sms_new_member');
            $table->dropColumn('sms_renew_member');
            $table->dropColumn('sms_before_expire_member');
            $table->dropColumn('sms_expire_member');
        });




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sw_gym_event_notifications');
        Schema::dropIfExists('sw_gym_member_credits');

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('active_activity');
            $table->dropColumn('active_subscription');

            $table->text('sms_new_member_message')->nullable();
            $table->text('sms_renew_member_message')->nullable();
            $table->text('sms_before_expire_member_message')->nullable();
            $table->text('sms_expire_member_message')->nullable();

            $table->boolean('sms_expire_member')->default(false);
            $table->boolean('sms_before_expire_member')->default(false);
            $table->boolean('sms_renew_member')->default(false);
            $table->boolean('sms_new_member')->default(false);

        });

//        Schema::table('sw_gym_money_boxes', function (Blueprint $table) {
//            $table->tinyInteger('type')->change();
//        });

        Schema::table('sw_gym_members', function (Blueprint $table) {
            $table->boolean('sms_expire_member')->default(false)->after('dob');
            $table->boolean('sms_before_expire_member')->default(false)->after('dob');
            $table->boolean('sms_renew_member')->default(false)->after('dob');
            $table->boolean('sms_new_member')->default(false)->after('dob');
        });
    }
}
