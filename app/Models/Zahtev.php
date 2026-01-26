<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;


class Zahtev extends Model
{
        protected $fillable=[
            'tip_zahteva',
            'status',
            'datum_kreiranja',
            'korisnik_id', //id korisnika ciji je zahtev

        ];
        protected $casts = [
            'datum_kreiranja'=> 'datetime',
        ];

        
}
