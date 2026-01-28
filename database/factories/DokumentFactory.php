<?php

namespace Database\Factories;

use App\Models\Dokument;
use App\Models\Zahtev;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dokument>
 */
class DokumentFactory extends Factory
{
    protected $model = Dokument::class;

    public function definition(): array
    {
        $tipDokumenta = $this->faker->randomElement([
            'izvod',
            'presuda',
            'licna_karta',
            'pasos'
        ]);

        return [
            'nazivFajla' => $tipDokumenta . '_' . $this->faker->uuid() . '.pdf',//'izvod_550e8400-e29b-41d4-a716-446655440000.pdf'

            'putanja' => 'dokumenti/' . $tipDokumenta . '/',//npr dokumenti/izvod, dokumenti/pasos

            'tipDokumeta' => $tipDokumenta,

            'broj_dokumenta' => strtoupper(
                $this->faker->bothify('DOC#######')
            ),

            'organ_izdavanja' => $this->faker->randomElement([
                'MUP Srbije',
                'Matična služba',
                'Osnovni sud',
                'Opštinska uprava'
            ]),

            'zahtev_id' => Zahtev::factory(),//laravel napravi zahtev, uzme njegov id, veze za dokument
        ];
    }
}

