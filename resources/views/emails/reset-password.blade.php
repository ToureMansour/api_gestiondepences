<x-mail::message>
# Reinitialisation de mot de passe

Vous avez demande une reinitialisation de votre mot de passe.

Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :

<x-mail::button :url="config('app.frontend_url', config('app.url')).'/reset-password?token='.$resetToken.'&email='.$email">
Reinitialiser mon mot de passe
</x-mail::button>

Ce lien expire dans 60 minutes.

Si vous n'etes pas a l'origine de cette demande, ignorez cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
