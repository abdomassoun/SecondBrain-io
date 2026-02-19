<?php

namespace App\Presentation\Http\Users\Requests\API\V1; 

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
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
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    // Optional: custom error messages
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be valid.',
            'password.required' => 'Password is required.',
        ];
    }
}
