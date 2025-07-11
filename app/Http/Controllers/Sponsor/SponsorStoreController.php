<?php

namespace App\Http\Controllers\Sponsor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\SponsorStoreRequest;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorStoreController extends Controller
{
    protected $sponsorRepository;

    public function __construct(SponsorRepositoryInterface $sponsorRepository)
    {
        $this->sponsorRepository = $sponsorRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(SponsorStoreRequest $request)
    {

        $data = $request->validated();

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images/sponsors', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $sponsor = $this->sponsorRepository->create($data);

        return response()->json([
            'message' => 'Sponsor created successfully',
            'data' => $sponsor,
        ]);
    }
}
