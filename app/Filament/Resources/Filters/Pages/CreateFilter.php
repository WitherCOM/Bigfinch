<?php

namespace App\Filament\Resources\Filters\Pages;

use App\Filament\Resources\Filters\FilterResource;
use App\Models\Filter;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateFilter extends CreateRecord
{
    protected static string $resource = FilterResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $filter = new Filter($data);
        $filter->user_id = Auth::id();
        $filter->save();

        return $filter;
    }
}
