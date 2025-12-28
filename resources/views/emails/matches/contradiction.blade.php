@extends('emails.layout')

@section('content')
<div style="background: #f59e0b; color: white; padding: 24px; text-align: center; border-radius: 8px; margin-bottom: 24px;">
    <h2 style="margin: 0; font-size: 24px; color: white;">⚠️ Incohérence de Score Détectée</h2>
</div>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-weight: 600; color: #92400e; font-size: 14px;">
        <strong>Action requise</strong><br>
        Les deux joueurs ont soumis des résultats différents pour ce match. Votre intervention est nécessaire pour valider le score correct.
    </p>
</div>

<div class="info-box">
    <h3 style="margin-top: 0; color: #0f172a; font-size: 18px;">{{ $tournament->name }}</h3>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Round :</strong> {{ $match->round->round_name ?? 'Round ' . $match->round->round_number }}</p>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Match ID :</strong> #{{ $match->id }}</p>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Deadline :</strong> {{ $match->deadline_at->format('d/m/Y H:i') }}</p>

    <div style="display: flex; justify-content: space-between; align-items: center; margin: 24px 0; padding: 20px; background-color: white; border-radius: 6px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; flex: 1;">
            <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 16px;">{{ $player1->name }}</h4>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Joueur 1</p>
        </div>
        <div style="font-weight: 600; font-size: 18px; color: #0f172a; padding: 0 20px;">VS</div>
        <div style="text-align: center; flex: 1;">
            <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 16px;">{{ $player2->name }}</h4>
            <p style="margin: 0; color: #64748b; font-size: 13px;">Joueur 2</p>
        </div>
    </div>
</div>

<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Scores soumis</h3>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; background-color: white; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb;">
    <thead>
        <tr style="background-color: #0f172a;">
            <th style="padding: 12px; text-align: left; color: white; font-size: 14px;">Soumis par</th>
            <th style="padding: 12px; text-align: left; color: white; font-size: 14px;">Score de {{ $player1->name }}</th>
            <th style="padding: 12px; text-align: left; color: white; font-size: 14px;">Score de {{ $player2->name }}</th>
        </tr>
    </thead>
    <tbody>
        <tr style="{{ $submission1->own_score !== $submission2->opponent_score ? 'background-color: #fef2f2;' : '' }}">
            <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px;">{{ $player1->name }}</td>
            <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px;">{{ $submission1->own_score }}</td>
            <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px;">{{ $submission1->opponent_score }}</td>
        </tr>
        <tr style="{{ $submission2->own_score !== $submission1->opponent_score ? 'background-color: #fef2f2;' : '' }}">
            <td style="padding: 12px; font-weight: 600; font-size: 14px;">{{ $player2->name }}</td>
            <td style="padding: 12px; font-size: 14px;">{{ $submission2->opponent_score }}</td>
            <td style="padding: 12px; font-size: 14px;">{{ $submission2->own_score }}</td>
        </tr>
    </tbody>
</table>

@if($submission1->proof_url || $submission2->proof_url)
<h3 style="color: #0f172a; margin: 24px 0 16px 0; font-size: 18px;">Preuves fournies</h3>
<div class="info-box">
    <ul style="margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8; color: #334155;">
        @if($submission1->proof_url)
            <li><strong>{{ $player1->name }} :</strong> <a href="{{ $submission1->proof_url }}" target="_blank" style="color: #0f172a; font-weight: 600;">Voir la preuve</a></li>
        @endif
        @if($submission2->proof_url)
            <li><strong>{{ $player2->name }} :</strong> <a href="{{ $submission2->proof_url }}" target="_blank" style="color: #0f172a; font-weight: 600;">Voir la preuve</a></li>
        @endif
    </ul>
</div>
@endif

<p style="margin: 24px 0; font-size: 15px; line-height: 1.6; color: #334155;">
    Veuillez examiner les preuves et valider le score correct dans l'interface d'administration.
</p>

<div style="text-align: center; margin: 28px 0;">
    <a href="{{ config('app.frontend_url') }}/organizer/tournaments/{{ $tournament->id }}/matches/{{ $match->id }}"
       style="display: inline-block; background: #0f172a; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Résoudre le conflit
    </a>
</div>

<div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 16px; margin: 24px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
        Cet email a été envoyé automatiquement par le système de gestion de tournois.<br>
        Vous recevez cet email car vous êtes l'organisateur du tournoi "{{ $tournament->name }}".
    </p>
</div>
@endsection
