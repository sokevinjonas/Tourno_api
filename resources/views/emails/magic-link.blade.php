@extends('emails.layout')

@section('content')
    <h1 style="color: #333333; margin-bottom: 20px; font-size: 24px;">Connexion à votre compte GPA</h1>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Bonjour,
    </p>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Vous avez demandé à vous connecter à votre compte GPA. Utilisez le code de vérification ci-dessous pour accéder à votre compte :
    </p>

    <div style="text-align: center; margin: 40px 0; background-color: #0f172a; padding: 30px; border-radius: 8px;">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #e2e8f0; text-transform: uppercase; letter-spacing: 1px;">
            Code de vérification
        </p>
        <p style="margin: 0; font-size: 42px; font-weight: bold; color: white; letter-spacing: 8px; font-family: monospace;">
            {{ $code }}
        </p>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px; color: #555555;">
            <strong>Important :</strong> Ce code est valide pendant <strong>{{ $expiresInMinutes }} minutes</strong> et ne peut être utilisé qu'une seule fois.
        </p>
    </div>

    <p class="warning-text" style="margin-top: 30px;">
        <strong>Vous n'avez pas demandé ce code ?</strong><br>
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité. Votre compte reste protégé.
    </p>
@endsection
