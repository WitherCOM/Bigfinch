<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\GocardlessToken;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GocardlessTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can_manage_settings;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GocardlessToken $token): bool
    {
        return $user->can_manage_settings;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can_manage_settings;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GocardlessToken $token): bool
    {
        return $user->can_manage_settings;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GocardlessToken $token): bool
    {
        return $user->can_manage_settings;
    }
}
