<x-filament-widgets::widget>
    <x-filament::section>
        <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
            Sélectionnez une salle pour voir les disponibilités
        </p>

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">
            @foreach(\Modules\Resa\Models\Salle::orderBy('libelle_court')->get() as $salle)
                <button
                    wire:click="selectSalle({{ $salle->id }})"
                    style="
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                        border-radius: 0.75rem;
                        border: 2px solid #e5e7eb;
                        background: white;
                        padding: 1.5rem;
                        text-align: center;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        cursor: pointer;
                        transition: border-color 0.2s, box-shadow 0.2s;
                        width: 100%;
                    "
                    onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'; this.style.background='#eef2ff';"
                    onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'; this.style.background='white';"
                >
                    <x-heroicon-o-building-office-2 style="width: 2rem; height: 2rem; color: #9ca3af;" />
                    <span style="font-weight: 600; color: #1f2937; font-size: 0.95rem;">
                        {{ $salle->libelle_court }}
                    </span>
                    @if($salle->libelle_long)
                        <span style="font-size: 0.75rem; color: #9ca3af;">{{ $salle->libelle_long }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
