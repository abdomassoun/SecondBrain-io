<?php

namespace App\Presentation\Http\Files\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadChunkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'chunk_data' => 'required|string',
            'original_name' => 'required|string|max:255',
            'total_size' => 'required|integer|min:1',
            'mime_type' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'upload_id.required' => 'Upload ID is required.',
            'chunk_index.required' => 'Chunk index is required.',
            'chunk_index.integer' => 'Chunk index must be an integer.',
            'total_chunks.required' => 'Total chunks is required.',
            'chunk_data.required' => 'Chunk data is required.',
            'original_name.required' => 'Original filename is required.',
            'total_size.required' => 'Total file size is required.',
        ];
    }
}
