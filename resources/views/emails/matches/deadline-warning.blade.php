@extends('emails.layout')

@section('content')
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">⏰ Rappel de Deadline</h2>
    <p style="margin: 10px 0 0 0; font-size: 15px; color: #fecaca;">Votre match expire dans {{ $minutesRemaining }} minutes</p>
</div>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $player->name }}</strong>,</p>

<div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #92400e; font-size: 14px;">
        Vous devez soumettre le résultat de votre match avant la deadline pour éviter une disqualification !
    </p>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Informations du Match</h3>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb;">
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Tournoi</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $match->tournament->name }}</td>
    </tr>
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Round</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $match->round->round_name ?? "Round {$match->round->round_number}" }}</td>
    </tr>
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Adversaire</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $opponent->name }}</td>
    </tr>
    <tr style="background-color: #fef3c7;">
        <td style="padding: 12px; font-weight: 600; color: #d97706; font-size: 14px;">Deadline</td>
        <td style="padding: 12px; font-weight: 600; color: #d97706; font-size: 16px; text-align: right;">{{ $match->deadline_at->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Que Faire ?</h3>

<div class="info-box">
    <ol style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8; color: #334155;">
        <li style="margin-bottom: 8px;">Jouer votre match avec {{ $opponent->name }}</li>
        <li style="margin-bottom: 8px;">Prendre une capture d'écran du résultat</li>
        <li style="margin-bottom: 8px;">Soumettre le résultat avant <strong>{{ $match->deadline_at->format('d/m/Y à H:i') }}</strong></li>
    </ol>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
       style="display: inline-block; background: #0f172a; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Soumettre le Résultat
    </a>
</div>

<div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #991b1b; font-weight: 600;">
        ⚠️ Attention : Si vous ne soumettez pas le résultat à temps
    </p>
    <ul style="margin: 10px 0; padding-left: 20px; color: #991b1b; font-size: 14px; line-height: 1.6;">
        @if($match->tournament->format === 'swiss')
            <li>Le match sera compté comme un match nul (0-0)</li>
            <li>Vous recevrez 1 point au lieu de 3</li>
        @else
            <li>Vous risquez d'être disqualifié</li>
            <li>Votre adversaire pourrait gagner par forfait</li>
            <li>Si aucun joueur ne soumet, les deux seront disqualifiés</li>
        @endif
    </ul>
</div>

<p style="margin-top: 24px; font-size: 15px; color: #334155;">
    Bonne chance !
</p>
@endsection
