<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;
    public User $user;//mejl treba da bude poslat useru
    public string $verificationUrl;//kroz taj mejl treba da mu bude poslat verifikacioni link
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $verificationUrl)//konstruktor koji setuje ta polja
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    public function build()//kreira mejl
    {
        return $this
        ->subject('Verifikacija email adrese')//naslov mejla
        ->markdown('emails.verify-email');//sadrzaj mejla, to je ono sto napisem u fajlu u views
    }
}
