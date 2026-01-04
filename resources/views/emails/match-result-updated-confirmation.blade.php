@extends('emails.layout')

@section('content')
    <h1 style="color: #333333; margin-bottom: 10px; font-size: 24px;">✏️ Modification de Score Confirmée</h1>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Bonjour <strong>{{ $player->name }}</strong>,
    </p>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Vous avez modifié avec succès votre soumission de résultat pour votre match.
    </p>

    <!-- Match Info -->
    <div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 6px;">
        <table style="width: 100%; font-size: 14px;">
            <tr>
                <td style="padding: 3px 0; color: #666;">Tournoi :</td>
                <td style="padding: 3px 0; color: #333; font-weight: 600; text-align: right;">{{ $match->tournament->name }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0; color: #666;">Round :</td>
                <td style="padding: 3px 0; color: #333; font-weight: 600; text-align: right;">Round {{ $match->round->round_number }}</td>
            </tr>
            <tr>
                <td style="padding: 3px 0; color: #666;">Adversaire :</td>
                <td style="padding: 3px 0; color: #333; font-weight: 600; text-align: right;">{{ $opponent->name }}</td>
            </tr>
        </table>
    </div>

    <!-- Score Submitted -->
    <div style="background-color: #f8f9fa; border-left: 4px solid #0f172a; padding: 15px; margin: 15px 0; border-radius: 6px;">
        <p style="margin: 0 0 8px 0; color: #0f172a; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
            📊 Score modifié
        </p>
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">Vous</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: #0f172a;">
                        {{ $matchResult->own_score }}
                    </p>
                </td>
                <td style="width: 10%; text-align: center;">
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #0f172a;">-</p>
                </td>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">{{ $opponent->name }}</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: #0f172a;">
                        {{ $matchResult->opponent_score }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px; color: #555555;">
            <strong>Prochaine étape :</strong> Votre adversaire doit maintenant soumettre son score. Si les scores correspondent, le match sera validé automatiquement.
        </p>
    </div>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ env('FRONTEND_URL') }}/tournaments/{{ $match->tournament->uuid }}" class="btn">
            Voir le tournoi
        </a>
    </div>

    <p style="margin-top: 20px; color: #6c757d; font-size: 13px; text-align: center; font-style: italic;">
        Vous pouvez modifier votre soumission autant de fois que nécessaire avant que le match ne soit validé.
    </p>
@endsection
