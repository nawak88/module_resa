<?php

namespace Modules\Resa\Filament\Resources\SallePostes;

use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Resa\Filament\Resources\SallePostes\Pages\CreateSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Pages\EditSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Pages\ListSallePostes;
use Modules\Resa\Filament\Resources\SallePostes\Pages\ViewSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Schemas\SallePosteForm;
use Modules\Resa\Filament\Resources\SallePostes\Schemas\SallePosteInfolist;
use Modules\Resa\Filament\Resources\SallePostes\Tables\SallePostesTable;

class SallePosteResource extends Resource
{
    protected static ?string $model = SallePoste::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 3;

  //    protected static ?string $recordTitleAttribute = 'SallePoste';
    // protected static ?string $recordTitleAttribute = 'libelle_court';  

    public static function form(Schema $schema): Schema
    {
        return SallePosteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SallePosteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SallePostesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('resa::admin')
            || $user?->can('resa::gestionnaire_salle');
    }

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user?->can('resa::admin')) {
            return $query;
        }

        // Gestionnaire : uniquement les postes de ses salles
        $salleIds = Salle::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user?->id))->pluck('id');

        return $query->whereIn('salle_id', $salleIds);
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
            'index' => ListSallePostes::route('/'),
            'create' => CreateSallePoste::route('/create'),
            'view' => ViewSallePoste::route('/{record}'),
            'edit' => EditSallePoste::route('/{record}/edit'),
        ];
    }
}
