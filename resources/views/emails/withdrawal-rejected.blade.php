@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">Demande de retrait refusée</h1>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">
    Bonjour <strong>{{ $user->name }}</strong>,
</p>

<p style="font-size: 16px; margin-bottom: 30px;">
    Nous sommes désolés de vous informer que votre demande de retrait a été refusée.
</p>

<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Pièces demandées</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Montant demandé</td>
            <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ number_format($transaction->net_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>
</div>

@if($transaction->rejection_reason)
<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <h3 style="margin: 0 0 10px 0; color: #0f172a; font-size: 16px;">Raison du refus</h3>
    <p style="margin: 0; font-size: 14px; color: #78350f; line-height: 1.6;">
        {{ $transaction->rejection_reason }}
    </p>
</div>
@endif

<div style="background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #166534;">
        <strong>✅ Vos pièces sont toujours disponibles:</strong> Les {{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces sont restées dans votre compte.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Pour plus d'informations ou si vous pensez qu'il s'agit d'une erreur, n'hésitez pas à nous contacter.
</p>
@endsection
