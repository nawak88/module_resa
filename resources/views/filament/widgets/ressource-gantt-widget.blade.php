@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Facades\FilamentColor;
    use Filament\Support\View\Components\ButtonComponent;
    use Guava\Calendar\Enums\Context;
    $etape = $this->etape;
@endphp

<x-filament-widgets::widget>
    @if($etape === 'lieu')
    {{-- ══════════════════════ ÉTAPE LIEU ══════════════════════ --}}
    <x-filament::section>
        <x-slot name="heading">{{ $this->getHeading() }}</x-slot>

        <style>
            .sel-card { position:relative;display:flex;flex-direction:column;justify-content:space-between;gap:1rem;border-radius:1rem;padding:1.5rem;text-align:left;border:none;cursor:pointer;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease; }
            .sel-card::before { content:'';position:absolute;inset:0;background:rgba(255,255,255,0);transition:background .2s ease; }
            .sel-card:hover { transform:translateY(-4px); }
            .sel-card:hover::before { background:rgba(255,255,255,.07); }
            .sel-card-lieu  { background:linear-gradient(135deg,#064e3b 0%,#059669 55%,#34d399 100%);box-shadow:0 4px 14px rgba(5,150,105,.25); }
            .sel-card-unite { background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 55%,#a78bfa 100%);box-shadow:0 4px 14px rgba(124,58,237,.25); }
            .dark .sel-card-lieu  { background:linear-gradient(135deg,#022c22 0%,#047857 55%,#10b981 100%); }
            .dark .sel-card-unite { background:linear-gradient(135deg,#2e1065 0%,#6d28d9 55%,#8b5cf6 100%); }
            .sel-card-icon { display:flex;align-items:center;justify-content:center;width:3.75rem;height:3.75rem;border-radius:1rem;background:rgba(255,255,255,.15);backdrop-filter:blur(4px);box-shadow:inset 0 1px 1px rgba(255,255,255,.25),0 2px 8px rgba(0,0,0,.15);flex-shrink:0; }
            .sel-card-title { font-size:1rem;font-weight:700;color:#fff; }
            .sel-card-desc  { margin-top:.25rem;font-size:.8125rem;line-height:1.4;color:rgba(255,255,255,.75); }
            .sel-card-cta   { display:flex;align-items:center;gap:.25rem;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.6);opacity:0;transition:opacity .15s ease; }
            .sel-card:hover .sel-card-cta { opacity:1;color:rgba(255,255,255,.95); }
        </style>

        @php $lieux = $this->getLieux(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($lieux as $lieu)
                <button wire:click="selectLieu({{ $lieu->id }})" class="sel-card sel-card-lieu">
                    <div style="display:flex;align-items:flex-start;gap:.75rem;">
                        <div class="sel-card-icon">
                            <x-heroicon-o-map-pin style="width:2rem;height:2rem;color:#a7f3d0;filter:drop-shadow(0 0 6px rgba(167,243,208,.7));"/>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <p class="sel-card-title">{{ $lieu->libelle_court ?? $lieu->libelle_long ?? $lieu->libelle ?? '' }}</p>
                        @if(!empty($lieu->libelle_long) && $lieu->libelle_long !== ($lieu->libelle_court ?? ''))
                            <p class="sel-card-desc">{{ $lieu->libelle_long }}</p>
                        @endif
                    </div>
                    <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0;"/></div>
                </button>
            @endforeach
        </div>
    </x-filament::section>

    @elseif($etape === 'unite')
    {{-- ══════════════════════ ÉTAPE UNITÉ ══════════════════════ --}}
    <x-filament::section>
        <x-slot name="heading">{{ $this->getHeading() }}</x-slot>

        <div class="mb-5">
            <x-filament::button wire:click="retourLieux" color="gray" icon="heroicon-o-arrow-left" size="sm">
                Retour aux lieux
            </x-filament::button>
        </div>

        @php $unites = $this->getUnites(); @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($unites as $unite)
                <button wire:click="selectUnite({{ $unite->id }})" class="sel-card sel-card-unite">
                    <div style="display:flex;align-items:flex-start;gap:.75rem;">
                        <div class="sel-card-icon">
                            <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#c4b5fd;filter:drop-shadow(0 0 6px rgba(196,181,253,.7));"/>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <p class="sel-card-title">{{ $unite->libelle_court ?? $unite->nom ?? $unite->libelle ?? '' }}</p>
                    </div>
                    <div class="sel-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0;"/></div>
                </button>
            @endforeach
        </div>
    </x-filament::section>

    @else
    {{-- ══════════════════════ GANTT (Guava Calendar) ══════════════════════ --}}
    <x-filament::section
        :after-header="$this->getCachedHeaderActionsComponent()"
        :footer="$this->getCachedFooterActionsComponent()"
    >
        <x-slot name="heading">{{ $this->getHeading() }}</x-slot>

        <style>
            .ec-event.ec-preview, .ec-now-indicator { z-index:30; }
        </style>

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
    </x-filament::section>
    <x-filament-actions::modals/>
    @endif
</x-filament-widgets::widget>


