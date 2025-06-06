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
        
        // Send demo notification with default content
        $result = $notificationService->sendSystemNotification(
            title: 'Meeting starts in 10 minutes!',
            message: 'Your meeting with John Doe is starting in 10 minutes. Please be on time.',
            data: [
                'demo' => true,
                'sent_at' => now()->toISOString(),
                'type' => 'meeting',
                'meeting_id' => 1,
                'meeting_title' => 'Meeting with John Doe',
                'meeting_start_time' => now()->addMinutes(10)->toISOString(),
                'meeting_end_time' => now()->addMinutes(20)->toISOString(),
                'meeting_location' => 'Room 101',
                'meeting_description' => 'This is a meeting with John Doe. Please be on time.',
                'meeting_participants' => ['John Doe', 'Jane Doe'],
                'meeting_status' => 'upcoming'
            ],
            severityType: 'success',
            users: collect([$user])
        );

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => "Demo notification sent successfully to {$user->name}",
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

