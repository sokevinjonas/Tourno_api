<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Score Soumis avec Succès</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">✅ Score Soumis!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Votre résultat a bien été enregistré</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Bonjour {{ $submitter->name }},</p>

        <div style="background-color: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #065f46;">
                Votre score a été soumis avec succès!
            </p>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Détails de la Soumission</h2>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 8px; overflow: hidden;">
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Tournoi:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $match->tournament->name }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Round:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $match->round->round_name ?? "Round {$match->round->round_number}" }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Adversaire:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $opponent->name }}</td>
            </tr>
            <tr style="background-color: #f0fdf4;">
                <td style="padding: 12px; font-weight: bold; color: #065f46;">Score Soumis:</td>
                <td style="padding: 12px; font-weight: bold; color: #065f46; font-size: 18px;">{{ $matchResult->own_score }} - {{ $matchResult->opponent_score }}</td>
            </tr>
        </table>

        <h2 style="color: #111827; margin-top: 30px;">Prochaines Étapes</h2>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <ul style="margin: 0; padding-left: 20px; line-height: 2;">
                <li>Votre adversaire <strong>{{ $opponent->name }}</strong> sera notifié de votre soumission</li>
                <li>Si {{ $opponent->name }} soumet le même score, le match sera <strong>validé automatiquement</strong></li>
                <li>Si les scores diffèrent, l'organisateur tranchera</li>
            </ul>
        </div>

        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #1e40af;">
                <strong>💡 Bon à savoir:</strong> Vous pouvez suivre l'état de votre match dans votre espace personnel.
            </p>
        </div>

        <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
            Merci d'utiliser Tourno!
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
