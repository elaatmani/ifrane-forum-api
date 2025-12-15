# Pusher Channels and Events Documentation

## Overview

This document describes all Pusher channels and events used in the application for real-time communication.

---

## Channel: `user.{userId}.messages`

### Purpose
A **multi-purpose private channel** used for sending various types of notifications and updates to individual users. This is the main channel for user-specific real-time updates.

### Authorization
**File:** `routes/channels.php` (Line 42-45)

```php
Broadcast::channel('user.{userId}.messages', function ($user, $userId) {
    // User can only listen to their own message notifications
    return (int) $user->id === (int) $userId;
});
```

**Security:** Users can only subscribe to their own channel (where `userId` matches their authenticated user ID).

---

## Events Broadcasted on `user.{userId}.messages`

### 1. Message Events

#### `message.sent`
**Event Class:** `App\Events\MessageSent`  
**When:** When a new message is sent in any conversation  
**Broadcasted to:** All participants of the conversation

**Channels:**
- `conversation.{conversation_id}` - For real-time message display in conversation view
- `user.{userId}.messages` - For global notifications to all participants

**Event Data:**
```json
{
  "id": "uuid",
  "conversation_id": "uuid",
  "sender_id": 1,
  "content": "Message text",
  "type": "text",
  "created_at": "2024-01-15T10:30:00.000000Z",
  "sender": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Frontend Usage:**
```javascript
// Listen for new messages
echo.private(`user.${userId}.messages`)
  .listen('.message.sent', (message) => {
    // Handle new message notification
    // Update conversation list
    // Show notification badge
    // Play sound if needed
  });
```

---

### 2. Conversation Events

#### `conversation.created`
**Event Class:** `App\Events\ConversationCreated`  
**When:** When a new conversation is created  
**Broadcasted to:** All participants of the conversation

**Event Data:**
```json
{
  "id": "uuid",
  "type": "direct|session|company",
  "users": [...],
  "last_message": {...}
}
```

#### `conversation.updated`
**Event Class:** `App\Events\ConversationUpdated`  
**When:** When a conversation is updated (e.g., new message, read status change)  
**Broadcasted to:** All participants of the conversation

---

### 3. Meeting Events

#### `meeting.created`
**Event Class:** `App\Events\MeetingCreated`  
**When:** When a new meeting is created  
**Broadcasted to:** Organizer and all participants

#### `meeting.accepted`
**Event Class:** `App\Events\MeetingAccepted`  
**When:** When a meeting is accepted  
**Broadcasted to:** Organizer and all participants

#### `meeting.declined`
**Event Class:** `App\Events\MeetingDeclined`  
**When:** When a meeting is declined  
**Broadcasted to:** Organizer and all participants

#### `meeting.started`
**Event Class:** `App\Events\MeetingStarted`  
**When:** When a meeting is started  
**Broadcasted to:** All participants

#### `meeting.completed`
**Event Class:** `App\Events\MeetingCompleted`  
**When:** When a meeting is completed  
**Broadcasted to:** All participants

#### `meeting.cancelled`
**Event Class:** `App\Events\MeetingCancelled`  
**When:** When a meeting is cancelled  
**Broadcasted to:** All participants

---

### 4. Notification Events

#### `notification.created`
**Event Class:** `App\Notifications\BroadcastableNotification`  
**When:** When a new notification is created  
**Broadcasted to:** The notification recipient

**Event Data:**
```json
{
  "id": 123,
  "title": "New Meeting Invitation",
  "message": "You have been invited to a meeting",
  "severity_type": "info",
  "data": {...}
}
```

#### `notification.updated`
**Event Class:** `App\Events\NotificationUpdated`  
**When:** When a notification is updated (e.g., marked as read, metadata updated)  
**Broadcasted to:** The notification owner

**Event Data:**
```json
{
  "id": 123,
  "read_at": "2024-01-15T10:30:00.000000Z",
  "unread": false,
  "data": {...}
}
```

#### `notification.deleted`
**Event Class:** `App\Events\NotificationDeleted`  
**When:** When a notification is deleted  
**Broadcasted to:** The notification owner

#### `notification.count.updated`
**Event Class:** `App\Events\NotificationCountUpdated`  
**When:** When the unread notification count changes  
**Broadcasted to:** The user

**Event Data:**
```json
{
  "user_id": 1,
  "unread_count": 5
}
```

---

### 5. Unread Count Events

#### `unread.updated`
**Event Class:** `App\Events\UnreadCountUpdated`  
**When:** When conversation unread count changes  
**Broadcasted to:** The user

**Event Data:**
```json
{
  "user_id": 1,
  "unread_count": 3,
  "conversation_id": "uuid",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

---

### 6. Video Call Events

#### `video-call.initiated`
**Event Class:** `App\Events\VideoCallInitiated`  
**When:** When a video call is initiated  
**Broadcasted to:** All participants

#### `video-call.accepted`
**Event Class:** `App\Events\VideoCallAccepted`  
**When:** When a video call is accepted  
**Broadcasted to:** All participants

#### `video-call.rejected`
**Event Class:** `App\Events\VideoCallRejected`  
**When:** When a video call is rejected  
**Broadcasted to:** All participants

#### `video-call.ended`
**Event Class:** `App\Events\VideoCallEnded`  
**When:** When a video call ends  
**Broadcasted to:** All participants

#### `video-call.participant.joined`
**Event Class:** `App\Events\VideoCallParticipantJoined`  
**When:** When a participant joins a video call  
**Broadcasted to:** All participants

#### `video-call.participant.left`
**Event Class:** `App\Events\VideoCallParticipantLeft`  
**When:** When a participant leaves a video call  
**Broadcasted to:** All participants

---

## Other Channels

### `conversation.{conversationId}`
**Purpose:** Real-time message updates within a specific conversation  
**Authorization:** User must be a participant in the conversation

**Events:**
- `message.sent` - New message in the conversation

**Usage:**
```javascript
// Listen for messages in a specific conversation
echo.private(`conversation.${conversationId}`)
  .listen('.message.sent', (message) => {
    // Add message to conversation view
    addMessageToConversation(message);
  });
```

---

## Frontend Implementation Example

### Complete Setup

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'pusher',
  key: 'your-pusher-key',
  cluster: 'your-cluster',
  authEndpoint: '/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${getToken()}`,
    },
  },
});

const userId = getCurrentUserId();

// Subscribe to user's personal messages channel
const userChannel = echo.private(`user.${userId}.messages`);

// Listen for new messages
userChannel.listen('.message.sent', (message) => {
  console.log('New message received:', message);
  // Update conversation list
  // Show notification
  // Update unread count
});

// Listen for conversation events
userChannel.listen('.conversation.created', (conversation) => {
  console.log('New conversation:', conversation);
  // Add to conversation list
});

userChannel.listen('.conversation.updated', (conversation) => {
  console.log('Conversation updated:', conversation);
  // Update conversation in list
});

// Listen for meeting events
userChannel.listen('.meeting.created', (event) => {
  console.log('Meeting created:', event);
});

userChannel.listen('.meeting.accepted', (event) => {
  console.log('Meeting accepted:', event);
});

// Listen for notification events
userChannel.listen('.notification.created', (notification) => {
  console.log('New notification:', notification);
  // Show notification badge
  // Display notification
});

userChannel.listen('.notification.updated', (notification) => {
  console.log('Notification updated:', notification);
  // Update notification in UI
});

userChannel.listen('.notification.count.updated', (data) => {
  console.log('Unread count:', data.unread_count);
  // Update notification badge count
});

// Listen for unread count updates
userChannel.listen('.unread.updated', (data) => {
  console.log('Unread count updated:', data);
  // Update conversation unread badge
});

// Listen for video call events
userChannel.listen('.video-call.initiated', (event) => {
  console.log('Video call initiated:', event);
  // Show incoming call UI
});

userChannel.listen('.video-call.accepted', (event) => {
  console.log('Video call accepted:', event);
  // Update call status
});

// Also subscribe to specific conversation channels when viewing a conversation
function subscribeToConversation(conversationId) {
  echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (message) => {
      // Add message to current conversation view
      addMessageToView(message);
    });
}
```

---

## Channel Authorization

All channels require authentication via `auth:api` middleware. The authorization logic is defined in `routes/channels.php`:

### `user.{userId}.messages`
- **Rule:** User can only listen to their own channel
- **Check:** `$user->id === $userId`

### `conversation.{id}`
- **Rule:** User must be a participant in the conversation
- **Check:** `$conversation->users()->where('user_id', $user->id)->exists()`

---

## Summary

### `user.{userId}.messages` Channel Role

This channel serves as a **unified notification channel** for all user-specific real-time updates:

1. **Message Notifications** - New messages in any conversation
2. **Conversation Updates** - New conversations, conversation changes
3. **Meeting Events** - All meeting-related events (created, accepted, declined, started, completed, cancelled)
4. **System Notifications** - Notification creation, updates, deletions, count updates
5. **Unread Counts** - Conversation and notification unread count updates
6. **Video Call Events** - All video call related events

### Why This Design?

- **Single Channel:** Users only need to subscribe to one channel (`user.{userId}.messages`) to receive all their real-time updates
- **Security:** Each user can only access their own channel
- **Efficiency:** Reduces the number of channel subscriptions needed
- **Unified Handling:** Frontend can handle all user-specific events in one place

---

## Event Naming Convention

All events use dot notation:
- `message.sent`
- `conversation.created`
- `meeting.accepted`
- `notification.created`
- `video-call.initiated`

**Frontend listens with dot prefix:**
```javascript
.listen('.message.sent', ...)  // Note the leading dot
.listen('.meeting.created', ...)
```

---

## Testing

To test Pusher events:

1. **Check Laravel Logs:** Events are logged when broadcasted
2. **Pusher Debug Console:** Use Pusher dashboard to see events
3. **Laravel Tinker:** Manually trigger events:
   ```php
   event(new \App\Events\MessageSent($message));
   ```
4. **Frontend Console:** Check browser console for received events

---

## Security Notes

- All channels require authentication
- Users can only access their own `user.{userId}.messages` channel
- Conversation channels verify user participation
- All events are sent via private channels (encrypted)

