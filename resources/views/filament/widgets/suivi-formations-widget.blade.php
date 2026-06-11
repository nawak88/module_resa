<x-filament-widgets::widget>
<style>
.sfwi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.sfwi-title{display:flex;align-items:center;gap:.5rem;font-size:.9375rem;font-weight:600;color:var(--gray-900,#111827);}
.dark .sfwi-title{color:#fff;}
.sfwi-subtitle{font-size:.75rem;color:var(--gray-400,#9ca3af);}
.sfwi-filters{display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;}
.sfwi-filter{display:flex;flex-direction:column;gap:.25rem;}
.sfwi-label{font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.sfwi-select,.sfwi-input{border:1px solid var(--gray-300,#d1d5db);border-radius:.5rem;background:#fff;padding:.375rem .75rem;font-size:.875rem;color:var(--gray-700,#374151);box-shadow:0 1px 2px 0 rgba(0,0,0,.05);}
.dark .sfwi-select,.dark .sfwi-input{border-color:#4b5563;background:#1f2937;color:#e5e7eb;}
.sfwi-input{width:11rem;}
.sfwi-empty{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:2.5rem 0;color:#9ca3af;}
.sfwi-empty svg{width:2rem;height:2rem;}
.sfwi-empty p{font-size:.875rem;}
.sfwi-table-wrap{overflow-x:auto;border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);}
.dark .sfwi-table-wrap{border-color:#374151;}
.sfwi-table{width:100%;font-size:.8125rem;border-collapse:collapse;}
.sfwi-table thead tr{background:var(--gray-50,#f9fafb);}
.dark .sfwi-table thead tr{background:#1f2937;}
.sfwi-table th{padding:.625rem 1rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);text-align:left;white-space:nowrap;}
.sfwi-table tbody tr{border-top:1px solid var(--gray-100,#f3f4f6);background:#fff;}
.dark .sfwi-table tbody tr{border-color:#374151;background:#111827;}
.sfwi-table tbody tr:hover{background:var(--gray-50,#f9fafb);}
.dark .sfwi-table tbody tr:hover{background:#1f2937;}
.sfwi-table td{padding:.625rem 1rem;color:var(--gray-600,#4b5563);}
.dark .sfwi-table td{color:#d1d5db;}
.sfwi-name{font-weight:500;color:var(--gray-900,#111827);}
.dark .sfwi-name{color:#fff;}
.sfwi-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:9999px;padding:.125rem .625rem;font-size:.7rem;font-weight:600;}
.sfwi-badge-ok{background:#dcfce7;color:#15803d;}
.dark .sfwi-badge-ok{background:rgba(21,128,61,.2);color:#4ade80;}
.sfwi-badge-wait{background:#fef3c7;color:#b45309;}
.dark .sfwi-badge-wait{background:rgba(180,83,9,.2);color:#fbbf24;}
.sfwi-valby{font-size:.7rem;color:var(--gray-500,#6b7280);}
.sfwi-count{margin-top:.5rem;font-size:.7rem;color:var(--gray-400,#9ca3af);text-align:right;}
</style>

    {{-- Titre --}}
    <div class="sfwi-header">
        <div class="sfwi-title">
            <x-heroicon-o-academic-cap style="width:1.25rem;height:1.25rem;color:#6366f1;flex-shrink:0;"/>
            Suivi des formations
            @if(! $this->isAdmin())
                <span class="sfwi-subtitle">(vos formations)</span>
            @endif
        </div>
    </div>

    {{-- Filtres --}}
    <div class="sfwi-filters">

        <div class="sfwi-filter">
            <label class="sfwi-label">Formation</label>
            <select wire:model.live="filtreFormationId" class="sfwi-select">
                <option value="">Toutes</option>
                @foreach($this->getFormationsDisponibles() as $f)
                    <option value="{{ $f->id }}">{{ $f->libelle_court }}</option>
                @endforeach
            </select>
        </div>

        <div class="sfwi-filter">
            <label class="sfwi-label">Période</label>
            <select wire:model.live="filtrePeriode" class="sfwi-select">
                <option value="semaine">Cette semaine</option>
                <option value="mois">Ce mois</option>
                <option value="annee">Cette année</option>
                <option value="tout">Tout</option>
            </select>
        </div>

        <div class="sfwi-filter">
            <label class="sfwi-label">Statut</label>
            <select wire:model.live="filtreStatut" class="sfwi-select">
                <option value="">Tous</option>
                <option value="1">Validé</option>
                <option value="0">En attente</option>
            </select>
        </div>

        <div class="sfwi-filter">
            <label class="sfwi-label">Stagiaire</label>
            <input wire:model.live.debounce.300ms="filtreNom"
                   type="text"
                   placeholder="Nom ou prénom…"
                   class="sfwi-input"/>
        </div>

    </div>

    {{-- Tableau --}}
    @php $rows = $this->getReservations(); @endphp

    @if($rows->isEmpty())
        <div class="sfwi-empty">
            <x-heroicon-o-inbox style="width:2rem;height:2rem;"/>
            <p>Aucune réservation de formation trouvée.</p>
        </div>
    @else
        <div class="sfwi-table-wrap">
            <table class="sfwi-table">
                <thead>
                    <tr>
                        <th>Stagiaires</th>
                        <th>Formation</th>
                        <th>Poste</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td class="sfwi-name">
                                @forelse($r['stagiaires'] as $s)
                                    <div>{{ $s }}</div>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>{{ $r['formation'] }}</td>
                            <td>{{ $r['poste'] }}</td>
                            <td>{{ $r['salle'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['debut'] }} – {{ $r['fin'] }}</td>
                            <td>
                                @if($r['valide'])
                                    <span class="sfwi-badge sfwi-badge-ok">✓ Validé</span>
                                @else
                                    <span class="sfwi-badge sfwi-badge-wait">⏳ En attente</span>
                                @endif
                            </td>
                            <td class="sfwi-valby">
                                @if($r['validated_by'])
                                    {{ $r['validated_by'] }}<br>
                                    <span style="color:var(--gray-400,#9ca3af);">{{ $r['validated_at'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="sfwi-count">{{ $rows->count() }} résultat(s)</p>
    @endif

</x-filament-widgets::widget>


