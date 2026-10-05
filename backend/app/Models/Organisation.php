<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organisation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        'uuid',

        'name',

        'slug',

        'type',

        'registration_number',

        'tax_number',

        'email',

        'phone',

        'website',

        'logo',

        'description',

        'country',

        'region',

        'district',

        'ward',

        'village',

        'address',

        'latitude',

        'longitude',

        'verified',

        'active',

        'settings',

    ];

    protected $casts = [

        'verified' => 'boolean',

        'active' => 'boolean',

        'settings' => 'array',

    ];

    protected static function booted()
    {
        static::creating(function ($organisation) {

            if (!$organisation->uuid) {
                $organisation->uuid = (string) Str::uuid();
            }

            if (!$organisation->slug) {
                $organisation->slug = Str::slug($organisation->name);
            }

        });
    }

    public function members()
    {
        return $this->hasMany(
            OrganisationMember::class
        );
    }

    public function farms()
    {
        return $this->hasMany(
            Farm::class
        );
    }
    
    public function warehouses()
{
    return $this->hasMany(
        FarmWarehouse::class
    );
}

public function loans()
{
    return $this->hasMany(
        Loan::class
    );
}

public function accounts()
{
    return $this->hasMany(
        Account::class
    );
}

public function journalEntries()
{
    return $this->hasMany(
        JournalEntry::class
    );
}

public function researchProjects()
{
    return $this->hasMany(
        ResearchProject::class
    );
}

public function documents()
{
    return $this->morphMany(
        Document::class,
        'documentable'
    );
}

public function tasks()
{
    return $this->hasMany(Task::class);
}

public function workflows()
{
    return $this->hasMany(Workflow::class);
}

}