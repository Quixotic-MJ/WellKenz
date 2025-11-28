<?php

namespace App\Http\Requests\Supervisor\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequisitionRequest extends FormRequest
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
            'reason' => 'required|string|in:Insufficient Stock,Invalid Request,Duplicate Request,Policy Violation,Quality Issues,Other',
            'comments' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Please select a rejection reason.',
            'reason.in' => 'Please select a valid rejection reason.',
            'comments.max' => 'Comments cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'reason' => 'Rejection Reason',
            'comments' => 'Comments',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => trim($this->reason),
            'comments' => trim($this->comments),
        ]);
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

    /**
     * Get the combined rejection reason with comments.
     */
    public function getCombinedReason(): string
    {
        $reason = $this->reason;
        $comments = $this->comments;

        if (!empty($comments)) {
            return "{$reason} - {$comments}";
        }

        return $reason;
    }
}