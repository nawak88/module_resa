<?php

namespace Modules\Resa\Filament\Widgets;

use App\Livewire\PanelSwitcher as BaseWidget;

//class PanelSwitcher extends BaseWidget
class PanelSwitcher 
{
    protected static bool $isDiscovered = false;

    protected string $view = 'resa::livewire.panel-switcher';
}
