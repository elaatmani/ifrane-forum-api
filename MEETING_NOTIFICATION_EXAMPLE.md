# Meeting Invitation Notification Example

## Complete Notification Structure

When a user receives a meeting invitation, they will receive a notification with the following structure:

### Database Record (UserNotification)

```json
{
  "id": 123,
  "user_id": 2,
  "title": "New Meeting Invitation",
  "message": "You have been invited to a meeting: Project Discussion",
  "notification_type": "system",
  "severity_type": "info",
  "read_at": null,
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z",
  "data": {
    "type": "meeting_invitation",
    "meeting_id": "123e4567-e89b-12d3-a456-426614174000",
    "meeting_type": "member_to_member",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements and timeline",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "status": "pending",
    "actions": [
      {
        "label": "Accept",
        "action": "accept",
        "method": "POST",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000/accept",
        "style": "primary"
      },
      {
        "label": "Decline",
        "action": "decline",
        "method": "POST",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000/decline",
        "style": "danger"
      },
      {
        "label": "View Details",
        "action": "view_details",
        "method": "GET",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000",
        "style": "secondary"
      }
    ]
  }
}
```

### Real-time Broadcast Format

When broadcasted via Pusher/Laravel Echo, the notification will be formatted as:

```json
{
  "id": 123,
  "type": "info",
  "title": "New Meeting Invitation",
  "message": "You have been invited to a meeting: Project Discussion",
  "time": "2024-01-15T10:30:00.000000Z",
  "unread": true,
  "notification_type": "system",
  "data": {
    "type": "meeting_invitation",
    "meeting_id": "123e4567-e89b-12d3-a456-426614174000",
    "meeting_type": "member_to_member",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements and timeline",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "status": "pending",
    "actions": [
      {
        "label": "Accept",
        "action": "accept",
        "method": "POST",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000/accept",
        "style": "primary"
      },
      {
        "label": "Decline",
        "action": "decline",
        "method": "POST",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000/decline",
        "style": "danger"
      },
      {
        "label": "View Details",
        "action": "view_details",
        "method": "GET",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000",
        "style": "secondary"
      }
    ]
  }
}
```

## Available Actions

### 1. Accept Meeting

**Action:** `accept`  
**Method:** `POST`  
**Endpoint:** `/api/meetings/{meetingId}/accept`  
**Payload:** None required

**Example Request:**
```javascript
// Frontend implementation
async function acceptMeeting(meetingId) {
  const response = await fetch(`/api/meetings/${meetingId}/accept`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
  
  const result = await response.json();
  return result;
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "title": "Project Discussion",
    "status": "accepted",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "whereby_meeting_id": "whereby_meeting_123",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
    "accepted_at": "2024-01-15T10:35:00.000000Z"
  }
}
```

### 2. Decline Meeting

**Action:** `decline`  
**Method:** `POST`  
**Endpoint:** `/api/meetings/{meetingId}/decline`  
**Payload:** Optional reason

**Example Request:**
```javascript
// Frontend implementation
async function declineMeeting(meetingId, reason = null) {
  const response = await fetch(`/api/meetings/${meetingId}/decline`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      reason: reason || null
    }),
  });
  
  const result = await response.json();
  return result;
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "title": "Project Discussion",
    "status": "declined",
    "declined_at": "2024-01-15T10:40:00.000000Z"
  }
}
```

### 3. View Details

**Action:** `view_details`  
**Method:** `GET`  
**Endpoint:** `/api/meetings/{meetingId}`  
**Payload:** None

**Example Request:**
```javascript
// Frontend implementation
async function viewMeetingDetails(meetingId) {
  const response = await fetch(`/api/meetings/${meetingId}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  
  const result = await response.json();
  return result;
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements and timeline",
    "meeting_type": "member_to_member",
    "status": "pending",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "organizer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "participants": [
      {
        "id": "uuid",
        "meeting_id": "123e4567-e89b-12d3-a456-426614174000",
        "user_id": 1,
        "role": "organizer",
        "status": "accepted"
      },
      {
        "id": "uuid",
        "meeting_id": "123e4567-e89b-12d3-a456-426614174000",
        "user_id": 2,
        "role": "attendee",
        "status": "invited"
      }
    ]
  }
}
```

## Frontend Implementation Example

### React/Vue Component Example

```javascript
// MeetingInvitationNotification.jsx
import React from 'react';

function MeetingInvitationNotification({ notification }) {
  const { data } = notification;
  const meeting = data;
  
  const handleAction = async (action) => {
    switch(action.action) {
      case 'accept':
        try {
          const response = await fetch(action.endpoint, {
            method: action.method,
            headers: {
              'Authorization': `Bearer ${getToken()}`,
              'Content-Type': 'application/json',
            },
          });
          const result = await response.json();
          if (result.success) {
            // Show success message
            // Update notification status
            // Refresh meeting list
          }
        } catch (error) {
          console.error('Failed to accept meeting:', error);
        }
        break;
        
      case 'decline':
        const reason = prompt('Optional: Provide a reason for declining');
        try {
          const response = await fetch(action.endpoint, {
            method: action.method,
            headers: {
              'Authorization': `Bearer ${getToken()}`,
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason }),
          });
          const result = await response.json();
          if (result.success) {
            // Show success message
            // Update notification status
          }
        } catch (error) {
          console.error('Failed to decline meeting:', error);
        }
        break;
        
      case 'view_details':
        // Navigate to meeting details page
        window.location.href = `/meetings/${meeting.meeting_id}`;
        break;
    }
  };
  
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };
  
  return (
    <div className="meeting-invitation-notification">
      <div className="notification-header">
        <h3>{notification.title}</h3>
        <span className="badge badge-info">{meeting.status}</span>
      </div>
      
      <div className="notification-body">
        <p>{notification.message}</p>
        
        <div className="meeting-details">
          <div className="detail-item">
            <strong>Organizer:</strong> {meeting.organizer.name}
          </div>
          <div className="detail-item">
            <strong>Scheduled:</strong> {formatDate(meeting.scheduled_at)}
          </div>
          <div className="detail-item">
            <strong>Duration:</strong> {meeting.duration_minutes} minutes
          </div>
          {meeting.location && (
            <div className="detail-item">
              <strong>Location:</strong> {meeting.location}
            </div>
          )}
          {meeting.description && (
            <div className="detail-item">
              <strong>Description:</strong> {meeting.description}
            </div>
          )}
        </div>
      </div>
      
      <div className="notification-actions">
        {meeting.actions.map((action, index) => (
          <button
            key={index}
            className={`btn btn-${action.style}`}
            onClick={() => handleAction(action)}
          >
            {action.label}
          </button>
        ))}
      </div>
    </div>
  );
}

export default MeetingInvitationNotification;
```

### Action Button Styles

- **Primary** (`style: "primary"`): Use for the main/positive action (Accept)
- **Danger** (`style: "danger"`): Use for negative/destructive actions (Decline)
- **Secondary** (`style: "secondary"`): Use for informational actions (View Details)

## Notification Flow

1. **User receives invitation** → Notification created and broadcasted
2. **User clicks "Accept"** → POST to `/api/meetings/{id}/accept`
   - Meeting status changes to "accepted" (if all participants accepted)
   - Whereby room is created automatically
   - Organizer receives acceptance notification
3. **User clicks "Decline"** → POST to `/api/meetings/{id}/decline`
   - Participant status changes to "declined"
   - Organizer receives decline notification
   - Meeting may be declined if required participant declines
4. **User clicks "View Details"** → Navigate to meeting details page
   - Shows full meeting information
   - Shows all participants and their statuses
   - Shows room URL if meeting is accepted

## Meeting Accepted Notification Example

When a participant accepts a meeting invitation, the organizer receives a notification:

### Notification Structure

```json
{
  "id": 124,
  "user_id": 1,
  "title": "Meeting Accepted",
  "message": "Jane Smith has accepted your meeting invitation: Project Discussion",
  "notification_type": "system",
  "severity_type": "success",
  "read_at": null,
  "created_at": "2024-01-15T10:35:00.000000Z",
  "updated_at": "2024-01-15T10:35:00.000000Z",
  "data": {
    "type": "meeting_accepted",
    "meeting_id": "123e4567-e89b-12d3-a456-426614174000",
    "meeting_type": "member_to_member",
    "title": "Project Discussion",
    "description": "Discussing the new project requirements and timeline",
    "scheduled_at": "2024-01-20T10:00:00.000000Z",
    "duration_minutes": 60,
    "timezone": "UTC",
    "location": "Virtual Meeting",
    "status": "accepted",
    "accepted_by": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "organizer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123",
    "whereby_meeting_id": "whereby_meeting_123",
    "actions": [
      {
        "label": "View Details",
        "action": "view_details",
        "method": "GET",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000",
        "style": "secondary"
      },
      {
        "label": "View Room",
        "action": "view_room",
        "method": "GET",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000",
        "style": "info",
        "room_url": "https://subdomain.whereby.com/room123",
        "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123"
      },
      {
        "label": "Start Meeting",
        "action": "start_meeting",
        "method": "POST",
        "endpoint": "/api/meetings/123e4567-e89b-12d3-a456-426614174000/start",
        "style": "primary"
      }
    ]
  }
}
```

### Available Actions for Accepted Meeting Notification

#### 1. View Details
**Action:** `view_details`  
**Method:** `GET`  
**Endpoint:** `/api/meetings/{meetingId}`  
Same as invitation notification - shows full meeting details.

#### 2. View Room
**Action:** `view_room`  
**Method:** `GET` (or direct navigation)  
**Purpose:** Opens the Whereby meeting room  
**Note:** This action includes `room_url` and `host_room_url` in the action data.

**Example Implementation:**
```javascript
case 'view_room':
  // Open the room URL in a new tab
  // Use host_room_url if user is the organizer
  const isOrganizer = notification.data.organizer.id === currentUserId;
  const roomUrl = isOrganizer 
    ? action.host_room_url 
    : action.room_url;
  window.open(roomUrl, '_blank');
  break;
```

#### 3. Start Meeting
**Action:** `start_meeting`  
**Method:** `POST`  
**Endpoint:** `/api/meetings/{meetingId}/start`  
**Available:** Only when meeting can be joined (15 min before to 1 hour after scheduled time) and user is organizer

**Example Request:**
```javascript
async function startMeeting(meetingId) {
  const response = await fetch(`/api/meetings/${meetingId}/start`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });
  
  const result = await response.json();
  if (result.success) {
    // Meeting status changed to 'in_progress'
    // Navigate to room or show success message
  }
  return result;
}
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "title": "Project Discussion",
    "status": "in_progress",
    "started_at": "2024-01-20T10:00:00.000000Z",
    "room_url": "https://subdomain.whereby.com/room123",
    "host_room_url": "https://subdomain.whereby.com/room123?hostKey=abc123"
  }
}
```

### Frontend Component Example

```javascript
// MeetingAcceptedNotification.jsx
import React from 'react';

function MeetingAcceptedNotification({ notification }) {
  const { data } = notification;
  const meeting = data;
  
  const handleAction = async (action) => {
    switch(action.action) {
      case 'view_details':
        window.location.href = `/meetings/${meeting.meeting_id}`;
        break;
        
      case 'view_room':
        // Check if user is organizer to use host URL
        const isOrganizer = meeting.organizer.id === currentUserId;
        const roomUrl = isOrganizer 
          ? action.host_room_url 
          : action.room_url;
        window.open(roomUrl, '_blank');
        break;
        
      case 'start_meeting':
        try {
          const response = await fetch(action.endpoint, {
            method: action.method,
            headers: {
              'Authorization': `Bearer ${getToken()}`,
              'Content-Type': 'application/json',
            },
          });
          const result = await response.json();
          if (result.success) {
            // Open room URL
            window.open(meeting.host_room_url, '_blank');
            // Show success message
            showSuccess('Meeting started successfully!');
          }
        } catch (error) {
          console.error('Failed to start meeting:', error);
          showError('Failed to start meeting. Please try again.');
        }
        break;
    }
  };
  
  return (
    <div className="meeting-accepted-notification">
      <div className="notification-header">
        <h3>{notification.title}</h3>
        <span className="badge badge-success">{meeting.status}</span>
      </div>
      
      <div className="notification-body">
        <p>{notification.message}</p>
        
        <div className="meeting-info">
          <div className="info-item">
            <strong>Accepted by:</strong> {meeting.accepted_by.name}
          </div>
          <div className="info-item">
            <strong>Meeting:</strong> {meeting.title}
          </div>
          <div className="info-item">
            <strong>Scheduled:</strong> {formatDate(meeting.scheduled_at)}
          </div>
          {meeting.room_url && (
            <div className="info-item">
              <strong>Room:</strong> Ready
            </div>
          )}
        </div>
      </div>
      
      <div className="notification-actions">
        {meeting.actions.map((action, index) => (
          <button
            key={index}
            className={`btn btn-${action.style}`}
            onClick={() => handleAction(action)}
          >
            {action.label}
          </button>
        ))}
      </div>
    </div>
  );
}

export default MeetingAcceptedNotification;
```

## Real-time Updates

Listen for notification broadcasts:

```javascript
// Using Laravel Echo
Echo.private(`user.${userId}.messages`)
  .listen('.notification.created', (notification) => {
    if (notification.data.type === 'meeting_invitation') {
      // Display invitation notification
      displayMeetingInvitation(notification);
    } else if (notification.data.type === 'meeting_accepted') {
      // Display accepted notification
      displayMeetingAccepted(notification);
    }
  });

// Also listen for meeting events
Echo.private(`user.${userId}.messages`)
  .listen('.meeting.accepted', (event) => {
    // Meeting was accepted - refresh meeting list
    refreshMeetings();
  });
```

## Error Handling

All actions should handle errors gracefully:

```javascript
try {
  const response = await fetch(endpoint, options);
  const result = await response.json();
  
  if (!result.success) {
    // Handle error
    showError(result.message || 'An error occurred');
  }
} catch (error) {
  // Handle network error
  showError('Network error. Please try again.');
}
```

