@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-bottom: 24px; font-size: 26px;">Le tournoi a commencé !</h2>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
    Le tournoi <strong>{{ $tournament->name }}</strong> vient de démarrer. Préparez-vous pour votre premier match !
</p>

@if($firstMatch && $opponent)
<div class="info-box">
    <h3 style="margin-bottom: 16px; color: #0f172a; font-size: 18px;">Votre premier match</h3>
    <p style="margin: 12px 0; font-size: 16px;">
        <strong>Adversaire :</strong> {{ $opponent->name }}
    </p>
    @if($firstMatch->deadline_at)
    <p style="margin: 8px 0; font-size: 14px; color: #dc2626; font-weight: 600;">
        <strong>⏰ Deadline :</strong> {{ \Carbon\Carbon::parse($firstMatch->deadline_at)->format('d/m/Y à H:i') }}
    </p>
    @endif
    @if($opponent->profile && $opponent->profile->whatsapp_number)
    <p style="margin: 8px 0;"><strong>Numéro WhatsApp :</strong> {{ $opponent->profile->whatsapp_number }}</p>

    <div style="text-align: center; margin: 24px 0;">
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $opponent->profile->whatsapp_number) }}"
           style="display: inline-block; background: #0f172a; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;"
           target="_blank">
            📱 Contacter sur WhatsApp
        </a>
    </div>
    @endif
    <p style="margin: 12px 0; color: #64748b; font-size: 14px; line-height: 1.5;">
        Contactez votre adversaire pour organiser votre match et soumettre les résultats avant la deadline.
    </p>
</div>

@elseif($firstMatch && !$opponent)
<div class="info-box" style="background-color: #d4edda; border-left: 4px solid #28a745;">
    <h3 style="margin-bottom: 10px; color: #28a745;">Bye - Passage automatique au tour suivant</h3>
    <p style="margin: 5px 0;">
        Vous n'avez pas d'adversaire pour ce premier tour et passez automatiquement au tour suivant.
    </p>
</div>
@endif

<div class="info-box">
    <h3 style="margin-bottom: 12px; color: #0f172a; font-size: 16px;">Informations du tournoi</h3>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Nom :</strong> {{ $tournament->name }}</p>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Jeu :</strong> {{ ucfirst(str_replace('_', ' ', $tournament->game_type)) }}</p>
    <p style="margin: 6px 0; font-size: 14px;"><strong>Format :</strong> {{ ucfirst($tournament->format) }}</p>
    @if($tournament->prize_pool > 0)
    <p style="margin: 6px 0; font-size: 14px;"><strong>Prize Pool :</strong> {{ number_format($tournament->prize_pool, 2) }} MLM</p>
    @endif
</div>

<div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 16px; margin: 24px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #334155;">
        <strong>Important :</strong> N'oubliez pas de fournir des preuves (captures d'écran) après votre match pour valider les résultats. Vous pouvez communiquer avec votre adversaire via le chat intégré sur la plateforme.
    </p>
</div>

<p style="margin: 24px 0; font-size: 15px; color: #334155;">
    Bonne chance et que le meilleur gagne !
</p>
@endsection
