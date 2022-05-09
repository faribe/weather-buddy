<?php

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('does not create a location without a name field', function () {
    $response = $this->postJson('/api/v1/location/add', []);
    $response->assertStatus(422);
});

it('can create a location', function () {
    $attributes = ['name' => 'Istanbul'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response->assertStatus(201)->assertJson(['message' => 'location stored']);
    $this->assertDatabaseHas('locations', $attributes);
});

it('does not create duplicate location', function () {
    $attributes = ['name' => 'Istanbul'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response->assertStatus(302)->assertJson(['message' => 'location already exisits']);
    $this->assertDatabaseHas('locations', $attributes);
});

