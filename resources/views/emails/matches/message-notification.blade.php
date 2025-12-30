@extends('emails.layout')

@section('content')
<h2 style="color: #333; margin-bottom: 20px;">Nouveau message dans votre match</h2>

<p style="margin-bottom: 15px;">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

<p style="margin-bottom: 20px;">
    <strong>{{ $sender->name }}</strong> vous a envoyé un message concernant votre match.
</p>

<div class="info-box">
    <h3 style="margin-bottom: 10px; color: #667eea;">Tournoi</h3>
    <p style="margin: 5px 0;"><strong>{{ $tournament->name }}</strong></p>
    <p style="margin: 5px 0; color: #6c757d;">{{ ucfirst(str_replace('_', ' ', $tournament->game_type)) }}</p>
</div>

<div class="info-box" style="background-color: #f8f9fa;">
    <h3 style="margin-bottom: 10px; color: #333;">Message</h3>
    <p style="margin: 5px 0; padding: 10px; background-color: white; border-radius: 4px; font-style: italic;">
        "{{ $matchMessage->message }}"
    </p>
    <p style="margin-top: 10px; color: #6c757d; font-size: 13px;">
        Envoyé le {{ $matchMessage->created_at->format('d/m/Y à H:i') }}
    </p>
</div>

<p style="margin-bottom: 20px;">
    Connectez-vous à la plateforme pour répondre et organiser votre match.
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ config('app.frontend_url') }}/matches/{{ $match->id }}" class="btn">
        Voir le match
    </a>
</div>

<p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
    <strong>Astuce :</strong> Utilisez le chat pour coordonner l'heure de votre match et n'oubliez pas de soumettre des preuves après la partie.
</p>
@endsection
