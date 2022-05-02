<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OnBoarding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onboarding:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // Seeding with the given cities
        $cities = [
            'New York',
            'London',
            'Paris',
            'Berlin',
            'Tokyo'
        ];

        foreach($cities as $city){
            $this->call('location:geocode', ['name' => $city]);
        }
    }
}
