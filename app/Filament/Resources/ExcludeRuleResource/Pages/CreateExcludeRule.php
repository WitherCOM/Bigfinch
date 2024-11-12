<?php

namespace App\Filament\Resources\ExcludeRuleResource\Pages;

use App\Enums\RuleType;
use App\Filament\Resources\ExcludeRuleResource;
use App\Models\Rule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateExcludeRule extends CreateRecord
{
    protected static string $resource = ExcludeRuleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $rule = new Rule($data);
        $rule->user_id = Auth::id();
        $rule->type = RuleType::EXCLUDE;
        $rule->save();
        return $rule;
    }
}
