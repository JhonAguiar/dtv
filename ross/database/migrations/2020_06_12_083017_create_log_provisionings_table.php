<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogProvisioningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_provisionings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('searchdate');
            $table->float('secondssearch');
            $table->string('searchmethod');
            $table->string('searchuser');
            $table->string('searchimsi');
            $table->string('technology');
            $table->string('profile');
            $table->string('searchcountry');
            $table->text('searchresponse');
            $table->string('searchtype');
            $table->binary('searchfile');
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
        Schema::dropIfExists('log_provisionings');
    }
}
