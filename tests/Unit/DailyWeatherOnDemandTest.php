<?php

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);


it('can fetch weather for a single location on demand', function () {
    $location = Location::factory()->create();
    $date = Carbon::now()->format('Y-m-d');
    $response = $this->getJson("/api/v1/weather/city/{$location->id}/{$date}");
    $response->assertStatus(200);
});
