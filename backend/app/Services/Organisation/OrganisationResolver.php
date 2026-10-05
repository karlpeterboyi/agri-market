<?php

namespace App\Services\Organisation;

use App\Models\User;
use App\Models\Organisation;

class OrganisationResolver
{
    public function current(User $user): ?Organisation
    {
        return $user->organisations()
            ->where('active', true)
            ->with('organisation')
            ->first()?->organisation;
    }
}