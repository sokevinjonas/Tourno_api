@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">✅</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">Retrait traité avec succès</h1>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">
    Bonjour <strong>{{ $user->name }}</strong>,
</p>

<p style="font-size: 16px; margin-bottom: 30px;">
    Votre demande de retrait a été approuvée et traitée avec succès. Le paiement a été envoyé à votre numéro.
</p>

<div style="background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Pièces retirées</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #ef4444;">- {{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
        <tr style="border-top: 2px solid #22c55e;">
            <td style="padding: 12px 0 0 0; color: #0f172a; font-size: 16px; font-weight: 700;">Montant envoyé</td>
            <td style="padding: 12px 0 0 0; text-align: right; font-weight: 700; color: #22c55e; font-size: 20px;">{{ number_format($transaction->net_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Numéro de paiement</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $transaction->payment_phone }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #f8f9fa; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #6b7280;">
        <strong style="color: #0f172a;">Traité le:</strong> {{ $transaction->processed_at->format('d/m/Y à H:i') }}
    </p>
</div>

<div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #1e40af;">
        <strong>💡 Information:</strong> Le paiement peut prendre quelques minutes pour apparaître sur votre compte selon votre opérateur.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Si vous ne recevez pas le paiement dans les 30 minutes, contactez-nous immédiatement.
</p>
@endsection
