@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Inscription confirmée !</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Votre inscription au tournoi <strong>{{ $tournament->name }}</strong> a été confirmée avec succès.
</p>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Détails du tournoi</h3>
    <p style="margin: 5px 0;"><strong>Nom :</strong> {{ $tournament->name }}</p>
    <p style="margin: 5px 0;"><strong>Jeu :</strong> {{ ucfirst(str_replace('_', ' ', $tournament->game)) }}</p>
    <p style="margin: 5px 0;"><strong>Frais d'inscription :</strong> {{ number_format($entry_fee, 2) }} MLM</p>
    <p style="margin: 5px 0;"><strong>Date de début :</strong> {{ $tournament->start_date->format('d/m/Y à H:i') }}</p>
    <p style="margin: 5px 0;"><strong>Participants max :</strong> {{ $tournament->max_participants }}</p>
</div>

<p style="margin-bottom: 20px;">
    Vous recevrez un nouvel email dès que le tournoi démarrera avec les informations de votre premier adversaire.
</p>

<p style="margin-bottom: 20px;">
    Bonne chance ! 🎮
</p>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Note :</strong> Si le tournoi n'est pas lancé dans les 48 heures après avoir atteint le nombre maximum de participants, vous serez automatiquement remboursé.
</p>
@endsection
