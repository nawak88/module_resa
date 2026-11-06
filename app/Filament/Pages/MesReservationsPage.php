<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\Resa\Filament\Widgets\MesReservationsWidget;

class MesReservationsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon           = 'heroicon-o-calendar-days';
    protected static ?string                 $navigationLabel          = 'Mes réservations';
    protected static ?string                 $title                    = 'Mes réservations';
    protected string                         $view                     = 'resa::filament.pages.mes-reservations-page';
    protected static string|\UnitEnum|null   $navigationGroup          = null;
    protected static bool                    $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderWidgets(): array
    {
        return [MesReservationsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return ['limite' => 0];
    }
}
