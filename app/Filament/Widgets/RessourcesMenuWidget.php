<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Widgets\Widget;
use Modules\Resa\Filament\Pages\RessourcesPage;
use Modules\Resa\Models\RessourceCategorie;

class RessourcesMenuWidget extends Widget
{
    protected string $view = 'resa::filament.widgets.ressources-menu-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy      = false;
    protected static ?int $sort        = 20;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getCategories()
    {
        try {
            $uniteId = auth()->user()?->unite_id;
            $categories = RessourceCategorie::with('ressourcesActives')
                ->orderBy('ordre')
                ->get()
                ->map(function ($cat) use ($uniteId) {
                    // Filtrer les ressources actives par unité
                    $cat->ressourcesActivesForUser = $cat->ressourcesActives
                        ->where(fn ($r) => $r->unite_id === null || $r->unite_id === $uniteId)
                        ->values();
                    return $cat;
                })
                ->filter(fn ($cat) => $cat->ressourcesActivesForUser->isNotEmpty());
            return $categories;
        } catch (\Throwable) {
            return collect();
        }
    }

    public function getCategorieUrl(int $id): string
    {
        return RessourcesPage::getUrl(['categorie' => $id]);
    }
}
