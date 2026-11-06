<x-filament-widgets::widget>
<style>
.mrwi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.mrwi-title{display:flex;align-items:center;gap:.5rem;font-size:.9375rem;font-weight:600;color:var(--gray-900,#111827);}
.dark .mrwi-title{color:#fff;}

.mrwi-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.75rem;margin-bottom:1.25rem;}
.mrwi-stat{border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);background:#fff;padding:.875rem 1rem;}
.dark .mrwi-stat{border-color:#374151;background:#111827;}
.mrwi-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.mrwi-stat-value{font-size:1.5rem;font-weight:700;color:var(--gray-900,#111827);margin-top:.25rem;}
.dark .mrwi-stat-value{color:#fff;}
.mrwi-stat-warn .mrwi-stat-value{color:#b45309;}
.dark .mrwi-stat-warn .mrwi-stat-value{color:#fbbf24;}

.mrwi-filters{display:flex;gap:.5rem;margin-bottom:1rem;}
.mrwi-filter-btn{border-radius:9999px;padding:.3rem .875rem;font-size:.8125rem;font-weight:600;border:1px solid var(--gray-300,#d1d5db);background:#fff;color:var(--gray-600,#4b5563);cursor:pointer;}
.dark .mrwi-filter-btn{border-color:#4b5563;background:#1f2937;color:#d1d5db;}
.mrwi-filter-btn-actif{background:#6366f1;border-color:#6366f1;color:#fff;}

.mrwi-empty{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:2.5rem 0;color:#9ca3af;}
.mrwi-empty svg{width:2rem;height:2rem;}
.mrwi-empty p{font-size:.875rem;}

.mrwi-table-wrap{overflow-x:auto;border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);}
.dark .mrwi-table-wrap{border-color:#374151;}
.mrwi-table{width:100%;font-size:.8125rem;border-collapse:collapse;}
.mrwi-table thead tr{background:var(--gray-50,#f9fafb);}
.dark .mrwi-table thead tr{background:#1f2937;}
.mrwi-table th{padding:.625rem 1rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);text-align:left;white-space:nowrap;}
.mrwi-table tbody tr{border-top:1px solid var(--gray-100,#f3f4f6);background:#fff;}
.dark .mrwi-table tbody tr{border-color:#374151;background:#111827;}
.mrwi-table tbody tr:hover{background:var(--gray-50,#f9fafb);}
.dark .mrwi-table tbody tr:hover{background:#1f2937;}
.mrwi-table tbody tr.mrwi-passee{opacity:.55;}
.mrwi-table td{padding:.625rem 1rem;color:var(--gray-600,#4b5563);}
.dark .mrwi-table td{color:#d1d5db;}
.mrwi-name{font-weight:500;color:var(--gray-900,#111827);}
.dark .mrwi-name{color:#fff;}
.mrwi-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:9999px;padding:.125rem .625rem;font-size:.7rem;font-weight:600;}
.mrwi-badge-salle{background:#e0e7ff;color:#4338ca;}
.dark .mrwi-badge-salle{background:rgba(67,56,202,.25);color:#a5b4fc;}
.mrwi-badge-materiel{background:#fae8ff;color:#a21caf;}
.dark .mrwi-badge-materiel{background:rgba(162,28,175,.25);color:#f0abfc;}
.mrwi-badge-ok{background:#dcfce7;color:#15803d;}
.dark .mrwi-badge-ok{background:rgba(21,128,61,.2);color:#4ade80;}
.mrwi-badge-wait{background:#fef3c7;color:#b45309;}
.dark .mrwi-badge-wait{background:rgba(180,83,9,.2);color:#fbbf24;}
.mrwi-count{margin-top:.5rem;font-size:.7rem;color:var(--gray-400,#9ca3af);text-align:right;}
.mrwi-valby{font-size:.7rem;color:var(--gray-500,#6b7280);}
</style>

    <div class="mrwi-header">
        <div class="mrwi-title">
            <x-heroicon-o-calendar-days style="width:1.25rem;height:1.25rem;color:#6366f1;flex-shrink:0;"/>
            Mes réservations
        </div>
        @if($limite)
            <a href="{{ \Modules\Resa\Filament\Pages\MesReservationsPage::getUrl() }}" class="text-sm font-semibold text-primary-600 hover:underline">
                Voir tout →
            </a>
        @endif
    </div>

    @php $stats = $this->getStats(); @endphp
    <div class="mrwi-stats">
        <div class="mrwi-stat">
            <div class="mrwi-stat-label">Total</div>
            <div class="mrwi-stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="mrwi-stat">
            <div class="mrwi-stat-label">À venir</div>
            <div class="mrwi-stat-value">{{ $stats['a_venir'] }}</div>
        </div>
        <div class="mrwi-stat mrwi-stat-warn">
            <div class="mrwi-stat-label">En attente</div>
            <div class="mrwi-stat-value">{{ $stats['en_attente'] }}</div>
        </div>
    </div>

    <div class="mrwi-filters">
        <button type="button" wire:click="$set('filtreStatut', null)" class="mrwi-filter-btn {{ $filtreStatut === null ? 'mrwi-filter-btn-actif' : '' }}">Toutes</button>
        <button type="button" wire:click="$set('filtreStatut', 1)" class="mrwi-filter-btn {{ $filtreStatut === 1 ? 'mrwi-filter-btn-actif' : '' }}">Validées</button>
        <button type="button" wire:click="$set('filtreStatut', 0)" class="mrwi-filter-btn {{ $filtreStatut === 0 ? 'mrwi-filter-btn-actif' : '' }}">En attente</button>
    </div>

    @php $rows = $this->getReservationsAffichees(); @endphp

    @if($rows->isEmpty())
        <div class="mrwi-empty">
            <x-heroicon-o-inbox style="width:2rem;height:2rem;"/>
            <p>Vous n'avez aucune réservation pour le moment.</p>
        </div>
    @else
        <div class="mrwi-table-wrap">
            <table class="mrwi-table">
                <thead>
                    <tr>
                        <th>Concerné(s)</th>
                        <th>Créé par</th>
                        <th>Type</th>
                        <th>Formation</th>
                        <th>Poste</th>
                        <th>Salle / Matériel</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                        <th>Présence</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr class="{{ $r['passee'] ? 'mrwi-passee' : '' }}">
                            <td class="mrwi-name">
                                @forelse($r['concernes'] as $c)
                                    <div>{{ $c }}</div>
                                @empty
                                    {{ $r['libelle'] }}
                                @endforelse
                            </td>
                            <td>{{ $r['cree_par'] ?? '—' }}</td>
                            <td>
                                @if($r['type'] === 'salle')
                                    <span class="mrwi-badge mrwi-badge-salle">Salle</span>
                                @else
                                    <span class="mrwi-badge mrwi-badge-materiel">Matériel</span>
                                @endif
                            </td>
                            <td>{{ $r['formation'] }}</td>
                            <td>{{ $r['poste'] }}</td>
                            <td>{{ $r['detail'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['debut_fmt'] }} – {{ $r['fin_fmt'] }}</td>
                            <td>
                                @if($r['valide'])
                                    <span class="mrwi-badge mrwi-badge-ok">Validée</span>
                                @else
                                    <span class="mrwi-badge mrwi-badge-wait">En attente</span>
                                @endif
                            </td>
                            <td class="mrwi-valby">
                                @if($r['validated_by'])
                                    {{ $r['validated_by'] }}<br>
                                    <span style="color:var(--gray-400,#9ca3af);">{{ $r['validated_at'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($r['peut_pointer'] && $r['presence'] === null)
                                    <div class="flex gap-1">
                                        <x-filament::button size="xs" color="success" wire:click="pointer({{ $r['id'] }}, true)">
                                            Présent
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:click="pointer({{ $r['id'] }}, false)">
                                            Absent
                                        </x-filament::button>
                                    </div>
                                @elseif($r['presence'] === true)
                                    <span class="mrwi-badge mrwi-badge-ok">Présent</span>
                                @elseif($r['presence'] === false)
                                    <span class="mrwi-badge mrwi-badge-wait" style="background:#fee2e2;color:#b91c1c;">Absent</span>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($r['peut_modifier'])
                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        wire:click="supprimer('{{ $r['type'] }}', {{ $r['id'] }})"
                                        wire:confirm="Supprimer cette réservation ?">
                                        Supprimer
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mrwi-count">
            @if($limite && $stats['total'] > $rows->count())
                {{ $rows->count() }} sur {{ $stats['total'] }} résultat(s)
            @else
                {{ $rows->count() }} résultat(s)
            @endif
        </p>
    @endif

</x-filament-widgets::widget>
