<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeSalles\Schemas\TypeSalleForm;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\ListRessourceCategories;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\CreateRessourceCategorie;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\EditRessourceCategorie;
use Modules\Resa\Models\RessourceCategorie;

class RessourceCategorieResource extends Resource
{
    protected static ?string $model = RessourceCategorie::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-tag';
    protected static ?string                 $navigationLabel = 'Catégories de ressources';
    protected static string|\UnitEnum|null   $navigationGroup = 'Ressources';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('resa::admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('libelle')->label('Libellé')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('libelle_court')->label('Libellé court')->maxLength(50),
            TextInput::make('ordre')->label('Ordre')->numeric()->default(0),
            Select::make('icone')
                ->label('Icône')
                ->searchable()
                ->options(TypeSalleForm::iconOptions())
                ->placeholder('Choisir une icône'),
            Select::make('couleur')
                ->label('Couleur')
                ->options(TypeSalleForm::colorOptions())
                ->placeholder('Choisir une couleur'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('ordre')->label('#')->sortable(),
                TextColumn::make('libelle')->label('Catégorie')->searchable()->sortable(),
                TextColumn::make('libelle_court')->label('Court'),
                TextColumn::make('couleur')->label('Couleur')->badge(),
                TextColumn::make('ressources_count')->label('Ressources')
                    ->counts('ressources')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessourceCategories::route('/'),
            'create' => CreateRessourceCategorie::route('/create'),
            'edit'   => EditRessourceCategorie::route('/{record}/edit'),
        ];
    }
}


