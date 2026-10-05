<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [

        'inventory_item_id',

        'farm_activity_id',

        'type',

        'quantity',

        'balance_after',

        'remarks',

    ];

    public function inventoryItem()
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }

    public function farmActivity()
    {
        return $this->belongsTo(
            FarmActivity::class
        );
    }
}