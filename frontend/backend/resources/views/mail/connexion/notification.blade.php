@component('mail::message')
# Nouvelle Connexion à votre Compte

Nous avons détecté une nouvelle connexion à votre compte avec les détails suivants :

- **Adresse IP :** {{ $ipAddress }}
- **Appareil :** {{ $device }}
- **Système d'exploitation :** {{ $os }}
- **Navigateur :** {{ $browser }}

Si vous ne reconnaissez pas cette activité, nous vous recommandons de changer votre mot de passe immédiatement et de contacter notre support.

Merci,  
L'équipe SOURX
@endcomponent
