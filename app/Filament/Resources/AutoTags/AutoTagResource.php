<?php

namespace App\Filament\Resources\AutoTags;

use App\Enums\NavGroup;
use App\Filament\Resources\AutoTags\Pages\CreateAutoTag;
use App\Filament\Resources\AutoTags\Pages\EditAutoTag;
use App\Filament\Resources\AutoTags\Pages\ListAutoTags;
use App\Models\AutoTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AutoTagResource extends Resource
{
    protected static ?string $model = AutoTag::class;

    protected static string|null|\UnitEnum $navigationGroup = NavGroup::TRANSACTIONS;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        $user = Auth::user();

        return $schema
            ->components([
                TextInput::make('tag'),
                DateTimePicker::make('from')
                    ->timezone($user->user_timezone)
                    ->required(),
                DateTimePicker::make('to')
                    ->timezone($user->user_timezone)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                //
                TextColumn::make('tag'),
                TextColumn::make('from')
                    ->dateTime('Y-m-d H:i:s', $user->user_timezone),
                TextColumn::make('to')
                    ->dateTime('Y-m-d H:i:s', $user->user_timezone),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('to', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAutoTags::route('/'),
            'create' => CreateAutoTag::route('/create'),
            'edit' => EditAutoTag::route('/{record}/edit'),
        ];
    }
}
