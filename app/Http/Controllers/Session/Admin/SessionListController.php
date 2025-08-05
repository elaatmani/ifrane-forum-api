<?php

namespace App\Http\Controllers\Session\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Session\Admin\SessionListResource;
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
        $sessions = $this->sessionRepository->paginate($perPage);

        $sessions->getCollection()->transform(function ($session) {
            return new SessionListResource($session);
        });

        return response()->json($sessions);
    }
}
