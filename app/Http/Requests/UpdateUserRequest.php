<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
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
            'first_name' => 'nullable|min:1|max:254',
            'last_name' => 'nullable|min:1|max:254',
            'phone' => 'nullable|min:1|max:254',
            'address' => 'nullable|min:1|max:254',
            'city' => 'nullable|min:1|max:254',
            'state' => 'nullable|min:1|max:254',
            'country' => 'nullable|min:1|max:254',
            'email' => ['string','required','email',Rule::unique('users', 'email')->ignore($this->route('user')->id)],
            'status' => ['required',new Enum(UserStatusEnum::class)],
            'type' => ['required',new Enum(UserTypeEnum::class)],
            'password' => [
                'string',
                'confirmed',
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
