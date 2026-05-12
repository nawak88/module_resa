<x-filament-widgets::widget>
    <x-filament::section>

        <style>
            .unite-card {
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
                background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 55%, #a78bfa 100%);
                box-shadow: 0 4px 14px rgba(124, 58, 237, 0.25);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .unite-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0);
                transition: background 0.2s ease;
            }

            .unite-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(124, 58, 237, 0.35); }
            .unite-card:hover::before { background: rgba(255, 255, 255, 0.07); }

            .dark .unite-card {
                background: linear-gradient(135deg, #2e1065 0%, #6d28d9 55%, #8b5cf6 100%);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
            }

            .unite-card-icon {
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

            .unite-card-badge {
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

            .unite-card-title {
                font-size: 1rem;
                font-weight: 700;
                color: #ffffff;
            }

            .unite-card-desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.4;
                color: rgba(255, 255, 255, 0.75);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .unite-card-cta {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.6);
                opacity: 0;
                transition: opacity 0.15s ease;
            }

            .unite-card:hover .unite-card-cta { opacity: 1; color: rgba(255, 255, 255, 0.95); }
        </style>

        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-500"/>
                @isset($lieu)
                    <span>{{ $lieu->libelle_court }} — Choisissez une unité</span>
                @else
                    <span>Choisissez une unité</span>
                @endisset
            </div>
        </x-slot>

        {{-- Bouton retour --}}
        @if (isset($lieu) && \Modules\RH\Models\LieuUnite::whereHas('unites', fn($q) => $q->whereHas('salles'))->count() > 1)
            <div class="mb-5">
                <x-filament::button
                    wire:click="retourLieux"
                    color="gray"
                    icon="heroicon-o-arrow-left"
                    size="sm"
                >
                    Retour aux lieux
                </x-filament::button>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($unites as $unite)
                @php $count = $unite->salles()->count(); @endphp
                <button
                    wire:click="selectUnite({{ $unite->id }})"
                    class="unite-card"
                >
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                        <div class="unite-card-icon">
                            <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#ddd6fe;filter:drop-shadow(0 0 6px rgba(221,214,254,0.7))"/>
                        </div>
                        @if ($count)
                            <span class="unite-card-badge">
                                <x-heroicon-o-calendar-days style="width:10px;height:10px"/>
                                {{ $count }}&nbsp;salle{{ $count > 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>

                    <div style="flex:1">
                        <p class="unite-card-title">{{ $unite->libelle_court }}</p>
                        @if ($unite->libelle_long)
                            <p class="unite-card-desc">{{ $unite->libelle_long }}</p>
                        @endif
                    </div>

                    <div class="unite-card-cta">
                        Voir les salles
                        <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/>
                    </div>
                </button>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
