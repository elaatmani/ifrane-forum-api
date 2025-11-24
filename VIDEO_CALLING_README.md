# Video Calling Backend Implementation

This document describes the complete video calling backend system implementation for the FoodEshow platform.

## Overview

The video calling system provides real-time video and voice communication capabilities with the following features:

- **Room Management**: Create, join, leave, and end video call rooms
- **Call Lifecycle**: Initiate, accept, reject, and end calls
- **Participant Management**: Track who joins/leaves calls
- **Real-time Events**: Broadcast call events via Pusher
- **Whereby Integration**: Professional video calling infrastructure
- **Auto-cleanup**: Automatic expiration of unanswered calls and expired rooms

## API Endpoints

### Room Management (6 endpoints)

#### 1. Create Room
```
POST /api/video-calls/rooms
```
**Request:**
```json
{
    "conversation_id": "uuid",
    "call_type": "video|voice"
}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "room_id": "uuid",
        "room_url": "whereby-url",
        "whereby_meeting_id": "id",
        "expires_at": "timestamp"
    }
}
```

#### 2. Get Room Details
```
GET /api/video-calls/rooms/{roomId}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "room_id": "uuid",
        "room_url": "url",
        "conversation_id": "uuid",
        "call_type": "video",
        "status": "active",
        "participants": [...]
    }
}
```

#### 3. Join Room
```
POST /api/video-calls/rooms/{roomId}/join
```
**Response:**
```json
{
    "success": true,
    "data": {
        "joined_at": "timestamp",
        "participant_count": 2
    }
}
```

#### 4. Leave Room
```
POST /api/video-calls/rooms/{roomId}/leave
```
**Response:**
```json
{
    "success": true,
    "data": {
        "left_at": "timestamp"
    }
}
```

#### 5. End Room
```
POST /api/video-calls/rooms/{roomId}/end
```
**Request:**
```json
{
    "duration": 600,
    "reason": "ended_by_user"
}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "ended_at": "timestamp",
        "duration": 600
    }
}
```

#### 6. Get Room Participants
```
GET /api/video-calls/rooms/{roomId}/participants
```
**Response:**
```json
{
    "success": true,
    "data": [
        {
            "user_id": "uuid",
            "name": "John",
            "joined_at": "timestamp",
            "status": "joined"
        }
    ]
}
```

### Call Management (5 endpoints)

#### 1. Initiate Call
```
POST /api/video-calls/calls/initiate
```
**Request:**
```json
{
    "conversation_id": "uuid",
    "call_type": "video|voice",
    "metadata": {
        "expires_at": "timestamp"
    }
}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": "uuid",
        "room_id": "uuid",
        "room_url": "url",
        "expires_at": "timestamp"
    }
}
```

#### 2. Accept Call
```
POST /api/video-calls/calls/{callId}/accept
```
**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": "uuid",
        "room_id": "uuid",
        "room_url": "url",
        "accepted_at": "timestamp"
    }
}
```

#### 3. Reject Call
```
POST /api/video-calls/calls/{callId}/reject
```
**Request:**
```json
{
    "reason": "declined_by_user|expired|busy"
}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": "uuid",
        "rejected_at": "timestamp",
        "reason": "declined_by_user"
    }
}
```

#### 4. End Call
```
POST /api/video-calls/calls/{callId}/end
```
**Request:**
```json
{
    "duration": 300,
    "reason": "ended_by_user"
}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": "uuid",
        "ended_at": "timestamp",
        "duration": 300
    }
}
```

#### 5. Get Call Details
```
GET /api/video-calls/calls/{callId}
```
**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": "uuid",
        "room_id": "uuid",
        "conversation_id": "uuid",
        "status": "active",
        "participants": [...]
    }
}
```

## Database Schema

### Tables

1. **`video_call_rooms`** - Stores room information
2. **`video_calls`** - Stores call lifecycle information
3. **`video_call_participants`** - Tracks participant status

### Key Features

- **Auto-expiration**: Calls expire after 60 seconds if not answered
- **Duration tracking**: Automatic calculation of call duration
- **Status management**: Complete call lifecycle tracking
- **Participant tracking**: Real-time participant status updates

## Real-time Events

The system broadcasts the following events via Pusher:

### Event Types

1. **`video_call.initiated`** - When a call is initiated
2. **`video_call.accepted`** - When a call is accepted
3. **`video_call.rejected`** - When a call is rejected
4. **`video_call.ended`** - When a call ends
5. **`video_call.participant_joined`** - When someone joins a room
6. **`video_call.participant_left`** - When someone leaves a room

### Broadcasting Channels

- **`conversation.{id}`** - Conversation-specific events
- **`user.{userId}.messages`** - User-specific notifications
- **`video-call.{id}`** - Call-specific events
- **`video-call-room.{id}`** - Room-specific events

## Whereby Integration

### Configuration

Add these environment variables:
```env
WHEREBY_API_KEY=your_api_key_here
WHEREBY_SUBDOMAIN=your_subdomain
```

### Features

- **Real API Integration**: Uses actual Whereby API when configured
- **Mock Fallback**: Automatic fallback to mock implementation for development
- **Room Creation**: Automatic meeting room creation
- **URL Generation**: Host and viewer room URLs

## Business Logic

### Call Lifecycle

1. **Initiated** → Call is created and waiting for response
2. **Ringing** → Call is being presented to recipient
3. **Accepted** → Call is active and connected
4. **Rejected** → Call was declined or expired
5. **Ended** → Call completed normally
6. **Missed** → Call expired without response

### Auto-cleanup

- **Expired Calls**: Automatically marked as missed after 60 seconds
- **Expired Rooms**: Automatically ended when expiration time is reached
- **Cleanup Command**: Manual cleanup via `php artisan video-calls:cleanup`

## Security & Authorization

### Authentication
- All endpoints require Bearer token authentication
- Uses existing Sanctum middleware

### Authorization
- Users can only access calls in conversations they participate in
- Room creators can manage their rooms
- Participants can join/leave rooms they have access to

### Validation
- Input validation for all endpoints
- UUID validation for IDs
- Enum validation for call types and statuses

## Error Handling

### Standard Response Format
```json
{
    "success": false,
    "message": "Error description",
    "code": "ERROR_CODE"
}
```

### HTTP Status Codes
- **200** - Success
- **201** - Created
- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden
- **404** - Not Found
- **500** - Server Error

## Usage Examples

### Frontend Integration

```javascript
// Listen for video call events
Echo.private(`conversation.${conversationId}`)
    .listen('.video_call.initiated', (e) => {
        console.log('Video call initiated:', e);
        showIncomingCall(e);
    })
    .listen('.video_call.accepted', (e) => {
        console.log('Call accepted:', e);
        startCall(e);
    })
    .listen('.video_call.ended', (e) => {
        console.log('Call ended:', e);
        endCall(e);
    });
```

### API Usage

```bash
# Initiate a video call
curl -X POST /api/video-calls/calls/initiate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"conversation_id": "uuid", "call_type": "video"}'

# Accept a call
curl -X POST /api/video-calls/calls/{callId}/accept \
  -H "Authorization: Bearer {token}"

# Join a room
curl -X POST /api/video-calls/rooms/{roomId}/join \
  -H "Authorization: Bearer {token}"
```

## Maintenance

### Cleanup Command
```bash
# Clean up expired calls and rooms
php artisan video-calls:cleanup
```

### Monitoring
- Check for expired calls and rooms
- Monitor Whereby API usage
- Track call statistics and analytics

## Future Enhancements

1. **Recording**: Add call recording capabilities
2. **Screen Sharing**: Enable screen sharing during calls
3. **File Transfer**: Allow file sharing during calls
4. **Call History**: Detailed call logs and analytics
5. **Multi-party Calls**: Support for group video calls
6. **Call Scheduling**: Pre-scheduled video calls
7. **Integration**: Webhook support for external systems

## Troubleshooting

### Common Issues

1. **Whereby API Errors**: Check API key and subdomain configuration
2. **Broadcasting Issues**: Verify Pusher configuration
3. **Permission Errors**: Ensure user has access to conversation
4. **Expired Calls**: Check auto-cleanup is running

### Debug Mode

Enable debug logging in `.env`:
```env
LOG_LEVEL=debug
```

## Support

For technical support or questions about the video calling system, please refer to the development team or create an issue in the project repository.

