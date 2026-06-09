<x-filament-widgets::widget>
    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-950">
                <x-heroicon-o-lock-closed class="h-5 w-5 text-primary-600 dark:text-primary-400"/>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Connectez-vous pour réserver</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">La consultation du calendrier est libre, la réservation nécessite un compte.</p>
            </div>
        </div>

        <x-filament::button wire:click="mountAction('login')" icon="heroicon-o-lock-closed">
            Se connecter
        </x-filament::button>
    </div>

    <x-filament-actions::modals/>
</x-filament-widgets::widget>
