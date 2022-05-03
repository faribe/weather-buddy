<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentWeather extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weather'           => 'array',
    ];
}
