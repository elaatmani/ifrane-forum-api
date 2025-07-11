<?php

namespace App\Http\Requests\Community;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Contracts\UserRepositoryInterface;

class CommunityMemberRequest extends FormRequest
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get current authenticated user
        $currentUser = auth()->user();
        
        // Check if current user is authenticated and has roles
        if (!$currentUser || !$currentUser->roles->count()) {
            return false;
        }

        // Get target user ID from route parameter
        $targetUserId = $this->route('id');
        
        if (!$targetUserId) {
            return false;
        }

        // Find target user
        $targetUser = $this->userRepository->find($targetUserId);
        
        if (!$targetUser || !$targetUser->roles->count()) {
            return false;
        }

        // Define allowed roles for current user to access community members
        $allowedRoles = ['admin', 'exhibitor', 'buyer', 'attendant', 'speaker', 'sponsor'];
        
        // Check if current user has any of the defined roles
        if (!$currentUser->hasAnyRole($allowedRoles)) {
            return false;
        }

        // Special case: If target user has "buyer" role, 
        // current user must have "exhibitor" or "admin" role
        if ($targetUser->hasRole('buyer')) {
            return $currentUser->hasAnyRole(['exhibitor', 'admin']);
        }

        // For other cases, allow access if current user has valid roles
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
