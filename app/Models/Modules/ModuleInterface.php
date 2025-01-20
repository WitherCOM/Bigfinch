<?php

namespace App\Models\Modules;


use App\Models\User;
use Illuminate\Support\Collection;

interface ModuleInterface
{
    public function getName(): string;
    public function before(Collection $transactions, User $user): Collection;
    public function after(User $user): void;
}
