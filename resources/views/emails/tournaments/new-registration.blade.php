@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Nouvelle inscription reçue</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $tournament->organizer->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Un nouveau participant vient de s'inscrire à votre tournoi <strong>{{ $tournament->name }}</strong>.
</p>

<div class="info-box" style="background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin-bottom: 20px;">
    <h3 style="margin-bottom: 10px; color: #4caf50;">Nouveau participant</h3>
    <p style="margin: 5px 0;"><strong>Nom :</strong> {{ $participant->name }}</p>
    <p style="margin: 5px 0;"><strong>Email :</strong> {{ $participant->email }}</p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">État du tournoi</h3>
    <p style="margin: 5px 0;"><strong>Participants inscrits :</strong> {{ $totalRegistrations }} / {{ $maxParticipants }}</p>
    <p style="margin: 5px 0;"><strong>Places restantes :</strong> {{ $spotsRemaining }}</p>

    @if ($spotsRemaining === 0)
        <p style="margin-top: 10px; color: #4caf50; font-weight: bold;">
            Le tournoi est complet ! Vous pouvez maintenant le démarrer.
        </p>
    @else
        <p style="margin-top: 10px; color: #ff9800;">
            En attente de {{ $spotsRemaining }} participant(s) supplémentaire(s).
        </p>
    @endif
</div>

<p style="margin: 20px 0;">
    <strong>Frais d'inscription reçus :</strong> {{ number_format($tournament->entry_fee, 2) }} MLM
</p>

<p style="margin-bottom: 20px;">
    Les fonds ont été ajoutés à votre wallet et seront bloqués automatiquement lorsque vous démarrerez le tournoi.
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        <strong>Astuce :</strong> Vous pouvez consulter la liste complète des participants depuis votre tableau de bord organisateur.
    </p>
</div>
@endsection
