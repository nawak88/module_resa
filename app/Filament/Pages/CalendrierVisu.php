<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;

use Modules\Resa\Models\Reservation;
use Modules\Resa\Filament\Widgets\CalendrierWidget;


class CalendrierVisu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Calendrier';
    protected string $view = 'resa::filament.pages.calendrier-visu';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('resa::reservation.view') ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [CalendrierWidget::class];
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            CalendrierWidget::class => [
                'adminMode' => true,
            ],
        ];
    }
}




