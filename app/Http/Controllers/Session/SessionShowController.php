<?php

namespace App\Http\Controllers\Session;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Session\SessionResource;
use App\Repositories\Contracts\SessionRepositoryInterface;

class SessionShowController extends Controller
{

    public function __construct(protected SessionRepositoryInterface $sessionRepository)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $session = $this->sessionRepository->find($id);

        return response()->json(new SessionResource($session));
    }
}
