<x-filament-widgets::widget>

{{-- ─── PAS DE SÉLECTION ────────────────────────────────────────────────────── --}}
@if(! $this->uniteActive || ! $this->typeResa)

    <div style="padding:2rem; text-align:center; display:flex; flex-direction:column; align-items:center; gap:1rem;">
        <div style="width:56px;height:56px;border-radius:14px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
            <x-heroicon-o-calendar-days style="width:28px;height:28px;color:#3b82f6;"/>
        </div>
        <div>
            <p style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:.4rem;">Réservations</p>
            <p style="font-size:.875rem;color:#6b7280;">Configurez votre lieu, unité et type de réservation.</p>
        </div>
        <x-filament::button wire:click="mountAction('configurer')" icon="heroicon-o-adjustments-horizontal">
            Configurer
        </x-filament::button>
    </div>

{{-- ─── SÉLECTION FAITE ─────────────────────────────────────────────────────── --}}
@else

    {{-- Barre résumé --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:8px 14px;margin-bottom:.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
        <div style="display:flex;align-items:center;gap:16px;font-size:.8125rem;color:#374151;">
            <span><span style="color:#94a3b8;font-size:.7rem;text-transform:uppercase;font-weight:600;">Lieu</span><br><strong>{{ $this->getLieuLabel() ?: '—' }}</strong></span>
            <span style="color:#d1d5db;">|</span>
            <span><span style="color:#94a3b8;font-size:.7rem;text-transform:uppercase;font-weight:600;">Unité</span><br><strong>{{ $this->getUniteLabel() ?: '—' }}</strong></span>
            <span style="color:#d1d5db;">|</span>
            <span><span style="color:#94a3b8;font-size:.7rem;text-transform:uppercase;font-weight:600;">Type</span><br><strong>{{ $this->getTypeLabel() }}</strong></span>
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
            ],
            key('rw-cal-' . $this->uniteActive . '-' . $this->typeResa)
        )
    @endif

@endif

<x-filament-actions::modals/>

</x-filament-widgets::widget>


