<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceReservation;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\ListRessourceReservations;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\CreateRessourceReservation;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\EditRessourceReservation;

class RessourceReservationResource extends Resource
{
    protected static ?string $model = RessourceReservation::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cube';
    protected static ?string                 $navigationLabel = 'Réservations matériels';
    protected static string|\UnitEnum|null   $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('resa::reservation.index')
            || $user?->can('resa::admin');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('ressource_id')
                    ->label('Ressource / Matériel')
                    ->options(fn () => Ressource::where('actif', true)->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Select::make('user_id')
                    ->label('Réservé par')
                    ->options(fn () => User::orderBy('nom')->pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->default(auth()->id()),

                DateTimePicker::make('debut')
                    ->label('Début')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('fin')
                    ->label('Fin')
                    ->required()
                    ->seconds(false)
                    ->after('debut'),

                Textarea::make('libelle')
                    ->label('Objet')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('debut', 'desc')
            ->columns([
                TextColumn::make('ressource.libelle_court')
                    ->label('Ressource')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ressource.categorie.libelle')
                    ->label('Catégorie')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('libelle')
                    ->label('Objet')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('user.nom')
                    ->label('Réservé par')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('debut')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('validation')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Validée' : 'En attente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('validatedBy.nom')
                    ->label('Validée par')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('validated_at')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('validation')
                    ->label('Statut')
                    ->options([
                        '0' => 'En attente',
                        '1' => 'Validée',
                    ]),

                SelectFilter::make('ressource_id')
                    ->label('Ressource')
                    ->options(fn () => Ressource::where('actif', true)->orderBy('libelle_court')->pluck('libelle_court', 'id')),
            ])
            ->recordActions([
                Action::make('toggleValidation')
                    ->label(fn (RessourceReservation $r) => $r->isValide() ? 'Invalider' : 'Valider')
                    ->icon(fn (RessourceReservation $r) => $r->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (RessourceReservation $r) => $r->isValide() ? 'warning' : 'success')
                    ->iconButton()
                    ->requiresConfirmation(fn (RessourceReservation $r) => ! $r->isValide())
                    ->visible(fn () => auth()->user()?->can('resa::admin'))
                    ->action(function (RessourceReservation $r): void {
                        $r->isValide() ? $r->invalider() : $r->valider(auth()->user());
                        Notification::make()
                            ->title($r->fresh()->isValide() ? 'Validée' : 'Invalidée')
                            ->success()->send();
                    }),

                \Filament\Actions\EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->visible(fn (RessourceReservation $r) =>
                        auth()->user()?->can('resa::admin') || auth()->id() === $r->user_id
                    ),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkActionGroup::make([
                    BulkAction::make('validerSelection')
                        ->label('Valider la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (RessourceReservation $r) => $r->valider(auth()->user()));
                            Notification::make()->title($records->count() . ' réservation(s) validée(s)')->success()->send();
                        }),

                    BulkAction::make('invaliderSelection')
                        ->label('Invalider la sélection')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (RessourceReservation $r) => $r->invalider());
                            Notification::make()->title($records->count() . ' réservation(s) invalidée(s)')->warning()->send();
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'ressource.categorie', 'validatedBy']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessourceReservations::route('/'),
            'create' => CreateRessourceReservation::route('/create'),
            'edit'   => EditRessourceReservation::route('/{record}/edit'),
        ];
    }
}
