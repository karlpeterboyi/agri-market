<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [

        'farm_warehouse_id',

        'name',

        'sku',

        'category',

        'brand',

        'unit',

        'quantity',

        'minimum_quantity',

        'unit_cost',

        'batch_number',

        'manufactured_at',

        'expiry_date',

        'supplier',

        'metadata',

    ];

    protected $casts = [

        'metadata' => 'array',

        'manufactured_at' => 'date',

        'expiry_date' => 'date',

    ];

    protected static function booted()
    {
        static::creating(function ($item) {

            if (!$item->sku) {

                $item->sku =
                    'SKU-' . strtoupper(Str::random(10));

            }

        });
    }

    public function warehouse()
    {
        return $this->belongsTo(
            FarmWarehouse::class,
            'farm_warehouse_id'
        );
    }

    public function stockMovements()
    {
        return $this->hasMany(
            StockMovement::class
        );
    }
}