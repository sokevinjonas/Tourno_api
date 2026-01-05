@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">⏳</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">Demande de retrait enregistrée</h1>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">
    Bonjour <strong>{{ $user->name }}</strong>,
</p>

<p style="font-size: 16px; margin-bottom: 30px;">
    Nous avons bien reçu votre demande de retrait. Elle est actuellement en attente de traitement par notre équipe.
</p>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Pièces à retirer</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #ef4444;">- {{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Montant équivalent</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ number_format($transaction->amount_money, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr style="border-top: 2px solid #f59e0b;">
            <td style="padding: 12px 0 0 0; color: #0f172a; font-size: 16px; font-weight: 700;">Vous recevrez</td>
            <td style="padding: 12px 0 0 0; text-align: right; font-weight: 700; color: #22c55e; font-size: 20px;">{{ number_format($transaction->net_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Numéro de paiement</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $transaction->payment_phone }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #1e40af;">
        <strong>⏱️ Délai de traitement:</strong> Votre demande sera traitée dans un délai de 24 à 48 heures.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Vous recevrez un email de confirmation une fois que votre retrait aura été traité.
</p>
@endsection
