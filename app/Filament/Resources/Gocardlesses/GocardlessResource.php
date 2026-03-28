<?php

namespace App\Filament\Resources\Gocardlesses;

use App\Enums\NavGroup;
use App\Filament\Resources\Gocardlesses\RelationManagers\IntegrationsRelationManager;
use App\Filament\Resources\Gocardlesses\RelationManagers\RequisitionsRelationManager;
use App\Filament\Resources\Gocardlesses\Pages\CreateGocardless;
use App\Filament\Resources\Gocardlesses\Pages\EditGocardless;
use App\Filament\Resources\Gocardlesses\Pages\ListGocardlesses;
use App\Filament\Resources\Gocardlesses\Pages\ViewGocardless;
use App\Filament\Resources\Gocardlesses\Schemas\GocardlessForm;
use App\Filament\Resources\Gocardlesses\Tables\GocardlessesTable;
use App\Models\GocardlessToken;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GocardlessResource extends Resource
{
    protected static ?string $model = GocardlessToken::class;

    protected static string | \UnitEnum | null $navigationGroup = NavGroup::ADMIN;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;



    public static function form(Schema $schema): Schema
    {
        return GocardlessForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GocardlessesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('secret_id'),
            TextEntry::make('access_token_expires_at')
                ->label('Access token expires')
                ->dateTime()
                ->placeholder('No token'),
            TextEntry::make('refresh_token_expires_at')
                ->label('Refresh token expires')
                ->dateTime()
                ->placeholder('No token'),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            IntegrationsRelationManager::make(),
            RequisitionsRelationManager::make()
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGocardlesses::route('/'),
            'create' => CreateGocardless::route('/create'),
            'view' => ViewGocardless::route('/{record}'),
            'edit' => EditGocardless::route('/{record}/edit'),
        ];
    }
}
