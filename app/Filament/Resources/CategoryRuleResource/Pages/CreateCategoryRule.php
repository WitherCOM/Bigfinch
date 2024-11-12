<?php

namespace App\Filament\Resources\CategoryRuleResource\Pages;

use App\Enums\RuleType;
use App\Filament\Resources\CategoryRuleResource;
use App\Models\Rule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateCategoryRule extends CreateRecord
{
    protected static string $resource = CategoryRuleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $rule = new Rule($data);
        $rule->user_id = Auth::id();
        $rule->type = RuleType::CATEGORY;
        $rule->save();
        return $rule;
    }
}
