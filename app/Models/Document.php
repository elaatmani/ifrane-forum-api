<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory;//, SoftDeletes;

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'file_url',
        'thumbnail_url',
        'type',
        'size',
        'extension',
        'mime_type',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
