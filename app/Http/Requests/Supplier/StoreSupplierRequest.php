<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')
            ],
            'contact_person' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')
            ],
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('suppliers', 'tax_id')
            ],
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'credit_limit' => 'nullable|numeric|min:0|max:999999999.99',
            'rating' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Supplier name is required.',
            'name.string' => 'Supplier name must be a valid string.',
            'name.max' => 'Supplier name must not exceed 255 characters.',
            'name.unique' => 'A supplier with this name already exists. Please choose a different name.',

            'contact_person.string' => 'Contact person must be a valid string.',
            'contact_person.max' => 'Contact person must not exceed 255 characters.',

            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'email.unique' => 'A supplier with this email address already exists. Please choose a different email.',

            'phone.string' => 'Phone number must be a valid string.',
            'phone.max' => 'Phone number must not exceed 50 characters.',

            'mobile.string' => 'Mobile number must be a valid string.',
            'mobile.max' => 'Mobile number must not exceed 50 characters.',

            'city.string' => 'City must be a valid string.',
            'city.max' => 'City must not exceed 100 characters.',

            'province.string' => 'Province must be a valid string.',
            'province.max' => 'Province must not exceed 100 characters.',

            'postal_code.string' => 'Postal code must be a valid string.',
            'postal_code.max' => 'Postal code must not exceed 20 characters.',

            'tax_id.string' => 'Tax ID must be a valid string.',
            'tax_id.max' => 'Tax ID must not exceed 50 characters.',
            'tax_id.unique' => 'A supplier with this Tax ID already exists.',

            'payment_terms.integer' => 'Payment terms must be a whole number.',
            'payment_terms.min' => 'Payment terms cannot be negative.',
            'payment_terms.max' => 'Payment terms cannot exceed 365 days.',

            'credit_limit.numeric' => 'Credit limit must be a valid number.',
            'credit_limit.min' => 'Credit limit cannot be negative.',
            'credit_limit.max' => 'Credit limit cannot exceed 999,999,999.99.',

            'rating.integer' => 'Rating must be a whole number.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',

            'notes.string' => 'Notes must be a valid string.',
            'notes.max' => 'Notes must not exceed 1000 characters.',

            'is_active.boolean' => 'Status must be either active or inactive.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string inputs
        $this->merge([
            'name' => trim($this->name),
            'contact_person' => trim($this->contact_person),
            'email' => trim($this->email),
            'phone' => trim($this->phone),
            'mobile' => trim($this->mobile),
            'address' => trim($this->address),
            'city' => trim($this->city),
            'province' => trim($this->province),
            'postal_code' => trim($this->postal_code),
            'tax_id' => trim($this->tax_id),
            'notes' => trim($this->notes)
        ]);
    }
}