<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget;

class ReservationsFormationPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon           = 'heroicon-o-clipboard-document-list';
    protected static ?string                 $navigationLabel          = 'Réservations formation';
    protected static ?string                 $title                    = 'Toutes les réservations';
    protected string                         $view                     = 'resa::filament.pages.reservations-formation-page';
    protected static string|\UnitEnum|null   $navigationGroup          = null;
    protected static bool                    $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return SuiviFormationsWidget::canView();
    }

    protected function getHeaderWidgets(): array
    {
        return [SuiviFormationsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        // "Voir tout" depuis le dashboard (qui ne montre que le mois en cours par
        // défaut) : ici on part sur toute la période, le gestionnaire affine ensuite
        // avec les filtres déjà disponibles sur le widget (statut, période, nom).
        return ['filtrePeriode' => 'tout'];
    }
}
