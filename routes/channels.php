<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}); 

Broadcast::channel('user.{userId}', function ($user, $userId) {
    if ($user->id === $userId) {
      return array('name' => $user->name, 'id' => $user->id);
    }
  });

Broadcast::channel('presence-channel.{id}', function ($user, $id) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('conversation.{id}', function ($user, $id) {
    // Check if user is participant in conversation
    $conversation = \App\Models\Conversation::find($id);
    if (!$conversation) {
        return false;
    }
    
    return $conversation->users()->where('user_id', $user->id)->exists();
});

Broadcast::channel('user.{userId}.messages', function ($user, $userId) {
    // User can only listen to their own message notifications
    return (int) $user->id === (int) $userId;
});

// Video Call Channels
Broadcast::channel('video-call.{id}', function ($user, $id) {
    // Check if user is participant in the video call conversation
    $videoCall = \App\Models\VideoCall::with('conversation')->find($id);
    if (!$videoCall) {
        return false;
    }
    
    return $videoCall->conversation->users()->where('user_id', $user->id)->exists();
});

Broadcast::channel('video-call-room.{id}', function ($user, $id) {
    // Check if user is participant in the video call room conversation
    $room = \App\Models\VideoCallRoom::with('conversation')->find($id);
    if (!$room) {
        return false;
    }
    
    return $room->conversation->users()->where('user_id', $user->id)->exists();
});
