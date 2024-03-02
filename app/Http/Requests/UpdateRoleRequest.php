<?php

namespace App\Http\Requests;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
{
    $this->merge([
        'role_id' => $this->route('role')->id,
        'role_priority' => $this->route('role')->priority
    ]);
}



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'name' => 'required|min:1|max:32',
            'role_id' => ['in:'.implode(',',Role::available()->pluck('id')->toArray())],
            'role_priority' => ['gte:'.auth()->user()->getPriority()],
            'permissions' => ['array','in:'.implode(',',Permission::where('guard_name','web')->pluck('name')->toArray())]
        ];
    }

    public function messages() {
        return [
            'role_id.in' => 'You are not allowed to change the permissions of this role!'
        ];
    }
}
