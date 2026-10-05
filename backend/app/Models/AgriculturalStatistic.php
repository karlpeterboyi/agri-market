<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgriculturalStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_code', 'indicator_name', 'category',
        'region', 'district', 'year', 'month',
        'value', 'unit', 'source', 'notes',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'year' => 'integer',
        'month' => 'integer',
    ];
}
