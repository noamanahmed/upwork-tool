<?php

namespace App\Http\Requests;

use App\Models\UserSetting;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateSettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $rules = [
            'old_password' => [
                Rule::requiredIf(function() use(&$request) { $request->has('password'); }),
                'string',
                Rule::notIn([$request->input('password')]),
            ],
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                Rule::notIn([$request->input('old_password')]), // Ensure new password is different from old password
            ]
        ];

        $rules['timezone'] = 'string|required';
        $rules['language'] = 'string|required';
        return $rules;
    }

    public function messages()
    {
        return [
            'old_password.required_if' => 'The old password is required when updating the password.',
            'old_password.not_in' => 'The new password must be different from the old password.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
        ];
    }
}
