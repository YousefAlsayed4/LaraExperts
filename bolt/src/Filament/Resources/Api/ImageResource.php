<?php

namespace  LaraExperts\Bolt\Filament\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'image_id'=>$this->id,
            'url'=>$this->getFullUrl(),
            'image_name'=>$this->file_name
        ];
    }
}
