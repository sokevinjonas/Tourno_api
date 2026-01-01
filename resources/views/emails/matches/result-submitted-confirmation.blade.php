@extends('emails.layout')

@section('content')
<div style="background: #22c55e; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">✅ Score Soumis</h2>
    <p style="margin: 10px 0 0 0; font-size: 15px; color: #d1fae5;">Votre résultat a bien été enregistré</p>
</div>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $submitter->name }}</strong>,</p>

<div style="background-color: #d1fae5; border-left: 4px solid #22c55e; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #065f46; font-size: 14px;">
        Votre score a été soumis avec succès !
    </p>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Détails de la Soumission</h3>

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
    <tr style="background-color: #f0fdf4;">
        <td style="padding: 12px; font-weight: 600; color: #065f46; font-size: 14px;">Score Soumis</td>
        <td style="padding: 12px; font-weight: 600; color: #065f46; font-size: 16px; text-align: right;">
            {{ $submitter->name }}: {{ $matchResult->own_score }} - {{ $opponent->name }}: {{ $matchResult->opponent_score }}
        </td>
    </tr>
</table>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Prochaines Étapes</h3>

<div class="info-box">
    <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8; color: #334155;">
        <li>Votre adversaire <strong>{{ $opponent->name }}</strong> sera notifié de votre soumission</li>
        <li>Si {{ $opponent->name }} soumet le même score, le match sera <strong>validé automatiquement</strong></li>
        <li>Si les scores diffèrent, l'organisateur tranchera</li>
    </ul>
</div>

<div style="background-color: #eff6ff; border-left: 4px solid #0f172a; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #1e40af;">
        <strong>💡 Bon à savoir :</strong> Vous pouvez suivre l'état de votre match dans votre espace personnel.
    </p>
</div>

<p style="margin-top: 24px; font-size: 15px; color: #334155;">
    Merci d'utiliser notre plateforme !
</p>
@endsection
