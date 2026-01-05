@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">💰</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">Dépôt de pièces initié</h1>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">
    Bonjour <strong>{{ $user->name }}</strong>,
</p>

<p style="font-size: 16px; margin-bottom: 30px;">
    Votre demande de dépôt a été initiée avec succès. Pour finaliser votre paiement, veuillez suivre les instructions ci-dessous.
</p>

<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Montant à payer</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #3b82f6; font-size: 18px;">{{ number_format($transaction->amount_money, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Frais (7%)</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #6b7280;">{{ number_format($transaction->fee_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr style="border-top: 2px solid #3b82f6;">
            <td style="padding: 12px 0 0 0; color: #0f172a; font-size: 16px; font-weight: 700;">Pièces que vous recevrez</td>
            <td style="padding: 12px 0 0 0; text-align: right; font-weight: 700; color: #22c55e; font-size: 20px;">{{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
    </table>
</div>

@if($paymentUrl)
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $paymentUrl }}" class="btn" style="display: inline-block; padding: 14px 28px; background: #3b82f6; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px;">
        Finaliser le paiement
    </a>
</div>
@endif

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #92400e;">
        <strong>⏱️ Important:</strong> Cette transaction sera automatiquement annulée si le paiement n'est pas finalisé dans les 30 minutes.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Vous recevrez une confirmation par email dès que votre paiement sera validé et que vos pièces seront ajoutées à votre compte.
</p>
@endsection
