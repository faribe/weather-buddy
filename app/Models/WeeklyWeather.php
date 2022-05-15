<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyWeather extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dt'             => 'datetime:Y-m-d, H:i:s',
        'sunrise'        => 'datetime:Y-m-d, H:i:s',
        'sunset'         => 'datetime:Y-m-d, H:i:s',
        'moonrise'       => 'datetime:Y-m-d, H:i:s',
        'moonset'        => 'datetime:Y-m-d, H:i:s',
        'temperature'    => 'array',
        'feels_like'     => 'array',
        'weather'        => 'array',
        'rain'           => 'array',
    ];

    public function currentWeather()
    {
        return $this->belongsTo(CurrentWeather::class);
    }
}
