@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Félicitations !</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $winner->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Vous avez remporté votre match dans le tournoi <strong>{{ $match->round->tournament->name }}</strong> !
</p>

<div style="background: #4caf50; padding: 25px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 24px; margin: 0 0 10px 0; font-weight: bold;">
        ✓ Victoire !
    </p>
    <p style="color: white; font-size: 32px; margin: 0; font-weight: bold;">
        {{ $winnerScore }} - {{ $loserScore }}
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
            <td style="padding: 8px 0;"><strong>Score final</strong></td>
            <td style="padding: 8px 0; text-align: right; color: #4caf50; font-weight: bold;">{{ $winnerScore }} - {{ $loserScore }}</td>
        </tr>
    </table>
</div>

<p style="margin: 30px 0 20px 0; font-size: 16px; color: #666;">
    Continuez sur cette lancée et donnez le meilleur de vous-même pour les prochains matchs !
</p>

<p style="margin: 30px 0 20px 0;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $match->round->tournament->uuid }}"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Voir le tournoi
    </a>
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        Bonne chance pour la suite du tournoi !
    </p>
</div>
@endsection
