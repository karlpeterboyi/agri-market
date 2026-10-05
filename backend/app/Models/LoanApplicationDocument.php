<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationDocument extends Model
{
    protected $fillable = [

        'loan_application_id',

        'document_type',

        'title',

        'file_path',

        'mime_type',

        'file_size',

        'verified',

        'verified_at',

        'verified_by',

        'verification_notes',
    ];

    protected $casts = [

        'verified' => 'boolean',

        'verified_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(
            LoanApplication::class,
            'loan_application_id'
        );
    }

    public function verifier()
    {
        return $this->belongsTo(
            User::class,
            'verified_by'
        );
    }
}