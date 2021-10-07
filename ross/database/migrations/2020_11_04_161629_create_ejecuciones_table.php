<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEjecucionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ejecuciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('executation_date')->nullable();
            $table->integer('executation_type');
            $table->string('file_ubication');
            $table->string('periodicity');
            $table->string('country');
            $table->string('username');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ejecuciones');
    }
}
