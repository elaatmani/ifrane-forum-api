<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\PasswordResetMail;
use App\Mail\MeetingInvitationMail;
use App\Mail\MeetingReminderMail;
use App\Models\Meeting;
use Carbon\Carbon;

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
    $product = Product::latest()->first();

    $categories = $product->categories->map(function($category) {
        return ['id' => $category->id, 'name' => $category->name];
    });

    return response()->json([
        // 'product' => $product,
        'categories' => $categories
    ]);
});

// Test document upload endpoint
Route::post('/test-document-upload', function (Illuminate\Http\Request $request) {
    return response()->json([
        'message' => 'Test endpoint for document upload',
        'has_file' => $request->hasFile('file'),
        'file_info' => $request->hasFile('file') ? [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'size' => $request->file('file')->getSize(),
            'mime_type' => $request->file('file')->getMimeType(),
            'extension' => $request->file('file')->getClientOriginalExtension(),
        ] : null,
        'all_data' => $request->all()
    ]);
});

// Simple mail tester (available only when debug is enabled)
Route::middleware([])->group(function () {
    Route::get('/mail-tester', function () {
        if (!config('app.debug')) {
            abort(404);
        }

        return view('mail-tester');
    })->name('debug.mail-tester');

    Route::post('/mail-tester', function (Request $request) {
        if (!config('app.debug')) {
            abort(404);
        }

        $data = $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:welcome,password_reset,meeting_invitation,meeting_reminder',
        ]);

        $email = $data['email'];
        $type = $data['type'];

        try {
            switch ($type) {
                case 'welcome':
                    $resetUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/reset-password?token=demo-token&email=' . urlencode($email);
                    Mail::to($email)->send(new WelcomeMail('Demo User', $resetUrl));
                    $msg = "Sent WelcomeMail to {$email}";
                    break;

                case 'password_reset':
                    $resetUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/reset-password?token=demo-token&email=' . urlencode($email);
                    Mail::to($email)->send(new PasswordResetMail('Demo User', $resetUrl));
                    $msg = "Sent PasswordResetMail to {$email}";
                    break;

                case 'meeting_invitation':
                    $meeting = new Meeting([
                        'title' => 'Demo Meeting Invitation',
                        'description' => 'This is a demo meeting invitation email.',
                        'meeting_type' => 'member_to_member',
                        'scheduled_at' => Carbon::now()->addMinutes(45),
                        'duration_minutes' => 60,
                        'timezone' => 'UTC',
                        'location' => 'Zoom / Online',
                        'status' => 'pending',
                    ]);
                    $actionUrl = url('/meetings/demo');
                    Mail::to($email)->send(new MeetingInvitationMail($meeting, 'Demo User', $actionUrl));
                    $msg = "Sent MeetingInvitationMail to {$email}";
                    break;

                case 'meeting_reminder':
                    $meeting = new Meeting([
                        'title' => 'Demo Meeting Reminder',
                        'description' => 'This is a demo meeting reminder email.',
                        'meeting_type' => 'member_to_member',
                        'scheduled_at' => Carbon::now()->addMinutes(15),
                        'duration_minutes' => 60,
                        'timezone' => 'UTC',
                        'location' => 'Zoom / Online',
                        'status' => 'accepted',
                    ]);
                    $joinUrl = url('/meetings/demo');
                    Mail::to($email)->send(new MeetingReminderMail($meeting, 'Demo User', $joinUrl));
                    $msg = "Sent MeetingReminderMail to {$email}";
                    break;

                default:
                    $msg = 'Unknown email type';
            }

            return back()->with('status', $msg);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    })->name('debug.mail-tester.send');
});

