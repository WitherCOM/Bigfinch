<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'jobs';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'reserved_at' => 'timestamp',
            'available_at' => 'timestamp',
            'created_at' => 'timestamp',
        ];
    }
}
