<?php

namespace App\Http\Controllers\Sponsor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\SponsorDeleteRequest;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorDeleteController extends Controller
{
    protected $sponsorRepository;

    public function __construct(SponsorRepositoryInterface $sponsorRepository)
    {
        $this->sponsorRepository = $sponsorRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(SponsorDeleteRequest $request, $id)
    {
        $sponsor = $this->sponsorRepository->delete($id);

        return response()->json([
            'message' => 'Sponsor deleted successfully',
            'data' => $sponsor,
        ]);
    }
}
