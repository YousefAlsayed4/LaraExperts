<?php

namespace  LaraExperts\Bolt\Filament\Resources\Api;

use  LaraExperts\Bolt\Filament\Resources\FormSectionFieldsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormSectionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'form_id'=>$this->form->id,
            'name'=>$this->name,
            'ordering'=>$this->ordering,
            'columns'=>$this->columns,
            'description'=>$this->description,
            'icon'=>$this->icon,
            'aside'=>$this->aside,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'compact'=>$this->compact,
            'options'=>$this->options,
            'fields'=>FormSectionFieldsResource::collection($this->fields),

        ];
    }
}
