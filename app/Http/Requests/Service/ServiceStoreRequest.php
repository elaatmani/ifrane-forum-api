<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
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
            // Required fields
            'name' => ['required', 'string', 'max:255'],
            
            // Optional fields
            'description' => ['nullable', 'string'],
            
            // File upload
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            
            // JSON array field for categories
            'categories' => ['nullable', 'string', 'json'],

            'status' => ['nullable', 'string', 'in:active,inactive'],
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
            'image.file' => 'Image must be a file.',
            'image.image' => 'Image must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'image.max' => 'Image file size cannot exceed 2MB.',
            'categories.json' => 'Categories must be a valid JSON array.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }


}
