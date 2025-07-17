<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'country_id' => 'sometimes|integer|exists:countries,id',
            'about' => 'sometimes|string|nullable',
            'linkedin_url' => 'sometimes|url|nullable',
            'instagram_url' => 'sometimes|url|nullable',
            'twitter_url' => 'sometimes|url|nullable',
            'facebook_url' => 'sometimes|url|nullable',
            'youtube_url' => 'sometimes|url|nullable',
            'github_url' => 'sometimes|url|nullable',
            'website_url' => 'sometimes|url|nullable',
            'contact_email' => 'sometimes|email|nullable',
            'language' => 'sometimes|string|nullable',
            'street' => 'sometimes|string|nullable',
            'city' => 'sometimes|string|nullable',
            'state' => 'sometimes|string|nullable',
            'postal_code' => 'sometimes|string|nullable',
            'full_name' => 'sometimes|string|nullable|max:255'
        ];

        if($this->hasFile('profile_image')) {
            $rules['profile_image'] = 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }

        return $rules;
    }


    public function messages(): array
    {
        return [
            'country_id.required' => 'Country is required',
            'full_name.max' => 'Full name must be less than 255 characters',
        ];
    }
}
