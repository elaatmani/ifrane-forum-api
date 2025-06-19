<?php

namespace App\Http\Requests\Company\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyStoreRequest extends FormRequest
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
            'primary_email' => ['required', 'string', 'email', 'max:50', 'unique:companies,primary_email'],
            
            // Optional fields with validation
            'secondary_email' => ['nullable', 'string', 'email', 'max:50'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'streaming_platform' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'primary_phone' => ['nullable', 'string', 'max:255'],
            'secondary_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            
            // Social media links
            'facebook' => ['nullable', 'string', 'url', 'max:255'],
            'twitter' => ['nullable', 'string', 'url', 'max:255'],
            'instagram' => ['nullable', 'string', 'url', 'max:255'],
            'linkedin' => ['nullable', 'string', 'url', 'max:255'],
            'youtube' => ['nullable', 'string', 'url', 'max:255'],
            
            // File uploads
            'logo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'background_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            
            // JSON array fields for relationships
            'user_ids' => ['nullable', 'string', 'json'],
            'category_ids' => ['nullable', 'string', 'json'],
            'certificate_ids' => ['nullable', 'string', 'json'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'name.max' => 'Company name cannot exceed 255 characters.',
            'primary_email.required' => 'Primary email is required.',
            'primary_email.email' => 'Primary email must be a valid email address.',
            'primary_email.unique' => 'This primary email is already registered.',
            'primary_email.max' => 'Primary email cannot exceed 50 characters.',
            'secondary_email.email' => 'Secondary email must be a valid email address.',
            'secondary_email.max' => 'Secondary email cannot exceed 50 characters.',
            'website.url' => 'Website must be a valid URL.',
            'streaming_platform.max' => 'Streaming platform cannot exceed 500 characters.',
            'country_id.exists' => 'Selected country does not exist.',
            'facebook.url' => 'Facebook URL must be a valid URL.',
            'twitter.url' => 'Twitter URL must be a valid URL.',
            'instagram.url' => 'Instagram URL must be a valid URL.',
            'linkedin.url' => 'LinkedIn URL must be a valid URL.',
            'youtube.url' => 'YouTube URL must be a valid URL.',
            'logo.file' => 'Logo must be a file.',
            'logo.image' => 'Logo must be an image.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
            'background_image.file' => 'Background image must be a file.',
            'background_image.image' => 'Background image must be an image.',
            'background_image.mimes' => 'Background image must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'background_image.max' => 'Background image file size cannot exceed 2MB.',
            'user_ids.json' => 'User IDs must be a valid JSON array.',
            'category_ids.json' => 'Category IDs must be a valid JSON array.',
            'certificate_ids.json' => 'Certificate IDs must be a valid JSON array.',
        ];
    }

    /**
     * Get additional validation rules after prepareForValidation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate user_ids array
            if ($this->has('user_ids') && is_array($this->user_ids)) {
                $userIds = $this->user_ids;
                if (!empty($userIds)) {
                    $existingUserIds = \App\Models\User::whereIn('id', $userIds)->pluck('id')->toArray();
                    $invalidUserIds = array_diff($userIds, $existingUserIds);
                    
                    if (!empty($invalidUserIds)) {
                        $validator->errors()->add('user_ids', 'Some user IDs do not exist: ' . implode(', ', $invalidUserIds));
                    }
                }
            }

            // Validate category_ids array
            if ($this->has('category_ids') && is_array($this->category_ids)) {
                $categoryIds = $this->category_ids;
                if (!empty($categoryIds)) {
                    $existingCategoryIds = \App\Models\Category::whereIn('id', $categoryIds)->pluck('id')->toArray();
                    $invalidCategoryIds = array_diff($categoryIds, $existingCategoryIds);
                    
                    if (!empty($invalidCategoryIds)) {
                        $validator->errors()->add('category_ids', 'Some category IDs do not exist: ' . implode(', ', $invalidCategoryIds));
                    }
                }
            }

            // Validate certificate_ids array
            if ($this->has('certificate_ids') && is_array($this->certificate_ids)) {
                $certificateIds = $this->certificate_ids;
                if (!empty($certificateIds)) {
                    $existingCertificateIds = \App\Models\Certificate::whereIn('id', $certificateIds)->pluck('id')->toArray();
                    $invalidCertificateIds = array_diff($certificateIds, $existingCertificateIds);
                    
                    if (!empty($invalidCertificateIds)) {
                        $validator->errors()->add('certificate_ids', 'Some certificate IDs do not exist: ' . implode(', ', $invalidCertificateIds));
                    }
                }
            }
        });
    }
}
