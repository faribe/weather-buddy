<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyWeather extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'temperature'    => 'array',
        'feels_like'     => 'array',
        'weather'        => 'array',
        'rain'           => 'array',
    ];
}
