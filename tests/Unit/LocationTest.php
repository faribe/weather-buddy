<?php

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('can not get all locations if DB is empty', function () {
    $response = $this->getJson('/api/v1/location');
    $response->assertStatus(404);
});

it('does not create a location without a name field', function () {
    $response = $this->postJson('/api/v1/location/add', []);
    $response->assertStatus(422);
});

it('can create a location', function () {
    $attributes = ['name' => 'Istanbul'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response->assertStatus(201);
    $this->assertDatabaseHas('locations', $attributes);
});

it('does not create duplicate location', function () {
    $attributes = ['name' => 'Istanbul'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response->assertStatus(201);
    $this->assertDatabaseHas('locations', $attributes);
});

it('can not find the location', function () {
    $attributes = ['name' => 'qw'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response->assertStatus(422);
});

it('can get all locations', function () {
    $attributes = ['name' => 'Istanbul'];
    $response = $this->postJson('/api/v1/location/add', $attributes);
    $response = $this->getJson('/api/v1/location');
    $response->assertStatus(200);
});


