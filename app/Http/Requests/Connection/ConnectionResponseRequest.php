<?php

namespace App\Http\Requests\Connection;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UserConnection;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;

class ConnectionResponseRequest extends FormRequest
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
        parent::__construct();
    }

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
        return [
            'action' => [
                'required',
                'string',
                'in:accept,decline',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Please specify the action to perform.',
            'action.string' => 'Invalid action format.',
            'action.in' => 'Action must be either "accept" or "decline".',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $connectionId = $this->route('connection');
            $userId = auth()->id();

            if ($connectionId && $userId) {
                // Check if the connection exists and belongs to the current user (as receiver)
                $connection = UserConnection::where('id', $connectionId)
                                          ->where('receiver_id', $userId)
                                          ->where('status', UserConnection::STATUS_PENDING)
                                          ->first();

                if (!$connection) {
                    $validator->errors()->add('connection', 'Connection request not found or you are not authorized to respond to it.');
                }
            }
        });
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Add connection ID and user ID to validated data
        $validated['connection_id'] = $this->route('connection');
        $validated['user_id'] = auth()->id();
        
        return $validated;
    }
} 