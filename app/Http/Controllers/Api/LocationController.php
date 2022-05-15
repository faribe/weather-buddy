<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocationRequest;
use App\Services\LocationService;

class LocationController extends Controller
{
    private $locationService;

    public function __construct()
    {
        $this->locationService = new LocationService;
    }

    public function index()
    {
        return $this->locationService->getAll();
    }

    public function store(LocationRequest $request)
    {
        return $this->locationService->handle($request);
    }

}
