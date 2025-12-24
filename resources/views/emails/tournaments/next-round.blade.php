@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Nouveau Round généré !</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $player->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Le Round <strong>{{ $round->round_number }}</strong> du tournoi <strong>{{ $tournament->name }}</strong> vient d'être généré !
</p>

<div style="background: #667eea; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 24px; margin: 0 0 10px 0; font-weight: bold;">
        Round {{ $round->round_number }}
    </p>
    @if($match)
        <p style="color: white; font-size: 18px; margin: 0;">
            Vous affronterez <strong>{{ $match->player1_id === $player->id ? $match->player2->name : $match->player1->name }}</strong>
        </p>
    @endif
</div>

@if($match)
<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Détails du match</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Tournoi</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $tournament->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Round</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">Round {{ $round->round_number }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Adversaire</strong></td>
            <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">
                {{ $match->player1_id === $player->id ? $match->player2->name : $match->player1->name }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0;"><strong>Statut</strong></td>
            <td style="padding: 8px 0; text-align: right; color: #ff9800; font-weight: bold;">
                À jouer
            </td>
        </tr>
    </table>
</div>
@endif

<div class="info-box" style="background-color: #e8f5e9; border-left: 4px solid #4caf50;">
    <h3 style="margin-bottom: 10px; color: #4caf50;">Préparez-vous !</h3>
    <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
        <li>Assurez-vous d'être disponible pour jouer votre match</li>
        <li>Contactez votre adversaire pour organiser le match</li>
        <li>Préparez votre meilleure stratégie</li>
        <li>N'oubliez pas de soumettre les scores après le match</li>
    </ul>
</div>

<p style="margin: 30px 0 20px 0;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $tournament->id }}"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Voir le tournoi
    </a>
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        Bonne chance pour ce nouveau round !
    </p>
</div>
@endsection
