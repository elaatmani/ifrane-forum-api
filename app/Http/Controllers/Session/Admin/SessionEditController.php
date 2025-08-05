<?php

namespace App\Http\Controllers\Session\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\SessionRepositoryInterface;
use App\Http\Resources\Session\Admin\SessionEditResource;

class SessionEditController extends Controller
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

        return response()->json(new SessionEditResource($session));
    }
}
