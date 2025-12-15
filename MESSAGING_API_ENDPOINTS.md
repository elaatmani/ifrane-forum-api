# Messaging API Endpoints - Frontend Integration Guide

## Base URL
All endpoints are prefixed with `/api/messaging`

## Authentication
All endpoints require authentication via `Bearer Token` (Sanctum).
Include the token in the `Authorization` header:
```
Authorization: Bearer {your_token}
```

---

## Endpoints Overview

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/messaging/conversations` | List user's conversations |
| GET | `/api/messaging/conversations/direct/{user}` | Get or create direct conversation |
| GET | `/api/messaging/conversations/session/{session}` | Get or create session conversation |
| GET | `/api/messaging/conversations/company/{company}` | Get or create company conversation |
| GET | `/api/messaging/conversations/{conversation}/messages` | Get conversation messages |
| POST | `/api/messaging/conversations/{conversation}/read` | Mark conversation as read |
| GET | `/api/messaging/conversations/{conversation}/participants` | Get conversation participants |
| GET | `/api/messaging/conversations/{conversation}/unread-count` | Get unread count for conversation |
| POST | `/api/messaging/conversations/{conversation}/messages/text` | Send text message |
| POST | `/api/messaging/conversations/{conversation}/messages/file` | Send file message |
| POST | `/api/messaging/conversations/{conversation}/messages/missed-call` | Send missed call message |
| POST | `/api/messaging/conversations/{conversation}/messages/video-call-request` | Send video call request |
| POST | `/api/messaging/conversations/{conversation}/messages/voice-call-request` | Send voice call request |
| POST | `/api/messaging/conversations/{conversation}/messages/call-ended` | Send call ended message |
| POST | `/api/messaging/conversations/{conversation}/messages/call-rejected` | Send call rejected message |
| POST | `/api/messaging/conversations/{conversation}/messages/call-accepted` | Send call accepted message |
| DELETE | `/api/messaging/messages/{message}` | Delete a message |
| GET | `/api/messaging/messages/unread-count` | Get total unread messages count |

---

## 1. List User's Conversations

**Endpoint:** `GET /api/messaging/conversations`

**Description:** Retrieves all conversations where the authenticated user is a participant.

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `type` | string | Filter by type: `direct`, `session`, `company` |
| `page` | integer | Page number for pagination (default: 1) |

### Example Request

```
GET /api/messaging/conversations?type=direct&page=1
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "type": "direct",
        "name": null,
        "created_at": "2024-01-15T10:00:00.000000Z",
        "users": [
          {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
          },
          {
            "id": 2,
            "name": "Jane Smith",
            "email": "jane@example.com"
          }
        ],
        "last_message": {
          "id": "uuid",
          "content": "Hello!",
          "sender_id": 1,
          "created_at": "2024-01-15T10:30:00.000000Z"
        },
        "unread_count": 3
      }
    ],
    "per_page": 20,
    "total": 10
  }
}
```

---

## 2. Get or Create Direct Conversation

**Endpoint:** `GET /api/messaging/conversations/direct/{user}`

**Description:** Gets or creates a direct conversation between the authenticated user and the specified user.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `user` | integer | The ID of the other user |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "type": "direct",
    "users": [...],
    "messages": [...]
  }
}
```

---

## 3. Get Conversation Messages

**Endpoint:** `GET /api/messaging/conversations/{conversation}/messages`

**Description:** Retrieves messages for a specific conversation with pagination.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `conversation` | string (UUID) | The conversation ID |

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 50) |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "uuid",
        "conversation_id": "uuid",
        "sender_id": 1,
        "content": "Hello!",
        "message_type": "text",
        "file_url": null,
        "metadata": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "sender": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      }
    ],
    "per_page": 50,
    "total": 100
  }
}
```

---

## 4. Mark Conversation as Read

**Endpoint:** `POST /api/messaging/conversations/{conversation}/read`

**Description:** Marks all messages in a conversation as read for the authenticated user.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `conversation` | string (UUID) | The conversation ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Conversation marked as read"
}
```

**Note:** This triggers an `unread.updated` event with the updated count.

---

## 5. Get Unread Count for Conversation

**Endpoint:** `GET /api/messaging/conversations/{conversation}/unread-count`

**Description:** Gets the unread message count for a specific conversation.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `conversation` | string (UUID) | The conversation ID |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

## 6. Get Total Unread Messages Count

**Endpoint:** `GET /api/messaging/messages/unread-count`

**Description:** Gets the total count of unread messages across all conversations for the authenticated user.

### Response (200 OK)

```json
{
  "count": 12
}
```

**Note:** This endpoint returns only the count (similar to the notification unread count endpoint).

### Example Usage

```javascript
// Fetch total unread messages count
async function getUnreadMessagesCount() {
  const response = await fetch('/api/messaging/messages/unread-count', {
    headers: {
      'Authorization': `Bearer ${getToken()}`
    }
  });
  const data = await response.json();
  return data.count;
}
```

---

## 7. Send Text Message

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/text`

**Description:** Sends a text message to a conversation.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `conversation` | string (UUID) | The conversation ID |

### Request Body

```json
{
  "content": "Hello, how are you?"
}
```

### Validation Rules

- `content`: required, string, max:1000

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "conversation_id": "uuid",
    "sender_id": 1,
    "content": "Hello, how are you?",
    "message_type": "text",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "sender": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  },
  "message": "Message sent successfully"
}
```

**Note:** 
- When a user sends a message, the conversation is automatically marked as read for them
- An `unread.updated` event is broadcasted to all participants
- The sender receives `count: 0` for that conversation

---

## 8. Send File Message

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/file`

**Description:** Sends a file message to a conversation.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `conversation` | string (UUID) | The conversation ID |

### Request Body (multipart/form-data)

| Field | Type | Description |
|-------|------|-------------|
| `file` | file | The file to upload (required, max: 10MB) |
| `content` | string | Optional caption/description (max: 500) |

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "conversation_id": "uuid",
    "sender_id": 1,
    "content": "Check this out",
    "message_type": "file",
    "file_url": "messages/files/filename.jpg",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "sender": {
      "id": 1,
      "name": "John Doe"
    }
  },
  "message": "File sent successfully"
}
```

---

## 9. Send Call-Related Messages

### 9.1 Missed Call

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/missed-call`

**Request Body:**
```json
{
  "metadata": {
    "call_type": "video",
    "duration": 0,
    "reason": "user_busy"
  }
}
```

### 9.2 Video Call Request

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/video-call-request`

**Request Body:**
```json
{
  "metadata": {
    "call_id": "uuid",
    "room_id": "uuid"
  }
}
```

### 9.3 Voice Call Request

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/voice-call-request`

**Request Body:**
```json
{
  "metadata": {
    "call_id": "uuid",
    "room_id": "uuid"
  }
}
```

### 9.4 Call Ended

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/call-ended`

**Request Body:**
```json
{
  "metadata": {
    "duration": 600,
    "ended_by": 1
  }
}
```

### 9.5 Call Rejected

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/call-rejected`

**Request Body:**
```json
{
  "metadata": {
    "reason": "user_busy"
  }
}
```

### 9.6 Call Accepted

**Endpoint:** `POST /api/messaging/conversations/{conversation}/messages/call-accepted`

**Request Body:**
```json
{
  "metadata": {
    "accepted_at": "2024-01-15T10:30:00Z"
  }
}
```

---

## 10. Delete Message

**Endpoint:** `DELETE /api/messaging/messages/{message}`

**Description:** Deletes a message (soft delete). Only the sender can delete their own messages.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `message` | string (UUID) | The message ID |

### Response (200 OK)

```json
{
  "success": true,
  "message": "Message deleted successfully"
}
```

---

## Real-Time Events

### Channel: `user.{userId}.messages`

All messaging-related real-time events are broadcasted on the user's personal messages channel.

### Event: `unread.updated`

**When:** 
- When a message is sent (to all participants)
- When a conversation is marked as read

**Event Data:**
```json
{
  "user_id": 1,
  "count": 5,
  "conversation_id": "uuid",
  "total_unread_count": 12,
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

**Fields:**
- `user_id`: The user ID receiving the update
- `count`: Unread count for the specific conversation (0 if user sent the last message)
- `conversation_id`: The conversation ID (null if not conversation-specific)
- `total_unread_count`: Total unread messages across all conversations (optional)
- `updated_at`: ISO 8601 timestamp

**Frontend Implementation:**
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

// Listen for unread count updates
echo.private(`user.${userId}.messages`)
  .listen('.unread.updated', (data) => {
    console.log('Unread count updated:', data);
    
    // Update conversation-specific unread count
    if (data.conversation_id) {
      updateConversationUnreadCount(data.conversation_id, data.count);
    }
    
    // Update total unread count
    if (data.total_unread_count !== undefined) {
      updateTotalUnreadCount(data.total_unread_count);
    }
  });
```

### Event: `message.sent`

**When:** When a new message is sent in any conversation

**Channels:**
- `conversation.{conversation_id}` - For real-time message display
- `user.{userId}.messages` - For global notifications

**Event Data:**
```json
{
  "id": "uuid",
  "conversation_id": "uuid",
  "sender_id": 1,
  "content": "Hello!",
  "message_type": "text",
  "created_at": "2024-01-15T10:30:00.000000Z",
  "sender": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Frontend Implementation:**
```javascript
// Listen for new messages on user channel
echo.private(`user.${userId}.messages`)
  .listen('.message.sent', (message) => {
    // Update conversation list
    // Show notification
    // Update unread count
    handleNewMessage(message);
  });

// Listen for messages in specific conversation
echo.private(`conversation.${conversationId}`)
  .listen('.message.sent', (message) => {
    // Add message to conversation view
    addMessageToView(message);
  });
```

### Event: `conversation.created`

**When:** When a new conversation is created

**Event Data:**
```json
{
  "id": "uuid",
  "type": "direct",
  "users": [...],
  "last_message": {...},
  "unread_counts": {
    "1": 0,
    "2": 0
  }
}
```

### Event: `conversation.updated`

**When:** When a conversation is updated (new message, read status change)

**Event Data:**
```json
{
  "id": "uuid",
  "type": "direct",
  "users": [...],
  "last_message": {...},
  "unread_counts": {
    "1": 0,
    "2": 3
  }
}
```

---

## Unread Count Behavior

### When User Sends a Message

1. Message is created and saved
2. Conversation is automatically marked as read for the sender (`last_read_at` updated)
3. `unread.updated` event is broadcasted to all participants:
   - **Sender**: receives `count: 0` for that conversation
   - **Other participants**: receive their actual unread count (may have increased)

### When User Marks Conversation as Read

1. `last_read_at` is updated to current time
2. `unread.updated` event is broadcasted with:
   - `count: 0` for that conversation
   - Updated `total_unread_count`

### Unread Count Calculation

- Only messages from **other users** are counted as unread
- Messages sent by the user themselves are never counted
- Count is based on messages created after `last_read_at`
- If `last_read_at` is null, all messages from other users are counted

---

## Error Responses

### 403 Forbidden

```json
{
  "success": false,
  "message": "You do not have access to this conversation"
}
```

### 400 Bad Request

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "content": ["The content field is required."]
  }
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "Conversation not found"
}
```

---

## Frontend Integration Example

### Complete Messaging Setup

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

// Listen for unread count updates
userChannel.listen('.unread.updated', (data) => {
  console.log('Unread count updated:', data);
  
  // Update conversation badge
  if (data.conversation_id) {
    updateConversationBadge(data.conversation_id, data.count);
  }
  
  // Update total unread count badge
  if (data.total_unread_count !== undefined) {
    updateTotalUnreadBadge(data.total_unread_count);
  }
});

// Listen for new messages
userChannel.listen('.message.sent', (message) => {
  console.log('New message:', message);
  // Update conversation list
  // Show notification
  // Play sound if needed
});

// Listen for conversation updates
userChannel.listen('.conversation.created', (conversation) => {
  console.log('New conversation:', conversation);
  // Add to conversation list
});

userChannel.listen('.conversation.updated', (conversation) => {
  console.log('Conversation updated:', conversation);
  // Update conversation in list
});

// Subscribe to specific conversation when viewing
function subscribeToConversation(conversationId) {
  echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (message) => {
      // Add message to current conversation view
      addMessageToView(message);
    });
}

// Fetch total unread count on app load
async function initializeMessaging() {
  try {
    const response = await fetch('/api/messaging/messages/unread-count', {
      headers: {
        'Authorization': `Bearer ${getToken()}`
      }
    });
    const data = await response.json();
    updateTotalUnreadBadge(data.count);
  } catch (error) {
    console.error('Failed to fetch unread count:', error);
  }
}

// Mark conversation as read when viewing
async function markConversationAsRead(conversationId) {
  try {
    await fetch(`/api/messaging/conversations/${conversationId}/read`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${getToken()}`
      }
    });
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
}
```

---

## Summary

### Key Endpoints

1. **`GET /api/messaging/messages/unread-count`** - Get total unread messages count
2. **`GET /api/messaging/conversations/{conversation}/unread-count`** - Get unread count for specific conversation
3. **`POST /api/messaging/conversations/{conversation}/read`** - Mark conversation as read

### Key Events

1. **`.unread.updated`** - Broadcasted when unread counts change
   - Includes `count` (conversation-specific) and `total_unread_count` (optional)
   - Broadcasted to all participants when messages are sent
   - Broadcasted when conversations are marked as read

2. **`.message.sent`** - Broadcasted when new messages are sent
   - Available on both `user.{userId}.messages` and `conversation.{id}` channels

### Important Notes

- When a user sends a message, their unread count for that conversation is automatically cleared (set to 0)
- Unread counts only include messages from other users
- The `unread.updated` event includes both conversation-specific and total counts
- All events are broadcasted on private channels requiring authentication

