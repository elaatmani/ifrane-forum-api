<?php

namespace App\Http\Controllers\Session\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Session\SessionStoreRequest;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Http\Resources\Session\Admin\SessionResource;

class SessionStoreController extends Controller
{
    public function __construct(protected SessionRepositoryInterface $sessionRepository)
    {
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

        return response()->json(SessionResource::make($session));
    }
}
