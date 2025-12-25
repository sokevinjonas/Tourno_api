@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Félicitations ! Vous avez gagné une récompense</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $winner->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Nous avons le plaisir de vous informer que vous avez terminé le tournoi <strong>{{ $tournament->name }}</strong>
    à la <strong>{{ $rank }}{{ $rank === 1 ? 'ère' : 'ème' }}</strong> place !
</p>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 16px; margin: 0 0 10px 0;">
        Votre récompense
    </p>
    <p style="color: white; font-size: 36px; margin: 0; font-weight: bold;">
        {{ number_format($prizeAmount, 2) }} MLM
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 15px; color: #667eea;">Détails de votre performance</h3>
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

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/wallet"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
        Voir mon wallet
    </a>
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments"
       style="display: inline-block; padding: 12px 30px; background-color: #4caf50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Prochain tournoi
    </a>
</p>

<p style="margin-bottom: 20px; text-align: center; color: #666;">
    Merci d'avoir participé et félicitations pour votre performance !
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        <strong>Besoin d'aide ?</strong> Consultez notre <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/faq">FAQ</a> ou contactez le support.
    </p>
</div>
@endsection
