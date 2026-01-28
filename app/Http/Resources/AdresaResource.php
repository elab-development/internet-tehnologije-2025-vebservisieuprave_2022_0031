<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdresaResource extends JsonResource
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
            'ulica' => $this->ulica,
            'broj' => $this->broj,
            'mesto' => $this->mesto,
            'opstina' => $this->opstina,
            'grad' => $this->grad,
            'postanski_broj' => $this->postanski_broj,
            'trajanje_prebivalista' => $this->trajanje_prebivalista,
            'uloga_adrese' => $this->uloga_adrese,
            'zahtev_id' => $this->zahtev_id,
           
        ];
    }
}
