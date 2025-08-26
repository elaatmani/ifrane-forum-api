<?php

namespace App\Http\Requests\Session;

use App\Models\Session;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class SessionJoinRequest extends FormRequest
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
            // No validation rules needed since role is always 'attendee'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // No custom messages needed
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sessionId = $this->route('sessionId');
            $session = Session::find($sessionId);

            if (!$session) {
                $validator->errors()->add('session', 'Session not found.');
                return;
            }

            // Check if session is active
            if (!$session->is_active) {
                $validator->errors()->add('session', 'This session is not active.');
                return;
            }

            // Check if session status is not "not scheduled"
            if ($session->status === 'not scheduled') {
                $validator->errors()->add('session', 'This session is not scheduled yet.');
                return;
            }

            // Check if session has started and ended (past session)
            $now = Carbon::now();
            
            if ($session->start_date && $session->end_date) {
                if ($now->isAfter($session->end_date)) {
                    $validator->errors()->add('session', 'This session has already ended.');
                    return;
                }
            } elseif ($session->start_date) {
                // If only start date is set, check if it's in the past
                if ($now->isAfter($session->start_date)) {
                    $validator->errors()->add('session', 'This session has already started.');
                    return;
                }
            }

            // Check if session is too far in the future (optional - you can remove this if not needed)
            if ($session->start_date && $now->diffInDays($session->start_date) > 365) {
                $validator->errors()->add('session', 'This session is too far in the future.');
                return;
            }
        });
    }
} 