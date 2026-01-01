@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Profil validé</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $user->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    Nous avons le plaisir de vous informer que votre profil a été validé par notre équipe de modération.
</p>

<div style="background: #4caf50; padding: 20px; border-radius: 8px; text-align: center; margin: 30px 0;">
    <p style="color: white; font-size: 18px; margin: 0; font-weight: bold;">
        ✓ Votre profil est maintenant actif !
    </p>
</div>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Ce que vous pouvez faire maintenant</h3>
    <ul style="margin: 0; padding-left: 20px;">
        <li style="margin-bottom: 8px;">Vous inscrire à des tournois</li>
        <li style="margin-bottom: 8px;">Participer aux compétitions</li>
        <li style="margin-bottom: 8px;">Suivre vos organisateurs préférés</li>
        <li style="margin-bottom: 8px;">Consulter votre wallet et votre historique</li>
    </ul>
</div>

<p style="margin: 30px 0 20px 0;">
    <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/tournaments"
       style="display: inline-block; padding: 12px 30px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Découvrir les tournois
    </a>
</p>

<p style="margin-bottom: 20px;">
    Merci de faire partie de la communauté G4M Pro Africa !
</p>

<div style="margin-top: 30px; padding: 15px; background-color: #f5f5f5; border-radius: 5px;">
    <p style="margin: 0; font-size: 14px; color: #555;">
        <strong>Besoin d'aide ?</strong> Consultez notre <a href="{{ env('FRONTEND_URL', 'http://localhost:4200') }}/faq">FAQ</a> ou contactez le support.
    </p>
</div>
@endsection
