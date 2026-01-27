<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adresa extends Model
{
    protected $fillable = [
        'ulica',
        'broj',
        'mesto', 
        'opstina',
        'grad', 
        'postanski_broj',
        'trajanje_prebivalista', // trajna ili privremena
        'uloga_adrese', // stara ili nova
        'zahtev_id'
    ];
     protected $casts = [
        'trajanje_prebivalista' => 'string',
    ];

    /**
 * Zahtev kojem adresa pripada
 */
public function zahtev()
{
    return $this->belongsTo(Zahtev::class, 'zahtev_id');
}
}
