<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Merchant extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'search_keys'
    ];

    protected $casts = [
        'search_keys' => 'array'
    ];

    public $timestamps = false;

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
                ->where('user_id', $user_id)->whereJsonContains('search_keys', $name)->first();
            if (is_null($merchant)) {
                $merchant = new Merchant;
                $merchant->name = $name;
                $merchant->search_keys = [$name];
                $merchant->user_id = $user_id;
                $merchant->save();
                $merchant->refresh();
            }
            return $merchant->id;
        }

        return null;
    }
}
