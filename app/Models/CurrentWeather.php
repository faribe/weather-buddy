<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentWeather extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dt'        => 'datetime:Y-m-d, hh:i:s',
        'sunrise'   => 'datetime:Y-m-d, H:i:s',
        'sunset'    => 'datetime:Y-m-d, H:i:s',
        'weather'   => 'array',
    ];

    public function weeklyWeather()
    {
        return $this->hasMany(WeeklyWeather::class);
    }
}
