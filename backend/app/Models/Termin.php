<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Termin extends Model
{
    use HasFactory;
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
    return $this->belongsTo(User::class, 'korisnik_id');
}
}
