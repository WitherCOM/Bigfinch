<?php

namespace App\Filament\Resources\Integrations\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use App\Filament\Resources\Integrations\IntegrationResource;
use App\Jobs\SyncTransactions;
use App\Models\Integration;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;

class ListIntegrations extends ListRecords
{
    protected static string $resource = IntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('sync_all')
            ->action(function () {
                $integrations = Auth::user()->integrations->map(function (Integration $integration) {
                    return new SyncTransactions($integration);
                });
                Bus::batch($integrations)->dispatch();
            })
        ];
    }
}
