
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Zone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('zones');
        Schema::create('zones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('business_areas');
        Schema::create('business_areas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120)->unique();
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('states');
        Schema::create('states', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('business_areas_id');
            $table->unsignedBigInteger('zones_id');
            $table->foreign('business_areas_id')->references('id')->on('business_areas')->onDelete('cascade');
            $table->foreign('zones_id')->references('id')->on('zones')->onDelete('cascade');
            $table->string('name', 120);
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('cities');
        Schema::create('cities', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('name', 120);
            $table->unsignedBigInteger('states_id')->default(3);
            $table->foreign('states_id')->references('id')->on('states')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('nodes');
        Schema::create('nodes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('enodeb_code', 120)->unique();
            $table->string('enodeb_name', 120);
            $table->unsignedBigInteger('cities_id')->default(3);
            $table->foreign('cities_id')->references('id')->on('cities')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sectors');
        Schema::create('sectors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('sector_id', 120)->unique();
            $table->string('radio', 80);
            $table->string('ne_type', 80);
            $table->unsignedBigInteger('nodes_id')->default(3);
            $table->foreign('nodes_id')->references('id')->on('nodes')->onDelete('cascade');
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
        Schema::dropIfExists('zones');
        Schema::dropIfExists('sectors');
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('business_areas');
    }
}
