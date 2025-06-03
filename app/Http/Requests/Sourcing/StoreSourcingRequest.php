<?php

namespace App\Http\Requests\Sourcing;

use Illuminate\Foundation\Http\FormRequest;

class StoreSourcingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('sourcing.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_name' => 'required|string',
            'product_url' => 'nullable|string',
            'destination_country' => 'required|string|not_in:not-selected',
            'note' => 'nullable',
            'shipping_method' => 'required|string',
            'status' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'product_name.required' => 'required',
            'shipping_method.required' => 'required',
            'status.required' => 'required',
            'destination_country.not_in' => 'invalid',
        ];
    }
}
