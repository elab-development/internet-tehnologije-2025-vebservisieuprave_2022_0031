@component('mail::message')
# Zdravo, {{ $user->ime }} {{ $user->prezime }}!

Dobili smo molbu za reset lozinke.

Klikni na dugme ispod da nastaviš proces resetovanja lozinke:

@component('mail::button', ['url'=> $resetUrl])
Resetuj lozinku
@endcomponent

Tvoj token za reset lozinke je:
**{{ $token }}**

Ako niste tražili reset lozinke, slobodno ignorišite ovaj mejl.

Hvala,<br>
{{ config('app.name') }}
@endcomponent
