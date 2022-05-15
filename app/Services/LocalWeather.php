<?php

namespace App\Services;

use App\Definitions\HttpCode;
use App\Events\ReportAlerts;
use App\Models\CurrentWeather;
use App\Models\Location;
use App\Models\WeeklyWeather;
use Carbon\Carbon;

class LocalWeather
{

    private $locationService;
    private $weatherServiceApi;

    public function __construct()
    {
        $this->locationService = new LocationService;
        $this->weatherServiceApi = new WeatherServiceApi;
    }

    public function handle()
    {
        $locations = $this->locationService->getAllLocations();
        $weatherData = null;
        foreach($locations as $location){
            $currentWeather = $this->fetchCurrentWeatherDataForLocation($location);
            $weatherData[strtolower($location->name)]['current'] = $currentWeather;
            $weatherData[strtolower($location->name)]['weekly'] = $currentWeather ? $currentWeather->weeklyWeather : null;
        }

        if(!is_null($weatherData)){
            return response()->json([
                "status" => "success",
                "data" => $weatherData
            ],HttpCode::OK);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "No Data Found!",
            ],HttpCode::UNPROCESSABLE_ENTITY);
        }

    }

    public function fetchCurrentWeatherDataForLocation(Location $location)
    {
        return $this->getLatestWeatherForLocation($location) ? $this->getLatestWeatherForLocation($location) : $this->getLatestWeatherForLocationFromApi($location);
    }

    public function getLatestWeatherForLocation(Location $location)
    {
        return CurrentWeather::where('locations_id', $location->id)->orderBy('dt', 'desc')->first();
    }

    public function getWeeklyWeatherForLocation(CurrentWeather $currentWeather)
    {
        return WeeklyWeather::where('current_weather_id', $currentWeather->id)->get();
    }

    public function getLatestWeatherForLocationFromApi(Location $location)
    {
        $weatherData = $this->getDailyWeatherFromWeatherService($location);
        return $this->storeWeatherData($location, $weatherData);
    }

    public function getDailyWeatherFromWeatherService(Location $location)
    {
        return $this->weatherServiceApi->getDailyWeather($location);
    }

    private function storeWeatherData(Location $location, $weatherData)
    {
        $currentWeather = false;

        if(isset($weatherData['current'])) {
            $currentWeatherData = $weatherData['current'];
            $currentWeatherData['locations_id'] = $location->id;
            $currentWeatherData['temperature'] = $currentWeatherData['temp'];
            $currentWeatherData['weather'] = json_encode($weatherData['current']['weather'][0]);
            $currentWeatherData['rain'] = isset($currentWeatherData['temp']) ? json_encode($currentWeatherData['temp']) : null;
            unset($currentWeatherData['temp']);

            if(isset($currentWeatherData['alerts'])){
                $this->updateLocationAlerts($location, $currentWeatherData['alerts']);
            }

            $currentWeather = CurrentWeather::where('locations_id', $location->id)->where('dt',$currentWeatherData['dt'])->get();

            if($currentWeather->count() == 0)
                $currentWeather = CurrentWeather::create($currentWeatherData);

            if(isset($weatherData['daily'])){
                $weeklyWeatherData = $weatherData['daily'];
    
                foreach($weeklyWeatherData as $datum){
                    $datum['current_weather_id'] = $currentWeather->id;
                    $datum['temperature'] = json_encode($datum['temp']);
                    $datum['feels_like'] = json_encode($datum['feels_like']);
                    $datum['weather'] = json_encode($datum['weather'][0]);
                    unset($datum['temp']);
    
                    WeeklyWeather::create($datum);
                }
            }
        }
        
        return $currentWeather;
        
    }

    private function updateLocationAlerts(Location $location, $alerts)
    {
        foreach($alerts as $alert){
            $alert['location_id'] = $location->id;
            $alert['tags'] = json_encode($alert['tags']);
            
            ReportAlerts::dispatch($alert);
        }

        return true;
    }

}