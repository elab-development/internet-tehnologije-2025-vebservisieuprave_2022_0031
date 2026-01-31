<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [ 'id' => $this->id,
            'tip_dokumenta' => $this->tip_dokumenta,
            'lokacija' => $this->lokacija,
            'datum_vreme' => $this->datum_vreme,
            'korisnik_id' => $this->korisnik_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            ];
    }  
}
