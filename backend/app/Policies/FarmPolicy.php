<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    public function view(User $user, Farm $farm): bool
    {
        return $farm->owner_id === $user->id;
    }

    public function update(User $user, Farm $farm): bool
    {
        return $farm->owner_id === $user->id;
    }

    public function delete(User $user, Farm $farm): bool
    {
        return $farm->owner_id === $user->id;
    }
}