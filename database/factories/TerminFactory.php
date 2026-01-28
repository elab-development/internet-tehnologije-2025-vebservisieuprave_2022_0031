<?php

namespace Database\Factories;

use App\Models\Termin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Termin>
 */
class TerminFactory extends Factory
{
    protected $model = Termin::class;

    public function definition(): array
    {
        return [
            'tip_dokumenta' => $this->faker->randomElement([
                'licna_karta',
                'pasos',
            ]),

            'lokacija' => $this->faker->randomElement([
                'MUP Beograd',
                'Opština Novi Sad',
                'Opština Niš',
                'Policijska stanica Zemun',
            ]),

            // termin u narednih 30 dana
            'datum_vreme' => $this->faker->dateTimeBetween('+1 day', '+30 days'),

            // automatski pravi korisnika ako ne proslediš postojeći
            'korisnik_id' => User::factory(),
        ];
    }

    /* BONUS STATES */

    public function licnaKarta(): static
    {
        return $this->state(fn () => [
            'tip_dokumenta' => 'licna_karta',
        ]);
    }

    public function pasos(): static
    {
        return $this->state(fn () => [
            'tip_dokumenta' => 'pasos',
        ]);
    }
}


