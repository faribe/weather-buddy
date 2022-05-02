<?php

namespace App\Services;

use Carbon\Carbon;

class WeatherService
{
    public function getDailyWeather($location)
    {
        $today = Carbon::now()->getTimestamp();
        $lat = $location->latitude;
        $lon = $location->longitutde;
        $appid = $this->getAppID();

        $httpClient = new \GuzzleHttp\Client();
        $request =
            $httpClient
                ->get("https://api.openweathermap.org/data/2.5/onecall?lat=${lat}&lon=${lon}&exclude=minutely,hourly&appid=${appid}");


        $response = json_decode($request->getBody()->getContents(),true);

        return $response;
    }

    public function getOnDemandWeather($location, $date)
    {
        $dt = Carbon::create($date)->getTimestamp();
        $lat = $location->latitude;
        $lon = $location->longitutde;
        $appid = $this->getAppID();

        $httpClient = new \GuzzleHttp\Client();
        $request =
            $httpClient
                ->get("https://api.openweathermap.org/data/2.5/onecall/timemachine?lat=${lat}&lon=${lon}&dt=${dt}&appid=${appid}");


        $response = json_decode($request->getBody()->getContents());

        return $response[count($response) - 1];
    }

    public function getLocationInformation($name)
    {
        $appid = $this->getAppID();

        $httpClient = new \GuzzleHttp\Client();
        $request =
            $httpClient
                ->get("http://api.openweathermap.org/geo/1.0/direct?q=${name}&limit=1&appid=${appid}");

        $response = json_decode($request->getBody()->getContents());

        return $response[count($response) - 1];
    }

    private function getAppID()
    {
        return config('services.weather.appid');
    }
}