@extends('emails.layout')

@section('content')
    <h1 style="color: #333333; margin-bottom: 10px; font-size: 24px;">🔄 Modification de Score</h1>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Bonjour <strong>{{ $opponent->name }}</strong>,
    </p>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        <strong>{{ $submitter->name }}</strong> a modifié son score soumis pour votre match.
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
                <td style="padding: 3px 0; color: #666;">Jeu :</td>
                <td style="padding: 3px 0; color: #333; font-weight: 600; text-align: right;">{{ ucfirst(str_replace('_', ' ', $match->tournament->game)) }}</td>
            </tr>
        </table>
    </div>

    <!-- Score Submitted -->
    <div style="background-color: #f8f9fa; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 6px;">
        <p style="margin: 0 0 8px 0; color: #f59e0b; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
            📊 Score modifié par {{ $submitter->name }}
        </p>
        <table style="width: 100%;">
            <tr>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">{{ $submitter->name }}</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: #0f172a;">
                        {{ $matchResult->own_score }}
                    </p>
                </td>
                <td style="width: 10%; text-align: center;">
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #0f172a;">-</p>
                </td>
                <td style="width: 45%; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #666; font-weight: 600;">Vous</p>
                    <p style="margin: 5px 0 0 0; font-size: 32px; font-weight: bold; color: #0f172a;">
                        {{ $matchResult->opponent_score }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    @if($matchResult->comment)
        <div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 6px;">
            <p style="margin: 0 0 5px 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 1px;">
                💬 Commentaire
            </p>
            <p style="margin: 0; font-size: 14px; color: #333; font-style: italic;">
                "{{ $matchResult->comment }}"
            </p>
        </div>
    @endif

    <div class="info-box" style="background-color: #fef3c7; border-left-color: #f59e0b;">
        <p style="margin: 0; font-size: 14px; color: #92400e;">
            <strong>Action requise :</strong> Vérifiez ce score et soumettez le vôtre pour valider le match. Si les scores correspondent, le match sera validé automatiquement.
        </p>
    </div>

    <div style="text-align: center; margin: 25px 0;">
        <a href="{{ env('FRONTEND_URL') }}/tournaments/{{ $match->tournament->uuid }}" class="btn">
            Soumettre mon score
        </a>
    </div>
@endsection
