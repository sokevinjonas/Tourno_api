@extends('emails.layout')

@section('title', 'Bienvenue Organisateur')

@section('content')
<p style="margin-bottom: 20px;">
    Bonjour <strong>{{ $organizer->name }}</strong>,
</p>

<div style="background: #667eea; padding: 30px; border-radius: 10px; text-align: center; margin: 30px 0;">
    <h2 style="color: white; font-size: 32px; margin: 0 0 10px 0;">
        Bienvenue sur G4M Pro Africa
    </h2>
    <p style="color: white; font-size: 20px; margin: 0;">
        Vous êtes maintenant <strong>Organisateur Certifié</strong>
    </p>
</div>

<p style="margin-bottom: 20px;">
    Félicitations pour être devenu organisateur certifié sur G4M Pro Africa ! Vous faites désormais partie d'une communauté d'organisateurs passionnés qui créent des expériences de jeu exceptionnelles.
</p>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Vos avantages en tant qu'Organisateur Certifié</h3>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li style="margin: 5px 0;">Badge "Certifié" affiché sur votre profil</li>
        <li style="margin: 5px 0;">Créez et gérez vos propres tournois</li>
        <li style="margin: 5px 0;">Collectez les frais d'inscription automatiquement</li>
        <li style="margin: 5px 0;">Accès aux statistiques de vos tournois</li>
        <li style="margin: 5px 0;">Construisez votre communauté de followers</li>
        <li style="margin: 5px 0;">Outils de gestion des matchs et scores</li>
        <li style="margin: 5px 0;">Notifications automatiques aux participants</li>
    </ul>
</div>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
    <h4 style="margin: 0 0 10px 0; color: #667eea;">Démarrez maintenant</h4>
    <ol style="margin: 10px 0; padding-left: 20px;">
        <li style="margin: 8px 0;"><strong>Complétez votre profil</strong> - Ajoutez votre bio et vos liens sociaux</li>
        <li style="margin: 8px 0;"><strong>Créez votre premier tournoi</strong> - Choisissez le jeu, le format et les prix</li>
        <li style="margin: 8px 0;"><strong>Invitez des joueurs</strong> - Partagez votre tournoi avec votre communauté</li>
        <li style="margin: 8px 0;"><strong>Gérez les matchs</strong> - Suivez les résultats en temps réel</li>
    </ol>
</div>

<div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
    <h4 style="margin: 0 0 10px 0; color: #856404;">Envie d'aller plus loin ?</h4>
    <p style="margin: 5px 0; color: #856404;">
        Vous pouvez demander un badge <strong>Vérifié</strong> ✓ ou <strong>Partenaire</strong> ★ pour encore plus d'avantages :
    </p>
    <ul style="margin: 10px 0; padding-left: 20px; color: #856404;">
        <li style="margin: 5px 0;">Visibilité maximale dans les recherches</li>
        <li style="margin: 5px 0;">Support prioritaire</li>
        <li style="margin: 5px 0;">Commission réduite sur les tournois</li>
        <li style="margin: 5px 0;">Outils marketing premium</li>
    </ul>
    <p style="margin: 10px 0 0 0; font-size: 14px; color: #856404;">
        Soumettez votre demande de vérification depuis votre tableau de bord
    </p>
</div>

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ config('app.frontend_url') }}/organizer/dashboard"
       style="display: inline-block; padding: 15px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
        Accéder à mon Tableau de Bord
    </a>
</p>

<p style="margin-bottom: 20px;">
    Nous sommes ravis de vous avoir parmi nous ! N'hésitez pas à explorer toutes les fonctionnalités et à créer des tournois mémorables.
</p>

<p style="margin-bottom: 20px;">
    Bonne chance et amusez-vous bien !
</p>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Besoin d'aide ?</strong><br>
    Notre équipe est là pour vous ! Consultez notre <a href="{{ config('app.frontend_url') }}/guide-organisateur" style="color: #667eea;">guide organisateur</a> ou contactez-nous à <a href="mailto:support@g4meproafrica.com" style="color: #667eea;">support@g4meproafrica.com</a>
</p>
@endsection
