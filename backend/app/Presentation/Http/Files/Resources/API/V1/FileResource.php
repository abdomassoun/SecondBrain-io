<?php

namespace App\Presentation\Http\Files\Resources\API\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        // $this is FileDTO
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_name' => $this->originalName,
            'size' => $this->size,
            'size_formatted' => $this->sizeFormatted,
            'mime_type' => $this->mimeType,
            'extension' => $this->extension,
            'owner_uuid' => $this->ownerUuid,
            'upload_date' => $this->uploadDate->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('M j, Y'),
            'updated_at' => $this->updatedAt->format('M j, Y'),
        ];
    }
}
