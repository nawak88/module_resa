<x-filament-widgets::widget>

{{-- ─── PAS DE SÉLECTION ────────────────────────────────────────────────────── --}}
@if(! $this->uniteActive || ! $this->typeResa)

    <div class="flex flex-col items-center gap-4 p-8 text-center">
        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-950">
            <x-heroicon-o-calendar-days class="h-7 w-7 text-primary-600 dark:text-primary-400"/>
        </div>
        <div>
            <p class="text-base font-bold text-gray-900 dark:text-white mb-1">Réservations</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Configurez votre lieu, unité et type de réservation.</p>
        </div>
        <x-filament::button wire:click="mountAction('configurer')" icon="heroicon-o-adjustments-horizontal">
            Configurer
        </x-filament::button>
    </div>

{{-- ─── SÉLECTION FAITE ─────────────────────────────────────────────────────── --}}
@else

    {{-- Barre résumé --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 mb-3 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-4 text-sm text-gray-700 dark:text-gray-200">
            <span>
                <span class="block text-[0.65rem] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Lieu</span>
                <strong>{{ $this->getLieuLabel() ?: '—' }}</strong>
            </span>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span>
                <span class="block text-[0.65rem] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Unité</span>
                <strong>{{ $this->getUniteLabel() ?: '—' }}</strong>
            </span>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <span>
                <span class="block text-[0.65rem] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Type</span>
                <strong>{{ $this->getTypeLabel() }}</strong>
            </span>
        </div>
        <x-filament::button wire:click="mountAction('configurer')" color="gray" icon="heroicon-o-pencil-square" size="sm">
            Modifier
        </x-filament::button>
    </div>

    {{-- Calendrier ou Gantt ressources --}}
    @if($this->typeResa === 'ressource')
        @livewire(\Modules\Resa\Filament\Widgets\RessourceGanttWidget::class,
            ['filtreUnite' => $this->uniteActive],
            key('rw-gantt-' . $this->uniteActive . '-' . $this->typeResa)
        )
    @else
        @livewire(\Modules\Resa\Filament\Widgets\CalendrierWidget::class,
            [
                'uniteActive'      => $this->uniteActive,
                'lieuActive'       => $this->lieuActive,
                'typeSalleActive'  => $this->getTypeSalleId(),
                'skipTypeSalleNav' => true,
                'modeForce'        => $this->typeResa === 'drone' ? 'simulateur' : 'classique',
            ],
            key('rw-cal-' . $this->uniteActive . '-' . $this->typeResa)
        )
    @endif

@endif

<x-filament-actions::modals/>

</x-filament-widgets::widget>


