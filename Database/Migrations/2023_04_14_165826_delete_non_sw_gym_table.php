
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteNonSwGymTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('push_tokens');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_user');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('sw_gym_push_tokens');
    }
}
