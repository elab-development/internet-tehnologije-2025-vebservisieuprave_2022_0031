@component('mail::message')
# Zdravo, {{ $user->ime }}!

Hvala sto ste se registrovali na nasu aplikaciju.

Molimo vas da verifikujete svoju email adresu klikom na dugme ispod:

@component('mail::button', ['url'=> $verificationUrl])
Verifikuj email
@endcomponent

Ako niste kreirali nalog, slobodno ignorisite ovu poruku.

Hvala,<br>
{{ config('app.name') }}
@endcomponent
