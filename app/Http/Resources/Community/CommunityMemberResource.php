<?php

namespace App\Http\Resources\Community;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Community\CommunityMemberService;

class CommunityMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $memberService = app(CommunityMemberService::class);
        
        return $memberService->transformUserToMemberData(
            $this->resource, 
            $request->user()
        );
    }
}
