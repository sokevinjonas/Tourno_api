@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">⏰</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">N'oubliez pas de finaliser votre dépôt</h1>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">
    Bonjour <strong>{{ $user->name }}</strong>,
</p>

<p style="font-size: 16px; margin-bottom: 30px;">
    Nous avons remarqué que vous avez initié un dépôt de pièces il y a quelques minutes, mais le paiement n'a pas encore été finalisé.
</p>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Montant</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #f59e0b; font-size: 18px;">{{ number_format($transaction->amount_money, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Pièces à recevoir</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #22c55e;">{{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
    </table>
</div>

@if($paymentUrl)
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $paymentUrl }}" class="btn" style="display: inline-block; padding: 14px 28px; background: #f59e0b; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Finaliser le paiement maintenant
    </a>
</div>
@endif

<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #991b1b;">
        <strong>⚠️ Attention:</strong> Cette transaction sera automatiquement annulée si le paiement n'est pas finalisé bientôt.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Si vous avez déjà effectué le paiement, veuillez ignorer ce message. Vos pièces seront créditées dès confirmation.
</p>

<p style="font-size: 14px; color: #6b7280; margin-top: 10px;">
    Si vous ne souhaitez plus effectuer ce dépôt, aucune action n'est requise de votre part.
</p>
@endsection
