<?php

namespace App\Http\Controllers\Sponsor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\SponsorUpdateRequest;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorUpdateController extends Controller
{
    protected $sponsorRepository;

    public function __construct(SponsorRepositoryInterface $sponsorRepository)
    {
        $this->sponsorRepository = $sponsorRepository;
    }
    /**
     * Handle the incoming request.
     */
    public function __invoke(SponsorUpdateRequest $request, $id)
    {
        $data = $request->validated();

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('images/sponsors', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $sponsor = $this->sponsorRepository->update($id, $data);

        return response()->json([
            'message' => 'Sponsor updated successfully',
            'data' => $sponsor,
        ]);
    }
}
