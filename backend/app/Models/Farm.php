<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Farm extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BelongsToOrganisation;

    protected $fillable = [
        'owner_id',
        'organisation_id',
        'name',
        'farm_code',
        'description',
        'farm_type',
        'ownership_type',
        'country',
        'region',
        'district',
        'ward',
        'village',
        'address',
        'latitude',
        'longitude',
        'elevation',
        'total_area_hectares',
        'cultivated_area_hectares',
        'irrigated_area_hectares',
        'registration_number',
        'certification',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'elevation' => 'decimal:2',
        'total_area_hectares' => 'decimal:4',
        'cultivated_area_hectares' => 'decimal:4',
        'irrigated_area_hectares' => 'decimal:4',
    ];

    protected static function booted()
    {
        static::creating(function ($farm) {
            if (empty($farm->farm_code)) {
                $farm->farm_code = 'FARM-' . strtoupper(Str::random(8));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function boundary()
    {
        return $this->hasOne(FarmBoundary::class);
    }

    public function fieldBlocks()
    {
        return $this->hasMany(FieldBlock::class);
    }

    public function cropCycles()
    {
        return $this->hasMany(CropCycle::class);
    }

    public function activities()
    {
        return $this->hasMany(FarmActivity::class);
    }

    public function warehouses()
    {
        return $this->hasMany(FarmWarehouse::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
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

public function aiRecommendations()
{
    return $this->hasMany(
        AIRecommendation::class
    );
}

public function weatherStation()
{
    return $this->belongsTo(
        WeatherStation::class
    );
}

    /*
    |--------------------------------------------------------------------------
    | Future Modules
    |--------------------------------------------------------------------------
    |
    | public function cropCalendars()
    | {
    |     return $this->hasMany(CropCalendar::class);
    | }
    |
    | public function loans()
    | {
    |     return $this->hasMany(LoanApplication::class);
    | }
    |
    | public function livestock()
    | {
    |     return $this->hasMany(Livestock::class);
    | }
    |
    */
}
