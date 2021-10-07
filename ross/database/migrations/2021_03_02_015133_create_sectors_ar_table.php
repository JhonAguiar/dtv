<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectorsArTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zones_ar');
        Schema::create('zones_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('business_areas_ar');
        Schema::create('business_areas_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('states_ar');
        Schema::create('states_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_areas_id');
            $table->unsignedBigInteger('zones_id');
            $table->foreign('business_areas_id')->references('id')->on('business_areas_ar')->onDelete('cascade');
            $table->foreign('zones_id')->references('id')->on('zones_ar')->onDelete('cascade');
            $table->string('name', 120);
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cities_ar');
        Schema::create('cities_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120);
            $table->unsignedBigInteger('states_id')->default(3);
            $table->foreign('states_id')->references('id')->on('states_ar')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('nodes_ar');
        Schema::create('nodes_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('enodeb_code', 120)->unique();
            $table->string('enodeb_name', 120);
            $table->unsignedBigInteger('cities_id')->default(3);
            $table->foreign('cities_id')->references('id')->on('cities_ar')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sectors_ar');
        Schema::create('sectors_ar', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('sector_id', 120)->unique();
            $table->string('radio', 80);
            $table->string('ne_type', 80);
            $table->unsignedBigInteger('nodes_id')->default(3);
            $table->foreign('nodes_id')->references('id')->on('nodes_ar')->onDelete('cascade');
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
        Schema::dropIfExists('zones_ar');
        Schema::dropIfExists('sectors_ar');
        Schema::dropIfExists('nodes_ar');
        Schema::dropIfExists('cities_ar');
        Schema::dropIfExists('states_ar');
        Schema::dropIfExists('business_areas_ar');
    }
}
