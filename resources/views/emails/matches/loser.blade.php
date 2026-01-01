@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Résultat de votre match</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $loser->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Votre match du tournoi <strong>{{ $match->round->tournament->name }}</strong> (Round {{ $match->round->round_number }}) est terminé.
</p>

<div style="background: #ff9800; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 18px; margin: 0 0 10px 0;">
        Score final
    </p>
    <p style="color: white; font-size: 32px; margin: 0; font-weight: bold;">
        {{ $loserScore }} - {{ $winnerScore }}
    </p>
</div>

<div class="info-box" style="background-color: #fff3e0; border-left: 4px solid #ff9800;">
    <h3 style="margin-bottom: 15px; color: #ff9800;">Ne baissez pas les bras !</h3>
    <p style="margin: 0 0 10px 0; font-size: 16px; line-height: 1.6;">
        Chaque défaite est une opportunité d'apprendre et de s'améliorer. Les plus grands champions ont tous connu des revers avant de triompher.
    </p>
    <p style="margin: 0; font-size: 16px; font-weight: bold; color: #667eea;">
        Le tournoi continue, et vous avez encore toutes vos chances de briller lors des prochains matchs !
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Conseils pour progresser</h3>
    <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
        <li>Analysez calmement ce match pour identifier vos points d'amélioration</li>
        <li>Entraînez-vous sur les aspects qui vous ont posé problème</li>
        <li>Restez concentré et déterminé pour les prochains rounds</li>
        <li>La persévérance est la clé du succès !</li>
    </ul>
</div>

<p style="margin: 30px 0 20px 0; font-size: 16px; color: #666; font-style: italic; text-align: center;">
    "Le succès n'est pas final, l'échec n'est pas fatal : c'est le courage de continuer qui compte."
</p>

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $match->round->tournament->uuid }}"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Voir le tournoi
    </a>
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #e8f5e9; border-radius: 5px; border-left: 4px solid #4caf50;">
    <p style="margin: 0; font-size: 14px; color: #2e7d32; font-weight: bold;">
        Continuez à vous battre ! Chaque round est une nouvelle opportunité de montrer votre valeur.
    </p>
</div>
@endsection
