<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #333; padding: 20px;">

<h2>Réservation annulée</h2>

<p>La réservation suivante a été supprimée.</p>

<table style="border-collapse: collapse; margin-top: 16px;">
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Salle</td>
        <td>{{ $reservation->sallePoste?->salle?->libelle_court ?? $reservation->sallePoste?->libelle_court ?? '—' }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Du</td>
        <td>{{ $reservation->debut?->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Au</td>
        <td>{{ $reservation->fin?->format('d/m/Y H:i') }}</td>
    </tr>
    @if($reservation->libelle_court)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Objet</td>
        <td>{{ $reservation->libelle_court }}</td>
    </tr>
    @endif
    @if($reservation->user)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Réservé par</td>
        <td>{{ $reservation->user->display_name ?? $reservation->user->email }}</td>
    </tr>
    @endif
</table>

</body>
</html>
