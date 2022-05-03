<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\LocationController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LocationGeoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'location:geocode {name}{name2=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and Store Location Geo Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $name2 = $this->argument('name2');
        

        if(!isset($name) || is_null($name)){
            Log::error("Error : name is required");
        }

        if($name2 == 'null'){
            $name2 = NULL;
        }

        if(!is_null($name2)){
            $name = $name.' '.$name2;
        }

        $location = app(LocationController::class)->fetchAndStoreLocationData($name);

        if($location)
            $this->info('Location data for '.$name.' was successfully stored!');
        else
            $this->error('Something went wrong! - Location:'.$name);
    }
}
