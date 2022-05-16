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

}
