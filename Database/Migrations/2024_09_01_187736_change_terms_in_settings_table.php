<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTermsInSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->mediumText('about_ar')->change();
            $table->mediumText('about_en')->change();
            $table->mediumText('terms_ar')->change();
            $table->mediumText('terms_en')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('about_ar')->change();
            $table->text('about_en')->change();
            $table->text('terms_ar')->change();
            $table->text('terms_en')->change();
        });
    }
}
