<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwGymUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sw_gym_user_permissions', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('branch_setting_id')->index()->default(1)->nullable();
            $table->foreign('branch_setting_id')->references('id')
                ->on('settings')
                ->onDelete('cascade');

            $table->unsignedInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')
                ->on('sw_gym_users')
                ->onDelete('cascade');

            $table->string('title_ar');
            $table->string('title_en');
            $table->text('permissions')->nullable(); // JSON array of permissions

            $table->softDeletes();
            $table->timestamps();
        });

        // Add permission_group_id to sw_gym_users table
        Schema::table('sw_gym_users', function (Blueprint $table) {
            $table->unsignedInteger('permission_group_id')->nullable()->after('permissions');
            $table->foreign('permission_group_id')->references('id')
                ->on('sw_gym_user_permissions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key from sw_gym_users
        Schema::table('sw_gym_users', function (Blueprint $table) {
            try {
                $table->dropForeign(['permission_group_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('permission_group_id');
        });

        Schema::dropIfExists('sw_gym_user_permissions');
    }
}

