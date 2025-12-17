<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ValidateResetPasswordTokenController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $email = $validated['email'];
        $token = $validated['token'];

        // Get the user by email
        $user = User::where('email', $email)->first();

        // If user doesn't exist, return invalid
        if (!$user) {
            return response()->json([
                'valid' => false
            ], 422);
        }

        // Check if the token is valid using Laravel's Password broker
        $isValid = Password::broker()->getRepository()->exists($user, $token);

        if ($isValid) {
            return response()->json([
                'valid' => true
            ], 200);
        }

        return response()->json([
            'valid' => false
        ], 422);
    }
}

