# Call Message Types Examples

This document shows how to use the new call-related message types in the messaging system.

## Available Message Types

### 1. Text Messages
- `text` - Regular text messages

### 2. File Messages  
- `file` - File uploads (images, documents, etc.)

### 3. Call Messages
- `missed_call` - Missed call notifications
- `video_call_request` - Video call requests
- `voice_call_request` - Voice call requests
- `call_ended` - Call ended notifications
- `call_rejected` - Call rejected notifications
- `call_accepted` - Call accepted notifications

### 4. System Messages
- `system` - System notifications

## API Endpoints

### Call-Related Endpoints

```bash
# Send missed call message
POST /api/messaging/conversations/{conversation}/messages/missed-call

# Send video call request
POST /api/messaging/conversations/{conversation}/messages/video-call-request

# Send voice call request  
POST /api/messaging/conversations/{conversation}/messages/voice-call-request

# Send call ended message
POST /api/messaging/conversations/{conversation}/messages/call-ended

# Send call rejected message
POST /api/messaging/conversations/{conversation}/messages/call-rejected

# Send call accepted message
POST /api/messaging/conversations/{conversation}/messages/call-accepted
```

## Usage Examples

### 1. Missed Call Message

```php
// In your controller or service
use App\Helpers\CallMetadataHelper;

$metadata = CallMetadataHelper::missedCall([
    'call_type' => 'video',
    'duration' => 0,
    'reason' => 'user_busy'
]);

$message = $messagingService->sendMissedCallMessage(
    $conversation,
    $sender,
    $metadata
);
```

**API Request:**
```json
POST /api/messaging/conversations/1/messages/missed-call
{
    "metadata": {
        "call_type": "video",
        "duration": 0,
        "reason": "user_busy"
    }
}
```

### 2. Video Call Request

```php
$metadata = CallMetadataHelper::videoCallRequest([
    'request_id' => 'call_12345',
    'expires_at' => now()->addMinutes(5)->toISOString()
]);

$message = $messagingService->sendVideoCallRequest(
    $conversation,
    $sender,
    $metadata
);
```

**API Request:**
```json
POST /api/messaging/conversations/1/messages/video-call-request
{
    "metadata": {
        "request_id": "call_12345",
        "expires_at": "2024-01-15T10:30:00Z"
    }
}
```

### 3. Voice Call Request

```php
$metadata = CallMetadataHelper::voiceCallRequest([
    'request_id' => 'call_67890'
]);

$message = $messagingService->sendVoiceCallRequest(
    $conversation,
    $sender,
    $metadata
);
```

### 4. Call Ended Message

```php
$metadata = CallMetadataHelper::callEnded([
    'call_type' => 'video',
    'duration' => 300, // 5 minutes in seconds
    'reason' => 'ended_by_user'
]);

$message = $messagingService->sendCallEndedMessage(
    $conversation,
    $sender,
    $metadata
);
```

### 5. Call Rejected Message

```php
$metadata = CallMetadataHelper::callRejected([
    'call_type' => 'video',
    'reason' => 'user_busy'
]);

$message = $messagingService->sendCallRejectedMessage(
    $conversation,
    $sender,
    $metadata
);
```

### 6. Call Accepted Message

```php
$metadata = CallMetadataHelper::callAccepted([
    'call_type' => 'video',
    'call_id' => 'active_call_12345'
]);

$message = $messagingService->sendCallAcceptedMessage(
    $conversation,
    $sender,
    $metadata
);
```

## Message Response Format

All call messages return the same response format:

```json
{
    "success": true,
    "data": {
        "id": 123,
        "conversation_id": 1,
        "sender_id": 5,
        "content": "Missed call",
        "message_type": "missed_call",
        "file_url": null,
        "metadata": {
            "call_type": "video",
            "timestamp": "2024-01-15T10:25:00Z",
            "duration": 0,
            "reason": "missed"
        },
        "created_at": "2024-01-15T10:25:00Z",
        "updated_at": "2024-01-15T10:25:00Z",
        "sender": {
            "id": 5,
            "name": "John Doe",
            "profile_image": "path/to/image.jpg"
        },
        "conversation": {
            "id": 1,
            "type": "direct",
            "name": null
        },
        "message_type": "missed_call"
    },
    "message": "Missed call message sent"
}
```

## Message Type Handling

The API returns the `message_type` field which can be used to determine how to handle each message:

```javascript
// Frontend JavaScript - Check message type
function handleMessage(message) {
    switch(message.message_type) {
        case 'text':
            return renderTextMessage(message);
        case 'file':
            return renderFileMessage(message);
        case 'missed_call':
            return renderMissedCallMessage(message);
        case 'video_call_request':
            return renderVideoCallRequest(message);
        case 'voice_call_request':
            return renderVoiceCallRequest(message);
        case 'call_ended':
            return renderCallEndedMessage(message);
        case 'call_rejected':
            return renderCallRejectedMessage(message);
        case 'call_accepted':
            return renderCallAcceptedMessage(message);
        case 'system':
            return renderSystemMessage(message);
        default:
            return renderUnknownMessage(message);
    }
}

// Helper functions to check message types
function isCallMessage(message) {
    return ['missed_call', 'video_call_request', 'voice_call_request', 
            'call_ended', 'call_rejected', 'call_accepted'].includes(message.message_type);
}

function isTextMessage(message) {
    return message.message_type === 'text';
}

function isFileMessage(message) {
    return message.message_type === 'file';
}

function isSystemMessage(message) {
    return message.message_type === 'system';
}
```

## Real-time Broadcasting

All call messages are automatically broadcasted to conversation participants:

```javascript
// Frontend JavaScript
Echo.private(`conversation.${conversationId}`)
    .listen('.message.sent', (e) => {
        console.log('New message:', e);
        
                 // Handle different message types
         switch(e.message_type) {
             case 'text':
                 handleTextMessage(e);
                 break;
             case 'file':
                 handleFileMessage(e);
                 break;
             case 'missed_call':
                 showMissedCallNotification(e);
                 break;
             case 'video_call_request':
                 showVideoCallRequest(e);
                 break;
             case 'voice_call_request':
                 showVoiceCallRequest(e);
                 break;
             case 'call_ended':
                 showCallEndedNotification(e);
                 break;
             case 'call_rejected':
                 showCallRejectedNotification(e);
                 break;
             case 'call_accepted':
                 showCallAcceptedNotification(e);
                 break;
             case 'system':
                 handleSystemMessage(e);
                 break;
         }
    });
```

## Metadata Structure

### Missed Call Metadata
```json
{
    "call_type": "video|voice",
    "timestamp": "2024-01-15T10:25:00Z",
    "duration": 0,
    "reason": "missed|user_busy|no_answer"
}
```

### Call Request Metadata
```json
{
    "call_type": "video|voice",
    "status": "requesting",
    "timestamp": "2024-01-15T10:25:00Z",
    "request_id": "unique_request_id",
    "expires_at": "2024-01-15T10:30:00Z"
}
```

### Call Ended Metadata
```json
{
    "call_type": "video|voice",
    "status": "ended",
    "timestamp": "2024-01-15T10:30:00Z",
    "duration": 300,
    "reason": "ended_by_user|ended_by_system|connection_lost"
}
```

### Call Rejected Metadata
```json
{
    "call_type": "video|voice",
    "status": "rejected",
    "timestamp": "2024-01-15T10:25:00Z",
    "reason": "user_busy|declined|no_answer"
}
```

### Call Accepted Metadata
```json
{
    "call_type": "video|voice",
    "status": "accepted",
    "timestamp": "2024-01-15T10:25:00Z",
    "call_id": "active_call_12345"
}
```

## Helper Methods

The `CallMetadataHelper` class provides convenient methods for creating metadata:

```php
use App\Helpers\CallMetadataHelper;

// Create missed call metadata
$metadata = CallMetadataHelper::missedCall(['call_type' => 'video']);

// Create video call request metadata
$metadata = CallMetadataHelper::videoCallRequest(['request_id' => 'call_123']);

// Check if call request is expired
$isExpired = CallMetadataHelper::isCallRequestExpired($metadata);

// Get call duration
$duration = CallMetadataHelper::getCallDuration($metadata);
```

## Integration with Call System

To integrate with your actual call system:

1. **When a call is initiated:**
   - Send `video_call_request` or `voice_call_request` message
   - Store the `request_id` for tracking

2. **When a call is accepted:**
   - Send `call_accepted` message
   - Start the actual call session

3. **When a call is rejected:**
   - Send `call_rejected` message
   - Clean up any pending call state

4. **When a call ends:**
   - Send `call_ended` message with duration
   - Clean up call session

5. **When a call is missed:**
   - Send `missed_call` message
   - Store for call history

This messaging system provides a complete foundation for call-related notifications and can be easily extended for additional call features. 