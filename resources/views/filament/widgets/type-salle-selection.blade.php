<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Choisissez un type de salle</x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($typeSalles as $typeSalle)
                <button
                    wire:click="selectTypeSalle({{ $typeSalle->id }})"
                    class="flex flex-col items-start gap-2 rounded-xl border border-gray-200 bg-white p-5 text-left shadow-sm transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center gap-3 w-full">
                        @if ($typeSalle->isSimulateur())
                            <x-heroicon-o-cpu-chip class="w-8 h-8 text-primary-500 shrink-0"/>
                        @else
                            <x-heroicon-o-building-office class="w-8 h-8 text-warning-500 shrink-0"/>
                        @endif
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $typeSalle->libelle_court }}
                            </div>
                            @if ($typeSalle->libelle_long)
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $typeSalle->libelle_long }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $typeSalle->salles()->count() }} salle(s) disponible(s)
                    </div>
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
