<?php

namespace App\Models;

use Database\Factories\UserFactory;
use App\Casts\ModulesCast;
use App\Enums\Direction;
use App\Enums\Flag;
use App\Models\Modules\ModuleInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PhpParser\Node\Scalar\MagicConst\Dir;
use ReflectionClass;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
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

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
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

    public function getStatisticalTransactionData(Carbon $fromDate, Direction $direction, Currency $displayCurrency): Collection
    {
        $rawTransactions = $this->transactions()
            ->with(['currency','currency.rates'])
            ->where('direction', $direction->value)
            ->where('date','>=', $fromDate)
            ->orderBy('date')
            ->get();
        return $rawTransactions->map(fn(Transaction $transaction) => [
            'date' => $transaction->date,
            'category' => $transaction->category?->name ?? __('Other'),
            'value' => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date)
        ]);
    }
}
