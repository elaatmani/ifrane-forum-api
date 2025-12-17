<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class UserSendEmailController extends Controller
{
    protected $repository;
    protected $notificationService;

    public function __construct(
        UserRepositoryInterface $repository,
        NotificationService $notificationService
    ) {
        $this->repository = $repository;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'type' => 'required|string|in:welcome,reset_password',
        ]);

        // Find the user
        $user = $this->repository->find($id);

        if (!$user) {
            return response()->json([
                'code' => 'ERROR',
                'message' => 'User not found'
            ], 404);
        }

        $type = $validated['type'];
        $sent = false;

        try {
            if ($type === 'welcome') {
                $sent = $this->notificationService->sendWelcomeEmail($user);
            } elseif ($type === 'reset_password') {
                // Generate password reset token
                $token = Password::broker()->createToken($user);
                $sent = $this->notificationService->sendPasswordResetEmail($user, $token);
            }

            if (!$sent) {
                Log::error('Failed to send email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'type' => $type
                ]);

                return response()->json([
                    'code' => 'ERROR',
                    'message' => 'Failed to send email. Please try again later.'
                ], 500);
            }

            return response()->json([
                'code' => 'SUCCESS',
                'message' => 'Email sent successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error sending email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'code' => 'ERROR',
                'message' => 'Unable to send email at this time.'
            ], 500);
        }
    }
}

