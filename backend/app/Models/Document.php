<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'organisation_id',

        'uploaded_by',

        'documentable_type',

        'documentable_id',

        'name',

        'original_name',

        'disk',

        'path',

        'mime_type',

        'size',

        'category',

        'visibility',

        'metadata',

    ];

    protected $casts = [

        'metadata' => 'array',

    ];

    protected static function booted()
    {
        static::creating(function ($document) {

            $document->uuid ??= (string) Str::uuid();

        });
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function organisation()
    {
        return $this->belongsTo(
            Organisation::class
        );
    }
}