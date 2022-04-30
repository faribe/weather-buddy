<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyWeatherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_weather', function (Blueprint $table) {
            $table->id();
            $table->integer('current_weather_id');
            $table->integer('dt');
            $table->integer('sunrise');
            $table->integer('sunset');
            $table->integer('moonrise');
            $table->integer('moonset');
            $table->integer('moon_phase');
            $table->longText('temperature');
            $table->longText('feels_like');
            $table->string('pressure');
            $table->string('humidity');
            $table->string('dew_point');
            $table->string('uvi');
            $table->string('clouds');
            $table->string('pop');
            $table->string('visibility');
            $table->string('wind_speed');
            $table->string('wind_deg');
            $table->string('wind_gust');
            $table->longText('weather');
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
        Schema::dropIfExists('weekly_weather');
    }
}
