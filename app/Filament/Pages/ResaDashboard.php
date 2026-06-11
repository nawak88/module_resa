<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Dashboard;

class ResaDashboard extends Dashboard
{
    protected static ?string $navigationLabel = 'Accueil';
    protected static ?int    $navigationSort  = 0;

    public function getTitle(): string
    {
        return 'Réservation — Accueil';
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-home';
    }
}


