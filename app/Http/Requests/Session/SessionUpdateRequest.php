<?php

namespace App\Http\Requests\Session;

use Illuminate\Foundation\Http\FormRequest;

class SessionUpdateRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|max:1024',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'link' => 'sometimes|required|string|max:255',
            'type_id' => 'sometimes|required|integer',
            'topic_id' => 'sometimes|required|integer',
            'language_id' => 'sometimes|required|integer',
            'is_active' => 'nullable|boolean',
            'speakers' => 'sometimes|required|array',
            'speakers.*' => 'required|integer',
        ];
    }
}
