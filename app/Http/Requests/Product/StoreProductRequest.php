<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('product.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'sku' => ['required','string','max:255', 'unique:products,sku'],
            'buying_price' => ['required', 'numeric'],
            'selling_price' => ['required', 'numeric'],
            'video_url' => ['nullable','string'],
            'store_url' => ['nullable','string'],
            'quantity' => ['nullable', 'numeric'],
            'stock_alert' => ['nullable', 'numeric'],
            'cross_products' => ['nullable', 'array'],
            'cross_products.*.price' => ['nullable', 'numeric'],
            'cross_products.*.cross_product_id' => ['nullable', 'numeric'],
            'cross_products.*.note' => ['nullable', 'string'],

            'offers' => ['nullable', 'array'],
            'offers.*.price' => ['nullable', 'numeric'],
            'offers.*.note' => ['nullable', 'string'],
            'offers.*.quantity' => ['nullable', 'numeric'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'required',
            'sku.required' => 'required',
            'sku.unique' => 'unique',
            'buying_price.required' => 'required',
            'selling_price.required' => 'required',

        ];
    }
}
