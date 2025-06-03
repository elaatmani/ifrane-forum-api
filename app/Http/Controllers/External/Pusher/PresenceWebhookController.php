<?php

namespace App\Http\Controllers\External\Pusher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PresenceWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $event = $request->input('events')[0];

        if($event['name'] == 'member_added') {
            $this->handlePusherMemberAdded($event);
        } else if ($event['name'] == 'member_removed') {
            $this->handlePusherMemberRemoved($event);
        }

        return response()->json([
            'message' => 'Presence Webhook Received'
        ]);
    }

    public function handlePusherMemberAdded($event)
    {
        $userId = $event['user_id'];

        DB::table('user_sessions')->insert([
            'user_id' => $userId,
            'login_time' => now(),
        ]);
    }

    public function handlePusherMemberRemoved($event)
    {
        $userId = $event['user_id'];
        DB::table('user_sessions')
            ->where('user_id', $userId)
            ->whereNull('logout_time')
            ->orderBy('login_time', 'desc')
            ->limit(1)
            ->update(['logout_time' => now()]);
    }
}
