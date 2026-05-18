<x-filament-widgets::widget>
    <x-filament::section>

        <style>
            .ss-card {
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
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 55%, #60a5fa 100%);
                box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .ss-card::before {
                content: '';
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0);
                transition: background 0.2s ease;
            }

            .ss-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(37, 99, 235, 0.35); }
            .ss-card:hover::before { background: rgba(255, 255, 255, 0.07); }

            .dark .ss-card {
                background: linear-gradient(135deg, #172554 0%, #1d4ed8 55%, #3b82f6 100%);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
            }

            .ss-card-icon {
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

            .ss-card-badge {
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

            .ss-card-title {
                font-size: 1rem;
                font-weight: 700;
                color: #ffffff;
            }

            .ss-card-desc {
                margin-top: 0.25rem;
                font-size: 0.8125rem;
                line-height: 1.4;
                color: rgba(255, 255, 255, 0.75);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .ss-card-cta {
                display: flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: rgba(255, 255, 255, 0.6);
                opacity: 0;
                transition: opacity 0.15s ease;
            }

            .ss-card:hover .ss-card-cta { opacity: 1; color: rgba(255, 255, 255, 0.95); }
        </style>

        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-500"/>
                @isset($typeSalle)
                    <span>{{ $typeSalle->libelle_court }}</span>
                @else
                    <span>Choisissez une salle</span>
                @endisset
            </div>
        </x-slot>

        {{-- Bouton retour --}}
        @if (isset($typeSalle) && \Modules\Resa\Models\TypeSalle::count() > 1)
            <div class="mb-5">
                <x-filament::button
                    wire:click="retourTypeSalles"
                    color="gray"
                    icon="heroicon-o-arrow-left"
                    size="sm"
                >
                    Retour aux types de salles
                </x-filament::button>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($salles as $salle)

                <button
                    wire:click="selectSalle({{ $salle->id }})"
                    class="ss-card"
                >
                    {{-- Top row : icône + capacité --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                        <div class="ss-card-icon">
                            <x-heroicon-o-calendar-days style="width:2rem;height:2rem;color:#bfdbfe;filter:drop-shadow(0 0 6px rgba(147,197,253,0.7))"/>
                        </div>

                        @if ($salle->capacite)
                            <span class="ss-card-badge">
                                <x-heroicon-o-user-group style="width:10px;height:10px"/>
                                {{ $salle->capacite }}&nbsp;max
                            </span>
                        @endif
                    </div>

                    {{-- Texte --}}
                    <div style="flex:1">
                        <p class="ss-card-title">{{ $salle->libelle_court }}</p>
                        @if ($salle->libelle_long)
                            <p class="ss-card-desc">{{ $salle->libelle_long }}</p>
                        @endif
                    </div>

                    {{-- CTA --}}
                    <div class="ss-card-cta">
                        Voir le calendrier
                        <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0"/>
                    </div>
                </button>

            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
