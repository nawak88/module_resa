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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;

class SalleResource extends Resource
{
    protected static ?string $model = Salle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
              
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre','asc')  
            ->reorderable('ordre') 
            ->defaultPaginationPageOption(25) 
            ->columns([
                
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
        ->filters([])
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
