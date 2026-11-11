<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:File');
    }

    public function view(User $user, File $file): bool
    {
        return $user->can('View:File') && $file->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('Create:File');
    }

    public function update(User $user, File $file): bool
    {
        return $user->can('Update:File') && $file->user_id === $user->id;
    }

    public function delete(User $user, File $file): bool
    {
        return $user->can('Delete:File') && $file->user_id === $user->id;
    }
}
