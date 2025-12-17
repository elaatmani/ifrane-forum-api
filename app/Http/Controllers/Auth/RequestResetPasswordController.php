<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class RequestResetPasswordController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $validated['email'];
        $user = User::where('email', $email)->first();

        // Always return success message for security (don't reveal if user exists)
        if (!$user) {
            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'If an account with this email exists, a reset link has been sent.'
            ], 200);
        }

        try {
            // Generate password reset token
            $token = Password::broker()->createToken($user);

            // Send reset password email
            $sent = $this->notificationService->sendPasswordResetEmail($user, $token);

            if (!$sent) {
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'email' => $email
                ]);
            }

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'If an account with this email exists, a reset link has been sent.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error processing password reset request', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'code' => 'ERROR',
                'message' => 'Unable to process reset request at this time.'
            ], 500);
        }
    }
}

