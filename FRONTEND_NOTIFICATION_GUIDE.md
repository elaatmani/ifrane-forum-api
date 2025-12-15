# Frontend Notification Integration Guide

## Quick Reference

### 📍 **Where to Find Notification Content:**

1. **API Endpoint** - Fetch notifications: `GET /api/notifications`
2. **Real-time** - Listen via Laravel Echo/Pusher: `user.{userId}.messages`
3. **Documentation** - See `MEETING_NOTIFICATION_EXAMPLE.md` for complete examples

---

## API Endpoints

### 1. Get All Notifications
```
GET /api/notifications
Query Parameters:
  - per_page (default: 10)
  - unread (boolean, default: false) - filter unread only
```

**Response:**
```json
{
  "data": [
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
        "actions": [...]
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

### 2. Get Unread Count
```
GET /api/notifications/unread-count
```

**Response:**
```json
{
  "count": 5
}
```

### 3. Mark as Read
```
POST /api/notifications/{notificationId}/mark-read
```

### 4. Mark All as Read
```
POST /api/notifications/mark-all-read
```

---

## Notification Structure

### Base Structure (from API)

```typescript
interface Notification {
  id: number;
  user_id: number;
  title: string;
  message: string;
  notification_type: string; // "system"
  severity_type: string; // "info" | "success" | "warning" | "error"
  read_at: string | null;
  created_at: string;
  updated_at: string;
  data: {
    type: string; // "meeting_invitation" | "meeting_accepted" | etc.
    // ... meeting-specific data
    actions: Action[];
  };
}

interface Action {
  label: string;
  action: string;
  method: "GET" | "POST" | "PUT" | "DELETE";
  endpoint: string;
  style: "primary" | "secondary" | "danger" | "info";
  // Additional fields may be present (e.g., room_url for view_room action)
}
```

---

## Meeting Notification Types

All meeting notifications follow this base structure in the `data` field:

```typescript
interface MeetingNotificationData {
  type: 'meeting_invitation' | 'meeting_accepted' | 'meeting_declined' | 'meeting_started' | 'meeting_completed';
  meeting_id: string; // UUID
  meeting_type: 'member_to_member' | 'member_to_company';
  title: string;
  description: string | null;
  scheduled_at: string; // ISO 8601
  duration_minutes: number;
  timezone: string;
  location: string | null;
  status: 'pending' | 'accepted' | 'declined' | 'cancelled' | 'in_progress' | 'completed';
  actions: Action[];
}
```

---

### 1. Meeting Invitation (`meeting_invitation`)

**Severity:** `info`  
**Who receives:** Invited participants  
**When:** When a user is invited to a meeting

**Data Structure:**
```json
{
  "type": "meeting_invitation",
  "meeting_id": "uuid",
  "meeting_type": "member_to_member" | "member_to_company",
  "title": "string",
  "description": "string",
  "scheduled_at": "ISO 8601 datetime",
  "duration_minutes": 60,
  "timezone": "UTC",
  "location": "string",
  "organizer": {
    "id": 1,
    "name": "string",
    "email": "string",
    "profile_image": "string"
  },
  "status": "pending",
  "actions": [
    {
      "label": "Accept",
      "action": "accept",
      "method": "POST",
      "endpoint": "/api/meetings/{meetingId}/accept",
      "style": "primary"
    },
    {
      "label": "Decline",
      "action": "decline",
      "method": "POST",
      "endpoint": "/api/meetings/{meetingId}/decline",
      "style": "danger"
    },
    {
      "label": "View Details",
      "action": "view_details",
      "method": "GET",
      "endpoint": "/api/meetings/{meetingId}",
      "style": "secondary"
    }
  ]
}
```

**Location in docs:** `MEETING_NOTIFICATION_EXAMPLE.md` - Lines 1-414

---

### 2. Meeting Accepted (`meeting_accepted`)

**Severity:** `success`  
**Who receives:** Organizer

**Data Structure:**
```json
{
  "type": "meeting_accepted",
  "meeting_id": "uuid",
  "meeting_type": "member_to_member" | "member_to_company",
  "title": "string",
  "description": "string",
  "scheduled_at": "ISO 8601 datetime",
  "duration_minutes": 60,
  "timezone": "UTC",
  "location": "string",
  "status": "accepted",
  "accepted_by": {
    "id": 2,
    "name": "string",
    "email": "string",
    "profile_image": "string"
  },
  "organizer": {
    "id": 1,
    "name": "string",
    "email": "string",
    "profile_image": "string"
  },
  "room_url": "string | null",
  "host_room_url": "string | null",
  "whereby_meeting_id": "string | null",
  "actions": [
    {
      "label": "View Details",
      "action": "view_details",
      "method": "GET",
      "endpoint": "/api/meetings/{meetingId}",
      "style": "secondary"
    },
    {
      "label": "View Room",
      "action": "view_room",
      "method": "GET",
      "endpoint": "/api/meetings/{meetingId}",
      "style": "info",
      "room_url": "string",
      "host_room_url": "string"
    },
    {
      "label": "Start Meeting",
      "action": "start_meeting",
      "method": "POST",
      "endpoint": "/api/meetings/{meetingId}/start",
      "style": "primary"
    }
  ]
}
```

**Location in docs:** `MEETING_NOTIFICATION_EXAMPLE.md` - Lines 415-692

---

## Frontend Implementation

### 1. Fetch Notifications

```javascript
// Fetch all notifications
async function fetchNotifications(unreadOnly = false) {
  const url = `/api/notifications?unread=${unreadOnly}`;
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
    },
  });
  const data = await response.json();
  return data.data; // Array of notifications
}

// Fetch unread count
async function fetchUnreadCount() {
  const response = await fetch('/api/notifications/unread-count', {
    headers: {
      'Authorization': `Bearer ${getToken()}`,
    },
  });
  const data = await response.json();
  return data.count;
}
```

### 2. Listen for Real-time Notifications

```javascript
// Using Laravel Echo
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

// Listen for new notifications
const userId = getCurrentUserId();
echo.private(`user.${userId}.messages`)
  .listen('.notification.created', (notification) => {
    // Handle new notification
    handleNewNotification(notification);
  });

// Listen for meeting events
echo.private(`user.${userId}.messages`)
  .listen('.meeting.created', (event) => {
    // Meeting was created
  })
  .listen('.meeting.accepted', (event) => {
    // Meeting was accepted
  });

// Listen for notification updates (when invitation is accepted/declined)
echo.private(`user.${userId}.messages`)
  .listen('.notification.updated', (notification) => {
    // Update existing notification in UI
    updateNotificationInUI(notification);
  });
```

### 3. Handle Meeting Actions

```javascript
async function handleMeetingAction(notification, action) {
  const baseUrl = '/api'; // Your API base URL
  const meeting = notification.data;
  
  switch(action.action) {
    case 'accept':
      return await fetch(`${baseUrl}${action.endpoint}`, {
        method: action.method,
        headers: {
          'Authorization': `Bearer ${getToken()}`,
          'Content-Type': 'application/json',
        },
      });
      
    case 'decline':
      const reason = prompt('Optional: Provide a reason for declining');
      return await fetch(`${baseUrl}${action.endpoint}`, {
        method: action.method,
        headers: {
          'Authorization': `Bearer ${getToken()}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason }),
      });
      
    case 'view_details':
      // Navigate to meeting details page
      window.location.href = `/meetings/${meeting.meeting_id}`;
      break;
      
    case 'view_room':
      // Open room URL
      const isOrganizer = meeting.organizer && meeting.organizer.id === getCurrentUserId();
      const roomUrl = isOrganizer 
        ? action.host_room_url 
        : action.room_url;
      window.open(roomUrl, '_blank');
      break;
      
    case 'start_meeting':
      const response = await fetch(`${baseUrl}${action.endpoint}`, {
        method: action.method,
        headers: {
          'Authorization': `Bearer ${getToken()}`,
          'Content-Type': 'application/json',
        },
      });
      const result = await response.json();
      if (result.success && meeting.host_room_url) {
        window.open(meeting.host_room_url, '_blank');
      }
      return result;
      
    case 'join_meeting':
      // Open room URL directly (from action or notification data)
      const joinUrl = action.room_url || meeting.room_url;
      if (joinUrl) {
        window.open(joinUrl, '_blank');
      }
      break;
      
    default:
      console.warn('Unknown action:', action.action);
  }
}
```

### 4. React Component Example

```jsx
import React, { useEffect, useState } from 'react';

function MeetingNotification({ notification }) {
  const { data } = notification;
  const meeting = data;
  
  const handleAction = async (action) => {
    try {
      const result = await handleMeetingAction(notification, action);
      if (result && result.ok) {
        const data = await result.json();
        if (data.success) {
          // Show success message
          // Refresh notifications
        }
      }
    } catch (error) {
      console.error('Action failed:', error);
    }
  };
  
  return (
    <div className={`notification notification-${notification.severity_type}`}>
      <div className="notification-header">
        <h4>{notification.title}</h4>
        {!notification.read_at && <span className="badge">New</span>}
      </div>
      <p>{notification.message}</p>
      
      {meeting.type === 'meeting_invitation' && (
        <div className="meeting-info">
          <p><strong>From:</strong> {meeting.organizer.name}</p>
          <p><strong>Scheduled:</strong> {new Date(meeting.scheduled_at).toLocaleString()}</p>
        </div>
      )}
      
      {meeting.type === 'meeting_accepted' && (
        <div className="meeting-info">
          <p><strong>Accepted by:</strong> {meeting.accepted_by.name}</p>
          {meeting.room_url && <p><strong>Room:</strong> Ready</p>}
        </div>
      )}
      
      {meeting.type === 'meeting_declined' && (
        <div className="meeting-info">
          <p><strong>Declined by:</strong> {meeting.declined_by.name}</p>
          <p><strong>Status:</strong> Meeting declined</p>
        </div>
      )}
      
      {meeting.type === 'meeting_started' && (
        <div className="meeting-info">
          <p><strong>Status:</strong> Meeting in progress</p>
          {meeting.room_url && <p><strong>Room:</strong> Active</p>}
        </div>
      )}
      
      {meeting.type === 'meeting_completed' && (
        <div className="meeting-info">
          <p><strong>Status:</strong> Completed</p>
          <p><strong>Completed at:</strong> {new Date(meeting.completed_at).toLocaleString()}</p>
        </div>
      )}
      
      <div className="notification-actions">
        {meeting.actions?.map((action, index) => (
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

export default MeetingNotification;
```

---

## File Locations

| Content | File | Lines |
|---------|------|-------|
| Meeting Invitation Structure | `MEETING_NOTIFICATION_EXAMPLE.md` | 1-414 |
| Meeting Accepted Structure | `MEETING_NOTIFICATION_EXAMPLE.md` | 415-692 |
| Frontend Component Examples | `MEETING_NOTIFICATION_EXAMPLE.md` | 273-414, 553-692 |
| API Endpoints | `MEETING_API_ENDPOINTS.md` | All |
| Notification Service Code | `app/Services/NotificationService.php` | 35-161 |

---

## Quick Checklist

- [ ] Fetch notifications from `/api/notifications`
- [ ] Listen for real-time updates via Laravel Echo
- [ ] Handle `meeting_invitation` type notifications
- [ ] Handle `meeting_accepted` type notifications
- [ ] Handle `meeting_declined` type notifications
- [ ] Handle `meeting_started` type notifications
- [ ] Handle `meeting_completed` type notifications
- [ ] Implement action handlers (accept, decline, view details, join meeting, etc.)
- [ ] Display notification badges/counts
- [ ] Mark notifications as read when viewed
- [ ] Handle errors gracefully

---

## Notification Updates

When a meeting invitation is accepted or declined, the original invitation notification is automatically updated:

### Updated Notification Metadata

**When Accepted:**
- `data.status` changes from `"pending"` to `"accepted"`
- `data.responded_at` is added with timestamp
- `data.room_url` and `data.host_room_url` are added if meeting is fully accepted
- `message` changes to: "You have accepted the meeting invitation: {title}"
- `severity_type` changes from `"info"` to `"success"`
- Actions are updated (remove accept/decline, add view room if available)

**When Declined:**
- `data.status` changes from `"pending"` to `"declined"`
- `data.responded_at` is added with timestamp
- `message` changes to: "You have declined the meeting invitation: {title}"
- `severity_type` changes from `"info"` to `"warning"`
- Actions are updated (remove accept/decline buttons)

### Real-time Update

The updated notification is broadcasted via `.notification.updated` event:

```javascript
// Listen for notification updates
echo.private(`user.${userId}.messages`)
  .listen('.notification.updated', (notification) => {
    // Find and update the notification in your UI
    const index = notifications.findIndex(n => n.id === notification.id);
    if (index !== -1) {
      notifications[index] = notification;
      // Re-render notification component
      updateNotificationComponent(notification);
    }
  });
```

## Testing

1. Create a meeting invitation → Check for `meeting_invitation` notification
2. Accept a meeting → 
   - Check for `meeting_accepted` notification (organizer receives)
   - Check original invitation notification is updated (participant receives update)
3. Decline a meeting → 
   - Check for `meeting_declined` notification (organizer receives)
   - Check original invitation notification is updated (participant receives update)
4. Start a meeting → Check for `meeting_started` notification (all participants receive)
5. Complete a meeting → Check for `meeting_completed` notification (all participants receive)
6. Check notification structure matches examples
7. Test all actions (accept, decline, view details, join meeting, start meeting, etc.)
8. Verify real-time updates work (both new notifications and updates)
9. Test room URL access (organizer vs participant)
10. Verify notification metadata updates correctly when accepting/declining

---

## Support

For complete examples and detailed documentation, see:
- `MEETING_NOTIFICATION_EXAMPLE.md` - Complete notification examples
- `MEETING_API_ENDPOINTS.md` - API endpoint documentation

