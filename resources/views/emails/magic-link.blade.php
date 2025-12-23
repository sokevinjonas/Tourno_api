@extends('emails.layout')

@section('content')
    <h1 style="color: #333333; margin-bottom: 20px; font-size: 24px;">Connexion à votre compte MLM</h1>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Bonjour,
    </p>

    <p style="margin-bottom: 20px; font-size: 16px; color: #555555;">
        Vous avez demandé à vous connecter à votre compte MLM. Cliquez sur le bouton ci-dessous pour accéder à votre compte :
    </p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $magicLink }}" class="btn">
            Se connecter à MLM
        </a>
    </div>

    <div class="info-box">
        <p style="margin: 0; font-size: 14px; color: #555555;">
            <strong>Important :</strong> Ce lien de connexion est valide pendant <strong>{{ $expiresInMinutes }} minutes</strong> et ne peut être utilisé qu'une seule fois.
        </p>
    </div>

    <p style="margin-top: 30px; font-size: 14px; color: #6c757d;">
        Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :
    </p>

    <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; color: #495057; margin-top: 10px;">
        {{ $magicLink }}
    </p>

    <p class="warning-text" style="margin-top: 30px;">
        <strong>Vous n'avez pas demandé ce lien ?</strong><br>
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.
    </p>
@endsection
