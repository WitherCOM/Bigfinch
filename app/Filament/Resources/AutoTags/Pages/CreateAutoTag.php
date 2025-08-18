<?php

namespace App\Filament\Resources\AutoTags\Pages;

use App\Filament\Resources\AutoTags\AutoTagResource;
use App\Models\AutoTag;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateAutoTag extends CreateRecord
{
    protected static string $resource = AutoTagResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $tag = new AutoTag($data);
        $tag->user_id = Auth::id();
        $tag->save();

        return $tag;
    }
}
