<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OnboardingRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        $user = $this->userRepository->find(auth()->id());

        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $profile = $user->profile;

        if(!$profile) {
            $profile = $user->profile()->create();
        }

        return response()->json([
            'profile' => [
                'full_name' => $user->name,
                'country_id' => $profile->country_id,
                'about' => $profile->about,
                'linkedin_url' => $profile->linkedin_url,
                'instagram_url' => $profile->instagram_url,
                'twitter_url' => $profile->twitter_url,
                'facebook_url' => $profile->facebook_url,
                'youtube_url' => $profile->youtube_url,
                'github_url' => $profile->github_url,
                'website_url' => $profile->website_url,
                'contact_email' => $profile->contact_email ?? $user->email,
                'language' => $profile->language ?? 'en',
                'profile_image' => $user->profile_image,
                'address' => [
                    'street' => $profile->street,
                    'city' => $profile->city,
                    'state' => $profile->state,
                    'country_id' => $profile->country_id,
                    'postal_code' => $profile->postal_code
                ]
            ],
        ]);
    }

    public function update(OnboardingRequest $request)
    {
        $user = $this->userRepository->find(auth()->id());

        if(!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $data = $request->validated();
        $profile = $user->profile;

        $userFields = [
            'name' => $data['full_name'] ?? $user->name,
            'is_completed' => true,
        ];


        if($request->hasFile('profile_image')) {
            $userFields['profile_image'] = $request->file('profile_image')->store('users', 'public');
        }

        $user->update($userFields);


        $profileFields = [
            'country_id' => $data['country_id'] ?? $profile->country_id,
            'about' => $data['about'] ?? $profile->about,
            'linkedin_url' => $data['linkedin_url'] ?? $profile->linkedin_url,
            'instagram_url' => $data['instagram_url'] ?? $profile->instagram_url,
            'twitter_url' => $data['twitter_url'] ?? $profile->twitter_url,
            'facebook_url' => $data['facebook_url'] ?? $profile->facebook_url,
            'youtube_url' => $data['youtube_url'] ?? $profile->youtube_url,
            'github_url' => $data['github_url'] ?? $profile->github_url,
            'website_url' => $data['website_url'] ?? $profile->website_url,
            'contact_email' => $data['contact_email'] ?? $profile->contact_email,
            'language' => $data['language'] ?? $profile->language,
            'street' => $data['street'] ?? $profile->street,
            'city' => $data['city'] ?? $profile->city,
            'state' => $data['state'] ?? $profile->state,
            'postal_code' => $data['postal_code'] ?? $profile->postal_code,
        ];

        $user->profile->update($profileFields);

        return response()->json([
            'message' => 'User updated successfully'
        ]);
    }

}
