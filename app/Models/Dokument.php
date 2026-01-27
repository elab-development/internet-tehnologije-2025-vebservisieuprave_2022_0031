<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokument extends Model
{
     protected $fillable = [
        'nazivFajla',
        'putanja', // kako bismo nasli dokument
        'tipDokumeta', // izvod, presuda, licna karta, pasos
        'broj_dokumenta',
        'organ_izdavanja',
        'zahtev_id', // broj zahteva kom pripada ovaj dokument
    ];

    /**
 * Zahtev kojem dokument pripada
 */
public function zahtev()
{
    return $this->belongsTo(Zahtev::class, 'zahtev_id');
}

}
