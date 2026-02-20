<?php

namespace App\Presentation\Http\Files\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class CompleteChunkedUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload_id' => 'required|string|exists:file_chunks,upload_id',
        ];
    }

    public function messages(): array
    {
        return [
            'upload_id.required' => 'Upload ID is required.',
            'upload_id.exists' => 'Invalid upload ID or upload session not found.',
        ];
    }
}
