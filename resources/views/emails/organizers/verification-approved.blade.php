@extends('emails.layout')

@section('title', 'Vérification Approuvée')

@section('content')
<p style="margin-bottom: 20px;">
    Bonjour <strong>{{ $organizer->name }}</strong>,
</p>

<div style="background: #667eea; padding: 30px; border-radius: 10px; text-align: center; margin: 30px 0;">
    <h2 style="color: white; font-size: 28px; margin: 0 0 10px 0;">
        Félicitations
    </h2>
    <p style="color: white; font-size: 18px; margin: 0;">
        Votre demande de vérification a été <strong>approuvée</strong>
    </p>
</div>

<p style="margin-bottom: 20px;">
    Nous avons le plaisir de vous informer que votre compte a été vérifié avec succès ! Vous êtes maintenant un <strong>{{ $badgeLabel }}</strong> {{ $badgeEmoji }} sur G4M Pro Africa.
</p>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Vos nouveaux avantages</h3>

    @if($badge === 'verified')
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li style="margin: 5px 0;">Badge "Vérifié" affiché sur votre profil</li>
        <li style="margin: 5px 0;">Visibilité accrue dans la liste des organisateurs</li>
        <li style="margin: 5px 0;">Confiance renforcée auprès des joueurs</li>
        <li style="margin: 5px 0;">Priorité dans les résultats de recherche</li>
        <li style="margin: 5px 0;">Accès aux outils d'analyse avancés</li>
    </ul>
    @elseif($badge === 'partner')
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li style="margin: 5px 0;">Badge "Partenaire Officiel" affiché sur votre profil</li>
        <li style="margin: 5px 0;">Mise en avant dans la section "Organisateurs Recommandés"</li>
        <li style="margin: 5px 0;">Support prioritaire de l'équipe G4M Pro Africa</li>
        <li style="margin: 5px 0;">Accès aux événements exclusifs partenaires</li>
        <li style="margin: 5px 0;">Commission réduite sur les tournois</li>
        <li style="margin: 5px 0;">Outils marketing et statistiques premium</li>
    </ul>
    @endif
</div>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;">
    <h4 style="margin: 0 0 10px 0; color: #667eea;">Prochaines étapes</h4>
    <ol style="margin: 10px 0; padding-left: 20px;">
        <li style="margin: 8px 0;">Complétez votre profil d'organisateur (bio, liens sociaux)</li>
        <li style="margin: 8px 0;">Créez votre premier tournoi vérifié</li>
        <li style="margin: 8px 0;">Partagez votre profil avec votre communauté</li>
        <li style="margin: 8px 0;">Engagez avec vos followers sur la plateforme</li>
    </ol>
</div>

<p style="margin: 30px 0 20px 0; text-align: center;">
    <a href="{{ config('app.frontend_url') }}/organizer/dashboard"
       style="display: inline-block; padding: 15px 40px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">
        Accéder à mon Tableau de Bord
    </a>
</p>

<p style="margin-bottom: 20px;">
    Merci de faire partie de la communauté G4M Pro Africa ! Nous sommes impatients de voir les tournois incroyables que vous allez créer.
</p>

<p style="margin-bottom: 20px;">
    Bonne chance et amusez-vous bien !
</p>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Besoin d'aide ?</strong><br>
    Si vous avez des questions ou besoin d'assistance, n'hésitez pas à contacter notre équipe support à <a href="mailto:support@g4meproafrica.com" style="color: #667eea;">support@g4meproafrica.com</a>
</p>
@endsection
