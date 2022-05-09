<?php

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    Route::get('/', [HomeController::class, 'welcome']);

    Route::prefix('location')->group(function (){
        Route::post('/add', [LocationController::class, 'store']);
    });

    Route::prefix('weather')->group(function (){
        Route::get('/', [WeatherController::class, 'index']);
        Route::get('/all/{date}', [WeatherController::class, 'locationWeatherByDateAll']);
        Route::get('city/{id}', [WeatherController::class, 'locationWeather']);
        Route::get('city/{id}/{date}', [WeatherController::class, 'locationWeatherByDate']);
    });

});
