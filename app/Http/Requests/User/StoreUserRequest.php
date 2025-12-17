<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('user.create');
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
            'email' => ['required','string', 'email','max:255', 'unique:users'],
            'password' => ['required', 'string','min:8','max:255'],
            // 'role_id' => ['required', 'exists:roles,id'],
            // 'role' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'required',
            'email.required' => 'required',
            'email.unique' => 'unique',
            'password.required' => 'required',
            'role_id.required' => 'required',
            'password.min' => 'min:9',
        ];
    }
}
