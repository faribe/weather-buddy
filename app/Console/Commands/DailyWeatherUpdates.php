<?php

namespace App\Console\Commands;

use App\Jobs\FetchDailyWeather;
use Illuminate\Console\Command;

class DailyWeatherUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Every 6 hours weather update';

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
        FetchDailyWeather::dispatch()->onQueue('default');
    }
}
