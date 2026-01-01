@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Profil rejeté</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Nous vous informons que votre profil n'a pas pu être validé par notre équipe de modération.
</p>

<div style="background: #ff5252; padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 18px; margin: 0; font-weight: bold;">
        Profil non validé
    </p>
</div>

<div class="info-box" style="background-color: #fff3e0; border-left: 4px solid #ff9800;">
    <h3 style="margin-bottom: 10px; color: #ff9800;">Raison du rejet</h3>
    <p style="margin: 0; color: #666;">{{ $rejectionReason }}</p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Que faire maintenant ?</h3>
    <ol style="margin: 0; padding-left: 20px;">
        <li style="margin-bottom: 8px;">Vérifiez les informations de votre profil</li>
        <li style="margin-bottom: 8px;">Corrigez les points mentionnés ci-dessus</li>
        <li style="margin-bottom: 8px;">Soumettez à nouveau votre profil pour validation</li>
    </ol>
</div>

<p style="margin: 30px 0 20px 0;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/profile/edit"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Modifier mon profil
    </a>
</p>

<p style="margin-bottom: 20px;">
    Notre équipe reste à votre disposition pour toute question.
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        <strong>Questions ?</strong> Contactez-nous à <a href="mailto:support@g4meproafrica">support@g4meproafrica</a>
    </p>
</div>
@endsection
