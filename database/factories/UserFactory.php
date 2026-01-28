<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{//da proizvede odredjene objekte, UserFactory proizvodi korisnike
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tip = $this->faker->randomElement(['domaci', 'strani', 'admin']);
        return [
            'ime' => $this->fake()->firstName(),
            'prezime' => $this->fake()->lastName(),
            'datum_rodjenja' => $this->faker->date('Y-m-d', '-18 years'),//korisnik ce imati najmanje 18 godina
            'pol' => $this->faker->randomElement(['M', 'Ž']),
            'tip_korisnika' => $tip,
            // uslovna polja
            'jmbg' => $tip === 'domaci'
                ? $this->faker->numerify('#############')
                : null,
            'broj_pasoša' => $tip === 'strani'
                ? strtoupper($this->faker->bothify('??######'))
                : null,

            'drzavljanstvo' => $tip === 'strani'
                ? $this->faker->country()
                : 'Srbija',

            'broj_zaposlenog' => $tip === 'admin'
                ? 'ADM-' . $this->faker->unique()->numberBetween(1000, 9999)
                : null,
            'datum_kreiranja_naloga' => now(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password', // hashed automatski
        ];
    }

    
}
