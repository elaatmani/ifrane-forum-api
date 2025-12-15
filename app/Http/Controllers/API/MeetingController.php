<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Models\Company;
use App\Services\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MeetingController extends Controller
{
    protected $meetingService;

    public function __construct(MeetingService $meetingService)
    {
        $this->meetingService = $meetingService;
    }

    /**
     * POST /api/meetings/member-to-member
     * Create member-to-member meeting
     */
    public function createMemberToMember(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $organizer = Auth::user();
            $otherUser = User::findOrFail($request->user_id);

            $meeting = $this->meetingService->createMemberToMemberMeeting(
                $organizer,
                $otherUser,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'participants.user']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/member-to-company
     * Create member-to-company meeting
     */
    public function createMemberToCompany(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|integer|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:480',
            'timezone' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $organizer = Auth::user();
            $company = Company::findOrFail($request->company_id);

            $meeting = $this->meetingService->createMemberToCompanyMeeting(
                $organizer,
                $company,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'company', 'participants.user']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/accept
     * Accept a meeting
     */
    public function accept(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            // Check if user is a participant
            if (!$meeting->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this meeting',
                ], 403);
            }

            $meeting = $this->meetingService->acceptMeeting($meeting, $user);

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'company', 'participants.user']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/decline
     * Decline a meeting
     */
    public function decline(Request $request, string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if (!$meeting->participants()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not a participant of this meeting',
                ], 403);
            }

            $meeting = $this->meetingService->declineMeeting(
                $meeting,
                $user,
                $request->get('reason')
            );

            return response()->json([
                'success' => true,
                'data' => $meeting->load(['organizer', 'user', 'company', 'participants.user']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/meetings
     * List user's meetings
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Meeting::where(function($q) use ($user) {
            $q->where('organizer_id', $user->id)
              ->orWhere('user_id', $user->id)
              ->orWhereHas('participants', function($p) use ($user) {
                  $p->where('user_id', $user->id);
              });
        })
        ->with(['organizer', 'user', 'company', 'participants.user']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('meeting_type', $request->type);
        }

        if ($request->has('upcoming')) {
            $query->upcoming();
        }

        $meetings = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $meetings,
        ]);
    }

    /**
     * GET /api/meetings/{meetingId}
     * Get meeting details
     */
    public function show(string $meetingId): JsonResponse
    {
        $meeting = Meeting::with([
            'organizer',
            'user',
            'company',
            'participants.user'
        ])->findOrFail($meetingId);

        $user = Auth::user();

        // Check access
        if (!$meeting->participants()->where('user_id', $user->id)->exists() &&
            $meeting->organizer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this meeting',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $meeting,
        ]);
    }

    /**
     * POST /api/meetings/{meetingId}/start
     * Start a meeting
     */
    public function start(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if ($meeting->organizer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the organizer can start the meeting',
                ], 403);
            }

            $meeting = $this->meetingService->startMeeting($meeting);

            return response()->json([
                'success' => true,
                'data' => $meeting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/meetings/{meetingId}/complete
     * Complete a meeting
     */
    public function complete(string $meetingId): JsonResponse
    {
        try {
            $meeting = Meeting::findOrFail($meetingId);
            $user = Auth::user();

            if ($meeting->organizer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the organizer can complete the meeting',
                ], 403);
            }

            $meeting = $this->meetingService->completeMeeting($meeting);

            return response()->json([
                'success' => true,
                'data' => $meeting,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}