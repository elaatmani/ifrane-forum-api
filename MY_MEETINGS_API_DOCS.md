# My Meetings API Documentation

## Base URL
All endpoints are prefixed with `/api/meetings`

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
| GET | `/api/meetings` | List user's meetings |
| GET | `/api/meetings/{meetingId}` | Get meeting details |
| POST | `/api/meetings/{meetingId}/accept` | Accept a meeting invitation |
| POST | `/api/meetings/{meetingId}/decline` | Decline a meeting invitation |
| POST | `/api/meetings/{meetingId}/start` | Start a meeting (organizer only) |
| POST | `/api/meetings/{meetingId}/complete` | Complete a meeting (organizer only) |

---

## 1. List User's Meetings

**Endpoint:** `GET /api/meetings`

**Description:** Retrieves all meetings where the authenticated user is:
- The organizer (`organizer_id`)
- The direct participant (`user_id` for member-to-member meetings)
- A participant through the `participants` table

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by status: `pending`, `accepted`, `declined`, `cancelled`, `in_progress`, `completed` | `?status=accepted` |
| `type` | string | Filter by type: `member_to_member`, `member_to_company` | `?type=member_to_member` |
| `upcoming` | boolean | Filter for upcoming meetings only (scheduled_at > now AND status in ['pending', 'accepted']) | `?upcoming=true` |
| `page` | integer | Page number for pagination (default: 1) | `?page=2` |

### Example Requests

```bash
# Get all meetings
GET /api/meetings

# Get only accepted meetings
GET /api/meetings?status=accepted

# Get upcoming meetings
GET /api/meetings?upcoming=true

# Get upcoming member-to-member meetings
GET /api/meetings?type=member_to_member&upcoming=true

# Get accepted meetings with pagination
GET /api/meetings?status=accepted&page=1
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "a07cfc0d-e410-478c-a894-f25857a00311",
        "title": "Project Discussion",
        "description": "Discussing the new project requirements",
        "meeting_type": "member_to_member",
        "status": "accepted",
        "scheduled_at": "2024-01-20T10:00:00.000000Z",
        "duration_minutes": 60,
        "timezone": "UTC",
        "location": "Virtual Meeting",
        "organizer_id": 1,
        "user_id": 2,
        "company_id": null,
        "whereby_meeting_id": "whereby_meeting_123",
        "room_url": "https://subdomain.whereby.com/room123",
        "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
        "created_at": "2024-01-15T08:00:00.000000Z",
        "updated_at": "2024-01-15T08:00:00.000000Z",
        "organizer": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "profile_image": "https://example.com/profile.jpg"
        },
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com",
          "profile_image": "https://example.com/profile2.jpg"
        },
        "company": null,
        "participants": [
          {
            "id": "uuid",
            "meeting_id": "a07cfc0d-e410-478c-a894-f25857a00311",
            "user_id": 1,
            "role": "organizer",
            "status": "accepted",
            "created_at": "2024-01-15T08:00:00.000000Z",
            "user": {
              "id": 1,
              "name": "John Doe",
              "email": "john@example.com"
            }
          },
          {
            "id": "uuid",
            "meeting_id": "a07cfc0d-e410-478c-a894-f25857a00311",
            "user_id": 2,
            "role": "attendee",
            "status": "accepted",
            "created_at": "2024-01-15T08:00:00.000000Z",
            "user": {
              "id": 2,
              "name": "Jane Smith",
              "email": "jane@example.com"
            }
          }
        ]
      }
    ],
    "first_page_url": "http://localhost/api/meetings?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/meetings?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://localhost/api/meetings?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "next_page_url": null,
    "path": "http://localhost/api/meetings",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | string (UUID) | Meeting unique identifier |
| `title` | string | Meeting title |
| `description` | string | Meeting description |
| `meeting_type` | string | Type: `member_to_member` or `member_to_company` |
| `status` | string | Status: `pending`, `accepted`, `declined`, `cancelled`, `in_progress`, `completed` |
| `scheduled_at` | datetime | Scheduled date and time (ISO 8601) |
| `duration_minutes` | integer | Meeting duration in minutes |
| `timezone` | string | Timezone |
| `location` | string | Meeting location |
| `organizer_id` | integer | ID of the meeting organizer |
| `user_id` | integer | ID of the other user (for member-to-member) |
| `company_id` | integer | ID of the company (for member-to-company) |
| `whereby_meeting_id` | string | Whereby meeting ID |
| `room_url` | string | Whereby room URL for participants |
| `host_room_url` | string | Whereby room URL for host/organizer |
| `organizer` | object | Organizer user details (includes `profile_image`) |
| `user` | object | Other user details (for member-to-member, includes `profile_image`) |
| `company` | object | Company details (for member-to-company) |
| `participants` | array | Array of meeting participants with their status |

---

## 2. Get Meeting Details

**Endpoint:** `GET /api/meetings/{meetingId}`

**Description:** Retrieves detailed information about a specific meeting. The authenticated user must be either the organizer or a participant.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | string (UUID) | The meeting ID |

### Example Request

```bash
GET /api/meetings/a07cfc0d-e410-478c-a894-f25857a00311
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "a07cfc0d-e410-478c-a894-f25857a00311",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements",
    "meeting_type": "member_to_member",
    "status": "accepted",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer_id": 1,
    "user_id": 2,
    "company_id": null,
    "whereby_meeting_id": "whereby_meeting_123",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
    "created_at": "2024-01-15T08:00:00.000000Z",
    "updated_at": "2024-01-15T08:00:00.000000Z",
    "organizer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "profile_image": "https://example.com/profile.jpg"
    },
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "profile_image": "https://example.com/profile2.jpg"
    },
    "company": null,
    "participants": [
      {
        "id": "uuid",
        "meeting_id": "a07cfc0d-e410-478c-a894-f25857a00311",
        "user_id": 1,
        "role": "organizer",
        "status": "accepted",
        "created_at": "2024-01-15T08:00:00.000000Z",
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      },
      {
        "id": "uuid",
        "meeting_id": "a07cfc0d-e410-478c-a894-f25857a00311",
        "user_id": 2,
        "role": "attendee",
        "status": "accepted",
        "created_at": "2024-01-15T08:00:00.000000Z",
        "user": {
          "id": 2,
          "name": "Jane Smith",
          "email": "jane@example.com"
        }
      }
    ]
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "You do not have access to this meeting"
}
```

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Meeting] {meetingId}"
}
```

---

## 3. Accept Meeting Invitation

**Endpoint:** `POST /api/meetings/{meetingId}/accept`

**Description:** Accepts a meeting invitation. Only participants can accept meetings.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | string (UUID) | The meeting ID |

### Example Request

```bash
POST /api/meetings/a07cfc0d-e410-478c-a894-f25857a00311/accept
```

**Request Body:** None required

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "a07cfc0d-e410-478c-a894-f25857a00311",
    "title": "Project Discussion",
    "status": "accepted",
    "organizer": {...},
    "user": {...},
    "company": {...},
    "participants": [
      {
        "id": "uuid",
        "user_id": 2,
        "status": "accepted",
        "user": {...}
      }
    ]
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "You are not a participant of this meeting"
}
```

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Meeting] {meetingId}"
}
```

### Notes

- When a meeting is accepted, the participant's status in the `participants` table is updated to `accepted`
- If all required participants accept, the meeting status may change to `accepted`
- Notifications are sent to the organizer when a participant accepts

---

## 4. Decline Meeting Invitation

**Endpoint:** `POST /api/meetings/{meetingId}/decline`

**Description:** Declines a meeting invitation. Only participants can decline meetings.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | string (UUID) | The meeting ID |

### Request Body (Optional)

```json
{
  "reason": "I have a conflicting appointment"
}
```

### Example Request

```bash
POST /api/meetings/a07cfc0d-e410-478c-a894-f25857a00311/decline
Content-Type: application/json

{
  "reason": "I have a conflicting appointment"
}
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "a07cfc0d-e410-478c-a894-f25857a00311",
    "title": "Project Discussion",
    "status": "declined",
    "organizer": {...},
    "user": {...},
    "company": {...},
    "participants": [
      {
        "id": "uuid",
        "user_id": 2,
        "status": "declined",
        "user": {...}
      }
    ]
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "You are not a participant of this meeting"
}
```

### Notes

- When a meeting is declined, the participant's status in the `participants` table is updated to `declined`
- The meeting status may change to `declined` if a required participant declines
- Notifications are sent to the organizer when a participant declines
- The `reason` field is optional but recommended for better communication

---

## 5. Start Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/start`

**Description:** Starts a meeting. Only the organizer can start a meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | string (UUID) | The meeting ID |

### Example Request

```bash
POST /api/meetings/a07cfc0d-e410-478c-a894-f25857a00311/start
```

**Request Body:** None required

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "a07cfc0d-e410-478c-a894-f25857a00311",
    "title": "Project Discussion",
    "status": "in_progress",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
    "organizer": {...},
    "participants": [...]
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Only the organizer can start the meeting"
}
```

### Notes

- Only the organizer can start a meeting
- The meeting status changes from `accepted` to `in_progress`
- A Whereby meeting room is created if not already created
- Notifications are sent to all participants when the meeting starts
- The `room_url` and `host_room_url` are provided in the response

---

## 6. Complete Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/complete`

**Description:** Marks a meeting as completed. Only the organizer can complete a meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | string (UUID) | The meeting ID |

### Example Request

```bash
POST /api/meetings/a07cfc0d-e410-478c-a894-f25857a00311/complete
```

**Request Body:** None required

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "a07cfc0d-e410-478c-a894-f25857a00311",
    "title": "Project Discussion",
    "status": "completed",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "organizer": {...},
    "participants": [...]
  }
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Only the organizer can complete the meeting"
}
```

### Notes

- Only the organizer can complete a meeting
- The meeting status changes from `in_progress` to `completed`
- Notifications are sent to all participants when the meeting is completed
- The meeting can only be completed if it's currently `in_progress`

---

## Meeting Status Flow

```
pending → accepted → in_progress → completed
   ↓
declined
   ↓
cancelled
```

### Status Descriptions

| Status | Description |
|--------|-------------|
| `pending` | Meeting created, waiting for participants to accept |
| `accepted` | All required participants have accepted |
| `declined` | One or more required participants declined |
| `cancelled` | Meeting was cancelled by organizer |
| `in_progress` | Meeting has started |
| `completed` | Meeting has been completed |

---

## Participant Status

| Status | Description |
|--------|-------------|
| `invited` | Participant has been invited but not responded |
| `accepted` | Participant has accepted the invitation |
| `declined` | Participant has declined the invitation |

---

## Frontend Integration Examples

### JavaScript/TypeScript

```typescript
// Get all meetings
async function getMyMeetings(filters?: {
  status?: string;
  type?: string;
  upcoming?: boolean;
  page?: number;
}) {
  const params = new URLSearchParams();
  if (filters?.status) params.append('status', filters.status);
  if (filters?.type) params.append('type', filters.type);
  if (filters?.upcoming) params.append('upcoming', 'true');
  if (filters?.page) params.append('page', filters.page.toString());

  const response = await fetch(`/api/meetings?${params}`, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
  });

  return await response.json();
}

// Get meeting details
async function getMeetingDetails(meetingId: string) {
  const response = await fetch(`/api/meetings/${meetingId}`, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
  });

  return await response.json();
}

// Accept meeting
async function acceptMeeting(meetingId: string) {
  const response = await fetch(`/api/meetings/${meetingId}/accept`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
  });

  return await response.json();
}

// Decline meeting
async function declineMeeting(meetingId: string, reason?: string) {
  const response = await fetch(`/api/meetings/${meetingId}/decline`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ reason }),
  });

  return await response.json();
}

// Start meeting
async function startMeeting(meetingId: string) {
  const response = await fetch(`/api/meetings/${meetingId}/start`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
  });

  return await response.json();
}

// Complete meeting
async function completeMeeting(meetingId: string) {
  const response = await fetch(`/api/meetings/${meetingId}/complete`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${getToken()}`,
      'Content-Type': 'application/json',
    },
  });

  return await response.json();
}

// Usage examples
const meetings = await getMyMeetings({ status: 'accepted', upcoming: true });
const meeting = await getMeetingDetails('a07cfc0d-e410-478c-a894-f25857a00311');
await acceptMeeting('a07cfc0d-e410-478c-a894-f25857a00311');
await declineMeeting('a07cfc0d-e410-478c-a894-f25857a00311', 'Busy');
await startMeeting('a07cfc0d-e410-478c-a894-f25857a00311');
await completeMeeting('a07cfc0d-e410-478c-a894-f25857a00311');
```

### React Hook Example

```typescript
import { useState, useEffect } from 'react';

function useMyMeetings(filters?: {
  status?: string;
  type?: string;
  upcoming?: boolean;
}) {
  const [meetings, setMeetings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchMeetings() {
      try {
        setLoading(true);
        const data = await getMyMeetings(filters);
        setMeetings(data.data.data);
      } catch (err) {
        setError(err);
      } finally {
        setLoading(false);
      }
    }

    fetchMeetings();
  }, [filters?.status, filters?.type, filters?.upcoming]);

  return { meetings, loading, error };
}

// Usage in component
function MyMeetingsList() {
  const { meetings, loading, error } = useMyMeetings({ 
    status: 'accepted',
    upcoming: true 
  });

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div>
      {meetings.map(meeting => (
        <div key={meeting.id}>
          <h3>{meeting.title}</h3>
          <p>{meeting.scheduled_at}</p>
          <button onClick={() => acceptMeeting(meeting.id)}>
            Accept
          </button>
        </div>
      ))}
    </div>
  );
}
```

---

## Error Handling

All endpoints follow a consistent error response format:

```json
{
  "success": false,
  "message": "Error message description"
}
```

### Common HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created (for POST requests) |
| 400 | Bad Request (validation errors) |
| 401 | Unauthorized (missing or invalid token) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found (resource doesn't exist) |
| 500 | Internal Server Error |

---

## Real-time Updates

Meetings support real-time updates via Pusher. Listen for events on the `user.{userId}.messages` channel:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

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

// Listen for meeting events
echo.private(`user.${userId}.messages`)
  .listen('.meeting.created', (event) => {
    // New meeting created
    console.log('New meeting:', event);
  })
  .listen('.meeting.accepted', (event) => {
    // Meeting was accepted
    console.log('Meeting accepted:', event);
  })
  .listen('.meeting.declined', (event) => {
    // Meeting was declined
    console.log('Meeting declined:', event);
  })
  .listen('.meeting.started', (event) => {
    // Meeting started
    console.log('Meeting started:', event);
  })
  .listen('.meeting.completed', (event) => {
    // Meeting completed
    console.log('Meeting completed:', event);
  });
```

---

## Notes

1. **Pagination**: The list endpoint returns 20 meetings per page by default
2. **Ordering**: Meetings are ordered by `scheduled_at` in descending order (newest first)
3. **Access Control**: Users can only access meetings where they are the organizer or a participant
4. **Profile Images**: Organizer and user objects include `profile_image` URLs
5. **Whereby Integration**: Meeting rooms are created automatically when meetings are started
6. **Notifications**: All meeting actions trigger real-time notifications to relevant participants

