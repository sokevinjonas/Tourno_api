<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel Deadline de Match</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">⏰ Rappel de Deadline</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Votre match expire dans {{ $hoursRemaining }} heure(s)</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Bonjour {{ $player->name }},</p>

        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #92400e;">
                Vous devez soumettre le résultat de votre match avant la deadline pour éviter une disqualification!
            </p>
        </div>

        <h2 style="color: #111827; margin-top: 30px;">Informations du Match</h2>

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
            <tr style="background-color: #fef3c7;">
                <td style="padding: 12px; font-weight: bold; color: #d97706;">Deadline:</td>
                <td style="padding: 12px; font-weight: bold; color: #d97706; font-size: 18px;">{{ $match->deadline_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <h2 style="color: #111827; margin-top: 30px;">Que Faire?</h2>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <ol style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">Jouer votre match avec {{ $opponent->name }}</li>
                <li style="margin-bottom: 10px;">Prendre une capture d'écran du résultat</li>
                <li style="margin-bottom: 10px;">Soumettre le résultat avant <strong>{{ $match->deadline_at->format('d/m/Y à H:i') }}</strong></li>
            </ol>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
               style="display: inline-block; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Soumettre le Résultat
            </a>
        </div>

        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #991b1b;">
                <strong>⚠️ Attention:</strong> Si vous ne soumettez pas le résultat à temps:
            </p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #991b1b; font-size: 14px;">
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

        <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
            Bonne chance!
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
