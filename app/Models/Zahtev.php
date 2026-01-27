<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;


class Zahtev extends Model
{
        protected $fillable=[
            'tip_zahteva',// promena bracnog statusa i promena prebivalista
            'status',
            'datum_kreiranja',
            'korisnik_id', //id korisnika ciji je zahtev
            

        
            'tip_promene', // sklapanje_braka ,razvod 
            'ime_partnera',
            'prezime_partnera',

        ];
        protected $casts = [
            'datum_kreiranja'=> 'datetime',
            'status'=> 'string',
            'tip_zahteva' => 'string'
        ];
    // dodajem zbog tipova zahteva
    const PREBIVALISTE = 'prebivaliste';
    const BRACNI_STATUS = 'bracni_status';

        /**
 * Korisnik koji je poslao zahtev
 */
public function korisnik()
{
    return $this->belongsTo(User::class, 'korisnik_id');
}
/**
 * Dokumenti povezani sa ovim zahtevom
 */
public function dokumenti()
{
    return $this->hasMany(Dokument::class);
}

/**
     * Stara adresa (samo za tip prebivaliste)
     */
    public function staraAdresa()
    {
        return $this->hasOne(Adresa::class, 'zahtev_id')
                    ->where('tip_adrese', 'stara')
                    ->where('tip_zahteva', self::PREBIVALISTE);
    }

    /**
     * Nova adresa (samo za tip prebivaliste)
     */
    public function novaAdresa()
    {
        return $this->hasOne(Adresa::class, 'zahtev_id')
                    ->where('tip_adrese', 'nova')
                    ->where('tip_zahteva', self::PREBIVALISTE);
    }
        


}
