@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Match nul</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $player->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Votre match du tournoi <strong>{{ $match->round->tournament->name }}</strong> (Round {{ $match->round->round_number }}) s'est terminé par un match nul.
</p>

<div style="background: #ff9800; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 18px; margin: 0 0 10px 0;">
        Score final
    </p>
    <p style="color: white; font-size: 32px; margin: 0; font-weight: bold;">
        {{ $score }} - {{ $score }}
    </p>
    <p style="color: white; font-size: 16px; margin: 10px 0 0 0;">
        Match nul
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Détails du match</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Tournoi</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $match->round->tournament->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Round</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">Round {{ $match->round->round_number }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Adversaire</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">
                {{ $match->player1_id === $player->id ? $match->player2->name : $match->player1->name }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong>Points gagnés</strong></td>
            <td style="padding: 8px 0; text-align: right; color: #ff9800; font-weight: bold;">1 point</td>
        </tr>
    </table>
</div>

<div class="info-box" style="background-color: #e3f2fd; border-left: 4px solid #2196f3;">
    <h3 style="margin-bottom: 10px; color: #2196f3;">Match équilibré</h3>
    <p style="margin: 0 0 10px 0; font-size: 16px; line-height: 1.6;">
        Un match nul témoigne d'une belle opposition entre deux adversaires de même niveau. Chacun repart avec 1 point au classement.
    </p>
    <p style="margin: 0; font-size: 16px; font-weight: bold; color: #667eea;">
        Continuez à vous améliorer pour faire la différence lors des prochains rounds !
    </p>
</div>

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $match->round->tournament->uuid }}"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Voir le tournoi
    </a>
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        Chaque point compte pour le classement final !
    </p>
</div>
@endsection
