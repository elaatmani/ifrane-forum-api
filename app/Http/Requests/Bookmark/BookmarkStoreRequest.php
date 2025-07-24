<?php

namespace App\Http\Requests\Bookmark;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookmarkStoreRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bookmarkable_type' => [
                'required',
                'string',
                Rule::in([
                    'product',
                    'company', 
                    'service',
                    'user',
                    'document',
                    'sponsor',
                    'category',
                    'certificate',
                    // Also allow full class names
                    'App\\Models\\Product',
                    'App\\Models\\Company',
                    'App\\Models\\Service',
                    'App\\Models\\User',
                    'App\\Models\\Document',
                    'App\\Models\\Sponsor',
                    'App\\Models\\Category',
                    'App\\Models\\Certificate',
                ])
            ],
            'bookmarkable_id' => [
                'required',
                'integer',
                'min:1'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bookmarkable_type.required' => 'The item type is required.',
            'bookmarkable_type.in' => 'The item type must be one of: product, company, service, user, document, sponsor, category, certificate.',
            'bookmarkable_id.required' => 'The item ID is required.',
            'bookmarkable_id.integer' => 'The item ID must be a valid number.',
            'bookmarkable_id.min' => 'The item ID must be a positive number.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'bookmarkable_type' => 'item type',
            'bookmarkable_id' => 'item ID',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Normalize bookmarkable_type to lowercase for consistent validation
        if ($this->has('bookmarkable_type')) {
            $this->merge([
                'bookmarkable_type' => strtolower($this->bookmarkable_type)
            ]);
        }
    }
} 