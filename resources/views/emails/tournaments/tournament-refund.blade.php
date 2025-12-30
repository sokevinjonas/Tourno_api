@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Remboursement de votre inscription</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Le tournoi <strong>{{ $tournament->name }}</strong> n'a pas pu être lancé dans les délais impartis.
</p>

<div class="info-box" style="background-color: #fff3cd; border-left: 4px solid #ffc107;">
    <h3 style="margin-bottom: 10px; color: #856404;">Tournoi annulé</h3>
    <p style="margin: 5px 0;">
        Le tournoi était complet mais n'a pas été démarré par l'organisateur dans les 48 heures suivant la date limite.
    </p>
    <p style="margin: 10px 0; font-size: 18px;">
        <strong>Montant remboursé :</strong> {{ number_format($refundAmount, 2) }} MLM
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Détails du tournoi</h3>
    <p style="margin: 5px 0;"><strong>Nom :</strong> {{ $tournament->name }}</p>
    <p style="margin: 5px 0;"><strong>Jeu :</strong> {{ ucfirst(str_replace('_', ' ', $tournament->game_type)) }}</p>
    <p style="margin: 5px 0;"><strong>Organisateur :</strong> {{ $tournament->organizer->name }}</p>
</div>

<p style="margin-bottom: 20px;">
    Le montant de <strong>{{ number_format($refundAmount, 2) }} MLM</strong> a été automatiquement crédité sur votre wallet.
</p>

<p style="margin-bottom: 20px;">
    Nous vous invitons à découvrir d'autres tournois disponibles sur la plateforme.
</p>

<p style="margin-bottom: 20px;">
    Merci de votre compréhension.
</p>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Note :</strong> Ce remboursement a été effectué automatiquement conformément à nos conditions d'utilisation.
</p>
@endsection
