@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-bottom: 24px; font-size: 26px;">Félicitations ! Vous avez gagné une récompense</h2>

<p style="margin-bottom: 16px; font-size: 15px; line-height: 1.6;">Bonjour <strong>{{ $winner->name }}</strong>,</p>

<p style="margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
    Nous avons le plaisir de vous informer que vous avez terminé le tournoi <strong>{{ $tournament->name }}</strong>
    à la <strong>{{ $rank }}{{ $rank === 1 ? 'ère' : 'ème' }}</strong> place !
</p>

<div style="background: #0f172a; padding: 32px; border-radius: 8px; text-align: center; margin: 32px 0;">
    <p style="color: #cbd5e1; font-size: 15px; margin: 0 0 12px 0;">
        Votre récompense
    </p>
    <p style="color: white; font-size: 36px; margin: 0; font-weight: 600;">
        {{ number_format($prizeAmount, 2) }} MLM
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 16px; color: #0f172a; font-size: 18px;">Détails de votre performance</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                <strong>Tournoi :</strong>
            </td>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                {{ $tournament->name }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                <strong>Classement final :</strong>
            </td>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">
                {{ $rank }}{{ $rank === 1 ? 'ère' : 'ème' }} place
            </td>
        </tr>
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">
                <strong>Récompense :</strong>
            </td>
            <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right; color: #4caf50; font-weight: bold;">
                {{ number_format($prizeAmount, 2) }} MLM
            </td>
        </tr>
    </table>
</div>

<div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #4caf50;">
    <p style="margin: 0; color: #2e7d32;">
        <strong>✓ Votre récompense a été créditée sur votre wallet</strong><br>
        <span style="font-size: 14px;">Les fonds sont immédiatement disponibles pour vos prochains tournois ou pour un retrait.</span>
    </p>
</div>

<p style="margin: 32px 0 24px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/wallet"
       style="display: inline-block; padding: 12px 28px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; margin-right: 12px;">
        Voir mon wallet
    </a>
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments"
       style="display: inline-block; padding: 12px 28px; background-color: #0f172a; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Prochain tournoi
    </a>
</p>

<p style="margin: 24px 0; text-align: center; color: #334155; font-size: 15px;">
    Merci d'avoir participé et félicitations pour votre performance !
</p>

<div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 16px; margin: 24px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #334155;">
        <strong>Besoin d'aide ?</strong> Consultez notre <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/faq" style="color: #0f172a; font-weight: 600;">FAQ</a> ou contactez le support.
    </p>
</div>
@endsection
