@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-bottom: 24px; font-size: 26px;">Nouveau Round disponible</h2>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $player->name }}</strong>,</p>

<p style="margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
    Le Round <strong>{{ $round->round_number }}</strong> du tournoi <strong>{{ $tournament->name }}</strong> vient d'être généré !
</p>

@php
    $opponent = null;
    if ($match) {
        $opponent = $match->player1_id === $player->id ? $match->player2 : $match->player1;
    }
@endphp

<div style="background: #0f172a; padding: 28px; border-radius: 8px; text-align: center; margin: 28px 0;">
    <p style="color: white; font-size: 22px; margin: 0 0 8px 0; font-weight: 600;">
        Round {{ $round->round_number }}
    </p>
    @if($match)
        @if($opponent)
        <p style="color: #e2e8f0; font-size: 16px; margin: 0;">
            Vous affronterez <strong style="color: white;">{{ $opponent->name }}</strong>
        </p>
        @else
        <p style="color: #e2e8f0; font-size: 16px; margin: 0;">
            <strong style="color: white;">Bye - Vous passez automatiquement au prochain round</strong>
        </p>
        @endif
    @endif
</div>

@if($match && $opponent)
<div class="info-box">
    <h3 style="margin-bottom: 16px; color: #0f172a; font-size: 18px;">Détails du match</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px;"><strong>Tournoi</strong></td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $tournament->name }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px;"><strong>Round</strong></td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">Round {{ $round->round_number }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px;"><strong>Adversaire</strong></td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">
                {{ $opponent->name }}
            </td>
        </tr>
        @if($opponent && $opponent->profile && $opponent->profile->whatsapp_number)
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px;"><strong>Numéro WhatsApp</strong></td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px;">{{ $opponent->profile->whatsapp_number }}</td>
        </tr>
        @endif
        @if($match->deadline_at)
        <tr>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-size: 14px;"><strong>⏰ Deadline</strong></td>
            <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 14px; color: #dc2626; font-weight: 600;">
                {{ \Carbon\Carbon::parse($match->deadline_at)->format('d/m/Y à H:i') }}
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0; font-size: 14px;"><strong>Statut</strong></td>
            <td style="padding: 10px 0; text-align: right; color: #f59e0b; font-weight: 600; font-size: 14px;">
                À jouer
            </td>
        </tr>
    </table>

    @if($opponent && $opponent->profile && $opponent->profile->whatsapp_number)
    <div style="text-align: center; margin: 24px 0;">
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $opponent->profile->whatsapp_number) }}"
           style="display: inline-block; background: #0f172a; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;"
           target="_blank">
            📱 Contacter sur WhatsApp
        </a>
    </div>
    @endif
</div>
@endif

<div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 16px; margin: 24px 0; border-radius: 6px;">
    <h3 style="margin-bottom: 12px; color: #0f172a; font-size: 16px;">Préparez-vous pour le match</h3>
    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 14px; color: #334155;">
        <li>Assurez-vous d'être disponible pour jouer votre match avant la deadline</li>
        <li>Contactez votre adversaire pour organiser le match</li>
        <li>Préparez votre meilleure stratégie</li>
        <li>N'oubliez pas de soumettre les scores et preuves après le match</li>
    </ul>
</div>

<p style="margin: 28px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $tournament->uuid }}"
       style="display: inline-block; padding: 12px 28px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;"
       target="_blank">
        Voir le tournoi
    </a>
</p>

<p style="margin: 24px 0; font-size: 15px; color: #334155; text-align: center;">
    Bonne chance pour ce nouveau round !
</p>
@endsection
