<?php

namespace App\Http\Controllers\Session\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Session\SessionUpdateRequest;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Http\Resources\Session\Admin\SessionResource;
class SessionUpdateController extends Controller
{
    public function __construct(protected SessionRepositoryInterface $sessionRepository)
    {
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
        }

        return response()->json(SessionResource::make($session));
    }
}
