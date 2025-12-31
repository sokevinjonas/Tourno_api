@extends('emails.layout')

@section('content')
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">⚠️ Action Requise Immédiatement</h2>
    <p style="margin: 10px 0 0 0; font-size: 15px; color: #fecaca;">Fonds Insuffisants pour Paiement des Prix</p>
</div>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $organizer->name }}</strong>,</p>

<div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #991b1b; font-size: 14px;">
        Votre tournoi "{{ $tournament->name }}" ne peut pas distribuer les prix aux gagnants car vos fonds sont insuffisants.
    </p>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Détails de la Situation</h3>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb;">
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Total des prix à distribuer</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ number_format($totalRequired, 2) }} GPA</td>
    </tr>
    <tr>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">Fonds bloqués disponibles</td>
        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ number_format($availableFunds, 2) }} GPA</td>
    </tr>
    <tr style="background-color: #fef2f2;">
        <td style="padding: 12px; font-weight: 600; color: #dc2626; font-size: 14px;">Montant manquant</td>
        <td style="padding: 12px; text-align: right; font-weight: 600; color: #dc2626; font-size: 16px;">{{ number_format($shortage, 2) }} GPA</td>
    </tr>
</table>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Action Requise dans les 48 Heures</h3>

<div class="info-box">
    <p style="margin: 0 0 12px 0; font-size: 14px; line-height: 1.6; color: #334155;">
        Vous devez ajouter <strong style="color: #dc2626;">{{ number_format($shortage, 2) }} GPA</strong> à votre portefeuille dans les <strong>48 heures</strong>.
    </p>

    <p style="margin: 12px 0 8px 0; font-size: 14px; color: #64748b; font-weight: 600;">
        Si les fonds ne sont pas ajoutés à temps :
    </p>
    <ul style="margin: 0; padding-left: 20px; color: #64748b; font-size: 14px; line-height: 1.6;">
        <li>Tous les participants seront remboursés</li>
        <li>Votre compte organisateur sera suspendu</li>
        <li>Vous ne pourrez plus créer de tournois</li>
    </ul>
</div>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url') }}/wallet"
       style="display: inline-block; background: #0f172a; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;"
       target="_blank">
        Ajouter des Fonds Maintenant
    </a>
</div>

<div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #854d0e; line-height: 1.6;">
        <strong>Rappel :</strong> Cette situation s'est produite car le total des prix à distribuer dépasse les frais d'inscription collectés pour ce tournoi.
    </p>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Informations du Tournoi</h3>

<table style="width: 100%; font-size: 14px; color: #334155; line-height: 1.8;">
    <tr>
        <td style="padding: 6px 0;"><strong>Nom :</strong></td>
        <td style="padding: 6px 0;">{{ $tournament->name }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 0;"><strong>Jeu :</strong></td>
        <td style="padding: 6px 0;">{{ ucfirst(str_replace('_', ' ', $tournament->game)) }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 0;"><strong>Participants :</strong></td>
        <td style="padding: 6px 0;">{{ $tournament->registrations()->count() }}</td>
    </tr>
</table>

<p style="margin-top: 24px; font-size: 14px; color: #64748b;">
    Si vous avez des questions, contactez notre support.
</p>
@endsection
