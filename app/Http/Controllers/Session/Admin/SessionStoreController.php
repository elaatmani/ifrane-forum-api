<?php

namespace App\Http\Controllers\Session\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\SessionStoreRequest;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Http\Resources\Session\Admin\SessionResource;
use App\Services\MessagingService;

class SessionStoreController extends Controller
{
    public function __construct(
        protected SessionRepositoryInterface $sessionRepository,
        protected MessagingService $messagingService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(SessionStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images/sessions', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $session = $this->sessionRepository->create($data);

        // Attach speakers with 'speaker' role
        if (!empty($data['speakers'])) {
            $speakerData = [];
            foreach ($data['speakers'] as $speakerId) {
                $speakerData[$speakerId] = ['role' => 'speaker', 'joined_at' => now()];
            }
            $session->users()->attach($speakerData);
        }

        // Create conversation for the session and add speakers to it
        $conversation = $this->messagingService->getSessionConversation($session);
        
        // Add speakers to the conversation
        if (!empty($data['speakers'])) {
            foreach ($data['speakers'] as $speakerId) {
                $user = \App\Models\User::find($speakerId);
                if ($user) {
                    $this->messagingService->addUserToSessionChat($session, $user);
                }
            }
        }

        return response()->json(SessionResource::make($session));
    }
}
