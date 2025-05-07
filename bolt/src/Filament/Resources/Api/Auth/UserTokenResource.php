<?php

namespace LaraExperts\Bolt\Filament\Resources\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTokenResource extends JsonResource
{
    private $additionalData;

    public function __construct($resource, $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role,
            'company_id' => $this->company_id,
        ], $this->additionalData);
    }
}
