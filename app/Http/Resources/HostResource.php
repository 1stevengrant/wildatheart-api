<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'family_name' => $this->family_name,
            'given_name' => $this->given_name,
            'md5email' => $this->md5email,
            'fullname' => $this->fullname,
        ];
    }
}
