<?php

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('throws error if no json body is sent to fetch data for all location', function () {
    $response = $this->getJson("/api/v1/weather/all");
    $response->assertStatus(422);
});

it('can fetch weather for a all location on demand', function () {
    $location = Location::factory()->create();
    $date = Carbon::now()->format('Y-m-d');
    $response = $this->json("GET","/api/v1/weather/all",["date" => $date]);
    $response->assertStatus(200);
});

it('throws error if no json body is sent to fetch for single location', function () {
    $response = $this->getJson("/api/v1/weather/city");
    $response->assertStatus(422);
});

it('can fetch weather for a single location on demand', function () {
    $location = Location::factory()->create();
    $date = Carbon::now()->format('Y-m-d');
    $response = $this->json("GET","/api/v1/weather/city",["location_id" => $location->id, "date" => $date]);
    $response->assertStatus(200);
});
