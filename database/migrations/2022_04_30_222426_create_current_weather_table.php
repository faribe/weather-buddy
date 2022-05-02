<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrentWeatherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('current_weather', function (Blueprint $table) {
            $table->id();
            $table->integer('locations_id');
            $table->integer('dt');
            $table->integer('sunrise');
            $table->integer('sunset');
            $table->string('temperature');
            $table->string('feels_like');
            $table->string('pressure');
            $table->string('humidity');
            $table->string('dew_point');
            $table->string('uvi');
            $table->string('clouds');
            $table->string('visibility');
            $table->string('wind_speed');
            $table->string('wind_deg');
            $table->string('wind_gust')->nullable();
            $table->longText('weather');
            $table->longText('rain')->nullable();
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
        Schema::dropIfExists('current_weather');
    }
}
