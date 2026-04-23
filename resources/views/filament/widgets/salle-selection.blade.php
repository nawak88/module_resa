<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            @isset($typeSalle)
                Salles — {{ $typeSalle->libelle_court }}
            @else
                Choisissez une salle
            @endisset
        </x-slot>

        @if (isset($typeSalle) && \Modules\Resa\Models\TypeSalle::count() > 1)
            <div class="mb-4">
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
                    class="flex flex-col items-start gap-2 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="font-semibold text-gray-900 dark:text-white">
                        {{ $salle->libelle_court }}
                    </div>
                    @if ($salle->libelle_long)
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $salle->libelle_long }}
                        </div>
                    @endif
                    @if ($salle->capacite)
                        <div class="text-xs text-gray-400">
                            {{ $salle->capacite }} personnes max
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
