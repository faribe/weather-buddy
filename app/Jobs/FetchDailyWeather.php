<?php

namespace App\Jobs;

use App\Http\Controllers\Api\WeatherController;
use App\Models\Location;
use App\Services\LocalWeather;
use App\Services\LocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchDailyWeather implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $locationService;
    private $localWeatherService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->locationService = new LocationService;
        $this->localWeatherService = new LocalWeather;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $locations = $this->locationService->getAllLocations();

        foreach($locations as $location){
            $data = $this->localWeatherService->getLatestWeatherForLocationFromApi($location);
            if($data)
                Log::info('The weather successful! - '.$location->name);
            else
                Log::error('Something went wrong! - '.$location->name);
        }
    }
}
