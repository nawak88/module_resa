@php
    $categories = $this->getCategories();
@endphp

<x-filament-widgets::widget>
    @if($categories->isNotEmpty())
    <x-filament::section>
        <x-slot name="heading">Réservation de ressources</x-slot>

        <style>
            .sel-res-0 { background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 55%,#60a5fa 100%); box-shadow:0 4px 14px rgba(37,99,235,.25); }
            .sel-res-1 { background:linear-gradient(135deg,#064e3b 0%,#059669 55%,#34d399 100%); box-shadow:0 4px 14px rgba(5,150,105,.25); }
            .sel-res-2 { background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 55%,#a78bfa 100%); box-shadow:0 4px 14px rgba(124,58,237,.25); }
            .sel-res-3 { background:linear-gradient(135deg,#78350f 0%,#d97706 55%,#fbbf24 100%); box-shadow:0 4px 14px rgba(217,119,6,.25); }
            .sel-res-4 { background:linear-gradient(135deg,#881337 0%,#e11d48 55%,#fb7185 100%); box-shadow:0 4px 14px rgba(225,29,72,.25); }
            .sel-res-5 { background:linear-gradient(135deg,#164e63 0%,#0891b2 55%,#67e8f9 100%); box-shadow:0 4px 14px rgba(8,145,178,.25); }
            .dark .sel-res-0 { background:linear-gradient(135deg,#172554 0%,#1d4ed8 55%,#3b82f6 100%); }
            .dark .sel-res-1 { background:linear-gradient(135deg,#022c22 0%,#047857 55%,#10b981 100%); }
            .dark .sel-res-2 { background:linear-gradient(135deg,#2e1065 0%,#6d28d9 55%,#8b5cf6 100%); }
            .dark .sel-res-3 { background:linear-gradient(135deg,#451a03 0%,#b45309 55%,#d97706 100%); }
            .dark .sel-res-4 { background:linear-gradient(135deg,#4c0519 0%,#be123c 55%,#f43f5e 100%); }
            .dark .sel-res-5 { background:linear-gradient(135deg,#083344 0%,#0e7490 55%,#06b6d4 100%); }
        </style>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categories as $i => $cat)
                @php
                    $colorClass = 'sel-res-' . ($i % 6);
                    $icon  = !empty($cat->icone) ? $cat->icone : 'heroicon-o-cube';
                    $count = $cat->ressourcesActivesForUser->count();
                @endphp
                <a href="{{ $this->getCategorieUrl($cat->id) }}" class="sel-card {{ $colorClass }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div class="sel-card-icon">
                            <x-dynamic-component :component="$icon"
                                style="width:2rem;height:2rem;color:rgba(255,255,255,.9);filter:drop-shadow(0 0 6px rgba(255,255,255,.4));"/>
                        </div>
                        <span class="sel-card-badge">
                            <x-heroicon-o-cube style="width:10px;height:10px;"/>
                            {{ $count }}&nbsp;ressource{{ $count > 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div style="flex:1;">
                        <p class="sel-card-title">{{ $cat->libelle }}</p>
                        @if($cat->libelle_court && $cat->libelle_court !== $cat->libelle)
                            <p class="sel-card-desc">{{ $cat->libelle_court }}</p>
                        @endif
                    </div>
                    <div class="sel-card-cta">
                        Réserver
                        <x-heroicon-o-arrow-right style="width:13px;height:13px;flex-shrink:0;"/>
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
    @endif
</x-filament-widgets::widget>


