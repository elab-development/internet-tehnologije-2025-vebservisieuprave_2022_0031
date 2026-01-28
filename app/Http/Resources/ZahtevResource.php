<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZahtevResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [
           ' id'             => $this->id,
            'tip_zahteva'    => $this->tip_zahteva,
            'status'          => $this->status,
            'datum_kreiranja' => $this->datum_kreiranja,
            'korisnik_id' => $this->korisnik_id,
            'korisnik' => $this->korisnik,
            'tip_promene' => $this->tip_promene,
            'ime_partnera' => $this->ime_partnera,
            'prezime_partnera' => $this->prezime_partnera,
            'datum_rodjenja_partnera' => $this->datum_rodjenja_partnera,
            'partner_pol' => $this->partner_pol,

            'broj_licnog_dokumenta' => $this->broj_licnog_dokumenta,
            'broj_licnog_dokumenta_partnera' => $this->broj_licnog_dokumenta_partnera,
            'datum_promene' => $this->datum_promene,

        ];
    }
}
