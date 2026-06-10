@php
    use Filament\Support\Facades\FilamentAsset;
    use Guava\Calendar\Enums\Context;
    use Filament\Support\Facades\FilamentColor;
    use Filament\Support\View\Components\ButtonComponent;
    use Modules\RH\Models\LieuUnite;
    use Modules\RH\Models\Unite;
    use Modules\Resa\Models\Salle;
    use Modules\Resa\Models\TypeSalle;
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        :after-header="$this->getCachedHeaderActionsComponent()"
        :footer="$this->getCachedFooterActionsComponent()"
    >

        <style>
            /* ══ Sélection cards ══════════════════════════════════════════════ */
            .sel-card {
                position: relative; display: flex; flex-direction: column;
                justify-content: space-between; gap: 1rem; border-radius: 1rem;
                padding: 1.5rem; text-align: left; border: none; cursor: pointer;
                overflow: hidden; transition: transform .2s ease, box-shadow .2s ease;
            }
            .sel-card::before { content:''; position:absolute; inset:0; background:rgba(255,255,255,0); transition:background .2s ease; }
            .sel-card:hover { transform:translateY(-4px); }
            .sel-card:hover::before { background:rgba(255,255,255,.07); }

            .sel-card-lieu     { background:linear-gradient(135deg,#064e3b 0%,#059669 55%,#34d399 100%); box-shadow:0 4px 14px rgba(5,150,105,.25); }
            .sel-card-unite    { background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 55%,#a78bfa 100%); box-shadow:0 4px 14px rgba(124,58,237,.25); }
            .sel-card-sim      { background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 55%,#60a5fa 100%); box-shadow:0 4px 14px rgba(37,99,235,.25); }
            .sel-card-classique{ background:linear-gradient(135deg,#78350f 0%,#d97706 55%,#fbbf24 100%); box-shadow:0 4px 14px rgba(217,119,6,.25); }
            .sel-card-salle    { background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 55%,#60a5fa 100%); box-shadow:0 4px 14px rgba(37,99,235,.25); }

            .dark .sel-card-lieu      { background:linear-gradient(135deg,#022c22 0%,#047857 55%,#10b981 100%); }
            .dark .sel-card-unite     { background:linear-gradient(135deg,#2e1065 0%,#6d28d9 55%,#8b5cf6 100%); }
            .dark .sel-card-sim       { background:linear-gradient(135deg,#172554 0%,#1d4ed8 55%,#3b82f6 100%); }
            .dark .sel-card-classique { background:linear-gradient(135deg,#451a03 0%,#b45309 55%,#d97706 100%); }
            .dark .sel-card-salle     { background:linear-gradient(135deg,#172554 0%,#1d4ed8 55%,#3b82f6 100%); }

            .sel-card-icon {
                display:flex; align-items:center; justify-content:center;
                width:3.75rem; height:3.75rem; border-radius:1rem;
                background:rgba(255,255,255,.15); backdrop-filter:blur(4px);
                box-shadow:inset 0 1px 1px rgba(255,255,255,.25), 0 2px 8px rgba(0,0,0,.15);
                flex-shrink:0;
            }
            .sel-card-badge {
                display:inline-flex; align-items:center; gap:.25rem; border-radius:9999px;
                background:rgba(255,255,255,.2); padding:.125rem .625rem;
                font-size:.7rem; font-weight:600; color:rgba(255,255,255,.9);
            }
            .sel-card-title { font-size:1rem; font-weight:700; color:#fff; }
            .sel-card-desc {
                margin-top:.25rem; font-size:.8125rem; line-height:1.4;
                color:rgba(255,255,255,.75); display:-webkit-box;
                -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
            }
            .sel-card-cta {
                display:flex; align-items:center; gap:.25rem; font-size:.75rem;
                font-weight:600; color:rgba(255,255,255,.6); opacity:0; transition:opacity .15s ease;
            }
            .sel-card:hover .sel-card-cta { opacity:1; color:rgba(255,255,255,.95); }

            /* ══ Calendar theme ═══════════════════════════════════════════════ */
            .ec-event.ec-preview, .ec-now-indicator { z-index:30; }

            .ec {
                --ec-today-bg-color:             rgba(59,130,246,.05);
                --ec-now-indicator-color:        #ef4444;
                --ec-highlight-color:            rgba(59,130,246,.12);
                --ec-event-bg-color:             #3b82f6;
                --ec-event-text-color:           #fff;
                --ec-button-bg-color:            #ffffff;
                --ec-button-border-color:        #e5e7eb;
                --ec-button-text-color:          #374151;
                --ec-button-active-bg-color:     #3b82f6;
                --ec-button-active-border-color: #2563eb;
                --ec-button-active-text-color:   #ffffff;
                --ec-border-color:               #e5e7eb;
            }
            .dark .ec {
                --ec-bg-fallback-color:          #1f2937;
                --ec-border-color:               rgba(255,255,255,.08);
                --ec-today-bg-color:             rgba(59,130,246,.1);
                --ec-button-bg-color:            #1f2937;
                --ec-button-border-color:        rgba(255,255,255,.12);
                --ec-button-text-color:          #e5e7eb;
                --ec-button-active-bg-color:     #3b82f6;
                --ec-button-active-border-color: #2563eb;
                --ec-button-active-text-color:   #ffffff;
                --ec-event-bg-color:             #2563eb;
            }
            .ec .ec-toolbar { margin-bottom:1.25rem; flex-wrap:wrap; gap:.5rem; }
            .ec .ec-title { font-size:1.05rem; font-weight:600; letter-spacing:-.015em; text-transform:capitalize; }
            .ec .ec-button { border-radius:.5rem; font-size:.8125rem; font-weight:500; box-shadow:0 1px 2px rgba(0,0,0,.06); transition:background-color .15s,border-color .15s,color .15s; }
            .ec .ec-button-group .ec-button { border-radius:0; }
            .ec .ec-button-group .ec-button:first-child { border-radius:.5rem 0 0 .5rem; }
            .ec .ec-button-group .ec-button:last-child  { border-radius:0 .5rem .5rem 0; }
            .ec .ec-header { background:linear-gradient(135deg,#1e40af 0%,#3b82f6 60%,#60a5fa 100%); border-radius:.5rem .5rem 0 0; overflow:hidden; }
            .ec .ec-header .ec-day { min-height:56px !important; line-height:56px !important; border-color:rgba(255,255,255,.15) !important; }
            .ec .ec-header .ec-day-head { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.85); }
            .ec .ec-header .ec-day.ec-today { background:rgba(255,255,255,.18); }
            .ec .ec-header .ec-day.ec-today .ec-day-head { color:#fff; font-weight:800; text-shadow:0 1px 4px rgba(0,0,0,.25); }
            .dark .ec .ec-header { background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 60%,#2563eb 100%); }
            .dark .ec .ec-header .ec-day { border-color:rgba(255,255,255,.08) !important; }
            .dark .ec .ec-header .ec-day-head { color:rgba(219,234,254,.85); }
            .dark .ec .ec-header .ec-day.ec-today { background:rgba(255,255,255,.12); }
            .dark .ec .ec-header .ec-day.ec-today .ec-day-head { color:#bfdbfe; text-shadow:0 1px 4px rgba(0,0,0,.4); }
            .ec .ec-time-grid .ec-time { font-size:.9rem; font-weight:600; color:#6b7280; }
            /* .ec-time-grid .ec-line { height: 60px !important; }
            .ec-time-grid .ec-time { height: 60px !important; line-height: 60px !important; } */
            .ec .ec-event { border-radius:.375rem; box-shadow:0 1px 3px rgba(0,0,0,.1); transition:transform .1s ease,box-shadow .1s ease; cursor:pointer; }
            .ec .ec-event:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(0,0,0,.15); }
            .ec .ec-event-title { font-weight:600; }
            .ec .ec-event-time { font-size:.6875rem; opacity:.85; font-weight:500; }
            .ec .ec-time-grid .ec-now-indicator { border-top-width:2px; }
            .ec .ec-time-grid .ec-now-indicator::before { width:10px; height:10px; margin-top:-6px; }
        </style>

        {{-- ══ Heading ══════════════════════════════════════════════════════ --}}
        @if($heading = $this->getHeading())
            <x-slot name="heading">{{ $heading }}</x-slot>
        @endif

        {{-- ══ Étape : lieu ═════════════════════════════════════════════════ --}}
        @if($this->etapeNavigation === 'lieu')
            <div wire:key="step-lieu">
            @php
                $lieux = LieuUnite::whereIn('id',
                    Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->pluck('lieu_unite_id')->unique()
                )->orderBy('ordre')->get();
            @endphp
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($lieux as $lieu)
                    @php $cnt = Unite::where('lieu_unite_id',$lieu->id)->whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->count(); @endphp
                    <button wire:click="selectLieu({{ $lieu->id }})" class="sel-card sel-card-lieu">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem">
                            <div class="sel-card-icon">
                                <x-heroicon-o-map-pin style="width:2rem;height:2rem;color:#a7f3d0;filter:drop-shadow(0 0 6px rgba(167,243,208,.7))"/>
                            </div>
                            @if($cnt)<span class="sel-card-badge"><x-heroicon-o-building-office-2 style="width:10px;height:10px"/> {{ $cnt }}&nbsp;unité{{ $cnt>1?'s':'' }}</span>@endif
                        </div>
                        <div style="flex:1">
                            <p class="sel-card-title">{{ $lieu->libelle_court }}</p>
                            @if($lieu->libelle_long)<p class="sel-card-desc">{{ $lieu->libelle_long }}</p>@endif
                        </div>
                        <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/></div>
                    </button>
                @endforeach
            </div>
            </div>{{-- wire:key=step-lieu --}}

        {{-- ══ Étape : unite ════════════════════════════════════════════════ --}}
        @elseif($this->etapeNavigation === 'unite')
            <div wire:key="step-unite">
            @php
                $unites = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
                    ->when($this->lieuActive, fn($q)=>$q->where('lieu_unite_id',$this->lieuActive))
                    ->orderBy('ordre')->get();
                $lieuLabel = $this->lieuActive ? LieuUnite::find($this->lieuActive)?->libelle_court : null;
            @endphp
            @if($lieuLabel && LieuUnite::whereIn('id', Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->pluck('lieu_unite_id')->unique())->count() > 1)
                <div class="mb-5">
                    <x-filament::button wire:click="retourLieux" color="gray" icon="heroicon-o-arrow-left" size="sm">Retour aux lieux</x-filament::button>
                </div>
            @endif
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($unites as $unite)
                    @php $cnt = Salle::where('unite_id',$unite->id)->count(); @endphp
                    <button wire:click="selectUnite({{ $unite->id }})" class="sel-card sel-card-unite">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem">
                            <div class="sel-card-icon">
                                <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#ddd6fe;filter:drop-shadow(0 0 6px rgba(221,214,254,.7))"/>
                            </div>
                            @if($cnt)<span class="sel-card-badge"><x-heroicon-o-calendar-days style="width:10px;height:10px"/> {{ $cnt }}&nbsp;salle{{ $cnt>1?'s':'' }}</span>@endif
                        </div>
                        <div style="flex:1">
                            <p class="sel-card-title">{{ $unite->libelle_court }}</p>
                            @if($unite->libelle_long)<p class="sel-card-desc">{{ $unite->libelle_long }}</p>@endif
                        </div>
                        <div class="sel-card-cta">Voir les salles <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/></div>
                    </button>
                @endforeach
            </div>
            </div>{{-- wire:key=step-unite --}}

        {{-- ══ Étape : type_salle ═══════════════════════════════════════════ --}}
        @elseif($this->etapeNavigation === 'type_salle')
            <div wire:key="step-type-salle">
            @php
                $typeSalles = TypeSalle::whereIn('id', $this->getSallesScopePublic()->pluck('type_salle_id')->unique())->orderBy('ordre')->get();
            @endphp
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($typeSalles as $typeSalle)
                    @php
                        $isSimulateur = $typeSalle->isSimulateur();
                        $count = $this->getSallesScopePublic()->where('type_salle_id', $typeSalle->id)->count();
                    @endphp
                    <button wire:click="selectTypeSalle({{ $typeSalle->id }})" class="sel-card {{ $isSimulateur ? 'sel-card-sim' : 'sel-card-classique' }}">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem">
                            <div class="sel-card-icon">
                                @if($isSimulateur)
                                    <x-heroicon-o-cpu-chip style="width:2rem;height:2rem;color:#bfdbfe;filter:drop-shadow(0 0 6px rgba(147,197,253,.7))"/>
                                @else
                                    <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#fde68a;filter:drop-shadow(0 0 6px rgba(253,211,77,.7))"/>
                                @endif
                            </div>
                            <span class="sel-card-badge"><x-heroicon-o-home-modern style="width:10px;height:10px"/> {{ $count }}&nbsp;salle{{ $count>1?'s':'' }}</span>
                        </div>
                        <div style="flex:1">
                            <p class="sel-card-title">{{ $typeSalle->libelle_court }}</p>
                            @if($typeSalle->libelle_long)<p class="sel-card-desc">{{ $typeSalle->libelle_long }}</p>@endif
                        </div>
                        <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/></div>
                    </button>
                @endforeach
            </div>
            </div>{{-- wire:key=step-type-salle --}}

        {{-- ══ Étape : salle ════════════════════════════════════════════════ --}}
        @elseif($this->etapeNavigation === 'salle')
            <div wire:key="step-salle">
            @php
                $salles = $this->getSallesScopePublic()->where('type_salle_id', $this->typeSalleActive)->orderBy('ordre')->get();
                $typeSalle = \Modules\Resa\Models\TypeSalle::find($this->typeSalleActive);
            @endphp
            @if($typeSalle && TypeSalle::whereIn('id', $this->getSallesScopePublic()->pluck('type_salle_id')->unique())->count() > 1)
                <div class="mb-5">
                    <x-filament::button wire:click="retourTypeSalles" color="gray" icon="heroicon-o-arrow-left" size="sm">Retour aux types de salles</x-filament::button>
                </div>
            @endif
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($salles as $salle)
                    <button wire:click="selectSalle({{ $salle->id }})" class="sel-card sel-card-salle">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem">
                            <div class="sel-card-icon">
                                <x-heroicon-o-calendar-days style="width:2rem;height:2rem;color:#bfdbfe;filter:drop-shadow(0 0 6px rgba(147,197,253,.7))"/>
                            </div>
                            @if($salle->capacite)<span class="sel-card-badge"><x-heroicon-o-user-group style="width:10px;height:10px"/> {{ $salle->capacite }}&nbsp;max</span>@endif
                        </div>
                        <div style="flex:1">
                            <p class="sel-card-title">{{ $salle->libelle_court }}</p>
                            @if($salle->libelle_long)<p class="sel-card-desc">{{ $salle->libelle_long }}</p>@endif
                        </div>
                        <div class="sel-card-cta">Voir le calendrier <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/></div>
                    </button>
                @endforeach
            </div>
            </div>{{-- wire:key=step-salle --}}

        {{-- ══ Étape : calendrier ═══════════════════════════════════════════ --}}
        @else
            <div wire:key="step-calendrier"> 
            <div
                wire:ignore
                x-load
                x-load-src="{{ FilamentAsset::getAlpineComponentSrc('calendar', 'guava/calendar') }}"
                x-data="calendar({
                    view: @js($this->getCalendarView()),
                    locale: @js($this->getLocale()),
                    firstDay: @js($this->getFirstDay()),
                    dayMaxEvents: @js($this->getDayMaxEvents()),
                    eventContent: @js($this->getEventContentJs()),
                    eventClickEnabled: @js($this->isEventClickEnabled()),
                    eventDragEnabled: @js($this->isEventDragEnabled()),
                    eventResizeEnabled: @js($this->isEventResizeEnabled()),
                    noEventsClickEnabled: @js($this->isNoEventsClickEnabled()),
                    dateClickEnabled: @js($this->isDateClickEnabled()),
                    dateSelectEnabled: @js($this->isDateSelectEnabled()),
                    datesSetEnabled: @js($this->isDatesSetEnabled()),
                    viewDidMountEnabled: @js($this->isViewDidMountEnabled()),
                    eventAllUpdatedEnabled: @js($this->isEventAllUpdatedEnabled()),
                    hasDateClickContextMenu: @js($this->hasContextMenu(Context::DateClick)),
                    hasDateSelectContextMenu: @js($this->hasContextMenu(Context::DateSelect)),
                    hasEventClickContextMenu: @js($this->hasContextMenu(Context::EventClick)),
                    hasNoEventsClickContextMenu: @js($this->hasContextMenu(Context::NoEventsClick)),
                    resources: @js($this->getResourcesJs()),
                    resourceLabelContent: @js($this->getResourceLabelContentJs()),
                    theme: @js($this->getTheme()),
                    options: @js($this->getOptions()),
                    eventAssetUrl: @js(FilamentAsset::getAlpineComponentSrc('calendar-event', 'guava/calendar')),
                })"
                @class(FilamentColor::getComponentClasses(ButtonComponent::class, 'primary'))
            >
                <div data-calendar></div>
                @if($this->hasContextMenu())
                    <x-guava-calendar::context-menu/>
                @endif
            </div>
            </div>{{-- wire:key=step-calendrier --}}
        @endif

    </x-filament::section>
    <x-filament-actions::modals/>
</x-filament-widgets::widget>


