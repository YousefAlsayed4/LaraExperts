<?php

namespace  LaraExperts\Bolt\Filament\Resources\Api;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'user_id'=> $this->user_id,
            'name'=>$this->name,
            'description'=> $this->description,
            'slug'=> $this->slug,
            'ordering'=>$this->ordering,
            'company_id'=> $this->company_id,
            'is_active'=>$this->is_active,
            'details'=>$this->details,
            'options'=> $this->options,
            'start_date'=>$this->start_date,
            'end_date'=>$this->end_date,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'deleted_at'=> $this->deleted_at,
            'extensions'=>$this->extensions,
            'slug_url'=>$this->slug_url,
            'sections'=> FormSectionsResource::collection($this->sections),

        ];
    }

}
