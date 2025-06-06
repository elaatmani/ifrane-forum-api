# Meeting Notification Structure Documentation

## Overview
This document describes the notification data structures used for meeting-related notifications in the system. The notifications are sent via real-time broadcasting using Laravel Echo and Pusher.

## Base Notification Structure

All notifications follow this base structure:

```json
{
  "id": 123,
  "type": "info|success|warning|error",
  "title": "Notification Title",
  "message": "Notification message content",
  "time": "2024-01-15T10:30:00.000000Z",
  "unread": true,
  "data": {
    // Meeting-specific data here
  },
  "notification_type": "system"
}
```

## Meeting Notification Types

### 1. Meeting Completed (`meeting_completed`)

**Severity**: `info`  
**When**: Sent after a meeting has ended  
**Purpose**: Confirm meeting completion and thank participants

```json
{
  "title": "Meeting Completed",
  "message": "Your meeting \"Project Planning Session\" has been completed. Thank you for attending!",
  "type": "info",
  "data": {
    "demo": true,
    "sent_at": "2024-01-15T10:30:00.000000Z",
    "type": "meeting_completed",
    "meeting": {
      "meeting_id": 101,
      "title": "Project Planning Session",
      "start_time": "2024-01-15T08:30:00.000000Z",
      "end_time": "2024-01-15T09:30:00.000000Z",
      "location": "Conference Room A",
      "description": "Weekly project planning and review session with the development team.",
      "participants": [
        {
          "id": 1,
          "name": "John Smith",
          "email": "john@example.com"
        },
        {
          "id": 2,
          "name": "Sarah Johnson",
          "email": "sarah@example.com"
        }
      ],
      "status": "completed",
      "organizer": {
        "id": 3,
        "name": "Mike Wilson",
        "email": "mike@example.com"
      },
      "meeting_type": "recurring",
      "duration_minutes": 60
    }
  }
}
```

### 2. Meeting Reminder (`meeting_reminder`)

**Severity**: `warning`  
**When**: Sent before a meeting starts (e.g., 15 minutes before)  
**Purpose**: Remind participants about upcoming meeting

```json
{
  "title": "Meeting Starting Soon!",
  "message": "Your meeting \"Client Presentation\" is starting in 15 minutes. Please join the meeting room.",
  "type": "warning",
  "data": {
    "demo": true,
    "sent_at": "2024-01-15T10:30:00.000000Z",
    "type": "meeting_reminder",
    "meeting": {
      "meeting_id": 102,
      "title": "Client Presentation",
      "start_time": "2024-01-15T10:45:00.000000Z",
      "end_time": "2024-01-15T11:45:00.000000Z",
      "location": "Zoom Meeting Room",
      "description": "Quarterly presentation to showcase project progress and deliverables.",
      "participants": [
        {
          "id": 4,
          "name": "Emily Davis",
          "email": "emily@example.com"
        },
        {
          "id": 5,
          "name": "Robert Brown",
          "email": "robert@example.com"
        },
        {
          "id": 6,
          "name": "Lisa Chen",
          "email": "lisa@example.com"
        }
      ],
      "status": "upcoming",
      "organizer": {
        "id": 1,
        "name": "John Smith",
        "email": "john@example.com"
      },
      "meeting_type": "presentation",
      "duration_minutes": 60,
      "meeting_url": "https://zoom.us/j/1234567890",
      "preparation_notes": "Please review the quarterly report before the meeting."
    }
  }
}
```

### 3. Meeting Invitation (`meeting_invitation`)

**Severity**: `success`  
**When**: Sent when user is invited to a future meeting  
**Purpose**: Notify about new meeting invitation and request confirmation

```json
{
  "title": "New Meeting Invitation",
  "message": "You have been invited to \"Sprint Planning Meeting\" scheduled for tomorrow. Please confirm your attendance.",
  "type": "success",
  "data": {
    "demo": true,
    "sent_at": "2024-01-15T10:30:00.000000Z",
    "type": "meeting_invitation",
    "meeting": {
      "meeting_id": 103,
      "title": "Sprint Planning Meeting",
      "start_time": "2024-01-16T09:00:00.000000Z",
      "end_time": "2024-01-16T11:00:00.000000Z",
      "location": "Conference Room B",
      "description": "Planning session for the upcoming 2-week sprint. We will discuss user stories, task assignments, and sprint goals.",
      "participants": [
        {
          "id": 7,
          "name": "Alex Thompson",
          "email": "alex@example.com"
        },
        {
          "id": 8,
          "name": "Maria Garcia",
          "email": "maria@example.com"
        },
        {
          "id": 9,
          "name": "David Lee",
          "email": "david@example.com"
        }
      ],
      "status": "scheduled",
      "organizer": {
        "id": 10,
        "name": "Jennifer Taylor",
        "email": "jennifer@example.com"
      },
      "meeting_type": "planning",
      "duration_minutes": 120,
      "agenda": [
        "Review previous sprint results",
        "Discuss upcoming user stories",
        "Assign tasks and responsibilities",
        "Set sprint goals and timeline"
      ],
      "requires_preparation": true,
      "preparation_notes": "Please review the backlog items and come prepared with questions."
    }
  }
}
```

## Meeting Object Properties

| Property | Type | Description |
|----------|------|-------------|
| `meeting_id` | integer | Unique identifier for the meeting |
| `title` | string | Meeting title/subject |
| `start_time` | string (ISO 8601) | Meeting start time |
| `end_time` | string (ISO 8601) | Meeting end time |
| `location` | string | Meeting location (room, Zoom link, etc.) |
| `description` | string | Detailed meeting description |
| `participants` | array | Array of participant objects with id, name, email |
| `status` | string | Meeting status: `completed`, `upcoming`, `scheduled` |
| `organizer` | object | Meeting organizer with id, name, email |
| `meeting_type` | string | Type: `recurring`, `presentation`, `planning`, etc. |
| `duration_minutes` | integer | Meeting duration in minutes |
| `meeting_url` | string (optional) | Video conference URL |
| `preparation_notes` | string (optional) | Notes for meeting preparation |
| `agenda` | array (optional) | Array of agenda items |
| `requires_preparation` | boolean (optional) | Whether meeting requires preparation |

## Meeting Status Values

- `completed` - Meeting has ended
- `upcoming` - Meeting is starting soon (within reminder window)
- `scheduled` - Meeting is planned for future date

## Meeting Types

- `recurring` - Regular recurring meeting
- `presentation` - Presentation or demo meeting
- `planning` - Planning or strategy meeting
- `standup` - Daily standup meeting
- `review` - Review or retrospective meeting
- `interview` - Interview meeting
- `training` - Training session
- `social` - Social or team building event

## Frontend Implementation Tips

### 1. Display Logic
```javascript
// Handle different notification types
function displayMeetingNotification(notification) {
  const meeting = notification.data.meeting;
  const type = notification.data.type;
  
  switch(type) {
    case 'meeting_completed':
      // Show completion confirmation
      // Maybe show meeting summary or feedback form
      break;
      
    case 'meeting_reminder':
      // Show urgent reminder with join button
      // Display countdown timer if needed
      // Show meeting URL if available
      break;
      
    case 'meeting_invitation':
      // Show invitation with accept/decline buttons
      // Display full meeting details
      // Show agenda if available
      break;
  }
}
```

### 2. Time Handling
```javascript
// Format meeting times
function formatMeetingTime(startTime, endTime) {
  const start = new Date(startTime);
  const end = new Date(endTime);
  
  return `${start.toLocaleTimeString()} - ${end.toLocaleTimeString()}`;
}

// Check if meeting is soon
function isMeetingSoon(startTime, minutesThreshold = 30) {
  const now = new Date();
  const meetingStart = new Date(startTime);
  const diffMinutes = (meetingStart - now) / (1000 * 60);
  
  return diffMinutes <= minutesThreshold && diffMinutes > 0;
}
```

### 3. Actions
```javascript
// Handle meeting actions
function handleMeetingAction(meetingId, action) {
  switch(action) {
    case 'join':
      // Open meeting URL or navigate to meeting page
      break;
    case 'accept':
      // Accept meeting invitation
      break;
    case 'decline':
      // Decline meeting invitation
      break;
    case 'view_details':
      // Show detailed meeting information
      break;
  }
}
```

## Real-time Updates

The notifications are broadcasted in real-time using Laravel Echo. Listen for updates like this:

```javascript
// Listen for new notifications
Echo.private(`user.${userId}`)
  .listen('.notification.created', (notification) => {
    if (notification.data.type.startsWith('meeting_')) {
      handleMeetingNotification(notification);
    }
  });
```

## Testing the Demo

Use these endpoints to test the notification system:

- `GET /demo/send-notification/{userId}` - Sends all 3 meeting scenarios
- `GET /demo/users` - Lists available users for testing
- `POST /demo/send-notification` - Send custom meeting notification

The demo will send 3 notifications representing different meeting scenarios:
1. **Past meeting** (completed)
2. **Coming meeting** (starting in 15 minutes)  
3. **Scheduled meeting** (tomorrow)

Each notification will have different severity levels and appropriate timing to showcase the various meeting states your frontend needs to handle. 