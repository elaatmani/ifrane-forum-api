<?php

namespace App\Http\Requests\Session;

use Illuminate\Foundation\Http\FormRequest;

class SessionStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'link' => 'required|string|max:255',
            'type_id' => 'required|integer',
            'topic_id' => 'required|integer',
            'language_id' => 'required|integer',
            'is_active' => 'nullable|boolean',
            'speakers' => 'required|array',
            'speakers.*' => 'required|integer',
        ];
    }
}
