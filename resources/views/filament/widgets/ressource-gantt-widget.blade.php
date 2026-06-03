@php
    use Illuminate\Support\Carbon;
    $viewResa = $this->getViewingReservation();
    $vue      = $this->vue;
    $etape    = $this->etape;

    if ($etape === 'gantt') {
        if ($vue === 'jour') {
            $rows   = $this->getRowsJour();
            $heures = $this->getHeures();
            $date   = Carbon::parse($this->currentDate);
        } elseif ($vue === 'semaine') {
            $rows     = $this->getRowsSemaine();
            $jours    = $this->getJoursSemaine();
            $jourNoms = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            $debutSem = Carbon::parse($this->semaineDebut);
            $finSem   = $debutSem->copy()->addDays(6);
        } else {
            $rows     = $this->getRowsGantt();
            $jours    = $this->getJoursSemaine();
            $jourNoms = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            $debutSem = Carbon::parse($this->semaineDebut);
            $finSem   = $debutSem->copy()->addDays(6);
            $heures   = $this->getHeures();
            $nbHeures = \Modules\Resa\Filament\Widgets\RessourceGanttWidget::HEURE_FIN
                      - \Modules\Resa\Filament\Widgets\RessourceGanttWidget::HEURE_DEBUT;
        }
    }
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Réservations de ressources</x-slot>

        @if($etape === 'lieu')
        {{-- ════════════════════════════════════════ ÉTAPE LIEU ═══════════════════════════════════ --}}
        @php $lieux = $this->getLieux(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($lieux as $lieu)
                <button wire:click="selectLieu({{ $lieu->id }})" class="sel-card sel-card-lieu">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div class="sel-card-icon">
                            <x-heroicon-o-map-pin style="width:2rem;height:2rem;color:#a7f3d0;filter:drop-shadow(0 0 6px rgba(167,243,208,.7));"/>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <p class="sel-card-title">{{ $lieu->libelle_court ?? $lieu->libelle_long }}</p>
                        @if($lieu->libelle_long)<p class="sel-card-desc">{{ $lieu->libelle_long }}</p>@endif
                    </div>
                    <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0;"/></div>
                </button>
            @endforeach
        </div>

        @elseif($etape === 'unite')
        {{-- ════════════════════════════════════════ ÉTAPE UNITÉ ══════════════════════════════════ --}}
        @php $unites = $this->getUnites(); @endphp
        <div class="mb-5">
            <x-filament::button wire:click="retourLieux" color="gray" icon="heroicon-o-arrow-left" size="sm">
                Retour aux lieux
            </x-filament::button>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($unites as $unite)
                <button wire:click="selectUnite({{ $unite->id }})" class="sel-card sel-card-unite">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div class="sel-card-icon">
                            <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#c4b5fd;filter:drop-shadow(0 0 6px rgba(196,181,253,.7));"/>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <p class="sel-card-title">{{ $unite->libelle_court ?? $unite->nom }}</p>
                    </div>
                    <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0;"/></div>
                </button>
            @endforeach
        </div>

        @else
        {{-- ════════════════════════════════════════ GANTT ════════════════════════════════════════ --}}
        <x-slot name="afterHeader">
            <div class="flex items-center gap-2 flex-wrap">
                @if($this->filtreUnite)
                    <x-filament::button wire:click="retourUnites" color="gray" icon="heroicon-o-arrow-left" size="sm">
                        Changer d'unité
                    </x-filament::button>
                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
                @endif

                {{-- Sélecteur de vue --}}
                <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 text-xs font-semibold">
                    @foreach(['jour' => 'Jour', 'semaine' => 'Semaine', 'gantt' => 'Gantt'] as $v => $label)
                        <button wire:click="setVue('{{ $v }}')"
                            class="px-3 py-1.5 transition-colors
                                {{ $vue === $v
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>

                <x-filament::button wire:click="precedent" color="gray" icon="heroicon-o-chevron-left" size="sm" />
                <x-filament::button wire:click="auJourdHui" color="gray" size="sm">Aujourd'hui</x-filament::button>
                <x-filament::button wire:click="suivant" color="gray" icon="heroicon-o-chevron-right" size="sm" />

                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 capitalize">
                    @if($vue === 'jour')
                        {{ $date->translatedFormat('l d MMMM Y') }}
                    @else
                        {{ $debutSem->translatedFormat('d MMM') }} — {{ $finSem->translatedFormat('d MMM Y') }}
                    @endif
                </span>
            </div>
        </x-slot>
        @endif

        <style>
            /* ── Cards lieu/unité ─────────────────────────────────────────────────── */
            .sel-card {
                position:relative; display:flex; flex-direction:column; justify-content:space-between;
                gap:1rem; border-radius:1rem; padding:1.5rem; text-align:left; border:none;
                cursor:pointer; overflow:hidden; transition:transform .2s ease,box-shadow .2s ease;
            }
            .sel-card::before { content:''; position:absolute; inset:0; background:rgba(255,255,255,0); transition:background .2s ease; }
            .sel-card:hover { transform:translateY(-4px); }
            .sel-card:hover::before { background:rgba(255,255,255,.07); }
            .sel-card-lieu  { background:linear-gradient(135deg,#064e3b 0%,#059669 55%,#34d399 100%); box-shadow:0 4px 14px rgba(5,150,105,.25); }
            .sel-card-unite { background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 55%,#a78bfa 100%); box-shadow:0 4px 14px rgba(124,58,237,.25); }
            .dark .sel-card-lieu  { background:linear-gradient(135deg,#022c22 0%,#047857 55%,#10b981 100%); }
            .dark .sel-card-unite { background:linear-gradient(135deg,#2e1065 0%,#6d28d9 55%,#8b5cf6 100%); }
            .sel-card-icon { display:flex; align-items:center; justify-content:center; width:3.75rem; height:3.75rem; border-radius:1rem;
                background:rgba(255,255,255,.15); backdrop-filter:blur(4px);
                box-shadow:inset 0 1px 1px rgba(255,255,255,.25),0 2px 8px rgba(0,0,0,.15); flex-shrink:0; }
            .sel-card-title { font-size:1rem; font-weight:700; color:#fff; }
            .sel-card-desc { margin-top:.25rem; font-size:.8125rem; line-height:1.4; color:rgba(255,255,255,.75); }
            .sel-card-cta { display:flex; align-items:center; gap:.25rem; font-size:.75rem;
                font-weight:600; color:rgba(255,255,255,.6); opacity:0; transition:opacity .15s ease; }
            .sel-card:hover .sel-card-cta { opacity:1; color:rgba(255,255,255,.95); }

            /* ── Gantt table ──────────────────────────────────────────────────────── */
            .rg-table { width:100%; border-collapse:collapse; font-size:.8125rem; }
            .rg-table th, .rg-table td { border:1px solid #e5e7eb; padding:0; }
            .dark .rg-table th, .dark .rg-table td { border-color:rgba(255,255,255,.08); }

            .rg-th-header { text-align:center; font-weight:700; font-size:.7rem; padding:.4rem .25rem;
                background:linear-gradient(135deg,#1e40af,#3b82f6); color:rgba(255,255,255,.9);
                min-width:70px; white-space:nowrap; }
            .rg-th-header.today { background:linear-gradient(135deg,#1d4ed8,#60a5fa); font-weight:800; }
            .rg-th-header.weekend { background:linear-gradient(135deg,#4b5563,#6b7280); opacity:.7; }
            .rg-th-header.current { background:linear-gradient(135deg,#1d4ed8,#60a5fa); }

            .rg-td-cat { font-weight:700; font-size:.7rem; text-transform:uppercase; letter-spacing:.06em;
                padding:.5rem .75rem; background:#f8fafc; color:#475569; vertical-align:middle; min-width:90px; }
            .dark .rg-td-cat { background:#1f2937; color:#94a3b8; }
            .rg-td-res { font-weight:500; padding:.4rem .75rem; background:#fff; color:#374151;
                min-width:120px; vertical-align:middle; white-space:nowrap; }
            .dark .rg-td-res { background:#111827; color:#e5e7eb; }

            .rg-cell-empty { height:44px; cursor:pointer; text-align:center; vertical-align:middle;
                color:#d1d5db; transition:background .15s; }
            .rg-cell-empty:hover { background:#eff6ff; color:#3b82f6; }
            .dark .rg-cell-empty:hover { background:rgba(59,130,246,.1); }
            .rg-cell-empty.weekend { background:#f9fafb; cursor:default; color:#e5e7eb; }
            .dark .rg-cell-empty.weekend { background:rgba(255,255,255,.02); }

            .rg-cell-resa { height:44px; vertical-align:middle; cursor:pointer; padding:0 6px; overflow:hidden; }
            .rg-resa-bar { border-radius:.375rem; padding:.25rem .5rem; font-size:.75rem; font-weight:600;
                color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
                box-shadow:0 1px 3px rgba(0,0,0,.12); height:30px; display:flex; align-items:center; }
            .rg-resa-bar.pending { background:#f59e0b; }
            .rg-resa-bar.validated { background:#22c55e; }

            /* Gantt */
            .rg-gantt-timeline { position:relative; height:44px; }
            .rg-gantt-bar { position:absolute; top:7px; height:30px; border-radius:.375rem;
                font-size:.7rem; font-weight:600; color:#fff; padding:0 .4rem;
                display:flex; align-items:center; overflow:hidden; white-space:nowrap;
                text-overflow:ellipsis; box-shadow:0 1px 3px rgba(0,0,0,.15); cursor:pointer;
                transition:filter .15s; z-index:1; }
            .rg-gantt-bar:hover { filter:brightness(1.1); }
            .rg-gantt-bar.pending { background:#f59e0b; }
            .rg-gantt-bar.validated { background:#22c55e; }
            .rg-gantt-grid { position:absolute; inset:0; display:flex; pointer-events:none; }
            .rg-gantt-grid-day { flex:1; border-right:1px solid #e5e7eb; }
            .rg-gantt-grid-day.weekend { background:rgba(243,244,246,.6); }
            .dark .rg-gantt-grid-day { border-color:rgba(255,255,255,.06); }
            .dark .rg-gantt-grid-day.weekend { background:rgba(255,255,255,.015); }
            .rg-gantt-today-line { position:absolute; top:0; bottom:0; width:2px; background:#ef4444;
                opacity:.7; pointer-events:none; z-index:2; }

            .rg-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:50;
                display:flex; align-items:center; justify-content:center; }
            .rg-modal { background:#fff; border-radius:1rem; padding:1.5rem; min-width:360px;
                max-width:480px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); }
            .dark .rg-modal { background:#1f2937; }
            .rg-input { width:100%; border:1px solid #d1d5db; border-radius:.5rem; padding:.5rem .75rem;
                font-size:.875rem; background:#fff; color:#111827; }
            .dark .rg-input { background:#374151; border-color:#4b5563; color:#f9fafb; }
            .rg-input:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.15); }
            .rg-empty-state { text-align:center; padding:3rem 1rem; color:#9ca3af; }
        </style>

        @if(empty($rows))
            <div class="rg-empty-state">
                <x-heroicon-o-cube class="w-12 h-12 mx-auto mb-3 opacity-30"/>
                <p class="font-semibold">Aucune ressource disponible.</p>
                <p class="text-sm mt-1">Un administrateur doit créer des catégories et des ressources.</p>
            </div>

        @elseif($vue === 'jour')
            {{-- ════════════════════════════════════════ VUE JOUR ══════════════════════════════════════ --}}
            <div style="overflow-x:auto;">
                <table class="rg-table">
                    <thead>
                        <tr>
                            <th class="rg-td-cat" style="border-bottom:none;"></th>
                            <th class="rg-td-res" style="border-bottom:none;"></th>
                            @foreach($heures as $h)
                                @php $isCurrent = now()->format('Y-m-d') === $this->currentDate && now()->hour === $h; @endphp
                                <th class="rg-th-header {{ $isCurrent ? 'current' : '' }}">
                                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $group)
                            @foreach($group['ressources'] as $ri => $ressourceRow)
                                <tr>
                                    @if($ri === 0)
                                        <td class="rg-td-cat" rowspan="{{ count($group['ressources']) }}">
                                            {{ $group['categorie']->libelle_court ?? $group['categorie']->libelle }}
                                        </td>
                                    @endif
                                    <td class="rg-td-res">{{ $ressourceRow['ressource']->nom }}</td>
                                    @foreach($ressourceRow['cells'] as $cell)
                                        @if($cell['type'] === 'reservation')
                                            <td class="rg-cell-resa" colspan="{{ $cell['colspan'] }}"
                                                wire:click="voirReservation({{ $cell['resa']->id }})"
                                                title="{{ $cell['resa']->libelle }} — {{ $cell['resa']->debut->format('H:i') }}-{{ $cell['resa']->fin->format('H:i') }}">
                                                <div class="rg-resa-bar {{ $cell['resa']->isValide() ? 'validated' : 'pending' }}">
                                                    {{ $cell['resa']->libelle ?: ($cell['resa']->user->nom ?? '—') }}
                                                </div>
                                            </td>
                                        @else
                                            <td class="rg-cell-empty"
                                                wire:click="ouvrirModal({{ $cell['ressource_id'] }}, '{{ $this->currentDate }} {{ str_pad($cell['heure'], 2, '0', STR_PAD_LEFT) }}:00', '{{ $this->currentDate }} {{ str_pad($cell['heure'] + 1, 2, '0', STR_PAD_LEFT) }}:00')">
                                                <span style="font-size:1.1rem;line-height:1;">+</span>
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($vue === 'semaine')
            {{-- ════════════════════════════════════════ VUE SEMAINE ═══════════════════════════════════ --}}
            <div style="overflow-x:auto;">
                <table class="rg-table">
                    <thead>
                        <tr>
                            <th class="rg-td-cat" style="border-bottom:none;"></th>
                            <th class="rg-td-res" style="border-bottom:none;"></th>
                            @foreach($jours as $i => $jour)
                                @php $isToday = $jour->isToday(); $isWeekend = $jour->isWeekend(); @endphp
                                <th class="rg-th-header {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}">
                                    {{ $jourNoms[$i] }}<br>
                                    <span style="font-size:.65rem;font-weight:500;opacity:.85;">{{ $jour->format('d/m') }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $group)
                            @foreach($group['ressources'] as $ri => $ressourceRow)
                                <tr>
                                    @if($ri === 0)
                                        <td class="rg-td-cat" rowspan="{{ count($group['ressources']) }}">
                                            {{ $group['categorie']->libelle_court ?? $group['categorie']->libelle }}
                                        </td>
                                    @endif
                                    <td class="rg-td-res">{{ $ressourceRow['ressource']->nom }}</td>
                                    @foreach($ressourceRow['cells'] as $cell)
                                        @if($cell['type'] === 'reservation')
                                            <td class="rg-cell-resa" colspan="{{ $cell['colspan'] }}"
                                                wire:click="voirReservation({{ $cell['resa']->id }})"
                                                title="{{ $cell['resa']->libelle }} — {{ $cell['resa']->debut->format('d/m H:i') }}-{{ $cell['resa']->fin->format('H:i') }}">
                                                <div class="rg-resa-bar {{ $cell['resa']->isValide() ? 'validated' : 'pending' }}">
                                                    <span class="text-xs opacity-75 mr-1">{{ $cell['resa']->debut->format('H:i') }}</span>
                                                    {{ $cell['resa']->libelle ?: ($cell['resa']->user->nom ?? '—') }}
                                                </div>
                                            </td>
                                        @else
                                            <td class="rg-cell-empty {{ $cell['weekend'] ? 'weekend' : '' }}"
                                                @if(!$cell['weekend'])
                                                    wire:click="ouvrirModal({{ $cell['ressource_id'] }}, '{{ $cell['date'] }} 08:00', '{{ $cell['date'] }} 09:00')"
                                                @endif>
                                                @if(!$cell['weekend'])
                                                    <span style="font-size:1.1rem;line-height:1;">+</span>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            {{-- ════════════════════════════════════════ VUE GANTT ════════════════════════════════════ --}}
            @php
                $todayOffset = null;
                $todayDate   = now()->format('Y-m-d');
                $todayHour   = now()->hour;
                foreach ($jours as $di => $jour) {
                    if ($jour->format('Y-m-d') === $todayDate) {
                        $heuresDuJour = max(0, min($todayHour, \Modules\Resa\Filament\Widgets\RessourceGanttWidget::HEURE_FIN)
                                           - \Modules\Resa\Filament\Widgets\RessourceGanttWidget::HEURE_DEBUT);
                        $todayOffset = round(($di * $nbHeures + $heuresDuJour) / (7 * $nbHeures) * 100, 3);
                    }
                }
            @endphp
            <div style="overflow-x:auto;">
                <table class="rg-table">
                    <thead>
                        {{-- Ligne jours --}}
                        <tr>
                            <th class="rg-td-cat" style="border-bottom:none;" rowspan="2"></th>
                            <th class="rg-td-res" style="border-bottom:none;" rowspan="2"></th>
                            @foreach($jours as $i => $jour)
                                @php $isToday = $jour->isToday(); $isWeekend = $jour->isWeekend(); @endphp
                                <th class="rg-th-header {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    colspan="{{ $nbHeures }}" style="min-width:{{ $nbHeures * 44 }}px;">
                                    {{ $jourNoms[$i] }} {{ $jour->format('d/m') }}
                                </th>
                            @endforeach
                        </tr>
                        {{-- Ligne heures --}}
                        <tr>
                            @foreach($jours as $jour)
                                @foreach($heures as $h)
                                    @php $isCurH = $jour->isToday() && now()->hour === $h; @endphp
                                    <th class="rg-th-header {{ $isCurH ? 'current' : '' }}"
                                        style="font-size:.6rem;padding:.2rem .1rem;min-width:44px;">
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $group)
                            @foreach($group['ressources'] as $ri => $ressourceRow)
                                <tr>
                                    @if($ri === 0)
                                        <td class="rg-td-cat" rowspan="{{ count($group['ressources']) }}">
                                            {{ $group['categorie']->libelle_court ?? $group['categorie']->libelle }}
                                        </td>
                                    @endif
                                    <td class="rg-td-res">{{ $ressourceRow['ressource']->nom }}</td>

                                    {{-- Cellule unique qui couvre toute la semaine --}}
                                    <td colspan="{{ 7 * $nbHeures }}" style="padding:0;position:relative;">
                                        <div class="rg-gantt-timeline" style="width:100%;">
                                            {{-- Grille jours --}}
                                            <div class="rg-gantt-grid">
                                                @foreach($jours as $jour)
                                                    <div class="rg-gantt-grid-day {{ $jour->isWeekend() ? 'weekend' : '' }}"
                                                         style="flex:0 0 calc(100%/7);"></div>
                                                @endforeach
                                            </div>
                                            {{-- Ligne "maintenant" --}}
                                            @if($todayOffset !== null)
                                                <div class="rg-gantt-today-line" style="left:{{ $todayOffset }}%;"></div>
                                            @endif
                                            {{-- Barres de réservation --}}
                                            @foreach($ressourceRow['bars'] as $bar)
                                                <div class="rg-gantt-bar {{ $bar['resa']->isValide() ? 'validated' : 'pending' }}"
                                                     style="left:{{ $bar['left'] }}%;width:calc({{ $bar['width'] }}% - 2px);"
                                                     wire:click="voirReservation({{ $bar['resa']->id }})"
                                                     title="{{ $bar['resa']->libelle }} — {{ $bar['resa']->debut->format('d/m H:i') }} → {{ $bar['resa']->fin->format('d/m H:i') }}">
                                                    {{ $bar['resa']->libelle ?: ($bar['resa']->user->nom ?? '—') }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Légende --}}
        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#f59e0b;"></span>
                En attente de validation
            </span>
            <span class="flex items-center gap-1.5">
                <span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#22c55e;"></span>
                Validée
            </span>
        </div>

        {{-- ══ Modal création ═══════════════════════════════════════════════ --}}
        @if($this->showModal)
            <div class="rg-modal-overlay" wire:click.self="fermerModal">
                <div class="rg-modal">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Nouvelle réservation</h3>
                        <button wire:click="fermerModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5"/>
                        </button>
                    </div>

                    @if(!empty($this->modalErrors))
                        <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                            @foreach($this->modalErrors as $err)<p>{{ $err }}</p>@endforeach
                        </div>
                    @endif

                    @php
                        $ressource = $this->modalRessourceId
                            ? \Modules\Resa\Models\Ressource::with('categorie')->find($this->modalRessourceId)
                            : null;
                    @endphp
                    @if($ressource)
                        <div class="mb-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-sm text-blue-700 dark:text-blue-300">
                            <span class="font-semibold">{{ $ressource->categorie->libelle }}</span>
                            → {{ $ressource->nom }}
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Objet / Description</label>
                            <input type="text" wire:model="modalLibelle" class="rg-input" placeholder="Ex : Transport équipe, Tournage...">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Début</label>
                                <input type="datetime-local" wire:model="modalDebut" class="rg-input">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Fin</label>
                                <input type="datetime-local" wire:model="modalFin" class="rg-input">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-5">
                        <x-filament::button wire:click="fermerModal" color="gray" size="sm">Annuler</x-filament::button>
                        <x-filament::button wire:click="creerReservation" color="primary" size="sm">
                            {{ $this->adminMode ? 'Créer et valider' : 'Demander la réservation' }}
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══ Modal vue réservation ════════════════════════════════════════ --}}
        @if($this->showViewModal && $viewResa)
            <div class="rg-modal-overlay" wire:click.self="fermerViewModal">
                <div class="rg-modal">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Réservation</h3>
                        <button wire:click="fermerViewModal" class="text-gray-400 hover:text-gray-600">
                            <x-heroicon-o-x-mark class="w-5 h-5"/>
                        </button>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div class="flex gap-2">
                            <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Ressource</dt>
                            <dd class="text-gray-800 dark:text-gray-200">
                                {{ $viewResa->ressource->categorie->libelle }} → {{ $viewResa->ressource->nom }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Objet</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $viewResa->libelle ?: '—' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Créneau</dt>
                            <dd class="text-gray-800 dark:text-gray-200">
                                {{ $viewResa->debut->format('d/m/Y H:i') }} → {{ $viewResa->fin->format('d/m/Y H:i') }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Demandeur</dt>
                            <dd class="text-gray-800 dark:text-gray-200">{{ $viewResa->user->nom ?? '—' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Statut</dt>
                            <dd>
                                @if($viewResa->isValide())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <x-heroicon-o-check-circle class="w-3 h-3"/> Validée
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <x-heroicon-o-clock class="w-3 h-3"/> En attente
                                    </span>
                                @endif
                            </dd>
                        </div>
                        @if($viewResa->isValide() && $viewResa->validatedBy)
                            <div class="flex gap-2">
                                <dt class="font-semibold text-gray-500 w-28 flex-shrink-0">Validée par</dt>
                                <dd class="text-gray-800 dark:text-gray-200">
                                    {{ $viewResa->validatedBy->nom }} — {{ $viewResa->validated_at?->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>

                    <div class="flex justify-between items-center mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex gap-2">
                            @if($this->adminMode || ($viewResa->isEnAttente() && $viewResa->user_id === auth()->id()))
                                <x-filament::button
                                    wire:click="supprimerReservation({{ $viewResa->id }})"
                                    wire:confirm="Supprimer cette réservation ?"
                                    color="danger" size="sm" icon="heroicon-o-trash">
                                    Supprimer
                                </x-filament::button>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            @if($this->adminMode)
                                @if($viewResa->isEnAttente())
                                    <x-filament::button wire:click="validerReservation({{ $viewResa->id }})"
                                        color="success" size="sm" icon="heroicon-o-check-circle">Valider</x-filament::button>
                                @else
                                    <x-filament::button wire:click="invaliderReservation({{ $viewResa->id }})"
                                        color="warning" size="sm" icon="heroicon-o-x-circle">Invalider</x-filament::button>
                                @endif
                            @endif
                            <x-filament::button wire:click="fermerViewModal" color="gray" size="sm">Fermer</x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>


