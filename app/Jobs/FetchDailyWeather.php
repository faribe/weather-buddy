<?php

namespace App\Jobs;

use App\Http\Controllers\Api\WeatherController;
use App\Models\Location;
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $locations = Location::all();

        foreach($locations as $location){
            $data = app(WeatherController::class)->fetchAndStoreWeatherData($location);

            if($data)
                Log::info('The weather successful! - '.$location->name);
            else
                Log::error('Something went wrong! - '.$location->name);
        }
    }
}
