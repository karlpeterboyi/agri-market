<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;
    use BelongsToOrganisation;

    protected $fillable = [

        'farm_id',

        'code',

        'name',

        'type',

        'parent_id',

        'system',
        'organisation_id',

    ];

    protected $casts = [

        'system' => 'boolean',

    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function parent()
    {
        return $this->belongsTo(Account::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class,'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
    
    public function organisation()
{
    return $this->belongsTo(
        Organisation::class
    );
}
}