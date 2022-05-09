<?php

namespace App\Http\Controllers\Api;

use App\Events\ReportAlerts;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    private $locationService;

    public function __construct()
    {
        $this->locationService = new LocationService;
    }

    public function store(LocationRequest $request)
    {
        return $this->locationService->handle($request);
    }

}
