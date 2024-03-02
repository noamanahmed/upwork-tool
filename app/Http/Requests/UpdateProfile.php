<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfile extends FormRequest
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
            'first_name' => 'string|required|min:1|max:254',
            'last_name' => 'string|required|min:1|max:254',
            'phone' => 'nullable|min:1|max:254',
            'address' => 'nullable|min:1|max:254',
            'city' => 'nullable|min:1|max:254',
            'state' => 'nullable|min:1|max:254',
            'country' => 'nullable|min:1|max:254',
            'phone' => 'nullable|min:1|max:254',

        ];
    }
}
