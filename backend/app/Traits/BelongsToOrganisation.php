<?php

namespace App\Traits;

use App\Models\Organisation;

trait BelongsToOrganisation
{
    public function organisation()
    {
        return $this->belongsTo(
            Organisation::class
        );
    }

    public function scopeForOrganisation($query, $organisationId)
    {
        return $query->where(
            'organisation_id',
            $organisationId
        );
    }
}