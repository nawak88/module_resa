<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget;

class RessourcesPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon           = 'heroicon-o-cube';
    protected static ?string                 $navigationLabel          = 'Ressources';
    protected string                         $view                     = 'resa::filament.pages.ressources-page';
    protected static string|\UnitEnum|null   $navigationGroup          = 'Ressources';
    protected static bool                    $shouldRegisterNavigation = false;

    public ?int $filtreCategorie = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $categorieId = request()->query('categorie');
        if ($categorieId && is_numeric($categorieId)) {
            $this->filtreCategorie = (int) $categorieId;
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [RessourceGanttWidget::class];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return ['filtreCategorie' => $this->filtreCategorie];
    }
}
