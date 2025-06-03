<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserNotification $userNotification): bool
    {
        return $user->id === $userNotification->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserNotification $userNotification): bool
    {
        return $user->id === $userNotification->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserNotification $userNotification): bool
    {
        return $user->id === $userNotification->user_id;
    }
} 