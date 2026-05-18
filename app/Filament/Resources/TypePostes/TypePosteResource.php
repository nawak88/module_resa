<?php

namespace Modules\Resa\Filament\Resources\TypePostes;

use Modules\Resa\Models\TypePoste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypePostes\Pages\CreateTypePoste;
use Modules\Resa\Filament\Resources\TypePostes\Pages\EditTypePoste;
use Modules\Resa\Filament\Resources\TypePostes\Pages\ListTypePostes;
use Modules\Resa\Filament\Resources\TypePostes\Schemas\TypePosteForm;
use Modules\Resa\Filament\Resources\TypePostes\Tables\TypePostesTable;

class TypePosteResource extends Resource
{
    protected static ?string $model = TypePoste::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 5;

    //protected static ?string $recordTitleAttribute = 'TypePoste';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypePosteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypePostesTable::configure($table);
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
            'index' => ListTypePostes::route('/'),
            'create' => CreateTypePoste::route('/create'),
            'edit' => EditTypePoste::route('/{record}/edit'),
        ];
    }
}
