<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryUpdate extends Model
{
    protected $fillable = [
        'transport_assignment_id',
        'status',
        'notes'
    ];

    public function assignment()
    {
        return $this->belongsTo(TransportAssignment::class);
    }
}