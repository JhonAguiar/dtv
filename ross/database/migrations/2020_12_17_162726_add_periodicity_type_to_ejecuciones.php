<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodicityTypeToEjecuciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ejecuciones', function (Blueprint $table) {
            $table->integer('periodicity_type')->nullable();
            $table->string('name_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ejecuciones', function (Blueprint $table) {
            $table->dropColumn('periodicity_type');
            $table->dropColumn('name_file');
        });
    }
}
