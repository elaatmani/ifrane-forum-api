<?php

namespace App\Http\Controllers\Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Session\SessionListResource;
use App\Repositories\Contracts\SessionRepositoryInterface;

class SessionListController extends Controller
{
    public function __construct(protected SessionRepositoryInterface $sessionRepository)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sessions = $this->sessionRepository
        ->query()
        ->where('status', 'scheduled')
        ->paginate($perPage);

        $sessions->getCollection()->transform(function ($session) {
            return new SessionListResource($session);
        });

        return response()->json($sessions);
    }

    public function upcoming(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sessions = $this->sessionRepository->upcoming($perPage);

        return response()->json($sessions);
    }
    
    public function past(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sessions = $this->sessionRepository->past($perPage);

        return response()->json($sessions);
    }
}
