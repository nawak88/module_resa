@php
    use Filament\Support\Facades\FilamentAsset;
    use Guava\Calendar\Enums\Context;
    use Filament\Support\Facades\FilamentColor;
    use Filament\Support\View\Components\ButtonComponent;
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        :after-header="$this->getCachedHeaderActionsComponent()"
        :footer="$this->getCachedFooterActionsComponent()"
    >

        <style>
            /* Zindex fixes (original) */
            .ec-event.ec-preview,
            .ec-now-indicator {
                z-index: 30;
            }

            /* ══════════════════════════════════════════════════════
               Custom Theme — EventCalendar Resa
               ══════════════════════════════════════════════════════ */

            /* ── Variables light mode ──────────────────────────── */
            .ec {
                --ec-today-bg-color:           rgba(59, 130, 246, 0.05);
                --ec-now-indicator-color:      #ef4444;
                --ec-highlight-color:          rgba(59, 130, 246, 0.12);
                --ec-event-bg-color:           #3b82f6;
                --ec-event-text-color:         #fff;
                --ec-button-bg-color:          #ffffff;
                --ec-button-border-color:      #e5e7eb;
                --ec-button-text-color:        #374151;
                --ec-button-active-bg-color:   #3b82f6;
                --ec-button-active-border-color: #2563eb;
                --ec-button-active-text-color: #ffffff;
                --ec-border-color:             #e5e7eb;
            }

            /* ── Variables dark mode ───────────────────────────── */
            .dark .ec {
                --ec-bg-fallback-color:        #1f2937;
                --ec-border-color:             rgba(255, 255, 255, 0.08);
                --ec-today-bg-color:           rgba(59, 130, 246, 0.1);
                --ec-button-bg-color:          #1f2937;
                --ec-button-border-color:      rgba(255, 255, 255, 0.12);
                --ec-button-text-color:        #e5e7eb;
                --ec-button-active-bg-color:   #3b82f6;
                --ec-button-active-border-color: #2563eb;
                --ec-button-active-text-color: #ffffff;
                --ec-event-bg-color:           #2563eb;
            }

            /* ── Toolbar ───────────────────────────────────────── */
            .ec .ec-toolbar {
                margin-bottom: 1.25rem;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .ec .ec-title {
                font-size: 1.05rem;
                font-weight: 600;
                letter-spacing: -0.015em;
                text-transform: capitalize;
            }

            /* ── Boutons arrondis ──────────────────────────────── */
            .ec .ec-button {
                border-radius: 0.5rem;
                font-size: 0.8125rem;
                font-weight: 500;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
                transition: background-color 0.15s, border-color 0.15s, color 0.15s;
            }

            .ec .ec-button-group .ec-button { border-radius: 0; }
            .ec .ec-button-group .ec-button:first-child { border-radius: 0.5rem 0 0 0.5rem; }
            .ec .ec-button-group .ec-button:last-child  { border-radius: 0 0.5rem 0.5rem 0; }

            /* ── En-têtes des jours (thead) — light ────────────── */
            .ec .ec-header {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 60%, #60a5fa 100%);
                border-radius: 0.5rem 0.5rem 0 0;
                overflow: hidden;
            }

            /* Hauteur augmentée — on écrase min-height + line-height de la lib */
            .ec .ec-header .ec-day {
                min-height: 56px !important;
                line-height: 56px !important;
                border-color: rgba(255, 255, 255, 0.15) !important;
            }

            .ec .ec-header .ec-day-head {
                font-size: 0.8rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: rgba(255, 255, 255, 0.85);
            }

            /* Jour courant */
            .ec .ec-header .ec-day.ec-today {
                background: rgba(255, 255, 255, 0.18);
            }

            .ec .ec-header .ec-day.ec-today .ec-day-head {
                color: #ffffff;
                font-weight: 800;
                text-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
            }

            /* ── En-têtes des jours (thead) — dark ─────────────── */
            .dark .ec .ec-header {
                background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 60%, #2563eb 100%);
            }

            .dark .ec .ec-header .ec-day {
                border-color: rgba(255, 255, 255, 0.08) !important;
            }

            .dark .ec .ec-header .ec-day-head {
                color: rgba(219, 234, 254, 0.85);
            }

            .dark .ec .ec-header .ec-day.ec-today {
                background: rgba(255, 255, 255, 0.12);
            }

            .dark .ec .ec-header .ec-day.ec-today .ec-day-head {
                color: #bfdbfe;
                text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
            }

            /* Labels horaires */
            .ec .ec-time-grid .ec-time {
                font-size: 0.6875rem;
                font-weight: 500;
                color: #9ca3af;
            }

            /* Événements */
            .ec .ec-event {
                border-radius: 0.375rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                transition: transform 0.1s ease, box-shadow 0.1s ease;
                cursor: pointer;
            }

            .ec .ec-event:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            }

            .ec .ec-event-title { font-weight: 600; }

            .ec .ec-event-time {
                font-size: 0.6875rem;
                opacity: 0.85;
                font-weight: 500;
            }

            /* Indicateur "maintenant" */
            .ec .ec-time-grid .ec-now-indicator { border-top-width: 2px; }
            .ec .ec-time-grid .ec-now-indicator::before {
                width: 10px;
                height: 10px;
                margin-top: -6px;
            }
        </style>

        @if($heading = $this->getHeading())
            <x-slot name="heading">
                {{ $this->getHeading() }}
            </x-slot>
        @endif

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
</x-filament-widgets::widget>
