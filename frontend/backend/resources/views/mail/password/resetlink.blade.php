<x-mail::message>
# Réinitialisation de votre mot de passe

Bonjour,

Vous recevez cet e-mail parce que nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.

<x-mail::panel>
    Réinitialiser le mot de passe en cliquant sur le bouton ci-dessous. Si vous ne pouvez pas cliquer sur le bouton, copiez et collez l'URL ci-dessous dans votre navigateur Web.

<a href="{{ $data['url'] }}">Lien</a>
</x-mail::panel>

<x-mail::button :url="$data['url']">
    Réinitialiser le mot de passe
</x-mail::button>

Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune autre action n'est requise.

Merci,<br> 
L'équipe
{{ config('app.name') }}
</x-mail::message>