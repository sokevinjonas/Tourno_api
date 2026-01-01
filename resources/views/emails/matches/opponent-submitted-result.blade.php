@extends('emails.layout')

@section('content')
<div style="background: #0f172a; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">📊 Score Soumis</h2>
    <p style="margin: 10px 0 0 0; font-size: 15px; color: #cbd5e1;">{{ $submitter->name }} a soumis le résultat de votre match</p>
</div>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $opponent->name }}</strong>,</p>

<div style="background-color: #dbeafe; border-left: 4px solid #0f172a; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #1e40af; font-size: 14px;">
        Votre adversaire <strong>{{ $submitter->name }}</strong> a soumis son score pour votre affrontement.
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
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Soumis par</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $submitter->name }}</td>
    </tr>
    <tr style="background-color: #f0f9ff;">
        <td style="padding: 12px; font-weight: 600; color: #0f172a; font-size: 14px;">Score Déclaré</td>
        <td style="padding: 12px; font-weight: 600; color: #0f172a; font-size: 16px; text-align: right;">
            {{ $submitter->name }}: {{ $matchResult->own_score }} - {{ $opponent->name }}: {{ $matchResult->opponent_score }}
        </td>
    </tr>
</table>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Que Devez-vous Faire ?</h3>

<div style="background-color: #f0fdf4; padding: 20px; border-radius: 6px; margin: 20px 0; border: 2px solid #22c55e;">
    <h4 style="margin-top: 0; color: #16a34a; font-size: 16px;">✅ Si Vous Êtes d'Accord avec ce Score</h4>
    <p style="margin: 10px 0 0 0; color: #334155; font-size: 14px; line-height: 1.6;">
        <strong>Vous n'avez plus besoin de faire quoi que ce soit !</strong><br>
        Le système considérera automatiquement ce score comme le résultat final du match.
    </p>
</div>

<div style="background-color: #fffbeb; padding: 20px; border-radius: 6px; margin: 20px 0; border: 2px solid #f59e0b;">
    <h4 style="margin-top: 0; color: #d97706; font-size: 16px;">⚠️ Si Vous N'Êtes PAS d'Accord</h4>
    <p style="margin: 10px 0; color: #334155; font-size: 14px; line-height: 1.6;">
        Vous pouvez <strong>contester</strong> en soumettant le bon score sur votre espace match avant la deadline.
    </p>
    <ul style="margin: 10px 0; padding-left: 20px; color: #334155; font-size: 14px; line-height: 1.6;">
        <li>Allez sur votre espace match</li>
        <li>Soumettez le score correct avec une capture d'écran</li>
        <li>L'organisateur <strong>{{ $match->tournament->organizer->name }}</strong> tranchera pour déterminer le vrai score final</li>
    </ul>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
       style="display: inline-block; background: #0f172a; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;"
       target="_blank">
        Voir le Match
    </a>
</div>

<div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #991b1b; font-weight: 600;">
        ⚠️ ATTENTION - Règle Importante
    </p>
    <p style="margin: 10px 0 0 0; font-size: 14px; color: #991b1b; line-height: 1.6;">
        Si vous contestez le score et que l'organisateur détermine que <strong>vous avez tort</strong>, vous serez <strong>DISQUALIFIÉ</strong> de la compétition.
        Ne contestez que si vous êtes <strong>absolument certain</strong> que le score soumis est incorrect.
    </p>
</div>

@if($match->deadline_at)
<div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #92400e; font-weight: 600;">
        ⏰ Deadline : {{ $match->deadline_at->format('d/m/Y à H:i') }}
    </p>
</div>
@endif

<p style="margin-top: 24px; font-size: 15px; color: #334155;">
    Bonne chance !
</p>
@endsection
