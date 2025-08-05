<?php

namespace App\Http\Requests\Service\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Required fields
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
            
            // File upload
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            
            // Array field for categories
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required.',
            'name.max' => 'Service name cannot exceed 255 characters.',
            'company_id.required' => 'Company ID is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
            'image.file' => 'Image must be a file.',
            'image.image' => 'Image must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'image.max' => 'Image file size cannot exceed 2MB.',
            'category_ids.array' => 'Category IDs must be an array.',
            'category_ids.*.integer' => 'Each category ID must be an integer.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
} 