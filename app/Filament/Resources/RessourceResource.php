<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Modules\RH\Models\Unite;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\CreateRessource;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\EditRessource;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\ListRessources;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;

class RessourceResource extends Resource
{
    protected static ?string $model = Ressource::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cube';
    protected static ?string                 $navigationLabel = 'Ressources';
    protected static string|\UnitEnum|null   $navigationGroup = 'Ressources';
    protected static ?int                    $navigationSort  = 11;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('resa::admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ressource_categorie_id')
                ->label('Catégorie')
                ->options(RessourceCategorie::orderBy('ordre')->pluck('libelle', 'id'))
                ->required()
                ->columnSpanFull(),

            Select::make('unite_id')
                ->label('Unité')
                ->options(Unite::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                ->searchable()
                ->nullable(),

            TextInput::make('libelle')->label('Libellé')->required()->maxLength(255),
            TextInput::make('libelle_court')->label('Libellé court')->maxLength(50),
            TextInput::make('ordre')->label('Ordre')->numeric()->default(0),
            Toggle::make('actif')->label('Actif')->default(true)
                ->helperText('Décoché = retiré du catalogue (ne plus proposer du tout).'),

            Select::make('statut')
                ->label('Disponibilité')
                ->options(Ressource::STATUTS)
                ->default(Ressource::STATUT_DISPONIBLE)
                ->required()
                ->native(false)
                ->helperText('Permet de signaler un équipement en entretien ou indisponible sans le retirer du catalogue.'),

            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('ordre')->label('#')->sortable(),
                TextColumn::make('categorie.libelle')->label('Catégorie')->sortable()->badge(),
                TextColumn::make('libelle')->label('Ressource')->searchable()->sortable(),
                TextColumn::make('libelle_court')->label('Court'),
                IconColumn::make('actif')->label('Actif')->boolean(),
                TextColumn::make('statut')
                    ->label('Disponibilité')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Ressource::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Ressource::STATUT_DISPONIBLE   => 'success',
                        Ressource::STATUT_ENTRETIEN     => 'warning',
                        Ressource::STATUT_INDISPONIBLE => 'danger',
                        default                         => 'gray',
                    }),
                TextColumn::make('reservations_count')->label('Réservations')
                    ->counts('reservations')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('ressource_categorie_id')
                    ->label('Catégorie')
                    ->options(RessourceCategorie::orderBy('ordre')->pluck('libelle', 'id')),
                SelectFilter::make('statut')
                    ->label('Disponibilité')
                    ->options(Ressource::STATUTS),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessources::route('/'),
            'create' => CreateRessource::route('/create'),
            'edit'   => EditRessource::route('/{record}/edit'),
        ];
    }
}
