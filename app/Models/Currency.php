<?php

namespace App\Models;

use ResourceBundle;
use Database\Factories\CurrencyFactory;
use App\Enums\CurrencyPosition;
use App\Enums\Direction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use PHPUnit\Util\Xml;

class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'iso_code',
        'position',
        'symbol'
    ];

    protected $casts = [
        'position' => CurrencyPosition::class
    ];

    public function name(): Attribute
    {
        return Attribute::get(function() {
            $bundle = ResourceBundle::create(App::currentLocale(), 'ICUDATA-curr');
            return $bundle->get('Currencies')->get($this->iso_code)->get(1);
        });
    }

    public function rate(): Attribute
    {
        return Attribute::get(fn() => $this->rates()->latest()->first()?->rate_to_huf ?? 1);
    }

    public function nearestRate(Carbon $day)
    {
        $lower = $this->rates->filter(fn(CurrencyRate $rate) => $rate->created_at->lte($day))->sortByDesc('created_at')->first();
        $upper = $this->rates->filter(fn(CurrencyRate $rate) => $rate->created_at->gte($day))->sortBy('created_at')->first();
        if (is_null($lower) && is_null($upper))
        {
            return 1;
        }
        else if (is_null($lower) && !is_null($upper))
        {
            return $upper->rate_to_huf;
        }
        else if (!is_null($lower) && is_null($upper))
        {
            return $lower->rate_to_huf;
        }
        else
        {
            return ($lower->rate_to_huf + $upper->rate_to_huf) / 2;
        }

    }

    public static function iso_codes(): Collection
    {
        $bundle = ResourceBundle::create('en', 'ICUDATA-curr');
        $currencies = collect($bundle->get('Currencies'));
        return $currencies->keys();
    }

    public function rates(): HasMany
    {
        return $this->hasMany(CurrencyRate::class);
    }

    public function format($value)
    {
        if ($this->position === CurrencyPosition::PREFIX)
        {
            return "$this->symbol $value";
        }
        else
        {
            return "$value $this->symbol";
        }
    }
}
