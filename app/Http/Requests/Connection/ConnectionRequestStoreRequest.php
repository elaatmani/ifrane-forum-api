<?php

namespace App\Http\Requests\Connection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;

class ConnectionRequestStoreRequest extends FormRequest
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
        $messageRequired = Config::get('connections.messages.required', true);
        $minLength = Config::get('connections.messages.min_length', 10);
        $maxLength = Config::get('connections.messages.max_length', 500);

        $rules = [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:sender_id',
            ],
            'message' => [
                $messageRequired ? 'required' : 'nullable',
                'string',
                "min:{$minLength}",
                "max:{$maxLength}",
            ],
        ];

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        $validationMessages = Config::get('connections.messages.validation_messages', []);
        $minLength = Config::get('connections.messages.min_length', 10);
        $maxLength = Config::get('connections.messages.max_length', 500);

        return [
            'receiver_id.required' => 'Please select a user to connect with.',
            'receiver_id.integer' => 'Invalid user ID format.',
            'receiver_id.exists' => 'The selected user does not exist.',
            'receiver_id.different' => 'You cannot send a connection request to yourself.',
            'message.required' => $validationMessages['required'] ?? 'A message is required when sending a connection request.',
            'message.string' => 'The message must be a valid text.',
            'message.min' => str_replace(':min', $minLength, $validationMessages['min'] ?? 'Connection message must be at least :min characters long.'),
            'message.max' => str_replace(':max', $maxLength, $validationMessages['max'] ?? 'Connection message cannot exceed :max characters.'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sender_id' => auth()->id(),
        ]);

        // If message is empty and not required, set default message
        if (empty($this->message) && !Config::get('connections.messages.required', true)) {
            $this->merge([
                'message' => Config::get('connections.messages.default_message', 'Hi! I would like to connect with you.'),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $senderId = auth()->id();
            $receiverId = $this->receiver_id;

            if ($senderId && $receiverId) {
                // Check if user can send connection request
                $canSend = $this->connectionRepository->canSendConnectionRequest($senderId, $receiverId);
                
                if (!$canSend['can_send']) {
                    $validator->errors()->add('receiver_id', $canSend['reason']);
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
        
        // Always include the sender_id in validated data
        $validated['sender_id'] = auth()->id();
        
        return $validated;
    }
} 