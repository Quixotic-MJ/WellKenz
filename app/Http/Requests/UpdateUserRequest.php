<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $profileId = $this->route('user')->profile?->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'role' => 'required|in:admin,supervisor,purchasing,inventory,employee',
            'phone' => 'nullable|string|max:20',
            'employee_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('user_profiles')->ignore($profileId)
            ],
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The full name is required.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'role.required' => 'Please select a role for the user.',
            'role.in' => 'The selected role is invalid.',
            'employee_id.unique' => 'This employee ID is already in use.',
        ];
    }
}
