<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites;

use  Modules\Resa\Models\TypeCapacite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\CreateTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\EditTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\ListTypeCapacites;
use Modules\Resa\Filament\Resources\TypeCapacites\Schemas\TypeCapaciteForm;
use Modules\Resa\Filament\Resources\TypeCapacites\Tables\TypeCapacitesTable;

class TypeCapaciteResource extends Resource
{
    protected static ?string $model = TypeCapacite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 5;

    //protected static ?string $recordTitleAttribute = 'TypeCapacite';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypeCapaciteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeCapacitesTable::configure($table);
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
            'index' => ListTypeCapacites::route('/'),
            'create' => CreateTypeCapacite::route('/create'),
            'edit' => EditTypeCapacite::route('/{record}/edit'),
        ];
    }
}
