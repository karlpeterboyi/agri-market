<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FarmWarehouse extends Model
{
    use HasFactory;
    use BelongsToOrganisation;

    protected $fillable = [

        'farm_id',

        'name',

        'code',

        'type',

        'location',

        'description',

        'active',
        'organisation_id',

    ];

    protected $casts = [

        'active' => 'boolean',

    ];

    protected static function booted()
    {
        static::creating(function ($warehouse) {

            if (!$warehouse->code) {

                $warehouse->code =
                    'WH-' . strtoupper(Str::random(8));

            }

        });
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }
    
    public function organisation()
{
    return $this->belongsTo(
        Organisation::class
    );
}
}