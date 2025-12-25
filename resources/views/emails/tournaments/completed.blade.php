@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Tournoi Terminé - {{ $tournament->name }}</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $participant->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Le tournoi <strong>{{ $tournament->name }}</strong> est maintenant terminé. Merci d'avoir participé !
</p>

@if($registration->final_rank <= 3)
    <div style="background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); padding: 25px; border-radius: 12px; text-align: center; margin: 25px 0; border: 3px solid #f9a825;">
        <p style="color: #333; font-size: 24px; margin: 0; font-weight: bold;">
            🏆 {{ $registration->final_rank }}{{ $registration->final_rank === 1 ? 'ère' : 'ème' }} place
        </p>
        @if($registration->prize_won > 0)
            <p style="color: #333; font-size: 16px; margin: 10px 0 0 0;">
                Récompense : <strong>{{ number_format($registration->prize_won, 2) }} MLM</strong>
            </p>
        @endif
    </div>
@else
    <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0;">
        <p style="color: #333; font-size: 20px; margin: 0; font-weight: bold;">
            Votre classement : {{ $registration->final_rank }}{{ $registration->final_rank === 1 ? 'ère' : 'ème' }} place
        </p>
    </div>
@endif

<div class="info-box">
    <h3 style="margin-bottom: 15px; color: #667eea;">Vos statistiques</h3>
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

<div class="info-box" style="margin-top: 25px;">
    <h3 style="margin-bottom: 15px; color: #667eea;">Podium</h3>
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

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments/{{ $tournament->id }}"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
        Voir le tournoi
    </a>
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments"
       style="display: inline-block; padding: 12px 30px; background-color: #4caf50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Prochains tournois
    </a>
</p>

<p style="margin-bottom: 20px; text-align: center; color: #666;">
    Merci d'avoir participé ! À bientôt pour de nouvelles compétitions.
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        <strong>Besoin d'aide ?</strong> Consultez notre <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/faq">FAQ</a> ou contactez le support.
    </p>
</div>
@endsection
