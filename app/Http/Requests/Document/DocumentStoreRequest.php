<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class DocumentStoreRequest extends FormRequest
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
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240', // 10MB max
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'thumbnail' => 'nullable|file|mimes:png,jpg,jpeg|max:10240',
            
            // These will be determined from the uploaded file
            'type' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'extension' => 'nullable|string|max:255',
            'mime_type' => 'nullable|string|max:255',

            
            'company_id' => 'nullable|exists:companies,id',
        ];
    }
}
