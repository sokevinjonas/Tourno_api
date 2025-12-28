@extends('emails.layout')

@section('content')
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">🚨 FINALE - Prolongation d'Urgence</h2>
    <p style="margin: 10px 0 0 0; font-size: 15px; color: #fecaca;">La deadline de la finale a été prolongée</p>
</div>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $player->name }}</strong>,</p>

<div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #991b1b; font-size: 14px;">
        Vous êtes en FINALE ! La deadline a expiré mais aucun joueur n'a soumis de résultat.
    </p>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Prolongation Exceptionnelle</h3>

<div class="info-box">
    <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #334155;">
        Comme il s'agit de la <strong style="color: #dc2626;">FINALE</strong> du tournoi, nous accordons une prolongation exceptionnelle de <strong>24 heures</strong>.
    </p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #fffbeb; border-radius: 6px; overflow: hidden; border: 1px solid #fbbf24;">
        <tr>
            <td style="padding: 12px; font-weight: 600; font-size: 14px;">Ancienne deadline</td>
            <td style="padding: 12px; text-align: right; text-decoration: line-through; font-size: 14px;">{{ $match->deadline_at->subHours(24)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr style="background-color: #dcfce7;">
            <td style="padding: 12px; font-weight: 600; color: #16a34a; font-size: 14px;">Nouvelle deadline</td>
            <td style="padding: 12px; text-align: right; font-weight: 600; color: #16a34a; font-size: 16px;">{{ $newDeadline->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Informations du Match de Finale</h3>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb;">
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Tournoi</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $match->tournament->name }}</td>
    </tr>
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Round</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">🏆 {{ $match->round->round_name ?? "Finale" }}</td>
    </tr>
    <tr>
        <td style="padding: 12px; font-weight: 600; font-size: 14px;">Adversaire</td>
        <td style="padding: 12px; text-align: right; font-size: 14px;">{{ $opponent->name }}</td>
    </tr>
</table>

<div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #991b1b; font-size: 14px;">
        ⚠️ DERNIÈRE CHANCE
    </p>
    <p style="margin: 10px 0 0 0; font-size: 14px; color: #991b1b; line-height: 1.6;">
        Si aucun résultat n'est soumis avant <strong>{{ $newDeadline->format('d/m/Y à H:i') }}</strong> :
    </p>
    <ul style="margin: 10px 0; padding-left: 20px; color: #991b1b; font-size: 14px; line-height: 1.6;">
        <li>Le tournoi sera <strong>annulé</strong></li>
        <li>Aucun champion ne sera déclaré</li>
        <li>Les deux finalistes seront disqualifiés</li>
    </ul>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
       style="display: inline-block; background: #0f172a; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Soumettre le Résultat MAINTENANT
    </a>
</div>

<div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #854d0e; line-height: 1.6;">
        <strong>Rappel :</strong> Contactez votre adversaire {{ $opponent->name }} dès que possible pour organiser votre match. C'est la FINALE !
    </p>
</div>

<p style="margin-top: 24px; font-size: 15px; color: #334155;">
    Nous comptons sur vous pour faire honneur à cette finale !
</p>
@endsection
