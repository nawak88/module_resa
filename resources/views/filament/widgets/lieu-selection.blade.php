<x-filament-widgets::widget>
    <x-filament::section>

        <style>
            .lieu-card {
                position: relative;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 1rem;
                border-radius: 1rem;
                padding: 1.5rem;
                text-align: left;
                border: none;
                cursor: pointer;
                overflow: hidden;
                background: linear-gradient(135deg, #064e3b 0%, #059669 55%, #34d399 100%);
                box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .lieu-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0);
                transition: background 0.2s ease;
            }

            .lieu-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(5, 150, 105, 0.35); }
            .lieu-card:hover::before { background: rgba(255, 255, 255, 0.07); }

            .dark .lieu-card {
                background: linear-gradient(135deg, #022c22 0%, #047857 55%, #10b981 100%);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
            }

            .lieu-card-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 3.75rem;
                height: 3.75rem;
                border-radius: 1rem;
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(4px);
                box-shadow: inset 0 1px 1px rgba(255,255,255,0.25), 0 2px 8px rgba(0,0,0,0.15);
                flex-shrink: 0;
            }

            .lieu-card-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                border-radius: 9999px;
                background: rgba(255, 255, 255, 0.2);
                padding: 0.125rem 0.625rem;
                font-size: 0.7rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.9);
            }

            .lieu-card-title {
                font-size: 1rem;
                font-weight: 700;
                color: #ffffff;
            }

            .lieu-card-desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.4;
                color: rgba(255, 255, 255, 0.75);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .lieu-card-cta {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.6);
                opacity: 0;
                transition: opacity 0.15s ease;
            }

            .lieu-card:hover .lieu-card-cta { opacity: 1; color: rgba(255, 255, 255, 0.95); }
        </style>

        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-map-pin class="w-5 h-5 text-primary-500"/>
                <span>Choisissez un lieu</span>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($lieux as $lieu)
                @php $count = $lieu->unites()->whereHas('salles')->count(); @endphp
                <button
                    wire:click="selectLieu({{ $lieu->id }})"
                    class="lieu-card"
                >
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                        <div class="lieu-card-icon">
                            <x-heroicon-o-map-pin style="width:2rem;height:2rem;color:#a7f3d0;filter:drop-shadow(0 0 6px rgba(167,243,208,0.7))"/>
                        </div>
                        @if ($count)
                            <span class="lieu-card-badge">
                                <x-heroicon-o-building-office-2 style="width:10px;height:10px"/>
                                {{ $count }}&nbsp;unité{{ $count > 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>

                    <div style="flex:1">
                        <p class="lieu-card-title">{{ $lieu->libelle_court }}</p>
                        @if ($lieu->libelle_long)
                            <p class="lieu-card-desc">{{ $lieu->libelle_long }}</p>
                        @endif
                    </div>

                    <div class="lieu-card-cta">
                        Sélectionner
                        <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/>
                    </div>
                </button>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
