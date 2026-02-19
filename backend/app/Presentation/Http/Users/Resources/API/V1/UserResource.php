<?php

namespace App\Presentation\Http\Users\Resources\API\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        // $this is UserDTO
        return [
            'id'         => $this->id,
            'uuid'       => $this->uuid,
            'email'      => $this->email,
            'created_at' => $this->createdAt->format('M j, Y'),
            'updated_at' => $this->updatedAt->format('M j, Y'),
        ];
    }
}

