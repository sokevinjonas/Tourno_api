<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Requise - Fonds Insuffisants</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">⚠️ Action Requise Immédiatement</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Fonds Insuffisants pour Paiement des Prix</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Bonjour {{ $organizer->name }},</p>

        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #991b1b;">
                Votre tournoi "{{ $tournament->name }}" ne peut pas distribuer les prix aux gagnants car vos fonds sont insuffisants.
            </p>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Détails de la Situation</h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 8px; overflow: hidden;">
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Total des prix à distribuer:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format($totalRequired, 2) }} MLM</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Fonds bloqués disponibles:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; text-align: right;">{{ number_format($availableFunds, 2) }} MLM</td>
            </tr>
            <tr style="background-color: #fef2f2;">
                <td style="padding: 12px; font-weight: bold; color: #dc2626;">Montant manquant:</td>
                <td style="padding: 12px; text-align: right; font-weight: bold; color: #dc2626; font-size: 18px;">{{ number_format($shortage, 2) }} MLM</td>
            </tr>
        </table>

        <h2 style="color: #111827; margin-top: 30px;">Action Requise dans les 48 Heures</h2>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 15px 0; font-size: 15px;">
                Vous devez ajouter <strong style="color: #dc2626;">{{ number_format($shortage, 2) }} MLM</strong> à votre portefeuille dans les <strong>48 heures</strong>.
            </p>

            <p style="margin: 0; font-size: 14px; color: #6b7280;">
                Si les fonds ne sont pas ajoutés à temps:
            </p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #6b7280; font-size: 14px;">
                <li>Tous les participants seront remboursés</li>
                <li>Votre compte organisateur sera suspendu</li>
                <li>Vous ne pourrez plus créer de tournois</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/wallet"
               style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Ajouter des Fonds Maintenant
            </a>
        </div>

        <div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #854d0e;">
                <strong>Rappel:</strong> Cette situation s'est produite car le total des prix à distribuer dépasse les frais d'inscription collectés pour ce tournoi.
            </p>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Informations du Tournoi</h2>
        <table style="width: 100%; font-size: 14px; color: #6b7280;">
            <tr>
                <td style="padding: 5px 0;"><strong>Nom:</strong></td>
                <td style="padding: 5px 0;">{{ $tournament->name }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0;"><strong>Jeu:</strong></td>
                <td style="padding: 5px 0;">{{ ucfirst(str_replace('_', ' ', $tournament->game)) }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0;"><strong>Participants:</strong></td>
                <td style="padding: 5px 0;">{{ $tournament->registrations()->count() }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
            Si vous avez des questions, contactez notre support.
        </p>

        <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">
            Cordialement,<br>
            L'équipe Tourno
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; padding: 20px; font-size: 12px; color: #9ca3af;">
        <p style="margin: 0;">Cet email a été envoyé automatiquement par le système Tourno.</p>
    </div>
</body>
</html>
