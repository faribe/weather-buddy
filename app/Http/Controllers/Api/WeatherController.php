<?php

namespace App\Http\Controllers\Api;

use App\Events\ReportAlerts;
use App\Http\Controllers\Controller;
use App\Http\Requests\WeatherRequest;
use App\Models\Alert;
use App\Models\CurrentWeather;
use App\Models\Location;
use App\Models\WeeklyWeather;
use App\Services\LocalWeather;
use App\Services\WeatherService;
use Carbon\Carbon;
use DateTime;

class WeatherController extends Controller
{
    private $localWeather;

    public function __construct()
    {
        $this->localWeather = new LocalWeather;
    }

    public function index()
    {
        return $this->localWeather->handle();
    }

    public function weatherByDate(WeatherRequest $weatherRequest)
    {
        return $this->localWeather->handleWithRequest($weatherRequest);
    }

    public function weatherByCity(WeatherRequest $weatherRequest)
    {
        return $this->localWeather->handleWithRequest($weatherRequest);
    }

    // /**
    //  * Fetch and Store Weather Data Using Location.
    //  */
    // public function fetchAndStoreWeatherData(Location $location)
    // {
    //     $weatherService = new WeatherService();
    //     $weatherData = $weatherService->getDailyWeather($location);

    //     $currentWeather = $this->storeWeatherData($location, $weatherData);
        
    //     if($currentWeather){
    //         return true;
    //     } else {
    //         return false;
    //     }

    // }

    /**
     * Trigger Event When there is an alert in a location.
     */
    // private function updateLocationAlerts(Location $location, $alerts)
    // {
    //     foreach($alerts as $alert){
    //         $alert['location_id'] = $location->id;
    //         $alert['tags'] = json_encode($alert['tags']);
            
    //         ReportAlerts::dispatch($alert);
    //     }

    //     return true;
    // }

    /**
     * Fetch and Store Weather Data on Demand.
     */
    public function fetchAndStoreWeatherDataOnDemand($location=null,$date)
    {
        if(is_null($location)){
            $locations = Location::all();
            foreach($locations as $location)
                $response[$location->name] = $this->callOnDemandService($location, $date);

        } else {
            $response = $this->callOnDemandService($location, $date);
        }
        
        return $response;
    }

    /**
     * Invoke WeatherService API third party.
     */
    private function callOnDemandService($location, $date)
    {
        $weatherService = new WeatherService();
        $weatherData = $weatherService->getOnDemandWeather($location, $date);
        
        if($weatherData->status() === 200){
            $currentWeather = $this->storeWeatherData($location, $weatherData->json());

            if($currentWeather){
                return response()->json([
                    "status" => "success",
                    "message" => "stored"
                ],200);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => "No Data Found!",
                ],422);
            }
            
        } else {

            return response()->json([
                "status" => "error",
                "message" => $weatherData->json()['message'],
            ],404);

        }

    }

    /**
     * Store Data to DB.
     */
    // private function storeWeatherData(Location $location, $weatherData)
    // {
    //     $currentWeather = false;

    //     if(isset($weatherData['current'])) {
    //         $currentWeatherData = $weatherData['current'];
    //         $currentWeatherData['locations_id'] = $location->id;
    //         $currentWeatherData['temperature'] = $currentWeatherData['temp'];
    //         $currentWeatherData['weather'] = json_encode($weatherData['current']['weather'][0]);
    //         $currentWeatherData['rain'] = isset($currentWeatherData['temp']) ? json_encode($currentWeatherData['temp']) : null;
    //         unset($currentWeatherData['temp']);

    //         if(isset($currentWeatherData['alerts'])){
    //             $this->updateLocationAlerts($location, $currentWeatherData['alerts']);
    //         }

    //         $currentWeather = CurrentWeather::where('locations_id', $location->id)->where('dt',$currentWeatherData['dt'])->get();

    //         if($currentWeather->count() == 0)
    //             $currentWeather = CurrentWeather::create($currentWeatherData);

    //         if(isset($weatherData['daily'])){
    //             $weeklyWeatherData = $weatherData['daily'];
    
    //             foreach($weeklyWeatherData as $datum){
    //                 $datum['current_weather_id'] = $currentWeather->id;
    //                 $datum['temperature'] = json_encode($datum['temp']);
    //                 $datum['feels_like'] = json_encode($datum['feels_like']);
    //                 $datum['weather'] = json_encode($datum['weather'][0]);
    //                 unset($datum['temp']);
    
    //                 WeeklyWeather::create($datum);
    //             }
    //         }
    //     }
        
    //     return $currentWeather;
        
    // }

    

    public function updateWeatherToLatest($locations)
    {
        $done = false;
        foreach($locations as $location){
            $done = $this->fetchAndStoreWeatherData($location);
        }
        return $done;
    }

    /**
     * Fetch Data from DB according to Location
     */
    public function locationWeather($id)
    {
        $currentWeather = null;
        $location = Location::find($id);
        $skip = false;

        if($location){
            $latestWeather = CurrentWeather::where('locations_id', $location->id)->orderBy('id', 'desc')->whereDate('created_at', Carbon::today())->first();

            if(!$latestWeather){
                $updated = $this->fetchAndStoreWeatherData($location);

                if($updated)
                    return $this->locationWeather($location->id);
                else
                    $skip = true;

            }

            if(!$skip && $latestWeather){
                $weeklyWeather = WeeklyWeather::where('current_weather_id', $latestWeather->id)->get();

                $currentWeather[strtolower($location->name)]['current'] = $latestWeather;
                $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather;
            }

            if(!is_null($currentWeather)){
                return response()->json([
                    "status" => "success",
                    "data" => $currentWeather
                ],200);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => "No Data Found!",
                ],422);
            }

        } else {
            return response()->json([
                "status" => "error",
                "message" => "No City Found!"
            ],404);
        }
    }

    /**
     * Fetch Data from DB according to Location and Date
     */
    public function locationWeatherByDate(WeatherRequest $weatherRequest)
    {
        $currentWeather = null;
        $location = Location::find($id);
        $dt = DateTime::createFromFormat("Y-m-d", $date);

        if($location){
            if($dt !== false && !array_sum($dt::getLastErrors())){

                $start = Carbon::create($date.' 00:00:00')->getTimestamp();
                $end = Carbon::create($date.' 23:59:59')->getTimestamp();

                $latestWeather = CurrentWeather::where('locations_id', $location->id)
                    ->where('dt','>=', $start)
                    ->where('dt','<=', $end)
                    ->orderBy('id', 'desc')
                    ->first();

                
                if($latestWeather){
                    $weeklyWeather = WeeklyWeather::where('current_weather_id', $latestWeather->id)->get();

                    $currentWeather[strtolower($location->name)]['current'] = $latestWeather;
                    $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather ?? $weeklyWeather;

                    if(!is_null($currentWeather)){
                        return response()->json([
                            "status" => "success",
                            "data" => $currentWeather
                        ],200);
                    } else {
                        return response()->json([
                            "status" => "error",
                            "message" => "No Data Found!",
                        ],422);
                    }   
                } else {

                    $response = $this->fetchAndStoreWeatherDataOnDemand($location,$date);

                    if($response->status() === 200){
                        return $this->locationWeatherByDate($id,$date);
                    }

                    return $response;

                }
                

            } else {
                return response()->json([
                    "status" => "error",
                    "message" => "invalide date format, YYYY-MM-DD is required!",
                ],422);
            }

        } else {
            return response()->json([
                "status" => "error",
                "message" => "No City Found!"
            ],404);
        }
    }

    /**
     * Fetch Data from DB according to All sLocation and Date
     */
    public function locationWeatherByDateAll(WeatherRequest $weatherRequest)
    {

        dd($weatherRequest->all());
        // $currentWeather = null;
        // $locations = Location::all();
        // $dt = DateTime::createFromFormat("Y-m-d", $date);

        // if($dt === false){
        //     return response()->json([
        //         "status" => "error",
        //         "message" => "invalide date format, YYYY-MM-DD is required!",
        //     ],422);
        // }

        // foreach($locations as $location){

        //     $start = Carbon::create($date.' 00:00:00')->getTimestamp();
        //     $end = Carbon::create($date.' 23:59:59')->getTimestamp();

        //     $latestWeather = CurrentWeather::where('locations_id', $location->id)
        //         ->where('dt','>=', $start)
        //         ->where('dt','<=', $end)
        //         ->orderBy('id', 'desc')
        //         ->first();

            
        //     if($latestWeather){
        //         $weeklyWeather = WeeklyWeather::where('current_weather_id', $latestWeather->id)->get();

        //         $currentWeather[strtolower($location->name)]['current'] = $latestWeather ?? $latestWeather;
        //         $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather ?? $weeklyWeather;
  
        //     } else {

        //         $response = $this->fetchAndStoreWeatherDataOnDemand($location,$date);

        //         if($response->status() === 200){
        //             $latestWeather2 = CurrentWeather::where('locations_id', $location->id)
        //             ->where('dt','>=', $start)
        //             ->where('dt','<=', $end)
        //             ->orderBy('id', 'desc')
        //             ->first();

        //             if($latestWeather2){
        //                 $weeklyWeather2 = WeeklyWeather::where('current_weather_id', $latestWeather2->id)->get();
        
        //                 $currentWeather[strtolower($location->name)]['current'] = $latestWeather2 ?? $latestWeather2;
        //                 $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather2 ?? $weeklyWeather2;
          
        //             }
        //         } else {
        //             return $response;
        //         }

        //     }
                
        // }

        // if(!is_null($currentWeather)){
        //     return response()->json([
        //         "status" => "success",
        //         "data" => $currentWeather
        //     ],200);
        // } else {
        //     return response()->json([
        //         "status" => "error",
        //         "message" => "No Data Found!",
        //     ],422);
        // } 

        
    }

}
