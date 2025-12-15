# How Video Calling Works - Complete Technical Explanation

This document provides a comprehensive explanation of how the video calling feature is implemented in this Laravel backend application.

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Core Components](#core-components)
3. [Call Lifecycle Flow](#call-lifecycle-flow)
4. [Room Management](#room-management)
5. [Whereby Integration](#whereby-integration)
6. [Real-time Broadcasting](#real-time-broadcasting)
7. [Database Schema](#database-schema)
8. [API Endpoints](#api-endpoints)
9. [Security & Authorization](#security--authorization)
10. [Auto-cleanup System](#auto-cleanup-system)
11. [Frontend Integration Guide](#frontend-integration-guide)

---

## Architecture Overview

The video calling system is built on a **three-layer architecture**:

1. **API Layer** (`app/Http/Controllers/API/`)
   - `VideoCallController` - Handles call lifecycle operations
   - `VideoCallRoomController` - Manages room operations

2. **Service Layer** (`app/Services/`)
   - `VideoCallService` - Core business logic for calls and rooms
   - `WherebyService` - Integration with Whereby API for meeting rooms
   - `MessagingService` - Integration with messaging system

3. **Data Layer** (`app/Models/`)
   - `VideoCall` - Represents a call instance
   - `VideoCallRoom` - Represents a meeting room
   - `VideoCallParticipant` - Tracks participant status

---

## Core Components

### 1. VideoCallService

The `VideoCallService` is the heart of the system. It orchestrates all video call operations:

**Key Methods:**
- `initiateCall()` - Creates a new call and room
- `acceptCall()` - Accepts an incoming call
- `rejectCall()` - Rejects a call
- `endCall()` - Ends an active call
- `createRoom()` - Creates a Whereby meeting room
- `joinRoom()` / `leaveRoom()` - Participant management
- `expireExpiredCalls()` - Auto-cleanup of expired calls

**How it works:**
```php
// When initiating a call:
1. Creates a Whereby meeting via WherebyService
2. Creates a VideoCallRoom record in database
3. Creates a VideoCall record with status 'initiated'
4. Adds conversation participants to the room
5. Broadcasts VideoCallInitiated event
```

### 2. WherebyService

Handles integration with Whereby's video conferencing API:

**Features:**
- **Real API Mode**: When `WHEREBY_API_KEY` is configured, makes actual API calls
- **Mock Mode**: Falls back to mock implementation for development
- **Room Creation**: Creates meeting rooms with unique URLs
- **URL Types**: 
  - `room_url` - Full participant access (camera/mic enabled)
  - `host_room_url` - Host with admin controls

**Configuration:**
```env
WHEREBY_API_KEY=your_api_key_here
WHEREBY_SUBDOMAIN=your_subdomain
```

### 3. Models

#### VideoCall Model
- **Status States**: `initiated`, `ringing`, `accepted`, `rejected`, `ended`, `missed`
- **Call Types**: `video`, `voice`
- **Key Methods**: `accept()`, `reject()`, `end()`, `markAsMissed()`
- **Auto-expiration**: Calls expire after 60 seconds if not answered

#### VideoCallRoom Model
- **Status States**: `active`, `ended`
- **Relationships**: 
  - Belongs to `Conversation`
  - Has many `VideoCall` instances
  - Has many `VideoCallParticipant` records
- **Key Methods**: `addParticipant()`, `markParticipantJoined()`, `endRoom()`

#### VideoCallParticipant Model
- **Status States**: `invited`, `joined`, `left`
- **Tracks**: Join/leave times, participant status

---

## Call Lifecycle Flow

### Step-by-Step: Initiating a Call

```
1. User clicks "Start Video Call" in conversation
   ↓
2. Frontend sends POST /api/video-calls/calls/initiate
   {
     "conversation_id": "uuid",
     "call_type": "video",
     "metadata": { "expires_at": "..." }
   }
   ↓
3. VideoCallController::initiate()
   - Validates request
   - Checks user has access to conversation
   ↓
4. VideoCallService::initiateCall()
   ↓
5. VideoCallService::createRoom()
   - Calls WherebyService::createMeeting()
   - Creates VideoCallRoom in database
   - Adds initiator as first participant
   ↓
6. Creates VideoCall record (status: 'initiated')
   ↓
7. Adds all conversation participants to room
   ↓
8. Broadcasts VideoCallInitiated event
   ↓
9. Returns response with call_id, room_id, room_url
```

### Step-by-Step: Accepting a Call

```
1. Recipient receives VideoCallInitiated event via Pusher
   ↓
2. Frontend shows incoming call UI
   ↓
3. User clicks "Accept"
   ↓
4. Frontend sends POST /api/video-calls/calls/{callId}/accept
   ↓
5. VideoCallController::accept()
   - Validates call exists and is in acceptable state
   - Checks user has access
   ↓
6. VideoCallService::acceptCall()
   - Updates VideoCall status to 'accepted'
   - Marks participant as 'joined' in room
   ↓
7. Broadcasts VideoCallAccepted event
   ↓
8. Frontend receives event and opens Whereby room URL
```

### Step-by-Step: Ending a Call

```
1. User clicks "End Call"
   ↓
2. Frontend sends POST /api/video-calls/calls/{callId}/end
   {
     "duration": 300,
     "reason": "ended_by_user"
   }
   ↓
3. VideoCallService::endCall()
   - Calculates duration if not provided
   - Updates VideoCall status to 'ended'
   - Ends the VideoCallRoom
   - Marks all participants as 'left'
   ↓
4. Broadcasts VideoCallEnded event
   ↓
5. Frontend closes video call UI
```

---

## Room Management

### Room Creation Process

1. **Whereby Meeting Creation**
   ```php
   WherebyService::createMeeting([
       'roomNamePrefix' => 'foodshow',
       'roomNamePattern' => 'human-short',
       'endDate' => now()->addHours(24)->toISOString()
   ])
   ```
   Returns:
   - `meeting_id` - Whereby's meeting identifier
   - `room_url` - Participant URL (full access)
   - `host_room_url` - Host URL (with controls)

2. **Database Room Creation**
   ```php
   VideoCallRoom::create([
       'conversation_id' => $conversation->id,
       'whereby_meeting_id' => $wherebyResult['meeting_id'],
       'room_url' => $wherebyResult['room_url'],
       'host_room_url' => $wherebyResult['host_room_url'],
       'call_type' => 'video',
       'status' => 'active',
       'expires_at' => now()->addMinutes(30)
   ])
   ```

### Participant Management

**Adding Participants:**
- When a room is created, all conversation participants are automatically added
- Status starts as `invited`
- When they join, status changes to `joined`

**Joining a Room:**
```php
POST /api/video-calls/rooms/{roomId}/join
```
- Checks user has access to conversation
- Adds participant if not already added
- Marks participant as `joined`
- Broadcasts `VideoCallParticipantJoined` event

**Leaving a Room:**
```php
POST /api/video-calls/rooms/{roomId}/leave
```
- Marks participant as `left`
- Broadcasts `VideoCallParticipantLeft` event

---

## Whereby Integration

### How Whereby Works

Whereby is a video conferencing infrastructure provider. The system integrates with their API to:

1. **Create Meeting Rooms**: Generate unique meeting URLs
2. **Manage Access**: Provide different URLs for hosts vs participants
3. **Handle Expiration**: Set room expiration times

### Integration Flow

#### High-Level Flow Diagram

```
┌─────────────────┐
│ VideoCallService│
└────────┬────────┘
         │
         │ createMeeting()
         ▼
┌─────────────────┐
│ WherebyService  │
└────────┬────────┘
         │
         ├─→ API Key Configured?
         │   ├─→ YES: Call Whereby API
         │   │   └─→ POST https://api.whereby.dev/v1/meetings
         │   │
         │   └─→ NO: Use Mock Implementation
         │       └─→ Generate fake URLs for development
         │
         ▼
┌─────────────────┐
│ Whereby API     │
│ Response        │
└─────────────────┘
```

#### Detailed Request Flow

**Step 1: Service Initialization**
```php
// WherebyService constructor
public function __construct()
{
    $this->apiKey = config('services.whereby.api_key');
    $this->subdomain = config('services.whereby.subdomain');
    $this->baseUrl = 'https://api.whereby.dev/v1';
}
```

**Step 2: Configuration Check**
```php
// In createMeeting() method
if (empty($this->apiKey)) {
    Log::info('No Whereby API key configured, using mock implementation');
    return $this->createMockMeeting($options);
}
```

**Step 3: API Request Preparation**
```php
$requestPayload = [
    'roomNamePrefix' => $options['roomNamePrefix'] ?? 'foodshow',
    'roomNamePattern' => $options['roomNamePattern'] ?? 'human-short',
    'roomMode' => $options['roomMode'] ?? 'normal',
    'endDate' => $options['endDate'] ?? now()->addHours(24)->toISOString(),
    'fields' => ['hostRoomUrl', 'viewerRoomUrl', 'meetingId']
];
```

**Step 4: HTTP Request Execution**
```php
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . $this->apiKey,
    'Content-Type' => 'application/json'
])->post($this->baseUrl . '/meetings', $requestPayload);
```

**Step 5: Response Processing**
```php
if ($response->successful()) {
    $data = $response->json();
    return [
        'success' => true,
        'meeting_id' => $data['meetingId'],
        'room_url' => $data['roomUrl'],
        'host_room_url' => $data['hostRoomUrl'],
        'room_name' => $data['roomName']
    ];
}
```

#### Complete Integration Sequence

```
1. User initiates call
   ↓
2. VideoCallService::initiateCall()
   ↓
3. VideoCallService::createRoom()
   ↓
4. WherebyService::createMeeting()
   ├─→ Check API key exists?
   │   ├─→ NO: createMockMeeting()
   │   │   └─→ Return mock data
   │   │
   │   └─→ YES: Prepare API request
   │       ├─→ Build request payload
   │       ├─→ Set headers (Authorization, Content-Type)
   │       ├─→ POST to https://api.whereby.dev/v1/meetings
   │       │
   │       ├─→ Success (200)?
   │       │   ├─→ YES: Parse response
   │       │   │   ├─→ Extract meetingId
   │       │   │   ├─→ Extract roomUrl
   │       │   │   ├─→ Extract hostRoomUrl
   │       │   │   └─→ Return structured data
   │       │   │
   │       │   └─→ NO: Handle error
   │       │       ├─→ Log error details
   │       │       ├─→ Check error type
   │       │       │   ├─→ 401: Invalid API key
   │       │       │   ├─→ 400: Bad request
   │       │       │   ├─→ 429: Rate limited
   │       │       │   └─→ 500: Server error
   │       │       └─→ Fallback to mock
   │       │
   │       └─→ Exception caught?
   │           ├─→ YES: Log exception
   │           └─→ Fallback to mock
   ↓
5. VideoCallRoom::create() with Whereby data
   ↓
6. Return room data to VideoCallService
   ↓
7. Broadcast VideoCallInitiated event
```

### API Request Structure

#### Request Headers
```http
POST /v1/meetings HTTP/1.1
Host: api.whereby.dev
Authorization: Bearer {WHEREBY_API_KEY}
Content-Type: application/json
```

#### Request Body
```json
{
  "roomNamePrefix": "foodshow",
  "roomNamePattern": "human-short",
  "roomMode": "normal",
  "endDate": "2024-01-15T10:30:00.000Z",
  "fields": [
    "hostRoomUrl",
    "viewerRoomUrl",
    "meetingId"
  ]
}
```

#### Request Parameters Explained

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `roomNamePrefix` | string | No | Prefix for room name (default: "foodshow") |
| `roomNamePattern` | string | No | Pattern for room name generation ("human-short", "uuid", etc.) |
| `roomMode` | string | No | Room mode: "normal", "group", "webinar" (default: "normal") |
| `endDate` | ISO 8601 | Yes | When the meeting room expires |
| `fields` | array | No | Which fields to return in response |

#### Successful Response (200 OK)
```json
{
  "meetingId": "12345678-abcd-1234-abcd-123456789abc",
  "roomName": "foodshow-happy-dog",
  "roomUrl": "https://subdomain.whereby.com/foodshow-happy-dog",
  "hostRoomUrl": "https://subdomain.whereby.com/foodshow-happy-dog?host=true",
  "viewerRoomUrl": "https://subdomain.whereby.com/foodshow-happy-dog?viewer=true",
  "startDate": "2024-01-15T10:00:00.000Z",
  "endDate": "2024-01-15T10:30:00.000Z"
}
```

#### Response Fields Mapping

| Whereby Field | Our System Field | Usage |
|---------------|------------------|-------|
| `meetingId` | `whereby_meeting_id` | Stored in `video_call_rooms` table |
| `roomUrl` | `room_url` | Main participant URL (camera/mic enabled) |
| `hostRoomUrl` | `host_room_url` | Host URL with admin controls |
| `viewerRoomUrl` | (not stored) | Spectator-only URL (optional) |
| `endDate` | `expires_at` | Room expiration timestamp |

### Error Handling & Fallback Mechanisms

#### Error Response Codes

**401 Unauthorized**
```json
{
  "error": "Invalid or expired API key"
}
```
**Action**: Log error, fallback to mock mode

**400 Bad Request**
```json
{
  "error": "Invalid request parameters",
  "details": {
    "endDate": "endDate is required"
  }
}
```
**Action**: Log validation errors, retry with corrected parameters

**429 Too Many Requests**
```json
{
  "error": "Rate limit exceeded",
  "retryAfter": 60
}
```
**Action**: Implement exponential backoff, queue request

**500 Internal Server Error**
```json
{
  "error": "Internal server error"
}
```
**Action**: Log error, fallback to mock mode

#### Fallback Strategy

```php
try {
    // Attempt real API call
    $response = Http::withHeaders([...])->post(...);
    
    if ($response->successful()) {
        return $this->parseResponse($response);
    }
    
    // API call failed - fallback to mock
    Log::warning('Whereby API failed, using mock', [
        'status' => $response->status(),
        'error' => $response->body()
    ]);
    
    return $this->createMockMeeting($options);
    
} catch (\Exception $e) {
    // Exception caught - fallback to mock
    Log::error('Whereby API exception', [
        'message' => $e->getMessage()
    ]);
    
    return $this->createMockMeeting($options);
}
```

### Mock Mode (Development)

#### When Mock Mode Activates

1. **No API Key Configured**
   ```env
   # WHEREBY_API_KEY not set in .env
   ```

2. **API Call Fails**
   - Network errors
   - API errors (401, 500, etc.)
   - Timeout exceptions

3. **Explicit Development Mode**
   ```php
   if (app()->environment('local')) {
       return $this->createMockMeeting($options);
   }
   ```

#### Mock Implementation Details

```php
protected function createMockMeeting(array $options = [])
{
    $roomName = ($options['roomNamePrefix'] ?? 'foodshow') . '-' . Str::random(8);
    $subdomain = $this->subdomain ?: 'demo';
    
    return [
        'success' => true,
        'meeting_id' => 'mock_' . Str::random(16),
        'room_url' => "https://{$subdomain}.whereby.com/{$roomName}",
        'host_room_url' => "https://{$subdomain}.whereby.com/{$roomName}?host=true",
        'room_name' => $roomName
    ];
}
```

#### Mock Mode Benefits

- ✅ **No Whereby Account Required**: Develop without API credentials
- ✅ **No API Costs**: Free development and testing
- ✅ **Predictable Behavior**: Consistent mock responses
- ✅ **Fast Development**: No network latency
- ✅ **Offline Development**: Works without internet

#### Mock Mode Limitations

- ❌ **No Real Video**: Mock URLs don't provide actual video calls
- ❌ **No Testing of Whereby Features**: Can't test Whereby-specific features
- ❌ **Different Behavior**: May behave differently than production

### Production Mode

#### Configuration Requirements

```env
# .env file
WHEREBY_API_KEY=your_actual_api_key_here
WHEREBY_SUBDOMAIN=your_subdomain
```

#### Configuration Loading

```php
// config/services.php
'whereby' => [
    'api_key' => env('WHEREBY_API_KEY'),
    'subdomain' => env('WHEREBY_SUBDOMAIN'),
],
```

#### Production Features

- ✅ **Real Meeting Rooms**: Creates actual Whereby meeting rooms
- ✅ **Real Video Calls**: Users can join and have video calls
- ✅ **Host Controls**: Host URLs provide admin controls
- ✅ **Room Expiration**: Automatic cleanup via Whereby API
- ✅ **Analytics**: Track real meeting usage

#### Production Best Practices

1. **API Key Security**
   - Never commit API keys to version control
   - Use environment variables
   - Rotate keys periodically
   - Use different keys for staging/production

2. **Error Monitoring**
   ```php
   Log::error('Whereby API error', [
       'status' => $response->status(),
       'error' => $response->body(),
       'request' => $requestPayload
   ]);
   ```

3. **Rate Limiting**
   - Whereby has rate limits (check their documentation)
   - Implement request queuing for high traffic
   - Use exponential backoff for retries

4. **Monitoring**
   - Track API call success/failure rates
   - Monitor response times
   - Alert on high error rates

### Rate Limiting & Throttling

#### Whereby API Limits

Whereby API has rate limits (check current documentation):
- **Free Tier**: Limited requests per minute
- **Paid Tier**: Higher limits

#### Implementation Strategy

```php
// Using Laravel's rate limiter
RateLimiter::for('whereby-api', function ($request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

#### Request Queuing

For high-traffic scenarios:

```php
// Queue Whereby API calls
dispatch(new CreateWherebyMeeting($conversationId, $callType))
    ->onQueue('whereby-api');
```

#### Exponential Backoff

```php
$attempts = 0;
$maxAttempts = 3;

while ($attempts < $maxAttempts) {
    try {
        $response = Http::withHeaders([...])->post(...);
        
        if ($response->successful()) {
            return $this->parseResponse($response);
        }
        
        if ($response->status() === 429) {
            $retryAfter = $response->header('Retry-After', 60);
            sleep($retryAfter * pow(2, $attempts)); // Exponential backoff
            $attempts++;
            continue;
        }
        
        break;
        
    } catch (\Exception $e) {
        $attempts++;
        if ($attempts >= $maxAttempts) {
            throw $e;
        }
        sleep(pow(2, $attempts)); // Exponential backoff
    }
}
```

### Integration Testing

#### Testing Mock Mode

```php
// tests/Feature/WherebyServiceTest.php
public function test_creates_mock_meeting_when_no_api_key()
{
    config(['services.whereby.api_key' => null]);
    
    $service = new WherebyService();
    $result = $service->createMeeting();
    
    $this->assertTrue($result['success']);
    $this->assertStringStartsWith('mock_', $result['meeting_id']);
    $this->assertStringContains('whereby.com', $result['room_url']);
}
```

#### Testing Production Mode

```php
public function test_creates_real_meeting_with_api_key()
{
    config(['services.whereby.api_key' => 'test-key']);
    
    Http::fake([
        'api.whereby.dev/v1/meetings' => Http::response([
            'meetingId' => 'test-meeting-id',
            'roomUrl' => 'https://test.whereby.com/room',
            'hostRoomUrl' => 'https://test.whereby.com/room?host=true',
            'roomName' => 'test-room'
        ], 200)
    ]);
    
    $service = new WherebyService();
    $result = $service->createMeeting();
    
    $this->assertTrue($result['success']);
    $this->assertEquals('test-meeting-id', $result['meeting_id']);
}
```

#### Testing Error Scenarios

```php
public function test_falls_back_to_mock_on_api_error()
{
    config(['services.whereby.api_key' => 'invalid-key']);
    
    Http::fake([
        'api.whereby.dev/v1/meetings' => Http::response([
            'error' => 'Invalid API key'
        ], 401)
    ]);
    
    $service = new WherebyService();
    $result = $service->createMeeting();
    
    // Should fallback to mock
    $this->assertTrue($result['success']);
    $this->assertStringStartsWith('mock_', $result['meeting_id']);
}
```

### Troubleshooting Integration Issues

#### Common Issues & Solutions

**Issue 1: "Invalid API key" Error**
```
Error: 401 Unauthorized
```
**Solutions:**
- Verify `WHEREBY_API_KEY` in `.env` file
- Check API key hasn't expired
- Ensure no extra spaces in API key
- Verify key has correct permissions

**Issue 2: "Rate limit exceeded"**
```
Error: 429 Too Many Requests
```
**Solutions:**
- Implement request queuing
- Add exponential backoff
- Check Whereby account limits
- Consider upgrading Whereby plan

**Issue 3: "Network timeout"**
```
Error: Connection timeout
```
**Solutions:**
- Increase HTTP timeout
- Check network connectivity
- Verify Whereby API status
- Implement retry logic

**Issue 4: "Room not found"**
```
Error: Meeting room doesn't exist
```
**Solutions:**
- Check `whereby_meeting_id` in database
- Verify room hasn't expired
- Check Whereby dashboard for room status

#### Debugging Tools

**1. Check API Key Status**
```php
$wherebyService = app(WherebyService::class);
$status = $wherebyService->checkApiKeyStatus();

// Returns:
// - 'not_configured': No API key set
// - 'invalid': API key is invalid
// - 'valid': API key is working
// - 'connection_error': Can't connect to API
```

**2. Get Configuration Status**
```php
$status = $wherebyService->getConfigStatus();
// Returns detailed configuration information
```

**3. Enable Debug Logging**
```env
LOG_LEVEL=debug
```

**4. Test API Connection**
```php
// In tinker or test
$service = app(WherebyService::class);
$result = $service->createMeeting([
    'endDate' => now()->addHours(1)->toISOString()
]);

dd($result);
```

### Integration Best Practices

#### 1. Environment-Based Configuration

```php
// Use different configs per environment
if (app()->environment('production')) {
    // Use real API
    $apiKey = config('services.whereby.api_key');
} else {
    // Use mock in development
    return $this->createMockMeeting($options);
}
```

#### 2. Caching API Responses

```php
// Cache meeting creation for short duration
$cacheKey = "whereby_meeting_{$conversationId}";
$meeting = Cache::remember($cacheKey, 60, function() use ($options) {
    return $this->createMeeting($options);
});
```

#### 3. Async Processing

```php
// For non-critical operations, use queues
dispatch(new CreateWherebyMeetingJob($conversationId))
    ->onQueue('whereby-api');
```

#### 4. Health Checks

```php
// Periodic health check
$schedule->call(function() {
    $service = app(WherebyService::class);
    $status = $service->checkApiKeyStatus();
    
    if ($status['status'] !== 'valid') {
        // Send alert
        Log::critical('Whereby API health check failed', $status);
    }
})->everyFiveMinutes();
```

#### 5. Monitoring & Metrics

```php
// Track API usage
$metrics = [
    'whereby_api_calls_total' => 0,
    'whereby_api_errors_total' => 0,
    'whereby_api_response_time' => 0,
];

// Increment on each call
$metrics['whereby_api_calls_total']++;
```

### Advanced Integration Scenarios

#### Scenario 1: Custom Room Configuration

```php
$options = [
    'roomNamePrefix' => 'custom-prefix',
    'roomNamePattern' => 'uuid', // or 'human-short'
    'roomMode' => 'group', // for group calls
    'endDate' => now()->addHours(2)->toISOString(),
    'fields' => ['hostRoomUrl', 'viewerRoomUrl', 'meetingId']
];

$result = $wherebyService->createMeeting($options);
```

#### Scenario 2: Meeting Room Deletion

```php
// Delete meeting when call ends
public function deleteMeeting($meetingId)
{
    if (empty($this->apiKey)) {
        return ['success' => true]; // Mock mode
    }
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey
    ])->delete($this->baseUrl . '/meetings/' . $meetingId);
    
    return ['success' => $response->successful()];
}
```

#### Scenario 3: Meeting Room Extension

```php
// Extend meeting expiration
public function extendMeeting($meetingId, $newEndDate)
{
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Content-Type' => 'application/json'
    ])->patch($this->baseUrl . '/meetings/' . $meetingId, [
        'endDate' => $newEndDate
    ]);
    
    return $response->successful();
}
```

#### Scenario 4: Batch Room Creation

```php
// Create multiple rooms at once (with rate limiting)
$conversations = Conversation::where(...)->get();

foreach ($conversations as $conversation) {
    // Queue to avoid rate limits
    dispatch(new CreateWherebyMeetingJob($conversation->id))
        ->delay(now()->addSeconds($index * 2)); // Stagger requests
}
```

---

## Real-time Broadcasting

### Event System

The system broadcasts 6 types of events via Pusher:

1. **VideoCallInitiated** - When a call starts
2. **VideoCallAccepted** - When call is accepted
3. **VideoCallRejected** - When call is rejected
4. **VideoCallEnded** - When call ends
5. **VideoCallParticipantJoined** - When someone joins
6. **VideoCallParticipantLeft** - When someone leaves

### Broadcasting Channels

Events are broadcast on multiple channels:

1. **Conversation Channel**: `conversation.{conversation_id}`
   - All participants in the conversation receive events here

2. **User Channels**: `user.{user_id}.messages`
   - Individual user notifications
   - Used for global notification system

3. **Call-Specific Channels**: `video-call.{call_id}`
   - Direct call updates (if implemented)

### Event Payload Example

```json
{
  "call_id": "uuid",
  "room_id": "uuid",
  "conversation_id": "uuid",
  "call_type": "video",
  "status": "initiated",
  "initiated_by": "user_uuid",
  "initiator_name": "John Doe",
  "room_url": "https://subdomain.whereby.com/room-name",
  "expires_at": "2024-01-15T10:30:00Z",
  "created_at": "2024-01-15T10:25:00Z"
}
```

---

## Database Schema

### video_calls Table
```sql
- id (UUID, primary key)
- room_id (UUID, foreign key → video_call_rooms)
- conversation_id (UUID, foreign key → conversations)
- call_type (enum: 'video', 'voice')
- status (enum: 'initiated', 'ringing', 'accepted', 'rejected', 'ended', 'missed')
- initiated_by (UUID, foreign key → users)
- accepted_by (UUID, nullable, foreign key → users)
- accepted_at (timestamp, nullable)
- rejected_at (timestamp, nullable)
- ended_at (timestamp, nullable)
- duration (integer, nullable, seconds)
- end_reason (string, nullable)
- reject_reason (string, nullable)
- created_at, updated_at, deleted_at
```

### video_call_rooms Table
```sql
- id (UUID, primary key)
- conversation_id (UUID, foreign key → conversations)
- whereby_meeting_id (string, Whereby's meeting ID)
- room_url (string, participant URL)
- host_room_url (string, host URL)
- call_type (enum: 'video', 'voice')
- status (enum: 'active', 'ended')
- created_by (UUID, foreign key → users)
- ended_at (timestamp, nullable)
- expires_at (timestamp, nullable)
- created_at, updated_at, deleted_at
```

### video_call_participants Table
```sql
- id (UUID, primary key)
- room_id (UUID, foreign key → video_call_rooms)
- user_id (UUID, foreign key → users)
- joined_at (timestamp, nullable)
- left_at (timestamp, nullable)
- status (enum: 'invited', 'joined', 'left')
- created_at, updated_at
```

---

## API Endpoints

### Call Management Endpoints

#### 1. Initiate Call
```
POST /api/video-calls/calls/initiate
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "conversation_id": "uuid",
  "call_type": "video|voice",
  "metadata": {
    "expires_at": "2024-01-15T10:30:00Z" // optional
  }
}

Response (201):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "room_id": "uuid",
    "participant_room_url": "https://...",
    "expires_at": "2024-01-15T10:30:00Z"
  }
}
```

#### 2. Accept Call
```
POST /api/video-calls/calls/{callId}/accept
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "room_id": "uuid",
    "room_url": "https://...",
    "accepted_at": "2024-01-15T10:25:00Z"
  }
}
```

#### 3. Reject Call
```
POST /api/video-calls/calls/{callId}/reject
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "reason": "declined_by_user|expired|busy" // optional
}

Response (200):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "rejected_at": "2024-01-15T10:25:00Z",
    "reason": "declined_by_user"
  }
}
```

#### 4. End Call
```
POST /api/video-calls/calls/{callId}/end
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "duration": 300, // optional, in seconds
  "reason": "ended_by_user" // optional
}

Response (200):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "ended_at": "2024-01-15T10:30:00Z",
    "duration": 300
  }
}
```

#### 5. Get Call Details
```
GET /api/video-calls/calls/{callId}
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "room_id": "uuid",
    "conversation_id": "uuid",
    "status": "accepted",
    "participants": [...]
  }
}
```

### Room Management Endpoints

#### 1. Create Room
```
POST /api/video-calls/rooms
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "conversation_id": "uuid",
  "call_type": "video|voice"
}

Response (201):
{
  "success": true,
  "data": {
    "call_id": "uuid",
    "room_id": "uuid",
    "participant_room_url": "https://...",
    "whereby_meeting_id": "meeting-id",
    "expires_at": "2024-01-15T10:30:00Z"
  }
}
```

#### 2. Get Room Details
```
GET /api/video-calls/rooms/{roomId}
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "room_id": "uuid",
    "participant_room_url": "https://...",
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
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "joined_at": "2024-01-15T10:25:00Z",
    "participant_count": 2
  }
}
```

#### 4. Leave Room
```
POST /api/video-calls/rooms/{roomId}/leave
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": {
    "left_at": "2024-01-15T10:30:00Z"
  }
}
```

#### 5. End Room
```
POST /api/video-calls/rooms/{roomId}/end
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "duration": 600, // optional
  "reason": "ended_by_user" // optional
}

Response (200):
{
  "success": true,
  "data": {
    "ended_at": "2024-01-15T10:30:00Z",
    "duration": 600
  }
}
```

#### 6. Get Room Participants
```
GET /api/video-calls/rooms/{roomId}/participants
Authorization: Bearer {token}

Response (200):
{
  "success": true,
  "data": [
    {
      "user_id": "uuid",
      "name": "John Doe",
      "joined_at": "2024-01-15T10:25:00Z",
      "status": "joined"
    }
  ]
}
```

---

## Security & Authorization

### Authentication
- All endpoints require `auth:sanctum` middleware
- Bearer token authentication required

### Authorization Checks

1. **Conversation Access**
   ```php
   // User must be a participant in the conversation
   $conversation->users()->where('user_id', $user->id)->exists()
   ```

2. **Room Access**
   ```php
   // User must be in the conversation that owns the room
   $room->conversation->users()->where('user_id', $user->id)->exists()
   ```

3. **Room Management**
   ```php
   // Only room creator can end the room
   $room->created_by === $user->id
   ```

### Validation Rules

- `conversation_id`: Must be valid UUID and exist in database
- `call_type`: Must be 'video' or 'voice'
- `callId` / `roomId`: Must be valid UUIDs
- `status` transitions: Enforced at model level

---

## Auto-cleanup System

### Expired Calls

Calls that are not answered within 60 seconds are automatically marked as `missed`:

```php
VideoCallService::expireExpiredCalls()
```

**Process:**
1. Finds calls with status `initiated` or `ringing`
2. Checks if created more than 60 seconds ago
3. Marks as `missed` with reason `expired`
4. Ends the room if no active calls remain

### Expired Rooms

Rooms that pass their `expires_at` timestamp are automatically ended:

```php
VideoCallService::cleanupExpiredRooms()
```

**Process:**
1. Finds active rooms where `expires_at < now()`
2. Ends all active calls in the room
3. Marks room as `ended`

### Cleanup Command

Manual cleanup can be run via Artisan:

```bash
php artisan video-calls:cleanup
```

This command:
- Expires old calls
- Cleans up expired rooms
- Returns count of cleaned items

**Scheduling:**
Add to `app/Console/Kernel.php`:
```php
$schedule->command('video-calls:cleanup')->everyMinute();
```

---

## Frontend Integration Guide

### 1. Listen for Incoming Calls

```javascript
// Using Laravel Echo
Echo.private(`conversation.${conversationId}`)
    .listen('.video_call.initiated', (event) => {
        console.log('Incoming call:', event);
        
        // Show incoming call UI
        showIncomingCallModal({
            callId: event.call_id,
            roomId: event.room_id,
            callType: event.call_type,
            initiatorName: event.initiator_name,
            roomUrl: event.room_url
        });
    });
```

### 2. Handle Call Acceptance

```javascript
async function acceptCall(callId) {
    try {
        const response = await fetch(`/api/video-calls/calls/${callId}/accept`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Open Whereby room URL
            window.open(data.data.room_url, '_blank');
            
            // Or embed in iframe
            // document.getElementById('video-call-container').src = data.data.room_url;
        }
    } catch (error) {
        console.error('Failed to accept call:', error);
    }
}
```

### 3. Listen for Call State Changes

```javascript
Echo.private(`conversation.${conversationId}`)
    .listen('.video_call.accepted', (event) => {
        console.log('Call accepted:', event);
        // Update UI to show call is active
    })
    .listen('.video_call.rejected', (event) => {
        console.log('Call rejected:', event);
        // Hide incoming call UI
    })
    .listen('.video_call.ended', (event) => {
        console.log('Call ended:', event);
        // Close video call UI
    })
    .listen('.video_call.participant_joined', (event) => {
        console.log('Participant joined:', event);
        // Update participant list
    })
    .listen('.video_call.participant_left', (event) => {
        console.log('Participant left:', event);
        // Update participant list
    });
```

### 4. Initiate a Call

```javascript
async function startVideoCall(conversationId) {
    try {
        const response = await fetch('/api/video-calls/calls/initiate', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                call_type: 'video',
                metadata: {
                    expires_at: new Date(Date.now() + 30 * 60 * 1000).toISOString()
                }
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Open room URL for initiator
            window.open(data.data.participant_room_url, '_blank');
            
            // Show call UI
            showActiveCallUI(data.data);
        }
    } catch (error) {
        console.error('Failed to start call:', error);
    }
}
```

### 5. End a Call

```javascript
async function endCall(callId, duration) {
    try {
        const response = await fetch(`/api/video-calls/calls/${callId}/end`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                duration: duration, // in seconds
                reason: 'ended_by_user'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Close video call UI
            closeVideoCallUI();
        }
    } catch (error) {
        console.error('Failed to end call:', error);
    }
}
```

### 6. Whereby Integration

Whereby provides an embeddable iframe for video calls:

```html
<iframe
    src="https://subdomain.whereby.com/room-name"
    allow="camera; microphone; fullscreen; speaker; display-capture"
    style="width: 100%; height: 600px; border: 0;"
></iframe>
```

Or use Whereby's JavaScript SDK for more control:
```javascript
// Include Whereby SDK
<script src="https://cdn.whereby.com/dist/whereby-embedded-sdk.js"></script>

// Initialize
const room = new WherebyEmbedded('https://subdomain.whereby.com/room-name', {
    onReady: () => {
        console.log('Room ready');
    },
    onLeave: () => {
        console.log('User left');
        // Notify backend
        leaveRoom(roomId);
    }
});
```

---

## Summary

The video calling system provides:

✅ **Complete Call Lifecycle**: Initiate → Accept/Reject → End  
✅ **Room Management**: Create, join, leave, end rooms  
✅ **Participant Tracking**: Real-time participant status  
✅ **Real-time Events**: Pusher broadcasting for instant updates  
✅ **Whereby Integration**: Professional video infrastructure  
✅ **Auto-cleanup**: Expired calls and rooms automatically cleaned  
✅ **Security**: Conversation-based access control  
✅ **Mock Mode**: Development without Whereby account  

The system is designed to be scalable, maintainable, and provides a seamless video calling experience integrated with the messaging system.

