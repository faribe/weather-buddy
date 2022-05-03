<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    /**
     * Fetch Data from third party API Daily Weather
     */
    public function getDailyWeather($location)
    {
        $lat = $location->latitude;
        $lon = $location->longitutde;
        $appid = $this->getAppID();

        return Http::get("https://api.openweathermap.org/data/2.5/onecall?lat=${lat}&lon=${lon}&exclude=minutely,hourly&appid=${appid}");   
    }

    /**
     * Fetch Data from third party API Daily Weather On Demand
     */
    public function getOnDemandWeather($location, $date)
    {
        $dt = Carbon::create($date)->getTimestamp();
        $lat = $location->latitude;
        $lon = $location->longitutde;
        $appid = $this->getAppID();

        return Http::get("https://api.openweathermap.org/data/2.5/onecall/timemachine?lat=${lat}&lon=${lon}&dt=${dt}&appid=${appid}");              
    }

    /**
     * Fetch Data from third party API Location GeoCode
     */
    public function getLocationInformation($name)
    {
        $appid = $this->getAppID();
        return Http::get("http://api.openweathermap.org/geo/1.0/direct?q=${name}&limit=1&appid=${appid}");
    }

    /**
     * Config APPID
     */
    private function getAppID()
    {
        return config('services.weather.appid');
    }
}