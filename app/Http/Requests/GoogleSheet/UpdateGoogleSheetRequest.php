<?php

namespace App\Http\Requests\GoogleSheet;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoogleSheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('sheet.update');
    }

    public function rules(): array
    {
        return [
            'sheet_name' => ['required','string','max:255'],
            'sheet_id' => ['required','string'],
            'name' => ['required','string'],
            'marketer_id' => ['nullable', 'integer'],
            // 'is_active' => ['required','boolean'],
        ];
    }

    public function messages()
    {
        return [
            'sheet_name.required' => 'required',
            'sheet_id.required' => 'required',
            'name.required' => 'required',
            'is_active.required' => 'required',
        ];
    }
}
