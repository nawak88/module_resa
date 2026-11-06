<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Modules\Resa\Traits\HasTablePrefix;
use App\Models\User;
use Modules\Resa\Models\Formation;

class Reservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid', 'user_id', 'created_by', 'salle_poste_id', 'libelle_court', 'libelle_long',
        'debut', 'fin', 'couleur', 'validation', 'formations_utilisees',
        'sous_categorie_id', 'validated_by', 'validated_at', 'data',
        'presence', 'presence_marquee_par', 'presence_marquee_at',
    ];

    protected $casts = [
        'debut'               => 'datetime',
        'fin'                 => 'datetime',
        'validated_at'        => 'datetime',
        'validation'          => 'integer',
        'formations_utilisees' => 'array',
        'data'                => 'array',
        'presence'            => 'boolean',
        'presence_marquee_at' => 'datetime',
    ];

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    /* ── Relations ────────────────────────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** La personne qui a effectué la réservation — peut différer du stagiaire (user_id)
     *  quand on réserve pour quelqu'un d'autre. */
    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sallePoste(): BelongsTo
    {
        return $this->belongsTo(SallePoste::class, 'salle_poste_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function sousCategorie(): BelongsTo
    {
        return $this->belongsTo(FormationSousCategorie::class, 'sous_categorie_id');
    }

    public function presenceMarqueePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presence_marquee_par');
    }

    /* ── Pointeuse ────────────────────────────────────────────────────────── */

    /** Le jour J (et pas avant) : pas de pointage pour une séance future ou passée. */
    public function peutPointer(): bool
    {
        return $this->isValide() && $this->debut?->isToday();
    }

    public function pointer(bool $present, User $marquePar): void
    {
        $this->update([
            'presence'             => $present,
            'presence_marquee_par' => $marquePar->id,
            'presence_marquee_at'  => now(),
        ]);
    }

    /* ── Scopes ───────────────────────────────────────────────────────────── */

    public function scopeValide($query)
    {
        return $query->where('validation', self::VALIDE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('validation', self::EN_ATTENTE);
    }

    /* ── Methodes metier ──────────────────────────────────────────────────── */

    public function isValide(): bool
    {
        return $this->validation === self::VALIDE;
    }

    public function isEnAttente(): bool
    {
        return $this->validation === self::EN_ATTENTE;
    }

    public function valider($admin): void
    {
        $this->update([
            'validation'   => self::VALIDE,
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);
    }

    public function invalider(): void
    {
        $this->update([
            'validation'   => self::EN_ATTENTE,
            'validated_by' => null,
            'validated_at' => null,
        ]);
    }

    /**
     * Noms des stagiaires/participants (data.users, ou le demandeur seul à défaut) —
     * utilisé pour l'affichage calendrier et l'email de validation.
     */
    public function participantsNoms(): array
    {
        $userIds = collect($this->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $this->user_id) {
            $userIds = collect([$this->user_id]);
        }

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::whereIn('id', $userIds)
            ->get()
            ->map(fn ($u) => trim("{$u->nom} {$u->prenom}") ?: $u->email)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Tous les destinataires légitimes d'une notification sur cette réservation :
     * le réservataire (créateur), tous les participants (marin 1, marin 2…), les
     * gestionnaires de la/les formation(s) et de la salle, plus les éventuelles
     * adresses de notification configurées (champ "email" de Salle/Formation).
     * Dédupliqué et filtré sur les adresses valides (cf. ReservationObserver).
     */
    public function destinataires(bool $inclureGestionnaires = true): array
    {
        $emails = [];

        if ($this->creePar?->email) {
            $emails[] = $this->creePar->email;
        } elseif ($this->user?->email) {
            // Anciennes réservations sans created_by : le stagiaire reste le seul
            // repère disponible.
            $emails[] = $this->user->email;
        }

        $userIds = collect($this->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $this->user_id) {
            $userIds = collect([$this->user_id]);
        }
        // NB : ->each(fn ($e) => $emails[] = $e) ne marche PAS ici — les arrow functions
        // PHP capturent $emails par valeur, donc l'ajout se perdait silencieusement.
        // array_push(..., ...$collection) le fait correctement.
        array_push($emails, ...User::whereIn('id', $userIds)->pluck('email')->all());

        // $inclureGestionnaires = false : cas de l'auto-validation (formation à
        // validation_auto, ou création directe par un gestionnaire déjà au courant),
        // pour ne pas spammer les gestionnaires d'un événement qu'ils n'ont pas eu à
        // traiter eux-mêmes — cf. ReservationObserver::created().
        if ($inclureGestionnaires) {
            if (! empty($this->formations_utilisees)) {
                $formations = Formation::whereIn('id', $this->formations_utilisees)->with('gestionnaires')->get();

                foreach ($formations as $formation) {
                    array_push($emails, ...$formation->gestionnaires->pluck('email')->all());

                    if ($formation->email) {
                        array_push($emails, ...explode(',', $formation->email));
                    }
                }
            }

            $salle = $this->sallePoste?->salle;
            if ($salle) {
                array_push($emails, ...$salle->gestionnaires->pluck('email')->all());

                if ($salle->email) {
                    array_push($emails, ...explode(',', $salle->email));
                }
            }
        }

        return collect($emails)
            ->map(fn ($email) => trim((string) $email))
            // Une adresse mal formée (ex. profil RH/LDAP incomplet) ne doit jamais faire
            // planter l'envoi : Mail::to() valide le format immédiatement, avant même
            // la mise en file (ShouldQueue ne protège pas contre ça).
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Préfixe stable pour l'objet des emails (ex. "[Resa #42]") : permet de créer une
     * règle de classement Outlook par réservation ou par module, indépendamment du
     * contenu (qui change selon l'étape : création, validation, rappel).
     */
    public function objetMail(): string
    {
        $quoi = ! empty($this->formations_utilisees)
            ? Formation::whereIn('id', $this->formations_utilisees)->pluck('libelle_court')->join(', ')
            : ($this->sallePoste?->salle?->libelle_court ?? $this->libelle_court);

        $quand = $this->debut?->format('d/m/Y H:i');

        return "[Resa #{$this->id}] {$quoi} — {$quand}";
    }

    /**
     * Vrai si l'utilisateur courant n'a pas besoin d'être distingué des autres pour
     * cette réservation : pas de formation concernée (salle classique), admin complet,
     * gestionnaire d'au moins une des formations utilisées, ou demandeur lui-même.
     * Utilisé uniquement pour l'affichage (couleur) sur le calendrier transversal.
     */
    private function estGereeParUtilisateurCourant(): bool
    {
        if (empty($this->formations_utilisees)) {
            return true;
        }

        $user = auth()->user();
        if (! $user) {
            return true;
        }

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($user->can('resa::admin') || $user->can('resa::gestionnaire_salle')) {
            return true;
        }

        return Formation::whereIn('id', $this->formations_utilisees)
            ->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }

    /* ── Guava Calendar — Eventable ───────────────────────────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        // ── Couleur formation ────────────────────────────────────────────────
        $formationColor = null;
        if (! empty($this->formations_utilisees)) {
            $couleur = Formation::whereIn('id', $this->formations_utilisees)->value('couleur');
            $formationColor = match ($couleur) {
                'primary'   => '#6366f1',
                'secondary' => '#64748b',
                'success'   => '#22c55e',
                'danger'    => '#ef4444',
                'warning'   => '#f59e0b',
                'info'      => '#3b82f6',
                'gray'      => '#6b7280',
                default     => null,
            };
        }
        // En attente : ambre, nettement distinct du vert/couleur formation des réservations
        // validées (même convention que RessourceReservation::couleur()) — le gris utilisé
        // auparavant se distinguait trop peu visuellement sur le calendrier.
        //
        // Quand le calendrier transversal montre aussi les réservations d'autres
        // formations (cf. CalendrierWidget::sallesFormationGlobale()), un gestionnaire
        // doit pouvoir distinguer d'un coup d'œil ce qui relève de sa formation de ce
        // qui ne l'est pas : couleurs grisées/estompées pour « pas la mienne ».
        $estMaFormation = $this->estGereeParUtilisateurCourant();

        $backgroundColor = match (true) {
            $this->isValide() && $estMaFormation   => $formationColor ?? '#22c55e',
            $this->isValide() && ! $estMaFormation => '#94a3b8', // slate — validée, autre formation
            ! $this->isValide() && $estMaFormation => '#f59e0b',
            default                                 => '#cbd5e1', // gris clair — en attente, autre formation
        };

        // ── Label formations ─────────────────────────────────────────────────
        $capacitesLabel = '';
        if (! empty($this->formations_utilisees)) {
            $capacitesLabel = Formation::whereIn('id', $this->formations_utilisees)
                ->orderBy('ordre')
                ->pluck('libelle_court')
                ->join(', ');
        }

        // ── Noms des utilisateurs (si connecté) ──────────────────────────────
        $isConnecte   = auth()->check();
        $isSimulateur = ! empty($this->formations_utilisees);

        $nomsUsers = [];
        if ($isConnecte) {
            // Tous les user IDs : data.users en priorité, sinon user_id seul
            $userIds = collect($this->data['users'] ?? [])->filter()->values();
            if ($userIds->isEmpty() && $this->user_id) {
                $userIds = collect([$this->user_id]);
            }
            if ($userIds->isNotEmpty()) {
                $nomsUsers = \App\Models\User::whereIn('id', $userIds)
                    ->get()
                    ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                    ->filter()
                    ->values()
                    ->toArray();
            }
        }

        // ── Titre affiché ────────────────────────────────────────────────────
        $labelTitre = $isSimulateur
            ? ($capacitesLabel ?: ($this->libelle_court ?? ''))
            : ($this->libelle_court ?? '');

        $lignes = array_filter([
            $labelTitre,
            ...array_map(fn ($n) => '👤 ' . $n, $nomsUsers),
        ]);

        return CalendarEvent::make($this)
            ->title(implode("\n", $lignes))
            ->start($this->debut ? \Carbon\Carbon::parse($this->debut->format('Y-m-d H:i:s'), 'Europe/Paris') : null)
            ->end($this->fin ? \Carbon\Carbon::parse($this->fin->format('Y-m-d H:i:s'), 'Europe/Paris') : null)
            ->resourceId($this->salle_poste_id)
            ->backgroundColor($backgroundColor)
            ->extendedProp('validation', $this->validation)
            ->extendedProp('description', $this->libelle_long)
            ->extendedProp('formations', $this->formations_utilisees ?? [])
            ->extendedProp('noms', $nomsUsers)
            ->extendedProp('capacites_label', $capacitesLabel)
            ->extendedProp('poste', $this->sallePoste?->libelle_court ?? '')
            ->extendedProp('heure_debut', $this->debut?->format('H:i') ?? '')
            ->extendedProp('heure_fin',   $this->fin?->format('H:i') ?? '')
            ->editable(! $this->isValide() && auth()->id() === $this->user_id);
    }
}
