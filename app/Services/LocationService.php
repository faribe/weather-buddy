<?php

namespace App\Services;

use App\Definitions\HttpCode;
use App\Http\Requests\LocationRequest;
use App\Models\Location;

class LocationService
{

    private $weatherService;
    private $location;

    public function __construct()
    {
        $this->weatherService = new WeatherService;
    }

    public function handle(LocationRequest $request)
    {
        $this->setLocation($request->name);
        return $this->checkOrStore();
    }

    public function checkOrStore()
    {
        return $this->getLocationFromDB() ? $this->getLocationFromDB() : $this->getLocationFromWeatherService();
    }

    public function createFromWeatherServiceData($locationData)
    {
        if($locationData->status() === 200 && !empty($locationData->json())){
            $data_array = $this->dataMapping($locationData->json()[0]);
            $check = $this->getLocationFromDB($data_array['name']);
            if(!$check){
                $location = Location::firstOrCreate($data_array);

                if($location){
                    return response()->json([
                        "status" => "success",
                        "message" => "location stored",
                    ],HttpCode::CREATED);
                } else {
                    return response()->json([
                        "status" => "error",
                        "message" => "error occured while storing location",
                    ],HttpCode::UNPROCESSABLE_ENTITY);
                }
            }
            else {
                return $check;
            }
                
        } else {
            return response()->json([
                "status" => "error",
                "message" => "no data returned from location service!",
            ],HttpCode::UNPROCESSABLE_ENTITY);
        }
    }

    public function getLocationFromDB($name=null)
    {
        if(is_null($name))
            $name = $this->getLocation();

        $location = Location::where('name', $name)->first();

        if($location){
            return response()->json([
                "status" => "found",
                "message" => "location already exisits",
            ],HttpCode::FOUND);
        } else {
            return false;
        }
    }

    public function getLocationFromWeatherService()
    {
        $locationData = $this->weatherService->getLocationInformation($this->getLocation());
        return $this->createFromWeatherServiceData($locationData);
    }

    public function setLocation($location)
    {
        $this->location = ucwords($location);
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function dataMapping($data)
    {
        $data['local_names'] = isset($data['local_names']) ? json_encode($data['local_names']) : null;
        $data['latitude'] = $data['lat'];
        $data['longitutde'] = $data['lon'];

        unset($data['lat'],$data['lon']);

        return $data;
    }
}