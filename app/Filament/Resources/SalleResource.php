<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Modules\Resa\Filament\Resources\SalleResource\Pages\ListSalles;
use Modules\Resa\Filament\Resources\SalleResource\Pages\CreateSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages\EditSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages;
use Modules\Resa\Filament\Resources\SalleResource\RelationManagers;
use Modules\Resa\Models\Salle;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\TypeSalle;

class SalleResource extends Resource
{
    protected static ?string $model = Salle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type_salle_id')
                    ->label('Type de salle')
                    ->options(TypeSalle::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->required()
                    ->native(false)
                    ->live(),

                Select::make('lieu_unite_id')
                    ->label('Lieu')
                    ->options(LieuUnite::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->native(false)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function ($set, $record) {
                        if ($record?->unite_id) {
                            $set('lieu_unite_id', Unite::find($record->unite_id)?->lieu_unite_id);
                        }
                    })
                    ->afterStateUpdated(fn ($set) => $set('unite_id', null)),

                Select::make('unite_id')
                    ->label('Unité')
                    ->options(fn ($get) => Unite::when(
                        $get('lieu_unite_id'),
                        fn ($q, $id) => $q->where('lieu_unite_id', $id),
                    )->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->native(false)
                    ->searchable(),
            TextInput::make('code')
                ->maxLength(255)
                ->default(null),
            TextInput::make('libelle_court')
                ->maxLength(500)
                ->default(null)
                ->label('Titre'),
            Textarea::make('libelle_long')
               
                ->default(null)
                ->label('Description'),
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('resa/salles') // ✅ stocké dans storage/app/public/planificationgpd/materiels
                ->visibility('public'),
            
            Select::make('color')
                ->label('Couleur')
                ->options([
                    'primary'   => 'Primary',
                    'secondary' => 'Secondary',
                    'success'   => 'Success',
                    'danger'    => 'Danger',
                    'warning'   => 'Warning',
                    'info'      => 'Info',
                    'gray'      => 'Gray',
                ])
                ->native(false),
            Textarea::make('data')
                ->columnSpanFull(),
                TextInput::make('capacite')
                    ->numeric()
                    ->minValue(1),
                \Modules\Resa\Filament\Components\EmailListField::make('email')
                    ->visible(fn ($get) => TypeSalle::find($get('type_salle_id'))?->mode === TypeSalle::MODE_CLASSIQUE),
                Select::make('gestionnaires')
                    ->label('Gestionnaires')
                    ->relationship('gestionnaires', 'nom')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Ces utilisateurs peuvent gérer cette salle et valider ses réservations.')
                    ->columnSpanFull()
                    // Pour une salle simulateur, la gestion se fait par formation (Resa > Formations),
                    // pas par salle : éviter de devoir assigner le gestionnaire à deux endroits.
                    ->visible(fn ($get) => TypeSalle::find($get('type_salle_id'))?->mode === TypeSalle::MODE_CLASSIQUE),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre','asc')  
            ->reorderable('ordre') 
            ->defaultPaginationPageOption(25) 
            ->columns([
                TextColumn::make('typeSalle.libelle_court')
                    ->label('Type de salle')
                    ->sortable()
                    ->badge(),
                TextColumn::make('unite.lieuUnite.libelle_court')
                    ->label('Lieu')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('unite.libelle_court')
                    ->label('Unité')
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libelle'),
                TextColumn::make('capacite')
                    ->searchable()
                    ->label('Capacite'),
                TextColumn::make('libelle_long')
                   
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image')
                ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('color')
                   
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('lieu')
                ->label('Lieu')
                ->options(LieuUnite::orderBy('ordre')->pluck('libelle_court', 'id'))
                ->query(fn ($query, $data) => $data['value']
                    ? $query->whereHas('unite', fn ($q) => $q->where('lieu_unite_id', $data['value']))
                    : $query
                ),
            SelectFilter::make('type_salle_id')
                ->label('Type de salle')
                ->options(TypeSalle::orderBy('ordre')->pluck('libelle_court', 'id')),
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toolbarActions([
            DeleteBulkAction::make(),
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
            'index' => ListSalles::route('/'),
            'create' => CreateSalle::route('/create'),
            'edit' => EditSalle::route('/{record}/edit'),
        ];
    }
}
