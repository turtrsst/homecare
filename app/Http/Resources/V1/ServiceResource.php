<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'icon' => $this->icon,
            'thumbnail' => $this->thumbnailUrl(),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'price' => (float) $this->price,
            'price_formatted' => $this->formattedPrice(),
            'jasa_sarana' => (float) $this->jasa_sarana,
            'jasa_pelayanan' => (float) $this->jasa_pelayanan,
            'price_note' => $this->price_note,
            'is_featured' => $this->is_featured,
        ];
    }
}
