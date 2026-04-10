<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\ListReservations;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\EditReservation;

use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel= 'Reservations';

    public static function canAccess(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        
        return  auth()->user()->canAny(['resa::reservation.index']);
    }


    public static function form(Schema $schema): Schema
    {
        return $schema
        ->components([
            TextInput::make('libelle_court')
                ->required()
                ->label('Titre de la Demande'),
            Select::make('salle_id')
                ->label('Salle')
                ->options(Salle::all()->pluck('libelle_court', 'id'))
                ->searchable()
                ->required(),
            DateTimePicker::make('debut')
                ->label('Début')
                ->required(),
            DateTimePicker::make('fin')
                ->label('Fin')
                ->required(),
            Textarea::make('libelle_long')
            ->label('Description de la Demande'),
            TextInput::make('display_name')
                ->required()
                ->label('Nom Prenom'),
            TextInput::make('email')
                ->required()
                ->label('Votre email'),
            TextInput::make('nid')
                ->required()
                ->label('Votre Nid'),
            //ColorPicker::make('couleur'),
            // Select::make('rh_marin_id')
            //         ->label('Marin')
            //         ->relationship('rhMarin', 'nom')
            //         ->searchable()
            //         ->preload()
            //         ->required(),
        ]);

    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('libelle_court')->label('Demande')->searchable(),
            TextColumn::make('salle.libelle_court')->label('Salle'),
            TextColumn::make('display_name')->label('Demandeur'),
            TextColumn::make('debut')->dateTime('d/m/Y H:i')->label('Début'),
            TextColumn::make('fin')->dateTime('d/m/Y H:i')->label('Fin'),

            // ✅ Colonne validation avec badge
            TextColumn::make('validation')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn ($state) => $state ? 'Validée' : 'En attente')
                ->color(fn ($state) => $state ? 'success' : 'warning'),
        ])
        ->filters([
            \Filament\Tables\Filters\SelectFilter::make('validation')
                ->label('Statut')
                ->options([
                    '0' => 'En attente',
                    '1' => 'Validée',
                ]),
        ])
        ->recordActions([
            // ✅ Toggle rapide validation
            \Filament\Actions\Action::make('toggleValidation')
                ->label(fn (Reservation $record) => $record->validation ? 'Invalider' : 'Valider')
                ->icon(fn (Reservation $record) => $record->validation ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (Reservation $record) => $record->validation ? 'warning' : 'success')
                ->iconButton()
                ->requiresConfirmation(fn (Reservation $record) => !$record->validation)
                ->modalHeading('Valider la réservation ?')
                ->modalDescription(fn (Reservation $record) => "Confirmer la validation de « {$record->libelle_court} » ?")
                ->action(function (Reservation $record): void {
                    $record->update(['validation' => !$record->validation]);

                    \Filament\Notifications\Notification::make()
                        ->title($record->validation ? 'Réservation validée' : 'Réservation invalidée')
                        ->success()
                        ->send();
                }),

            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        ->toolbarActions([
            DeleteBulkAction::make(),

            // ✅ Validation en masse
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\BulkAction::make('validerSelection')
                    ->label('Valider la sélection')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (\Illuminate\Database\Eloquent\Collection $records) =>
                        $records->each->update(['validation' => 1])
                    ),
            ]),
        ]);
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
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit' => EditReservation::route('/{record}/edit'),
            
            
        ];
    }
}
