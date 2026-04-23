<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\EditReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\ListReservations;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;
use App\Models\User;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string                 $navigationLabel = 'Reservations';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 1;

    /* ── Acces ────────────────────────────────────────────────────────────── */

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canAny(['resa::reservation.index']);
    }

    /* ── Formulaire ───────────────────────────────────────────────────────── */

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Grid::make(2)->schema([
                TextInput::make('libelle_court')
                    ->label('Intitule de la reservation')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('user_id')
                    ->label('Demandeur')
                    ->options(fn () => User::orderBy('nom')->pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->default(auth()->id()),

                Select::make('salle_poste_id')
                    ->label('Poste')
                    ->options(function () {
                        return SallePoste::with('salle')
                            ->get()
                            ->mapWithKeys(fn ($poste) =>
                                [$poste->id => $poste->salle->libelle_court . ' — ' . $poste->libelle_court]
                            );
                    })
                    ->searchable()
                    ->required(),

                DateTimePicker::make('debut')
                    ->label('Date debut')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('fin')
                    ->label('Date fin')
                    ->required()
                    ->seconds(false)
                    ->after('debut'),

                CheckboxList::make('capacites_utilisees')
                    ->label('Capacites utilisees')
                    ->options(TypeCapacite::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->columns(3)
                    ->columnSpanFull(),

                Textarea::make('libelle_long')
                    ->label('Informations complementaires')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    /* ── Table ────────────────────────────────────────────────────────────── */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('debut', 'desc')
            ->columns([
                TextColumn::make('libelle_court')
                    ->label('Reservation')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('user.nom')
                    ->label('Demandeur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sallePoste.salle.libelle_court')
                    ->label('Salle')
                    ->sortable(),

                TextColumn::make('sallePoste.libelle_court')
                    ->label('Poste')
                    ->sortable(),

                TextColumn::make('capacites_label')
                    ->label('Capacites')
                    ->getStateUsing(function (Reservation $record): string {
                        $ids = $record->capacites_utilisees;
                        if (empty($ids) || ! is_array($ids)) return '—';
                        return TypeCapacite::whereIn('id', $ids)
                            ->orderBy('ordre')
                            ->pluck('libelle_court')
                            ->join(', ') ?: '—';
                    }),

                TextColumn::make('debut')
                    ->label('Debut')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('validation')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Validee' : 'En attente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('validatedBy.nom')
                    ->label('Valide par')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('validated_at')
                    ->label('Valide le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('validation')
                    ->label('Statut')
                    ->options([
                        '0' => 'En attente',
                        '1' => 'Validee',
                    ]),

                SelectFilter::make('salle')
                    ->label('Salle')
                    ->relationship('sallePoste.salle', 'libelle_court'),
            ])
            ->recordActions([
                // Toggle validation rapide
                Action::make('toggleValidation')
                    ->label(fn (Reservation $record) => $record->isValide() ? 'Invalider' : 'Valider')
                    ->icon(fn (Reservation $record) => $record->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Reservation $record) => $record->isValide() ? 'warning' : 'success')
                    ->iconButton()
                    ->requiresConfirmation(fn (Reservation $record) => ! $record->isValide())
                    ->modalHeading('Valider la reservation ?')
                    ->modalDescription(fn (Reservation $record) => 'Confirmer la validation de « ' . $record->libelle_court . ' » ?')
                    ->visible(fn () => auth()->user()?->can('resa::validation_salle'))
                    ->action(function (Reservation $record): void {
                        $record->isValide()
                            ? $record->invalider()
                            : $record->valider(auth()->user());

                        Notification::make()
                            ->title($record->fresh()->isValide() ? 'Reservation validee' : 'Reservation invalidee')
                            ->success()
                            ->send();
                    }),

                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkActionGroup::make([
                    BulkAction::make('validerSelection')
                        ->label('Valider la selection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::validation_salle'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (Reservation $record) =>
                                $record->valider(auth()->user())
                            );

                            Notification::make()
                                ->title($records->count() . ' reservation(s) validee(s)')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('invaliderSelection')
                        ->label('Invalider la selection')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::validation_salle'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (Reservation $record) => $record->invalider());

                            Notification::make()
                                ->title($records->count() . ' reservation(s) invalidee(s)')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }

    /* ── Relations ────────────────────────────────────────────────────────── */

    public static function getRelations(): array
    {
        return [];
    }

    /* ── Pages ────────────────────────────────────────────────────────────── */

    public static function getPages(): array
    {
        return [
            'index'  => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit'   => EditReservation::route('/{record}/edit'),
        ];
    }

    /* ── Query de base ────────────────────────────────────────────────────── */

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'sallePoste.salle', 'validatedBy']);
    }
}
