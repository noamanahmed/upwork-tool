<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Repositories\RoleRepository;
use App\Services\RoleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'first_name' => 'nullable|min:1|max:32',
            'last_name' => 'nullable|min:1|max:32',
            'phone' => 'nullable|min:1|max:254',
            'address' => 'nullable|min:1|max:254',
            'city' => 'nullable|min:1|max:254',
            'state' => 'nullable|min:1|max:254',
            'country' => 'nullable|min:1|max:254',
            'email' => 'string|required|email|unique:users',
            'status' => ['required',new Enum(UserStatusEnum::class)],
            'type' => ['required','required_if:type,'.UserTypeEnum::EMPLOYEE->value,new Enum(UserTypeEnum::class)],
            'role' => ['required','in:'. implode(',',app(RoleRepository::class)->pluckIds()->toArray())],
            'password' => [
                'string',
                'required',
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
            ]
        ];

    }

    public function messages()
    {
        return [
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.',
        ];
    }
}
