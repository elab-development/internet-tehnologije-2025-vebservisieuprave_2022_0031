<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    protected $table='termini';
     protected $fillable = [
        'tip_dokumenta', // licnakarta  ili  pasos
        'lokacija',
        'datum_vreme',
        'korisnik_id',  // FK na korisnika
    ];
    protected $casts = [
        'datum_vreme' => 'datetime', 
        'tip_dokumenta' => 'string',
        'lokacija'=>'string',
    ];

  /**
 * Korisnik kojem termin pripada
 */
public function korisnik()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
