<?php

namespace App\Repositories\Eloquent;

use App\Models\Sponsor;
use App\Repositories\Contracts\SponsorRepositoryInterface;

class SponsorRepository extends BaseRepository implements SponsorRepositoryInterface
{
    public function __construct(Sponsor $model)
    {
        parent::__construct($model);
    }


}
