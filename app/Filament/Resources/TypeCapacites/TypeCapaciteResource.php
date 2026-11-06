<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites;

use  Modules\Resa\Models\Formation;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
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
    protected static ?string $model = Formation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 7;

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

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user?->can('resa::admin') || $user?->can('resa::formation.index')) {
            return $query;
        }

        // Gestionnaire de formation : uniquement les formations qu'il gère.
        return $query->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user?->id));
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
