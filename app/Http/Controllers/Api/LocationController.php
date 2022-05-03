<?php

namespace App\Http\Controllers\Api;

use App\Events\ReportAlerts;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{

    /**
     * Fetch and Store Location Data
     */
    public function fetchAndStoreLocationDatafromRequest(Request $request)
    {
        
        $messages = [
            'required' => 'The name field is required.'
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ], $messages);
 
        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "message" => $validator->customMessages['required'],
            ],422);
        }

        $input = $request->all();

        $name = $input['name'];

        $weatherService = new WeatherService();
        $locationData = $weatherService->getLocationInformation($name);

        if($locationData->status() === 200 && !empty($locationData)){
            $data_array = $locationData->json();
            $data_array = $data_array[0];

            $data_array['local_names'] = isset($data_array['local_names']) ?? json_encode($data_array['local_names']);
            $data_array['latitude'] = $data_array['lat'];
            $data_array['longitutde'] = $data_array['lon'];

            unset($data_array['lat'],$data_array['lon']);

            $location = Location::create($data_array);

            if($location){
                return response()->json([
                    "status" => "success",
                    "message" => "Location stored",
                ],201);
            } else {
                return response()->json([
                    "status" => "error",
                    "message" => "no location data found!",
                ],422);
            }
        } else {
            return response()->json([
                "status" => "error",
                "message" => "no data found!",
            ],422);
        }
        
    }

    /**
     * Fetch and Store Location Data
     */
    public function fetchAndStoreLocationData($name)
    {
        $weatherService = new WeatherService();
        $locationData = $weatherService->getLocationInformation($name);

        if($locationData->status() === 200){
            $data_array = $locationData->json();
            $data_array = $data_array[0];

            $data_array['local_names'] = isset($data_array['local_names']) ?? json_encode($data_array['local_names']);
            $data_array['latitude'] = $data_array['lat'];
            $data_array['longitutde'] = $data_array['lon'];

            unset($data_array['lat'],$data_array['lon']);

            

            $location = Location::create($data_array);

            if($location){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        
    }

}
