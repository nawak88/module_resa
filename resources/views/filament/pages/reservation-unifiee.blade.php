<x-filament-panels::page>

@php
    $etape = $this->etape;
@endphp

<style>
    .ru-card { position:relative;display:flex;flex-direction:column;justify-content:space-between;gap:1rem;border-radius:1rem;padding:1.5rem;text-align:left;border:none;cursor:pointer;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease; }
    .ru-card::before { content:'';position:absolute;inset:0;background:rgba(255,255,255,0);transition:background .2s ease; }
    .ru-card:hover { transform:translateY(-4px); }
    .ru-card:hover::before { background:rgba(255,255,255,.07); }
    .ru-card-lieu  { background:linear-gradient(135deg,#064e3b 0%,#059669 55%,#34d399 100%);box-shadow:0 4px 14px rgba(5,150,105,.25); }
    .ru-card-unite { background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 55%,#60a5fa 100%);box-shadow:0 4px 14px rgba(37,99,235,.25); }
    .dark .ru-card-lieu  { background:linear-gradient(135deg,#022c22 0%,#047857 55%,#10b981 100%); }
    .dark .ru-card-unite { background:linear-gradient(135deg,#172554 0%,#1d4ed8 55%,#3b82f6 100%); }
    .ru-card-icon { display:flex;align-items:center;justify-content:center;width:3.5rem;height:3.5rem;border-radius:.875rem;background:rgba(255,255,255,.15);backdrop-filter:blur(4px);flex-shrink:0; }
    .ru-card-title { font-size:1rem;font-weight:700;color:#fff; }
    .ru-card-desc  { margin-top:.25rem;font-size:.8125rem;color:rgba(255,255,255,.75); }
    .ru-card-cta   { display:flex;align-items:center;gap:.25rem;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.6);opacity:0;transition:opacity .15s; }
    .ru-card:hover .ru-card-cta { opacity:1;color:rgba(255,255,255,.95); }
</style>

@if($etape === 'lieu')
    @php $lieux = $this->getLieux(); @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($lieux as $lieu)
            <button wire:click="selectLieu({{ $lieu->id }})" class="ru-card ru-card-lieu">
                <div style="display:flex;align-items:flex-start;gap:.75rem;">
                    <div class="ru-card-icon">
                        <x-heroicon-o-map-pin style="width:2rem;height:2rem;color:#a7f3d0;"/>
                    </div>
                </div>
                <div style="flex:1;">
                    <p class="ru-card-title">{{ $lieu->libelle_court ?? $lieu->libelle_long ?? '' }}</p>
                    @if($lieu->libelle_long && $lieu->libelle_long !== ($lieu->libelle_court ?? ''))
                        <p class="ru-card-desc">{{ $lieu->libelle_long }}</p>
                    @endif
                </div>
                <div class="ru-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;"/></div>
            </button>
        @endforeach
    </div>

@elseif($etape === 'unite')
    @php $unites = $this->getUnites(); @endphp
    <div class="mb-5">
        <x-filament::button wire:click="retourLieux" color="gray" icon="heroicon-o-arrow-left" size="sm">
            Retour aux lieux
        </x-filament::button>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($unites as $unite)
            <button wire:click="selectUnite({{ $unite->id }})" class="ru-card ru-card-unite">
                <div style="display:flex;align-items:flex-start;gap:.75rem;">
                    <div class="ru-card-icon">
                        <x-heroicon-o-building-office-2 style="width:2rem;height:2rem;color:#bfdbfe;"/>
                    </div>
                </div>
                <div style="flex:1;">
                    <p class="ru-card-title">{{ $unite->libelle_court ?? $unite->nom ?? '' }}</p>
                </div>
                <div class="ru-card-cta">Sélectionner <x-heroicon-o-arrow-right style="width:13px;height:13px;"/></div>
            </button>
        @endforeach
    </div>

@else
    <div class="mb-2">
        <x-filament::button wire:click="retourUnites" color="gray" icon="heroicon-o-arrow-left" size="sm">
            Changer d'unité
        </x-filament::button>
    </div>
@endif

</x-filament-panels::page>


