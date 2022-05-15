<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'local_names' => json_decode($this->local_names),
            'lat' => $this->latitude,
            'lon' => $this->longitutde,
            'country' => $this->country,
            'state' => $this->state,
        ];
    }
}
