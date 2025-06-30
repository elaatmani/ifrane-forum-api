<?php

namespace App\Repositories\Eloquent;

use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class DocumentRepository extends BaseRepository implements DocumentRepositoryInterface
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }


}
