<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "name"=> "Sydney",
            "local_names"=> [
                "eo" => "Sidnejo",
                "ko" => "시드니",
                "mi" => "Poihākena",
                "mk" => "Сиднеј",
                "en" => "Sydney"
            ],
            "latitude"=> -33.768528,
            "longitutde"=> 150.9568559523945,
            "country" => "AU",
            "state" => "New South Wales"
        ];
    }
}
