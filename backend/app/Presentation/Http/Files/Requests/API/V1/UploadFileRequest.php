<?php

namespace App\Presentation\Http\Files\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:102400', // 100MB max
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File is required.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'File size must not exceed 100MB.',
        ];
    }
}
