<?php

namespace App\Presentation\Http\Users\Requests\API\V1; 

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'otp' => 'required|string|size:6',
            'new_password' => 'required|string|min:8',
        ];
    }

    // Optional: custom error messages
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be valid.',
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be 6 characters long.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters.',
        ];
    }
}
