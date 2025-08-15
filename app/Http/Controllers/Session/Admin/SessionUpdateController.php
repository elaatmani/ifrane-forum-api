<?php

namespace App\Http\Controllers\Session\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Session\SessionUpdateRequest;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Http\Resources\Session\Admin\SessionResource;
use App\Services\MessagingService;

class SessionUpdateController extends Controller
{
    public function __construct(
        protected SessionRepositoryInterface $sessionRepository,
        protected MessagingService $messagingService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(SessionUpdateRequest $request, $id)
    {
        $data = $request->validated();

        if($request->has('image') && $request->file('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('sessions', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $session = $this->sessionRepository->update($id, $data);

        // Handle speaker updates if provided
        if (isset($data['speakers'])) {
            // Remove existing speakers
            $session->users()->wherePivot('role', 'speaker')->detach();
            
            // Attach new speakers with 'speaker' role
            if (!empty($data['speakers'])) {
                $speakerData = [];
                foreach ($data['speakers'] as $speakerId) {
                    $speakerData[$speakerId] = ['role' => 'speaker', 'joined_at' => now()];
                }
                $session->users()->attach($speakerData);
            }

            // Ensure conversation exists and add new speakers to it
            $conversation = $this->messagingService->getSessionConversation($session);
            
            // Add new speakers to the conversation if they're not already there
            if (!empty($data['speakers'])) {
                foreach ($data['speakers'] as $speakerId) {
                    $user = \App\Models\User::find($speakerId);
                    if ($user && !$this->messagingService->canUserAccessConversation($conversation, $user)) {
                        $this->messagingService->addUserToSessionChat($session, $user);
                    }
                }
            }
        }

        return response()->json(SessionResource::make($session));
    }
}
