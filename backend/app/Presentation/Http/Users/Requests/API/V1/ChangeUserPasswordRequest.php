<?php

namespace App\Presentation\Http\Users\Requests\API\V1; 

use Illuminate\Foundation\Http\FormRequest;

class ChangeUserPasswordRequest extends FormRequest
{
    // Optional: authorize the request
    public function authorize(): bool
    {
        return true; // set to false if you want to restrict
    }

    // Validation rules
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:current_password',
        ];
    }

    // Optional: custom error messages
    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
            'new_password.different' => 'New password must be different from current password.',
        ];
    }
}
