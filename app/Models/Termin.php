<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
     protected $fillable = [
        'tip_dokumenta', // licna_karta / pasos
        'lokacija',
        'datum_vreme',
        'korisnik_id',  // FK na korisnika
    ];
    protected $casts = [
        'datum_vreme' => 'datetime', 
        'tip_dokumenta' => 'string',
        'lokacija'=>'string',
    ];
}
