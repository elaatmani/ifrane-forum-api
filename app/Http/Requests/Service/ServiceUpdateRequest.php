<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'categories' => ['nullable', 'string', 'json'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'is_image_updated' => ['nullable', 'string', 'in:true,false'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Service name cannot exceed 255 characters.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'image.max' => 'Image file size cannot exceed 2MB.',
        ];
    }
}
