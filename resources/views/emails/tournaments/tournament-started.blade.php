@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Le tournoi a commencé ! 🎮</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Le tournoi <strong>{{ $tournament->name }}</strong> vient de démarrer !
</p>

@if($firstMatch && $opponent)
<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Votre premier match</h3>
    <p style="margin: 10px 0; font-size: 18px;">
        <strong>Adversaire :</strong> {{ $opponent->name }}
    </p>
    @if($opponent->email)
    <p style="margin: 5px 0;"><strong>Email :</strong> {{ $opponent->email }}</p>
    @endif
    <p style="margin: 10px 0; color: #6c757d; font-size: 14px;">
        Contactez votre adversaire pour organiser votre match et soumettre les résultats.
    </p>
</div>
@elseif($firstMatch && !$opponent)
<div class="info-box" style="background-color: #d4edda; border-left: 4px solid #28a745;">
    <h3 style="margin-bottom: 10px; color: #28a745;">Bye - Passage automatique au tour suivant</h3>
    <p style="margin: 5px 0;">
        Vous n'avez pas d'adversaire pour ce premier tour et passez automatiquement au tour suivant.
    </p>
</div>
@endif

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Rappel du tournoi</h3>
    <p style="margin: 5px 0;"><strong>Nom :</strong> {{ $tournament->name }}</p>
    <p style="margin: 5px 0;"><strong>Jeu :</strong> {{ ucfirst(str_replace('_', ' ', $tournament->game_type)) }}</p>
    <p style="margin: 5px 0;"><strong>Format :</strong> {{ ucfirst($tournament->format) }}</p>
    @if($tournament->prize_pool > 0)
    <p style="margin: 5px 0;"><strong>Prize Pool :</strong> {{ number_format($tournament->prize_pool, 2) }} MLM</p>
    @endif
</div>

<p style="margin-bottom: 20px;">
    Vous pouvez accéder à votre match et communiquer avec votre adversaire via le chat intégré sur la plateforme.
</p>

<p style="margin-bottom: 20px;">
    Bonne chance ! 💪
</p>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Astuce :</strong> N'oubliez pas de fournir des preuves (captures d'écran) après votre match pour valider les résultats.
</p>
@endsection
