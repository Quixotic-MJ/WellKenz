<?php

namespace App\Http\Requests\Supervisor\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->role === 'supervisor';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:500',
            'override_stock' => 'boolean',
            'override_stock_reason' => 'required_if:override_stock,true|string|max:200',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'override_stock_reason.required_if' => 'A reason is required when overriding stock constraints.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'override_stock_reason.max' => 'Override reason cannot exceed 200 characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'override_stock' => 'Stock Override',
            'override_stock_reason' => 'Override Reason',
            'notes' => 'Approval Notes',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->recordErrorMessages($validator);
        parent::failedValidation($validator);
    }

    /**
     * Record the error messages to the session.
     */
    private function recordErrorMessages($validator)
    {
        $errors = $validator->errors();
        foreach ($errors->all() as $error) {
            toasts()->error($error);
        }
    }
}