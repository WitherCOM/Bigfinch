<?php

namespace App\Filament\Resources\Integrations\Pages;

use App\Filament\Resources\Integrations\IntegrationResource;
use App\Models\Integration;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateIntegration extends CreateRecord
{
    protected static string $resource = IntegrationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $integration = new Integration($data);
        $integration->user_id = Auth::id();
        $integration->fillBasics($data['institution_id']);
        $integration->createRequisition();
        $integration->save();
        return $integration;
    }
}
