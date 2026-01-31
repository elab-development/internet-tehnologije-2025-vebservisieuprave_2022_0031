<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail//ovaj model ce imati email verified at
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ime',
        'prezime',
        'datum_rodjenja',
        'pol',
        'tip_korisnika',   // domaci, strani, administrator
        'jmbg',            // za domace
        'broj_pasosa',     // za strane
        'drzavljanstvo',   // za strane
        'broj_zaposlenog', //za admina
        'datum_kreiranja_naloga',
        'email',
        'password',
        'profile_photo_path'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
    'datum_rodjenja' => 'date',
    'datum_kreiranja_naloga' => 'datetime', // dodato
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    

    /**
 * Svi zahtevi koje je korisnik poslao
 */
public function zahtevi()
{
    return $this->hasMany(Zahtev::class);
}

/**
 * Svi termini koje je korisnik zakazao
 */
public function termini()
{
    return $this->hasMany(Termin::class);
}
// Helper metode
    public function isAdmin() { return $this->tip_korisnika === 'admin'; }
    public function isDomaci() { return $this->tip_korisnika === 'domaci'; }
    public function isStrani() { return $this->tip_korisnika === 'strani'; }

}
