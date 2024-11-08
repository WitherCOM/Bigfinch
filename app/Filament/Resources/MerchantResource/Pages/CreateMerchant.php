<?php

namespace App\Filament\Resources\MerchantResource\Pages;

use App\Filament\Resources\MerchantResource;
use App\Models\Merchant;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMerchant extends CreateRecord
{
    protected static string $resource = MerchantResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $merchant = new Merchant($data);
        $merchant->user_id = Auth::id();
        $merchant->save();

        return $merchant;
    }
}
