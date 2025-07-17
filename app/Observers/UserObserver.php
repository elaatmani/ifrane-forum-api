<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Skip profile creation in certain conditions
        if ($this->shouldSkipProfileCreation()) {
            return;
        }

        try {
            // Create empty profile for the new user
            $user->profile()->create();
            
            Log::info("Profile created for user", [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
        } catch (\Exception $e) {
            // Log error but don't fail user creation
            Log::error("Failed to create profile for user", [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // When user is soft deleted, also soft delete their profile
        if ($user->profile) {
            try {
                $user->profile->delete();
                
                Log::info("Profile soft deleted for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to soft delete profile for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // When user is restored, also restore their profile
        if ($user->profile()->withTrashed()->exists()) {
            try {
                $user->profile()->withTrashed()->restore();
                
                Log::info("Profile restored for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to restore profile for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // When user is permanently deleted, also permanently delete their profile
        if ($user->profile()->withTrashed()->exists()) {
            try {
                $user->profile()->withTrashed()->forceDelete();
                
                Log::info("Profile force deleted for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to force delete profile for user", [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Determine if profile creation should be skipped.
     */
    protected function shouldSkipProfileCreation(): bool
    {
        // Skip during testing
        if (app()->environment('testing')) {
            return true;
        }

        // Skip during seeding (console commands)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return true;
        }

        // Skip if disabled via config
        if (config('app.auto_create_user_profiles', true) === false) {
            return true;
        }

        return false;
    }
}
