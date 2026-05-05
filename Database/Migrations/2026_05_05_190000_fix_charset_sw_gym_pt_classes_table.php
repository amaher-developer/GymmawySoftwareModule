<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCharsetSwGymPtClassesTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE `sw_gym_pt_classes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down()
    {
        //
    }
}
