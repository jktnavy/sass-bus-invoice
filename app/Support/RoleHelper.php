<?php

namespace App\Support;

use App\Models\User;

class RoleHelper
{
    public static function hasAnyRole(?User $user, array $roles): bool
    {
        if (! $user || ! $user->is_active) {
            return false;
        }

        if ($user->role === 'superadmin') {
            return true;
        }

        return in_array($user->role, $roles, true);
    }
}
