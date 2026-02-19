<?php

namespace App\Presentation\Http\Users\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class ForgotUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be valid.',
        ];
    }
}
