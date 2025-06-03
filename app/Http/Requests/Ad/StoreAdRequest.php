<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
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
            'platform' => ['required','max:255'],
            'product_id' => ['required','integer','max:255'],
            'spent_in' => ['required','date','max:255'],
            // 'stopped_at' => ['nullable','date'],
            'spend' => ['numeric'],

        ];
    }

    public function messages()
    {
        return [
            'platform.required' => 'required',
            'product_id.required' => 'required',
            'spent_in.required' => 'required',
        ];
    }
}
