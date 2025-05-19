<?php

namespace App\Models;

use App\Casts\ModulesCast;
use App\Models\Modules\ModuleInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use ReflectionClass;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array'
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function defaultCurrencyId(): Attribute {
        return Attribute::get(function () {
            if (!isset($this->settings['default_currency_id'])) {
                $settings = $this->settings;
                $settings['default_currency_id'] = Currency::first()->id;
                $this->settings = $settings;
                $this->save();
            }
            return $this->settings['default_currency_id'];
        });
    }
}
