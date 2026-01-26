<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokument extends Model
{
     protected $fillable = [
        'nazivFajla',
        'putanja', // kako bismo nasli dokument
        'tipDokumeta',
        'tipZahteva', //promena prebivalista ili bracnog statusa
        'zahtev_id',
    ];

}
