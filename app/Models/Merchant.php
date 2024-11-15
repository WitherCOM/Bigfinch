<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Merchant extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'search_keys',
        'user_id'
    ];

    protected $casts = [
        'search_keys' => 'array'
    ];

    public $timestamps = false;

    protected static function booting()
    {
        parent::booting();

        parent::creating(function (Merchant $merchant) {
            if (empty($merchant->search_keys))
            {
                $merchant->search_keys = [json_encode($merchant->name)];
            }
        });
    }

    public function keyFactors(): Attribute
    {
        return Attribute::get(fn () => count($this->search_keys));
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function getMerchant(array $data, $user_id)
    {
        $value = floatval($data['transactionAmount']['amount']);
        $name = "";
        if (($value > 0) && array_key_exists('debtorName',$data) && !array_key_exists('creditorName',$data))
        {
            $name = $data['debtorName'];
        }
        else if (($value < 0) && array_key_exists('creditorName',$data) && !array_key_exists('debtorName',$data))
        {
            $name = $data['creditorName'];
        }
        else
        {
            $name = null;
        }
        if (!is_null($name))
        {
            $merchant = Merchant::query()
                ->where('user_id', $user_id)->whereJsonContains('search_keys', json_encode($name))->first();
            if (is_null($merchant)) {
                $merchant = Merchant::create([
                   'name' => $name,
                   'user_id' => $user_id
                ]);
            }
            return $merchant->id;
        }

        return null;
    }
}
