<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CurrencyPolicy
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
    public function view(User $user, Currency $currency): bool
    {
        return true;
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
    public function update(User $user, Currency $currency): bool
    {
        return $user->can_manage_settings;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Currency $currency): bool
    {
        return $user->can_manage_settings;
    }
}
