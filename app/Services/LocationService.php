<?php

namespace App\Services;

use App\Http\Requests\LocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Traits\ApiResponses;

class LocationService
{

    use ApiResponses;

    private $weatherServiceApi;
    private $location;

    public function __construct()
    {
        $this->weatherServiceApi = new WeatherServiceApi;
    }


    public function handle(LocationRequest $request)
    {
        $this->setLocation($request->name);
        return $this->getLocationFromDB() ? $this->getLocationFromDB() : $this->getLocationFromWeatherService();
    }

    public function getLocationFromDB($name=null)
    {
        if(is_null($name))
            $name = $this->getLocation();

        $location = $this->fetchLocationByName($name);

        if($location){
            return $this->okResponse(new LocationResource($location));
        } else {
            return false;
        }
    }

    public function getLocationFromWeatherService($name=null)
    {
        if(is_null($name))
            $name = $this->getLocation();

        $locationData = $this->weatherServiceApi->getLocationInformation($name);
        return $this->createFromWeatherServiceData($locationData);
    }

    private function createFromWeatherServiceData($locationData)
    {
        if($locationData->status() === 200 && !empty($locationData->json())){
            $data_array = $this->dataMapping($locationData->json()[0]);
            $check = $this->getLocationFromDB($data_array['name']);
            if(!$check){
                $location = $this->createLocation($data_array);

                if($location){
                    return $this->createdResponse(new LocationResource($location));
                } else {
                    return $this->notFoundResponse([],"error occured while storing location");
                }
            }
                
        } else {
            return $this->unprocessableResponse([],"no data returned from location service");
        }
    }

    private function setLocation($location)
    {
        $this->location = ucwords($location);
    }

    private function getLocation()
    {
        return $this->location;
    }

    public function getAllLocations()
    {
        return Location::all();
    }

    public function fetchLocation($id)
    {
        return Location::find($id);
    }

    public function fetchLocationByName($name)
    {
        return Location::whereName($name)->first();
    }

    private function createLocation($data)
    {
        return Location::firstOrCreate($data);
    }

    public function getAll()
    {
        $locations = $this->getAllLocations();

        if($locations->isNotEmpty()){
            return $this->okResponse(LocationResource::collection($locations));
        } else {
            return $this->notFoundResponse([],"no location found");
        }

    }

    private function dataMapping($data)
    {
        $data['local_names'] = isset($data['local_names']) ? json_encode($data['local_names']) : null;
        $data['latitude'] = $data['lat'];
        $data['longitutde'] = $data['lon'];

        unset($data['lat'],$data['lon']);

        return $data;
    }
}