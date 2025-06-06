<?php

use Illuminate\Support\Facades\Route;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/test', function () {
    return 'test';
});

// Demo Notification Routes
Route::get('/demo/send-notification/{userId}', function ($userId) {
    try {
        // Find the user
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User with ID {$userId} not found"
            ], 404);
        }

        // Create notification service instance
        $notificationService = app(NotificationService::class);
        
        $sentNotifications = [];
        $results = [];
        
        // 1. Past Meeting Notification (meeting that ended)
        $result1 = $notificationService->sendSystemNotification(
            title: 'Meeting Completed',
            message: 'Your meeting "Project Planning Session" has been completed. Thank you for attending!',
            data: [
                'demo' => true,
                'sent_at' => now()->toISOString(),
                'type' => 'meeting_completed',
                'meeting' => [
                    'meeting_id' => 101,
                    'title' => 'Project Planning Session',
                    'start_time' => now()->subHours(2)->toISOString(),
                    'end_time' => now()->subHour()->toISOString(),
                    'location' => 'Conference Room A',
                    'description' => 'Weekly project planning and review session with the development team.',
                    'participants' => [
                        ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com'],
                        ['id' => 2, 'name' => 'Sarah Johnson', 'email' => 'sarah@example.com']
                    ],
                    'status' => 'completed',
                    'organizer' => ['id' => 3, 'name' => 'Mike Wilson', 'email' => 'mike@example.com'],
                    'meeting_type' => 'recurring',
                    'duration_minutes' => 60
                ]
            ],
            severityType: 'info',
            users: collect([$user])
        );
        
        // 2. Coming Meeting Notification (starts soon)  
        $result2 = $notificationService->sendSystemNotification(
            title: 'Meeting Starting Soon!',
            message: 'Your meeting "Client Presentation" is starting in 15 minutes. Please join the meeting room.',
            data: [
                'demo' => true,
                'sent_at' => now()->toISOString(),
                'type' => 'meeting_reminder',
                'meeting' => [
                    'meeting_id' => 102,
                    'title' => 'Client Presentation',
                    'start_time' => now()->addMinutes(15)->toISOString(),
                    'end_time' => now()->addMinutes(75)->toISOString(),
                    'location' => 'Zoom Meeting Room',
                    'description' => 'Quarterly presentation to showcase project progress and deliverables.',
                    'participants' => [
                        ['id' => 4, 'name' => 'Emily Davis', 'email' => 'emily@example.com'],
                        ['id' => 5, 'name' => 'Robert Brown', 'email' => 'robert@example.com'],
                        ['id' => 6, 'name' => 'Lisa Chen', 'email' => 'lisa@example.com']
                    ],
                    'status' => 'upcoming',
                    'organizer' => ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com'],
                    'meeting_type' => 'presentation',
                    'duration_minutes' => 60,
                    'meeting_url' => 'https://zoom.us/j/1234567890',
                    'preparation_notes' => 'Please review the quarterly report before the meeting.'
                ]
            ],
            severityType: 'warning',
            users: collect([$user])
        );
        
        // 3. Scheduled Meeting Notification (future meeting)
        $result3 = $notificationService->sendSystemNotification(
            title: 'New Meeting Invitation',
            message: 'You have been invited to "Sprint Planning Meeting" scheduled for tomorrow. Please confirm your attendance.',
            data: [
                'demo' => true,
                'sent_at' => now()->toISOString(),
                'type' => 'meeting_invitation',
                'meeting' => [
                    'meeting_id' => 103,
                    'title' => 'Sprint Planning Meeting',
                    'start_time' => now()->addDay()->setHour(9)->setMinute(0)->toISOString(),
                    'end_time' => now()->addDay()->setHour(11)->setMinute(0)->toISOString(),
                    'location' => 'Conference Room B',
                    'description' => 'Planning session for the upcoming 2-week sprint. We will discuss user stories, task assignments, and sprint goals.',
                    'participants' => [
                        ['id' => 7, 'name' => 'Alex Thompson', 'email' => 'alex@example.com'],
                        ['id' => 8, 'name' => 'Maria Garcia', 'email' => 'maria@example.com'],
                        ['id' => 9, 'name' => 'David Lee', 'email' => 'david@example.com']
                    ],
                    'status' => 'scheduled',
                    'organizer' => ['id' => 10, 'name' => 'Jennifer Taylor', 'email' => 'jennifer@example.com'],
                    'meeting_type' => 'planning',
                    'duration_minutes' => 120,
                    'agenda' => [
                        'Review previous sprint results',
                        'Discuss upcoming user stories',
                        'Assign tasks and responsibilities',
                        'Set sprint goals and timeline'
                    ],
                    'requires_preparation' => true,
                    'preparation_notes' => 'Please review the backlog items and come prepared with questions.'
                ]
            ],
            severityType: 'success',
            users: collect([$user])
        );
        
        $results = [$result1, $result2, $result3];
        $sentNotifications = [
            'past_meeting' => 'Meeting Completed',
            'coming_meeting' => 'Meeting Starting Soon!', 
            'scheduled_meeting' => 'New Meeting Invitation'
        ];
        
        if (collect($results)->every(fn($result) => $result === true)) {
            return response()->json([
                'success' => true,
                'message' => "Demo meeting notifications sent successfully to {$user->name}",
                'notifications_sent' => $sentNotifications,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ],
                'note' => 'Check your notifications - you should see 3 different meeting scenarios'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Some notifications failed to send',
                'results' => $results
            ], 500);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

Route::post('/demo/send-notification', function (Request $request) {
    try {
        // Validate request
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:1000',
            'severity_type' => 'nullable|in:info,success,warning,error',
            'data' => 'nullable|array'
        ]);

        // Find the user
        $user = User::find($validated['user_id']);
        
        // Set defaults if not provided
        $title = $validated['title'] ?? 'Custom Demo Notification';
        $message = $validated['message'] ?? 'This is a custom demo notification with your content.';
        $severityType = $validated['severity_type'] ?? 'info';
        $data = array_merge([
            'demo' => true,
            'sent_at' => now()->toISOString(),
            'type' => 'custom_demo'
        ], $validated['data'] ?? []);

        // Create notification service instance
        $notificationService = app(NotificationService::class);
        
        // Send custom notification
        $result = $notificationService->sendSystemNotification(
            title: $title,
            message: $message,
            data: $data,
            severityType: $severityType,
            users: collect([$user])
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Custom notification sent successfully to {$user->name}",
                'notification' => [
                    'title' => $title,
                    'message' => $message,
                    'severity_type' => $severityType,
                    'data' => $data
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification'
            ], 500);
        }

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

// Helper route to list users for testing
Route::get('/demo/users', function () {
    $users = User::select('id', 'name', 'email')->take(10)->get();
    
    return response()->json([
        'success' => true,
        'message' => 'Available users for testing notifications',
        'users' => $users,
        'usage' => [
            'quick_demo' => 'GET /demo/send-notification/{userId}',
            'custom_demo' => 'POST /demo/send-notification with JSON body'
        ]
    ]);
});

