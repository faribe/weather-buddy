<?php

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);


it('can fetch weather for all locations', function () {
    $location = Location::factory()->create();
    $response = $this->getJson('/api/v1/weather');
    $response->assertStatus(200);
});