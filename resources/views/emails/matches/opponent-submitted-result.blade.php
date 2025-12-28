<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Adversaire a Soumis son Score</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 28px;">📊 Score Soumis!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">{{ $submitter->name }} a soumis le résultat de votre match</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Bonjour {{ $opponent->name }},</p>

        <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-weight: bold; color: #1e40af;">
                Votre adversaire <strong>{{ $submitter->name }}</strong> a soumis son score pour votre affrontement.
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
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">Soumis par:</td>
                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">{{ $submitter->name }}</td>
            </tr>
            <tr style="background-color: #eff6ff;">
                <td style="padding: 12px; font-weight: bold; color: #1e40af;">Score Déclaré:</td>
                <td style="padding: 12px; font-weight: bold; color: #1e40af; font-size: 18px;">
                    {{ $submitter->name }}: {{ $matchResult->own_score }} - Vous: {{ $matchResult->opponent_score }}
                </td>
            </tr>
        </table>

        <h2 style="color: #111827; margin-top: 30px;">Que Devez-vous Faire?</h2>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #e5e7eb;">
            <h3 style="margin-top: 0; color: #10b981; font-size: 18px;">✅ Si Vous Êtes d'Accord avec ce Score</h3>
            <p style="margin: 10px 0; color: #374151;">
                <strong>Vous n'avez plus besoin de faire quoi que ce soit!</strong><br>
                Le système considérera automatiquement ce score comme le résultat final du match.
            </p>
        </div>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #f59e0b;">
            <h3 style="margin-top: 0; color: #d97706; font-size: 18px;">⚠️ Si Vous N'Êtes PAS d'Accord</h3>
            <p style="margin: 10px 0; color: #374151;">
                Vous pouvez <strong>contester</strong> en soumettant le bon score sur votre espace match avant la deadline.
            </p>
            <ul style="margin: 10px 0; padding-left: 20px; color: #374151;">
                <li>Allez sur votre espace match</li>
                <li>Soumettez le score correct avec une capture d'écran</li>
                <li>L'organisateur <strong>{{ $match->tournament->organizer->name }}</strong> tranchera pour déterminer le vrai score final</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}"
               style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
                Voir le Match
            </a>
        </div>

        <div style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #991b1b;">
                <strong>⚠️ ATTENTION - Règle Importante:</strong>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #991b1b;">
                Si vous contestez le score et que l'organisateur détermine que <strong>vous avez tort</strong>, vous serez <strong>DISQUALIFIÉ</strong> de la compétition.
            </p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #991b1b;">
                Ne contestez que si vous êtes <strong>absolument certain</strong> que le score soumis est incorrect.
            </p>
        </div>

        @if($match->deadline_at)
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #92400e;">
                <strong>⏰ Deadline:</strong> {{ $match->deadline_at->format('d/m/Y à H:i') }}
            </p>
        </div>
        @endif

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
