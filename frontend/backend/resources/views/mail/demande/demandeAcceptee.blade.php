<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande Acceptée</title>
</head>
<body>
    <h1>Bonjour {{ $user['fields']['prenom'] }} {{ $user['fields']['nom'] }},</h1>
    <p>Votre demande a été acceptée avec succès.</p>
    
    @if($message)
    <p><strong>Message :</strong> {{ $message }}</p>
    @endif

    @if(count($attachments) > 0)
    <p><strong>Pièces jointes :</strong></p>
    <ul>
        @foreach($attachments as $attachment)
        <li><a href="{{ $attachment }}">{{ $attachment }}</a></li>
        @endforeach
    </ul>
    @endif

    <p>Merci,</p>
    <p>L'équipe de gestion des demandes</p>
</body>
</html>
