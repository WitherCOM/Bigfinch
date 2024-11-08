<?php

namespace App\Models;

use App\Enums\CurrencyPosition;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory;
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'iso_code',
        'position',
        'symbol',
        'rate_to_huf',
    ];

    protected $casts = [
        'position' => CurrencyPosition::class
    ];

    public function name(): Attribute
    {
        return Attribute::get(function() {
            $bundle = \ResourceBundle::create(App::currentLocale(), 'ICUDATA-curr');
            return $bundle->get('Currencies')->get($this->iso_code)->get(1);
        });
    }

    public static function iso_codes(): Collection
    {
        $bundle = \ResourceBundle::create('en', 'ICUDATA-curr');
        $currencies = collect($bundle->get('Currencies'));
        return $currencies->keys();
    }
}
