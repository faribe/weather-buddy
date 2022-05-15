<?php

namespace App\Services;

use App\Definitions\HttpCode;
use App\Events\ReportAlerts;
use App\Http\Requests\WeatherRequest;
use App\Http\Resources\CurrentWeatherResource;
use App\Http\Resources\WeeklyWeatherResource;
use App\Models\CurrentWeather;
use App\Models\Location;
use App\Models\WeeklyWeather;
use App\Traits\ApiResponses;
use Carbon\Carbon;

class LocalWeather
{

    use ApiResponses;

    private $locationService;
    private $weatherServiceApi;

    public function __construct()
    {
        $this->locationService = new LocationService;
        $this->weatherServiceApi = new WeatherServiceApi;
    }

    public function handle($location=null, $date=null)
    {
        return is_null($location) ? $this->getForAllLocations($date) : $this->getForSingleLocation($location, $date);
    }

    public function handleWithRequest(WeatherRequest $weatherRequest)
    {
        return $this->handle($weatherRequest->location_id, $weatherRequest->date);
    }

    private function getForSingleLocation($location, $date)
    {
        # code...
    }

    private function getForAllLocations($date=null)
    {
        $locations = $this->locationService->getAllLocations();
        $weatherData = null;
        foreach($locations as $location){
            $currentWeather = $this->fetchCurrentWeatherDataForLocation($location, $date);
            $weatherData[strtolower($location->name)]['current'] = new CurrentWeatherResource($currentWeather);
            $weatherData[strtolower($location->name)]['weekly'] = $currentWeather->weeklyWeather->isNotEmpty() ? WeeklyWeatherResource::collection($currentWeather->weeklyWeather) : null;
        }

        if(!is_null($weatherData)){
            return $this->okResponse($weatherData);
        } else {
            return $this->unprocessableResponse([],"no data returned");
        }
    }

    public function fetchCurrentWeatherDataForLocation(Location $location, $date)
    {
        return $this->getLatestWeatherForLocation($location, $date) ? $this->getLatestWeatherForLocation($location, $date) : $this->getLatestWeatherForLocationFromApi($location, $date);
    }

    public function getLatestWeatherForLocation(Location $location, $date)
    {
        return is_null($date) ? CurrentWeather::whereLocationsId($location->id)->latest('dt')->first() : CurrentWeather::whereLocationsId($location->id)->whereBetween('dt',[$this->dateFormated($date)['start'],$this->dateFormated($date)['end']])->first();
    }

    public function getWeeklyWeatherForLocation(CurrentWeather $currentWeather)
    {
        return WeeklyWeather::whereCurrentWeatherId($currentWeather->id)->get();
    }

    public function getDailyWeatherFromWeatherService(Location $location, $date)
    {
        return is_null($date) ? $this->weatherServiceApi->getDailyWeather($location) : $this->weatherServiceApi->getOnDemandWeather($location, $date);
    }

    public function getLatestWeatherForLocationFromApi(Location $location, $date)
    {
        $weatherData = $this->getDailyWeatherFromWeatherService($location, $date);
        return $this->storeWeatherData($location, $weatherData);
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

    private function dateFormated($date)
    {
        return [
            'start' => Carbon::create($date.' 00:00:00')->getTimestamp(),
            'end' => Carbon::create($date.' 23:59:59')->getTimestamp()
        ];
    }

}