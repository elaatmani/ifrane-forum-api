# Meeting API Endpoints - Frontend Integration Guide

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
| POST | `/api/meetings/member-to-member` | Create member-to-member meeting |
| POST | `/api/meetings/member-to-company` | Create member-to-company meeting |
| GET | `/api/meetings` | List user's meetings |
| GET | `/api/meetings/{meetingId}` | Get meeting details |
| POST | `/api/meetings/{meetingId}/cept



cept` | Accept a meeting invitation |
| POST | `/api/meetings/{meetingId}/decline` | Decline a meeting invitation |
| POST | `/api/meetings/{meetingId}/start` | Start a meeting (organizer only) |
| POST | `/api/meetings/{meetingId}/complete` | Complete a meeting (organizer only) |

---

## 1. Create Member-to-Member Meeting

**Endpoint:** `POST /api/meetings/member-to-member`

**Description:** Creates a meeting between the authenticated user (organizer) and another user.

### Request Payload

```json
{
  "user_id": "integer (required)",
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "scheduled_at": "datetime (required, ISO 8601, must be in future)",
  "duration_minutes": "integer (optional, min:15, max:480)",
  "timezone": "string (optional, max:50)",
  "location": "string (optional, max:255)"
}
```

### Example Request

```json
{
  "user_id": 123,
  "title": "Project Discussion",
  "description": "Discussing the new project requirements",
  "scheduled_at": "2024-01-20T10:00:00Z",
  "duration_minutes": 60,
  "timezone": "UTC",
  "location": "Virtual Meeting"
}
```

### Response (201 Created)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements",
    "meeting_type": "member_to_member",
    "status": "pending",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer_id": 1,
    "user_id": 2,
    "company_id": null,
    "whereby_meeting_id": null,
    "room_url": null,
    "host_room_url": null,
    "organizer": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com"
    },
    "user": {
      "id": "uuid",
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "participants": [
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 1,
        "role": "organizer",
        "status": "accepted",
        "user": {
          "id": 1,
          "name": "John Doe"
        }
      },
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 2,
        "role": "attendee",
        "status": "invited",
        "user": {
          "id": 2,
          "name": "Jane Smith"
        }
      }
    ]
  }
}
```

### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "user_id": ["The user id field is required."],
    "scheduled_at": ["The scheduled at must be a date after now."]
  }
}
```

---

## 2. Create Member-to-Company Meeting

**Endpoint:** `POST /api/meetings/member-to-company`

**Description:** Creates a meeting between the authenticated user (organizer) and a company.

### Request Payload

```json
{
  "company_id": "integer (required)",
  "title": "string (required, max:255)",
  "description": "string (optional)",
  "scheduled_at": "datetime (required, ISO 8601, must be in future)",
  "duration_minutes": "integer (optional, min:15, max:480)",
  "timezone": "string (optional, max:50)",
  "location": "string (optional, max:255)"
}
```

### Example Request

```json
{
  "company_id": 456,
  "title": "Business Proposal",
  "description": "Presenting our business proposal",
  "scheduled_at": "2024-01-20T14:00:00Z",
  "duration_minutes": 90,
  "timezone": "UTC",
  "location": "Virtual Meeting"
}
```

### Response (201 Created)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Business Proposal",
    "description": "Presenting our business proposal",
    "meeting_type": "member_to_company",
    "status": "pending",
    "scheduled_at": "2024-01-20T14:00:00.000000Z",
    "duration_minutes": 90,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer_id": 1,
    "user_id": null,
    "company_id": 456,
    "whereby_meeting_id": null,
    "room_url": null,
    "host_room_url": null,
    "organizer": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com"
    },
    "company": {
      "id": 456,
      "name": "Acme Corp",
      "email": "contact@acme.com"
    },
    "participants": [
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 1,
        "role": "organizer",
        "status": "accepted",
        "user": {
          "id": "uuid",
          "name": "John Doe"
        }
      },
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 2,
        "role": "attendee",
        "status": "invited",
        "user": {
          "id": "uuid",
          "name": "Company Rep"
        }
      }
    ]
  }
}
```

### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "company_id": ["The company id field is required."],
    "scheduled_at": ["The scheduled at must be a date after now."]
  }
}
```

---

## 3. List User's Meetings

**Endpoint:** `GET /api/meetings`

**Description:** Retrieves all meetings where the authenticated user is the organizer, direct participant, or a participant through the participants table.

### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status: `pending`, `accepted`, `declined`, `cancelled`, `in_progress`, `completed` |
| `type` | string | Filter by type: `member_to_member`, `member_to_company` |
| `upcoming` | boolean | Filter for upcoming meetings only (scheduled_at > now AND status in ['pending', 'accepted']) |
| `page` | integer | Page number for pagination (default: 1) |

### Example Request

```
GET /api/meetings?status=accepted&upcoming=true&page=1
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
        "organizer": {
          "id": "uuid",
          "name": "John Doe",
          "email": "john@example.com"
        },
        "user": {
          "id": "uuid",
          "name": "Jane Smith",
          "email": "jane@example.com"
        },
        "company": null,
        "participants": [
          {
            "id": "uuid",
            "meeting_id": "uuid",
            "user_id": 1,
            "role": "organizer",
            "status": "accepted",
            "user": {
              "id": "uuid",
              "name": "John Doe"
            }
          },
          {
            "id": "uuid",
            "meeting_id": "uuid",
            "user_id": 1,
            "role": "attendee",
            "status": "accepted",
            "user": {
              "id": "uuid",
              "name": "Jane Smith"
            }
          }
        ]
      }
    ],
    "first_page_url": "http://example.com/api/meetings?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://example.com/api/meetings?page=1",
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://example.com/api/meetings?page=1",
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
    "path": "http://example.com/api/meetings",
    "per_page": 20,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

## 4. Get Meeting Details

**Endpoint:** `GET /api/meetings/{meetingId}`

**Description:** Retrieves detailed information about a specific meeting. User must be a participant or organizer.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | uuid | The ID of the meeting |

### Example Request

```
GET /api/meetings/123e4567-e89b-12d3-a456-426614174000
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
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
    "accepted_at": "2024-01-15T08:00:00.000000Z",
    "declined_at": null,
    "cancelled_at": null,
    "started_at": null,
    "completed_at": null,
    "organizer": {
      "id": "uuid",
      "name": "John Doe",
      "email": "john@example.com"
    },
    "user": {
      "id": "uuid",
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "company": null,
    "participants": [
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 1,
        "role": "organizer",
        "status": "accepted",
        "user": {
          "id": "uuid",
          "name": "John Doe"
        }
      },
      {
        "id": "uuid",
        "meeting_id": "uuid",
        "user_id": 2,
        "role": "attendee",
        "status": "accepted",
        "user": {
          "id": "uuid",
          "name": "Jane Smith"
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

## 5. Accept Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/accept`

**Description:** Accepts a meeting invitation. User must be a participant of the meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | uuid | The ID of the meeting |

### Request Payload

No payload required.

### Example Request

```
POST /api/meetings/123e4567-e89b-12d3-a456-426614174000/accept
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Project Discussion",
    "status": "accepted",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "whereby_meeting_id": "whereby_meeting_123",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
    "accepted_at": "2024-01-15T08:00:00.000000Z",
    "organizer": { ... },
    "user": { ... },
    "company": null,
    "participants": [ ... ]
  }
}
```

**Note:** When a meeting is accepted and all required participants have accepted, the meeting status changes to `accepted` and a Whereby room is automatically created. The `room_url` and `host_room_url` will be populated in the response.

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "You are not a participant of this meeting"
}
```

---

## 6. Decline Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/decline`

**Description:** Declines a meeting invitation. User must be a participant of the meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | uuid | The ID of the meeting |

### Request Payload

```json
{
  "reason": "string (optional)"
}
```

### Example Request

```json
{
  "reason": "I have a scheduling conflict"
}
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Project Discussion",
    "status": "declined",
    "declined_at": "2024-01-15T08:00:00.000000Z",
    "organizer": { ... },
    "user": { ... },
    "company": null,
    "participants": [ ... ]
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

---

## 7. Start Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/start`

**Description:** Starts a meeting. Only the organizer can start a meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | uuid | The ID of the meeting |

### Request Payload

No payload required.

### Example Request

```
POST /api/meetings/123e4567-e89b-12d3-a456-426614174000/start
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Project Discussion",
    "status": "in_progress",
    "started_at": "2024-01-20T10:00:00.000000Z",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123"
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

---

## 8. Complete Meeting

**Endpoint:** `POST /api/meetings/{meetingId}/complete`

**Description:** Marks a meeting as completed. Only the organizer can complete a meeting.

### Path Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `meetingId` | uuid | The ID of the meeting |

### Request Payload

No payload required.

### Example Request

```
POST /api/meetings/123e4567-e89b-12d3-a456-426614174000/complete
```

### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Project Discussion",
    "status": "completed",
    "completed_at": "2024-01-20T11:00:00.000000Z"
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

---

## Meeting Status Values

| Status | Description |
|--------|-------------|
| `pending` | Meeting created, waiting for acceptance |
| `accepted` | All required participants have accepted |
| `declined` | Meeting was declined by a participant |
| `cancelled` | Meeting was cancelled |
| `in_progress` | Meeting is currently in progress |
| `completed` | Meeting has been completed |

## Meeting Types

| Type | Description |
|------|-------------|
| `member_to_member` | Meeting between two users |
| `member_to_company` | Meeting between a user and a company |

## Participant Status Values

| Status | Description |
|--------|-------------|
| `invited` | Participant has been invited but not responded |
| `accepted` | Participant has accepted the invitation |
| `declined` | Participant has declined the invitation |

## Participant Roles

| Role | Description |
|------|-------------|
| `organizer` | User who created the meeting |
| `attendee` | Regular participant |

---

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Error message"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Meeting] {id}"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error message"
}
```

---

## Notes for Frontend Integration

1. **Authentication**: All endpoints require a valid Bearer token in the Authorization header.

2. **Date Format**: Use ISO 8601 format for dates (e.g., `2024-01-20T10:00:00Z`).

3. **Pagination**: The list endpoint uses Laravel's pagination. Default page size is 20.

4. **Whereby Integration**: When a meeting is accepted and all required participants have accepted, a Whereby room is automatically created. The `room_url` is for regular participants, and `host_room_url` is for the organizer/host.

5. **Real-time Updates**: The system broadcasts events via Pusher when meetings are created, accepted, declined, started, or completed. Listen to these events for real-time updates:
   - `user.{user_id}.messages` channel
   - Events: `MeetingCreated`, `MeetingAccepted`, `MeetingDeclined`, etc.

6. **Meeting Access**: Users can only access meetings where they are:
   - The organizer
   - A direct participant (user_id matches)
   - A participant through the participants table

7. **Time Validation**: `scheduled_at` must be in the future when creating a meeting.

8. **Duration**: Duration is in minutes, with a minimum of 15 and maximum of 480 (8 hours).

