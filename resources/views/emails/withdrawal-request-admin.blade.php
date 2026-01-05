@extends('emails.layout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="font-size: 48px; margin-bottom: 10px;">🔔</div>
    <h1 style="color: #0f172a; margin: 0; font-size: 24px; font-weight: 700;">Nouvelle demande de retrait</h1>
</div>

<p style="font-size: 16px; margin-bottom: 30px;">
    Une nouvelle demande de retrait a été soumise et nécessite votre attention.
</p>

<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <h3 style="margin: 0 0 15px 0; color: #0f172a; font-size: 16px;">Informations utilisateur</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Nom</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Email</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $user->email }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #f0fdf4; border-left: 4px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 6px;">
    <h3 style="margin: 0 0 15px 0; color: #0f172a; font-size: 16px;">Détails de la transaction</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Transaction</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">#{{ $transaction->uuid }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Pièces</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #ef4444;">- {{ number_format($transaction->amount_coins, 2, ',', ' ') }} pièces</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Montant</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #22c55e; font-size: 18px;">{{ number_format($transaction->net_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Numéro</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $transaction->payment_phone }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Méthode</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 0; color: #6b7280; font-size: 14px;">Date</td>
            <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $transaction->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; margin: 20px 0; border-radius: 6px;">
    <p style="margin: 0; font-size: 14px; color: #92400e;">
        <strong>⚠️ Action requise:</strong> Veuillez traiter cette demande dans les 48 heures.
    </p>
</div>

<p style="font-size: 14px; color: #6b7280; margin-top: 30px;">
    Connectez-vous au panneau d'administration pour approuver ou rejeter cette demande.
</p>
@endsection
