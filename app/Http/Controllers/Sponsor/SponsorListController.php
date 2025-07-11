<?php

namespace App\Http\Controllers\Sponsor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorListController extends Controller
{
    protected $sponsorRepository;

    public function __construct(SponsorRepositoryInterface $sponsorRepository)
    {
        $this->sponsorRepository = $sponsorRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $sponsors = $this->sponsorRepository
        ->query()
        ->when(!auth()->user()->hasRole('admin'), function($query) {
            $query->where('is_active', true);
        })
        ->when($request->search, function($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->paginate($perPage);

        $sponsors->getCollection()->transform(function($sponsor) {
            $sponsor->image = asset('storage/' . $sponsor->image);
            return $sponsor;
        });

        return response()->json([
            'message' => 'Sponsors fetched successfully',
            'data' => $sponsors,
        ]);
    }
}
