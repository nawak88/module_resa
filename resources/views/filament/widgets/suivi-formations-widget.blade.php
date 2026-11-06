<x-filament-widgets::widget>
<style>
.sfwi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.sfwi-title{display:flex;align-items:center;gap:.5rem;font-size:.9375rem;font-weight:600;color:var(--gray-900,#111827);}
.dark .sfwi-title{color:#fff;}
.sfwi-subtitle{font-size:.75rem;color:var(--gray-400,#9ca3af);}

.sfwi-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:.75rem;margin-bottom:1.25rem;}
.sfwi-stat{border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);background:#fff;padding:.875rem 1rem;}
.dark .sfwi-stat{border-color:#374151;background:#111827;}
.sfwi-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.sfwi-stat-value{font-size:1.5rem;font-weight:700;color:var(--gray-900,#111827);margin-top:.25rem;}
.dark .sfwi-stat-value{color:#fff;}
.sfwi-stat-warn .sfwi-stat-value{color:#b45309;}
.dark .sfwi-stat-warn .sfwi-stat-value{color:#fbbf24;}

.sfwi-tabs{display:flex;gap:.375rem;margin-bottom:1rem;flex-wrap:wrap;}
.sfwi-tab{border-radius:9999px;padding:.375rem 1rem;font-size:.8125rem;font-weight:600;border:1px solid var(--gray-300,#d1d5db);background:#fff;color:var(--gray-600,#4b5563);cursor:pointer;}
.dark .sfwi-tab{border-color:#4b5563;background:#1f2937;color:#d1d5db;}
.sfwi-tab-actif{background:#6366f1;border-color:#6366f1;color:#fff;}

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

.sfwi-cal-wrap{margin-bottom:1.5rem;}
</style>

    {{-- Titre --}}
    <div class="sfwi-header">
        <div class="sfwi-title">
            <x-heroicon-o-academic-cap style="width:1.25rem;height:1.25rem;color:#6366f1;flex-shrink:0;"/>
            @if($this->isAdmin())
                Suivi des formations
            @else
                Mes formations
                <span class="sfwi-subtitle">— réservations à valider</span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ \Modules\Resa\Filament\Pages\ReservationsFormationPage::getUrl() }}" class="text-sm font-semibold text-primary-600 hover:underline">
                Voir tout →
            </a>
            <button type="button" wire:click="ouvrirNouvelleReservation" class="fi-btn fi-size-md fi-color-primary fi-btn-color-primary">
                <x-heroicon-o-plus style="width:1rem;height:1rem;"/>
                Nouvelle réservation
            </button>
        </div>
    </div>

    {{-- Onglets (uniquement si plusieurs formations gérées, hors admin) --}}
    @if($this->afficherOnglets())
        <div class="sfwi-tabs">
            <button type="button"
                    wire:click="selectionnerFormation(null)"
                    class="sfwi-tab {{ $this->filtreFormationId === null ? 'sfwi-tab-actif' : '' }}">
                Toutes
            </button>
            @foreach($this->getFormationsDisponibles() as $f)
                <button type="button"
                        wire:click="selectionnerFormation({{ $f->id }})"
                        class="sfwi-tab {{ $this->filtreFormationId === $f->id ? 'sfwi-tab-actif' : '' }}">
                    {{ $f->libelle_court }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Cartes statistiques --}}
    @php $stats = $this->getStats(); @endphp
    <div class="sfwi-stats">
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Formations gérées</div>
            <div class="sfwi-stat-value">{{ $stats['nb_formations'] }}</div>
        </div>
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Réservations à venir</div>
            <div class="sfwi-stat-value">{{ $stats['a_venir'] }}</div>
        </div>
        <div class="sfwi-stat sfwi-stat-warn">
            <div class="sfwi-stat-label">En attente de validation</div>
            <div class="sfwi-stat-value">{{ $stats['en_attente'] }}</div>
        </div>
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Réservations ce mois</div>
            <div class="sfwi-stat-value">{{ $stats['ce_mois'] }}</div>
        </div>
    </div>

    {{-- Calendrier (CalendrierWidget Guava, mode transversal filtré par formation) --}}
    <div class="sfwi-cal-wrap">
        @livewire(\Modules\Resa\Filament\Widgets\CalendrierWidget::class,
            [
                'modeFormationGlobale' => true,
                'filtreFormations'     => $this->getFormationIdsCalendrier(),
            ],
            key('sfwi-cal-' . implode('-', $this->getFormationIdsCalendrier()) . '-' . $this->calendarVersion)
        )
    </div>

    {{-- Filtres --}}
    <div class="sfwi-filters">

        @if($this->isAdmin())
            <div class="sfwi-filter">
                <label class="sfwi-label">Formation</label>
                <select wire:model.live="filtreFormationId" class="sfwi-select">
                    <option value="">Toutes</option>
                    @foreach($this->getFormationsDisponibles() as $f)
                        <option value="{{ $f->id }}">{{ $f->libelle_court }}</option>
                    @endforeach
                </select>
            </div>
        @endif

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
                        <th>Concerné(s)</th>
                        <th>Créé par</th>
                        <th>Formation</th>
                        <th>Poste</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                        <th>Présence</th>
                        <th></th>
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
                            <td>{{ $r['cree_par'] ?? '—' }}</td>
                            <td>{{ $r['formation'] }}</td>
                            <td>{{ $r['poste'] }}</td>
                            <td>{{ $r['salle'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['debut'] }} – {{ $r['fin'] }}</td>
                            <td>
                                @if($r['valide'])
                                    <span class="sfwi-badge sfwi-badge-ok">Validé</span>
                                @else
                                    <span class="sfwi-badge sfwi-badge-wait">En attente</span>
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
                            <td>
                                @if($r['peut_pointer'] && $r['presence'] === null)
                                    <div class="flex gap-1">
                                        <x-filament::button size="xs" color="success" wire:click="pointerGestionnaire({{ $r['id'] }}, true)">
                                            Présent
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:click="pointerGestionnaire({{ $r['id'] }}, false)">
                                            Absent
                                        </x-filament::button>
                                    </div>
                                @elseif($r['presence'] === true)
                                    <span class="sfwi-badge sfwi-badge-ok">Présent</span>
                                @elseif($r['presence'] === false)
                                    <span class="sfwi-badge" style="background:#fee2e2;color:#b91c1c;">Absent</span>
                                @else
                                    <span style="color:var(--gray-400,#9ca3af);">—</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    wire:click="mountAction('voirStagiaire', { reservationId: {{ $r['id'] }} })">
                                    Voir
                                </x-filament::button>
                                @if($r['peut_valider'])
                                    <x-filament::button
                                        size="xs"
                                        :color="$r['valide'] ? 'warning' : 'success'"
                                        wire:click="toggleValidation({{ $r['id'] }})"
                                        wire:confirm="{{ $r['valide'] ? 'Invalider cette réservation ?' : 'Valider cette réservation ?' }}">
                                        {{ $r['valide'] ? 'Invalider' : 'Valider' }}
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="sfwi-count">{{ $rows->count() }} résultat(s)</p>
    @endif

    <x-filament-actions::modals/>
</x-filament-widgets::widget>
