<?php

namespace Modules\Resa\Filament\Resources\TypeSalles;

use Modules\Resa\Models\TypeSalle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\CreateTypeSalle;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\EditTypeSalle;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\ListTypeSalles;
use Modules\Resa\Filament\Resources\TypeSalles\Schemas\TypeSalleForm;
use Modules\Resa\Filament\Resources\TypeSalles\Tables\TypeSallesTable;

class TypeSalleResource extends Resource
{
    protected static ?string $model = TypeSalle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 4;

    //protected static ?string $recordTitleAttribute = 'TypeSalle';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypeSalleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeSallesTable::configure($table);
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
            'index' => ListTypeSalles::route('/'),
            'create' => CreateTypeSalle::route('/create'),
            'edit' => EditTypeSalle::route('/{record}/edit'),
        ];
    }
}
