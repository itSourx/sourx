<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande Refusée</title>
</head>
<body>
    <h1>Bonjour {{ $user['fields']['prenom'] }} {{ $user['fields']['nom'] }},</h1>
    <p>Votre demande a été refusée.</p>
    
    @if($message)
    <p><strong>Motif :</strong> {{ $message }}</p>
    @endif

    <p>Merci de votre compréhension.</p>
    <p>L'équipe de gestion des demandes</p>
</body>
</html>
