<?php

namespace App\Http\Controllers\Api;

use App\Events\ReportAlerts;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\CurrentWeather;
use App\Models\Location;
use App\Models\WeeklyWeather;
use App\Services\WeatherService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class WeatherController extends Controller
{

    public function fetchAndStoreWeatherData(Location $location)
    {
        $weatherService = new WeatherService();
        $weatherData = $weatherService->getDailyWeather($location);

        $currentWeather = $this->storeWeatherData($location, $weatherData);
        
        if($currentWeather){
            return true;
        } else {
            return false;
        }

    }

    public function updateLocationAlerts(Location $location, $alerts)
    {
        foreach($alerts as $alert){
            $alert['location_id'] = $location->id;
            $alert['tags'] = json_encode($alert['tags']);
            
            ReportAlerts::dispatch($alert);
        }

        return true;
    }

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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::all();

        $currentWeather = null;

        foreach($locations as $location){
            $latestWeather = CurrentWeather::where('locations_id', $location->id)->orderBy('dt', 'desc')->first();
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

    }

    public function locationWeather($id)
    {
        $currentWeather = null;
        $location = Location::find($id);

        if($location){
            $latestWeather = CurrentWeather::where('locations_id', $location->id)->orderBy('id', 'desc')->first();
            $weeklyWeather = WeeklyWeather::where('current_weather_id', $latestWeather->id)->get();

            $currentWeather[strtolower($location->name)]['current'] = $latestWeather;
            $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather;

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

    public function locationWeatherByDate($id,$date)
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

    public function locationWeatherByDateAll($date)
    {
        $currentWeather = null;
        $locations = Location::all();
        $dt = DateTime::createFromFormat("Y-m-d", $date);

        if($dt === false){
            return response()->json([
                "status" => "error",
                "message" => "invalide date format, YYYY-MM-DD is required!",
            ],422);
        }

        foreach($locations as $location){

            $start = Carbon::create($date.' 00:00:00')->getTimestamp();
            $end = Carbon::create($date.' 23:59:59')->getTimestamp();

            $latestWeather = CurrentWeather::where('locations_id', $location->id)
                ->where('dt','>=', $start)
                ->where('dt','<=', $end)
                ->orderBy('id', 'desc')
                ->first();

            
            if($latestWeather){
                $weeklyWeather = WeeklyWeather::where('current_weather_id', $latestWeather->id)->get();

                $currentWeather[strtolower($location->name)]['current'] = $latestWeather ?? $latestWeather;
                $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather ?? $weeklyWeather;
  
            } else {

                $response = $this->fetchAndStoreWeatherDataOnDemand($location,$date);

                if($response->status() === 200){
                    $latestWeather2 = CurrentWeather::where('locations_id', $location->id)
                    ->where('dt','>=', $start)
                    ->where('dt','<=', $end)
                    ->orderBy('id', 'desc')
                    ->first();

                    if($latestWeather2){
                        $weeklyWeather2 = WeeklyWeather::where('current_weather_id', $latestWeather2->id)->get();
        
                        $currentWeather[strtolower($location->name)]['current'] = $latestWeather2 ?? $latestWeather2;
                        $currentWeather[strtolower($location->name)]['weekly'] = $weeklyWeather2 ?? $weeklyWeather2;
          
                    }
                } else {
                    return $response;
                }

                

            }
                
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
