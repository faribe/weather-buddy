<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyWeatherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'datetime' => $this->dt,
            'sunrise'=> $this->sunrise,
            'sunset'=> $this->sunset,
            'moonrise'=> $this->moonrise,
            'moonset'=> $this->moonset,
            'moon_phase'=> $this->moon_phase,
            'temperature'=> json_decode($this->temperature),
            'feels_like'=> json_decode($this->feels_like),
            'pressure'=> $this->pressure,
            'humidity'=> $this->humidity,
            'dew_point'=> $this->dew_point,
            'dew_point_celsius'=> number_format((float)$this->dew_point - 273.15, 2, '.', ''),
            'dew_point_fahrenheit'=> number_format((float)(($this->dew_point - 273.15) * (9/5) + 32),2, '.', ''),
            'uvi'=> $this->uvi,
            'clouds'=> $this->clouds,
            'pop'=> $this->pop,
            'visibility'=> $this->visibility,
            'wind_speed_mps'=> number_format((float)$this->wind_speed, 2, '.', ''),
            'wind_speed_mph'=> number_format((float)$this->wind_speed * 2.237, 2, '.', ''),
            'wind_deg'=> $this->wind_deg,
            'wind_gust_mps'=> number_format((float)$this->wind_gust, 2, '.', ''),
            'wind_gust_mph'=> number_format((float)$this->wind_gust * 2.237, 2, '.', ''),
            'weather'=> json_decode($this->weather),
            'rain'=> $this->rain
        ];
    }
}
