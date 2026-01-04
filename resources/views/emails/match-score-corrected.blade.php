@extends('emails.layout')

@section('content')
    <h1 style="color: #333333; margin-bottom: 10px; font-size: 24px;">⚠️ Correction de Score</h1>

    <p style="margin-bottom: 15px; font-size: 14px; color: #dc3545; font-weight: 600;">
        Une erreur a été détectée dans les scores de votre match. L'organisateur a procédé à une correction.
    </p>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Bonjour <strong>{{ $player->name }}</strong>,
    </p>

    <!-- Old Score -->
    <div style="background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 6px;">
        <p style="margin: 0 0 8px 0; color: #6c757d; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
            ❌ Ancien score (erroné)
        </p>
        <table style="width: 100%; text-decoration: line-through; opacity: 0.5;">
            <tr>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666;">Vous</p>
                    <p style="margin: 5px 0 0 0; font-size: 28px; font-weight: bold; color: #999;">{{ $oldPlayerScore }}</p>
                </td>
                <td style="width: 10%; text-align: center;">
                    <p style="margin: 0; font-size: 20px; color: #999;">-</p>
                </td>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666;">Adversaire</p>
                    <p style="margin: 5px 0 0 0; font-size: 28px; font-weight: bold; color: #999;">{{ $oldOpponentScore }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- New Score -->
    <div style="background-color: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 6px;">
        <p style="margin: 0 0 8px 0; color: #28a745; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
            ✅ Nouveau score (officiel)
        </p>
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">Vous</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: {{ $result === 'win' ? '#28a745' : ($result === 'loss' ? '#dc3545' : '#6c757d') }};">
                        {{ $newPlayerScore }}
                    </p>
                </td>
                <td style="width: 10%; text-align: center;">
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #0f172a;">-</p>
                </td>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">Adversaire</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: {{ $result === 'win' ? '#dc3545' : ($result === 'loss' ? '#28a745' : '#6c757d') }};">
                        {{ $newOpponentScore }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Result -->
    @if($result === 'win')
        <div class="info-box" style="background-color: #d4edda; border-left-color: #28a745;">
            <p style="margin: 0; color: #155724; font-size: 16px; font-weight: 600;">
                🎉 Félicitations ! Vous avez gagné ce match.
            </p>
            <p style="margin: 5px 0 0 0; color: #155724; font-size: 14px;">
                Le classement du tournoi a été mis à jour.
            </p>
        </div>
    @elseif($result === 'loss')
        <div class="info-box" style="background-color: #f8d7da; border-left-color: #dc3545;">
            <p style="margin: 0; color: #721c24; font-size: 16px; font-weight: 600;">
                Vous avez perdu ce match.
            </p>
            <p style="margin: 5px 0 0 0; color: #721c24; font-size: 14px;">
                Continuez vos efforts pour les prochains matchs !
            </p>
        </div>
    @else
        <div class="info-box">
            <p style="margin: 0; color: #555555; font-size: 16px; font-weight: 600;">
                🤝 Match nul
            </p>
            <p style="margin: 5px 0 0 0; color: #555555; font-size: 14px;">
                Les deux joueurs ont obtenu le même score.
            </p>
        </div>
    @endif

    <!-- Tournament Info -->
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
                <td style="padding: 3px 0; color: #666;">Jeu :</td>
                <td style="padding: 3px 0; color: #333; font-weight: 600; text-align: right;">{{ ucfirst(str_replace('_', ' ', $match->tournament->game)) }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ env('FRONTEND_URL') }}/tournaments/{{ $match->tournament->uuid }}" class="btn">
            Voir le tournoi
        </a>
    </div>

    <p style="margin-top: 20px; color: #6c757d; font-size: 13px; text-align: center; font-style: italic;">
        Cette correction a été effectuée par l'organisateur. Votre classement et statistiques ont été mis à jour.
    </p>
@endsection
