<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Transaction');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->can('View:Transaction') && $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('Create:Transaction');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->can('Update:Transaction') && ($transaction->user_id === $user->id) && ! $transaction->trashed();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->can('Delete:Transaction') && $transaction->user_id === $user->id;
    }

    /**
     * Determine whether the user can force delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->can('ForceDelete:Transaction') && $transaction->user_id === $user->id;
    }
}
