<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DokumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        return [
            'id' => $this->id,
            'nazivFajla' => $this->nazivFajla,
            'putanja' => $this->putanja,
            'tipDokumeta' => $this->tipDokumeta,
            'broj_dokumenta' => $this->broj_dokumenta,
            'organ_izdavanja' => $this->organ_izdavanja,
            'zahtev_id' => $this->zahtev_id,
            
        ];
    }
}
