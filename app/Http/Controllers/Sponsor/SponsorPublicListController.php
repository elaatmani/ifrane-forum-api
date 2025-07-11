<?php

namespace App\Http\Controllers\Sponsor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Sponsor\SponsorPublicResource;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorPublicListController extends Controller
{
    public function __construct(
        private SponsorRepositoryInterface $sponsorRepository
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $sponsors = $this->sponsorRepository->query()
        ->where('is_active', true)
        ->get();

        $sponsors->transform(function($sponsor) {
            return new SponsorPublicResource($sponsor);
        });

        return response()->json($sponsors);
    }
}
