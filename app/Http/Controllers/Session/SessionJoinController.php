<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\SessionJoinRequest;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionJoinController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SessionJoinRequest $request, $sessionId)
    {
        $session = Session::findOrFail($sessionId);
        $user = Auth::user();

        // Check if user is already attending this session
        $isAttending = $session->users()->where('user_id', $user->id)->exists();

        if ($isAttending) {
            // User is already attending, so leave the session
            $session->users()->detach($user->id);

            return response()->json([
                'message' => 'Successfully left the session',
                'status' => 'left',
                'session' => [
                    'id' => $session->id,
                    'name' => $session->name,
                    'action' => 'left'
                ]
            ], 200);
        } else {
            // User is not attending, so join the session
            $session->users()->attach($user->id, [
                'role' => 'attendant',
                'joined_at' => now()
            ]);

            return response()->json([
                'message' => 'Successfully joined the session',
                'status' => 'joined',
                'session' => [
                    'id' => $session->id,
                    'name' => $session->name,
                    'role' => 'attendant',
                    'joined_at' => now()->format('Y-m-d H:i:s'),
                    'action' => 'joined'
                ]
            ], 201);
        }
    }
} 