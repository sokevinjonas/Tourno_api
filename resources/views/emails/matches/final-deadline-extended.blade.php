<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prolongation de la Finale</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">🚨 FINALE - Prolongation d'Urgence</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">La deadline de la finale a été prolongée</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Bonjour {{ $player->name }},</p>

        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #991b1b;">
                Vous êtes en FINALE! La deadline a expiré mais aucun joueur n'a soumis de résultat.
            </p>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Prolongation Exceptionnelle</h2>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 15px 0; font-size: 15px;">
                Comme il s'agit de la <strong style="color: #dc2626;">FINALE</strong> du tournoi, nous accordons une prolongation exceptionnelle de <strong>24 heures</strong>.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #fef3c7; border-radius: 8px; overflow: hidden;">
                <tr>
                    <td style="padding: 12px; font-weight: bold;">Ancienne deadline:</td>
                    <td style="padding: 12px; text-align: right; text-decoration: line-through;">{{ $match->deadline_at->subHours(24)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr style="background-color: #dcfce7;">
                    <td style="padding: 12px; font-weight: bold; color: #16a34a;">Nouvelle deadline:</td>
                    <td style="padding: 12px; text-align: right; font-weight: bold; color: #16a34a; font-size: 18px;">{{ $newDeadline->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Informations du Match de Finale</h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 8px; overflow: hidden;">
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Tournoi:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $match->tournament->name }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Round:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">🏆 {{ $match->round->round_name ?? "Finale" }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; font-weight: bold;">Adversaire:</td>
                <td style="padding: 12px;">{{ $opponent->name }}</td>
            </tr>
        </table>

        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #991b1b;">
                ⚠️ DERNIÈRE CHANCE
            </p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #991b1b;">
                Si aucun résultat n'est soumis avant <strong>{{ $newDeadline->format('d/m/Y à H:i') }}</strong>:
            </p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #991b1b; font-size: 14px;">
                <li>Le tournoi sera <strong>annulé</strong></li>
                <li>Aucun champion ne sera déclaré</li>
                <li>Les deux finalistes seront disqualifiés</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
               style="display: inline-block; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Soumettre le Résultat MAINTENANT
            </a>
        </div>

        <div style="background-color: #fefce8; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #854d0e;">
                <strong>Rappel:</strong> Contactez votre adversaire {{ $opponent->name }} dès que possible pour organiser votre match. C'est la FINALE!
            </p>
        </div>

        <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
            Nous comptons sur vous pour faire honneur à cette finale!
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
