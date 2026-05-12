<x-filament-widgets::widget>
    <x-filament::section>

        <style>
            .ts-card {
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
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            }

            .ts-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0);
                transition: background 0.2s ease;
            }

            .ts-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2); }
            .ts-card:hover::before { background: rgba(255, 255, 255, 0.07); }

            .ts-card-sim {
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #60a5fa 100%);
            }

            .ts-card-classique {
                background: linear-gradient(135deg, #78350f 0%, #d97706 55%, #fbbf24 100%);
            }

            /* Dark mode — légèrement assombri */
            .dark .ts-card-sim {
                background: linear-gradient(135deg, #172554 0%, #1d4ed8 55%, #3b82f6 100%);
            }

            .dark .ts-card-classique {
                background: linear-gradient(135deg, #451a03 0%, #b45309 55%, #d97706 100%);
            }

            .ts-card-icon {
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

            .ts-card-badge {
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

            .ts-card-title {
                font-size: 1rem;
                font-weight: 700;
                color: #ffffff;
            }

            .ts-card-desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.4;
                color: rgba(255, 255, 255, 0.75);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .ts-card-cta {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.6);
                opacity: 0;
                transition: opacity 0.15s ease;
            }

            .ts-card:hover .ts-card-cta { opacity: 1; color: rgba(255, 255, 255, 0.95); }
        </style>

        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-squares-2x2 class="w-5 h-5 text-primary-500"/>
                <span>Choisissez un type de salle</span>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($typeSalles as $typeSalle)
                @php
                    $isSimulateur = $typeSalle->isSimulateur();
                    $count = $typeSalle->salles()->count();
                @endphp

                <button
                    wire:click="selectTypeSalle({{ $typeSalle->id }})"
                    class="ts-card {{ $isSimulateur ? 'ts-card-sim' : 'ts-card-classique' }}"
                >
                    {{-- Top row : icône + badge --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                        <div class="ts-card-icon">
                            @if ($isSimulateur)
                                <x-heroicon-o-cpu-chip style="width:2rem;height:2rem;color:#bfdbfe;filter:drop-shadow(0 0 6px rgba(147,197,253,0.7))"/>
                            @else
                                <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#fde68a;filter:drop-shadow(0 0 6px rgba(253,211,77,0.7))"/>
                            @endif
                        </div>

                        <span class="ts-card-badge">
                            <x-heroicon-o-home-modern style="width:10px;height:10px"/>
                            {{ $count }}&nbsp;salle{{ $count > 1 ? 's' : '' }}
                        </span>
                    </div>

                    {{-- Texte --}}
                    <div style="flex:1">
                        <p class="ts-card-title">{{ $typeSalle->libelle_court }}</p>
                        @if ($typeSalle->libelle_long)
                            <p class="ts-card-desc">{{ $typeSalle->libelle_long }}</p>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="ts-card-cta">
                        Sélectionner
                        <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/>
                    </div>
                </button>

            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
