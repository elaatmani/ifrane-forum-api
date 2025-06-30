<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class DocumentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation by converting string booleans to actual booleans.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_file_updated' => $this->convertToBoolean($this->input('is_file_updated')),
            'is_thumbnail_updated' => $this->convertToBoolean($this->input('is_thumbnail_updated')),
        ]);
    }

    /**
     * Convert string boolean values to actual booleans.
     */
    private function convertToBoolean($value): ?bool
    {
        if (is_null($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower((string) $value);
        
        return in_array($value, ['true', '1', 'yes', 'on']) ? true : 
               (in_array($value, ['false', '0', 'no', 'off']) ? false : null);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // File update flags
            'is_file_updated' => 'nullable|boolean',
            'is_thumbnail_updated' => 'nullable|boolean',
            
            // Main file - only required if is_file_updated is true
            'file' => 'required_if:is_file_updated,true|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240', // 10MB max
            
            // Thumbnail - only required if is_thumbnail_updated is true
            'thumbnail' => 'required_if:is_thumbnail_updated,true|file|mimes:png,jpg,jpeg|max:10240',
            
            // Document details - all optional for updates
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            
            // These will be determined from the uploaded file (read-only)
            'type' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'extension' => 'nullable|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required_if' => 'File is required when updating the document file.',
            'thumbnail.required_if' => 'Thumbnail is required when updating the thumbnail.',
            'file.mimes' => 'File must be a PDF, Word, Excel, or PowerPoint document.',
            'thumbnail.mimes' => 'Thumbnail must be a PNG, JPG, or JPEG image.',
            'file.max' => 'File size cannot exceed 10MB.',
            'thumbnail.max' => 'Thumbnail size cannot exceed 10MB.',
        ];
    }
}
