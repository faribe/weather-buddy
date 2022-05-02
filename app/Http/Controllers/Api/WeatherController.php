<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CurrentWeather;
use App\Models\Location;
use App\Models\WeeklyWeather;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{

    public function fetchAndStoreWeatherData(Location $location)
    {
        $weatherService = new WeatherService();
        $weatherData = $weatherService->getDailyWeather($location);

        $currentWeatherData = $weatherData['current'];
        $currentWeatherData['locations_id'] = $location->id;
        $currentWeatherData['temperature'] = $currentWeatherData['temp'];
        $currentWeatherData['weather'] = json_encode($weatherData['current']['weather'][0]);
        $currentWeatherData['rain'] = isset($currentWeatherData['temp']) ? json_encode($currentWeatherData['temp']) : null;
        unset($currentWeatherData['temp']);

        $currentWeather = CurrentWeather::create($currentWeatherData);

        $weeklyWeatherData = $weatherData['daily'];

        foreach($weeklyWeatherData as $datum){
            $datum['current_weather_id'] = $currentWeather->id;
            $datum['temperature'] = json_encode($datum['temp']);
            $datum['feels_like'] = json_encode($datum['feels_like']);
            $datum['weather'] = json_encode($datum['weather'][0]);
            unset($datum['temp']);

            WeeklyWeather::create($datum);
        }
        
        if($currentWeather){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
