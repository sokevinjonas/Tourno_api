<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incohérence de score</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .match-info {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .players {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }
        .player {
            text-align: center;
            flex: 1;
        }
        .vs {
            font-weight: bold;
            font-size: 20px;
            color: #667eea;
            padding: 0 20px;
        }
        .scores-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .scores-table th,
        .scores-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .scores-table th {
            background-color: #667eea;
            color: white;
        }
        .conflict {
            background-color: #ffebee;
            font-weight: bold;
        }
        .action-button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ Incohérence de Score Détectée</h1>
    </div>

    <div class="content">
        <div class="alert">
            <strong>Action requise</strong><br>
            Les deux joueurs ont soumis des résultats différents pour ce match. Votre intervention est nécessaire pour valider le score correct.
        </div>

        <div class="match-info">
            <h2 style="margin-top: 0; color: #667eea;">{{ $tournament->name }}</h2>
            <p><strong>Round:</strong> {{ $match->round->round_name ?? 'Round ' . $match->round->round_number }}</p>
            <p><strong>Match ID:</strong> #{{ $match->id }}</p>
            <p><strong>Deadline:</strong> {{ $match->deadline_at->format('d/m/Y H:i') }}</p>

            <div class="players">
                <div class="player">
                    <h3>{{ $player1->name }}</h3>
                    <p style="color: #666;">Joueur 1</p>
                </div>
                <div class="vs">VS</div>
                <div class="player">
                    <h3>{{ $player2->name }}</h3>
                    <p style="color: #666;">Joueur 2</p>
                </div>
            </div>
        </div>

        <h3>Scores soumis :</h3>
        <table class="scores-table">
            <thead>
                <tr>
                    <th>Soumis par</th>
                    <th>Score de {{ $player1->name }}</th>
                    <th>Score de {{ $player2->name }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="{{ $submission1->own_score !== $submission2->opponent_score ? 'conflict' : '' }}">
                    <td><strong>{{ $player1->name }}</strong></td>
                    <td>{{ $submission1->own_score }}</td>
                    <td>{{ $submission1->opponent_score }}</td>
                </tr>
                <tr class="{{ $submission2->own_score !== $submission1->opponent_score ? 'conflict' : '' }}">
                    <td><strong>{{ $player2->name }}</strong></td>
                    <td>{{ $submission2->opponent_score }}</td>
                    <td>{{ $submission2->own_score }}</td>
                </tr>
            </tbody>
        </table>

        @if($submission1->proof_url || $submission2->proof_url)
        <h3>Preuves fournies :</h3>
        <ul>
            @if($submission1->proof_url)
                <li><strong>{{ $player1->name }}:</strong> <a href="{{ $submission1->proof_url }}" target="_blank">Voir la preuve</a></li>
            @endif
            @if($submission2->proof_url)
                <li><strong>{{ $player2->name }}:</strong> <a href="{{ $submission2->proof_url }}" target="_blank">Voir la preuve</a></li>
            @endif
        </ul>
        @endif

        <p style="margin-top: 30px;">
            Veuillez examiner les preuves et valider le score correct dans l'interface d'administration.
        </p>

        <div style="text-align: center;">
            <a href="{{ config('app.frontend_url') }}/organizer/tournaments/{{ $tournament->id }}/matches/{{ $match->id }}" class="action-button">
                Résoudre le conflit
            </a>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système de gestion de tournois.</p>
            <p>Vous recevez cet email car vous êtes l'organisateur du tournoi "{{ $tournament->name }}".</p>
        </div>
    </div>
</body>
</html>
