<x-mail::message>
# Nouvelle réservation

Une nouvelle réservation a été créée.

**Salle :** {{ $reservation->sallePoste?->salle?->libelle_court ?? $reservation->sallePoste?->libelle_court ?? '—' }}

**Du :** {{ $reservation->debut?->format('d/m/Y H:i') }}

**Au :** {{ $reservation->fin?->format('d/m/Y H:i') }}

@if($reservation->libelle_court)
**Objet :** {{ $reservation->libelle_court }}
@endif

@if($reservation->user)
**Réservé par :** {{ $reservation->user->name }}
@endif

</x-mail::message>
