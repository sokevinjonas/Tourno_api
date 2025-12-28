@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-bottom: 24px; font-size: 26px;">Tournoi Terminé - {{ $tournament->name }}</h2>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $participant->name }}</strong>,</p>

<p style="margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
    Le tournoi <strong>{{ $tournament->name }}</strong> est maintenant terminé. Merci d'avoir participé !
</p>

@if($registration->final_rank <= 3)
    <div style="background: #fef3c7; padding: 28px; border-radius: 8px; text-align: center; margin: 28px 0; border: 3px solid #f59e0b;">
        <p style="color: #0f172a; font-size: 24px; margin: 0; font-weight: 600;">
            🏆 {{ $registration->final_rank }}{{ $registration->final_rank === 1 ? 'ère' : 'ème' }} place
        </p>
        @if($registration->prize_won > 0)
            <p style="color: #0f172a; font-size: 16px; margin: 12px 0 0 0; font-weight: 600;">
                Récompense : <strong>{{ number_format($registration->prize_won, 2) }} MLM</strong>
            </p>
        @endif
    </div>
@else
    <div style="background: #f1f5f9; padding: 24px; border-radius: 8px; text-align: center; margin: 28px 0;">
        <p style="color: #0f172a; font-size: 20px; margin: 0; font-weight: 600;">
            Votre classement : {{ $registration->final_rank }}{{ $registration->final_rank === 1 ? 'ère' : 'ème' }} place
        </p>
    </div>
@endif

<div class="info-box">
    <h3 style="margin-bottom: 16px; color: #0f172a; font-size: 18px;">Vos statistiques</h3>
    <table style="width: 100%; border-collapse: collapse;">
        @if($tournament->format === 'swiss')
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                    <strong>Points totaux :</strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                    {{ $registration->tournament_points }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                    <strong>Victoires :</strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                    {{ $registration->wins }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                    <strong>Nuls :</strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                    {{ $registration->draws }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                    <strong>Défaites :</strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                    {{ $registration->losses }}
                </td>
            </tr>
        @else
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                    <strong>Éliminé au :</strong>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                    {{ $registration->eliminated ? 'Round ' . $registration->eliminated_round : 'Vainqueur' }}
                </td>
            </tr>
        @endif
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                <strong>Classement final :</strong>
            </td>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: bold;">
                {{ $registration->final_rank }}{{ $registration->final_rank === 1 ? 'ère' : 'ème' }} / {{ $tournament->registrations()->count() }}
            </td>
        </tr>
    </table>
</div>

<div class="info-box" style="margin-top: 28px;">
    <h3 style="margin-bottom: 16px; color: #0f172a; font-size: 18px;">Podium</h3>
    @foreach($topPlayers as $index => $player)
        <div style="padding: 12px; margin-bottom: 8px; background: {{ $index === 0 ? '#fff8e1' : ($index === 1 ? '#f5f5f5' : '#fbe9e7') }}; border-radius: 8px; display: flex; align-items: center;">
            <span style="font-size: 24px; margin-right: 12px;">
                {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') }}
            </span>
            <div style="flex: 1;">
                <strong>{{ $player->user->name }}</strong>
                @if($tournament->format === 'swiss')
                    <span style="color: #666; font-size: 14px;"> - {{ $player->tournament_points }} pts</span>
                @endif
            </div>
            @if($player->prize_won > 0)
                <span style="color: #4caf50; font-weight: bold;">
                    {{ number_format($player->prize_won, 2) }} MLM
                </span>
            @endif
        </div>
    @endforeach
</div>

<p style="margin: 32px 0 24px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $tournament->id }}"
       style="display: inline-block; padding: 12px 28px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; margin-right: 12px;">
        Voir le tournoi
    </a>
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments"
       style="display: inline-block; padding: 12px 28px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Prochains tournois
    </a>
</p>

<p style="margin: 24px 0; text-align: center; color: #334155; font-size: 15px;">
    Merci d'avoir participé ! À bientôt pour de nouvelles compétitions.
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 16px; margin: 24px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #334155;">
        <strong>Besoin d'aide ?</strong> Consultez notre <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/faq" style="color: #0f172a; font-weight: 600;">FAQ</a> ou contactez le support.
    </p>
</div>
@endsection
