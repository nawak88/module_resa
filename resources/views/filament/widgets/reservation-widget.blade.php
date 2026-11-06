<x-filament-widgets::widget>

{{-- ─── PLUSIEURS SITES/UNITÉS : pas encore résolu, on passe par le modal ─────── --}}
@if(! $this->uniteActive)
<style>
.rw-empty{padding:2.5rem 1.5rem;display:flex;flex-direction:column;align-items:center;gap:0;}
.rw-empty-hero{width:100%;max-width:480px;margin:0 auto 2rem;display:flex;flex-direction:column;align-items:center;gap:1.5rem;}
.rw-empty-icon-wrap{width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(99,102,241,.35);}
.rw-empty-icon-wrap svg{width:40px;height:40px;color:#fff;}
.rw-empty-title{font-size:1.5rem;font-weight:700;color:#111827;margin:0;letter-spacing:-.02em;}
.dark .rw-empty-title{color:#f9fafb;}
.rw-empty-sub{font-size:.9375rem;color:#6b7280;margin:0;max-width:340px;line-height:1.6;}
.dark .rw-empty-sub{color:#9ca3af;}
.rw-steps{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;justify-content:center;}
.rw-step{display:flex;align-items:flex-start;gap:.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:.875rem 1rem;width:140px;flex-direction:column;}
.dark .rw-step{background:#1e293b;border-color:#334155;}
.rw-step-num{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rw-step-label{font-size:.8125rem;font-weight:600;color:#374151;margin:0;}
.dark .rw-step-label{color:#e2e8f0;}
.rw-step-desc{font-size:.75rem;color:#9ca3af;margin:0;}
</style>

    <div class="rw-empty">
        <div class="rw-empty-hero">
            <div class="rw-empty-icon-wrap">
                <x-heroicon-o-calendar-days style="width:40px;height:40px;color:#fff;"/>
            </div>
            <div style="text-align:center;">
                <p class="rw-empty-title">Bienvenue sur Resa</p>
                <p class="rw-empty-sub" style="margin-top:.5rem;">Configurez votre espace de réservation en 2 étapes pour accéder au calendrier.</p>
            </div>
        </div>

        <div class="rw-steps">
            <div class="rw-step">
                <div class="rw-step-num">1</div>
                <p class="rw-step-label">Lieu & Unité</p>
                <p class="rw-step-desc">Choisissez votre site et unité</p>
            </div>
            <div class="rw-step">
                <div class="rw-step-num">2</div>
                <p class="rw-step-label">Type</p>
                <p class="rw-step-desc">Salle, drone ou ressource</p>
            </div>
        </div>

        <x-filament::button
            wire:click="mountAction('configurer')"
            icon="heroicon-o-adjustments-horizontal"
            size="lg"
            style="background:linear-gradient(135deg,#3b82f6,#6366f1);border:none;padding:.75rem 2rem;font-size:1rem;box-shadow:0 4px 14px rgba(99,102,241,.4);">
            Commencer la configuration
        </x-filament::button>
    </div>

{{-- ─── DASHBOARD : choix direct du type de réservation ───────────────────────── --}}
@elseif(! $this->typeResa)
<style>
.rw-dash{padding:2rem 1.5rem;}
.rw-dash-title{font-size:1.25rem;font-weight:700;color:#111827;margin:0 0 .25rem;}
.dark .rw-dash-title{color:#f9fafb;}
.rw-dash-sub{font-size:.875rem;color:#6b7280;margin:0 0 1.5rem;}
.dark .rw-dash-sub{color:#9ca3af;}
.rw-dash-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;}
.rw-dash-card{display:flex;flex-direction:column;align-items:flex-start;gap:.75rem;text-align:left;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;cursor:pointer;transition:border-color .15s,box-shadow .15s;}
.rw-dash-card:hover{border-color:#6366f1;box-shadow:0 4px 14px rgba(99,102,241,.15);}
.dark .rw-dash-card{background:#1e293b;border-color:#334155;}
.rw-dash-icon{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;}
.rw-dash-icon svg{width:22px;height:22px;color:#fff;}
.rw-dash-label{font-size:.9375rem;font-weight:600;color:#111827;margin:0;}
.dark .rw-dash-label{color:#f3f4f6;}
.rw-dash-tooltip{font-size:.75rem;color:#6b7280;margin:0;line-height:1.4;}
.dark .rw-dash-tooltip{color:#9ca3af;}
</style>

    <div class="rw-dash">
        <p class="rw-dash-title">Que voulez-vous réserver ?</p>
        <p class="rw-dash-sub">{{ $this->getUniteLabel() }}</p>

        <div class="rw-dash-grid">
            @foreach($this->choixTypes($this->uniteActive) as $type => $choix)
                <button type="button" wire:click="selectionnerType('{{ $type }}')" class="rw-dash-card">
                    <div class="rw-dash-icon">
                        <x-dynamic-component :component="$choix->icon" style="width:22px;height:22px;color:#fff;"/>
                    </div>
                    <p class="rw-dash-label">{{ $choix->label }}</p>
                    @if($choix->tooltip)
                        <p class="rw-dash-tooltip">{{ $choix->tooltip }}</p>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

{{-- ─── SÉLECTION FAITE ─────────────────────────────────────────────────────── --}}
@else

    {{-- Barre résumé : lieu/unité en stand-by (un seul site pour l'instant), seul le
         type de réservation reste un choix affiché/modifiable ici. --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 mb-3 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-4 text-sm text-gray-700 dark:text-gray-200">
            <span>
                <span class="block text-[0.65rem] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Type</span>
                <strong>{{ $this->getTypeLabel() }}</strong>
            </span>
        </div>
        <x-filament::button wire:click="changerType" color="gray" icon="heroicon-o-pencil-square" size="sm">
            Changer
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
