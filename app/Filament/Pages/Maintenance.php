<?php

namespace App\Filament\Pages;

use App\Enums\NavGroup;
use App\Filament\Pages\Maintenance\Widgets\JobsTable;
use App\Filament\Pages\Maintenance\Widgets\MigrationsTable;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Maintenance extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = NavGroup::ADMIN;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    public static function canAccess(): bool
    {
        return auth()->user()?->can_manage_settings ?? false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents([
                        JobsTable::class,
                        MigrationsTable::class,
                    ])),
            ]);
    }
}
