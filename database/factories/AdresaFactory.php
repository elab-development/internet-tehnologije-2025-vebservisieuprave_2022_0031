<?php

namespace Database\Factories;

use App\Models\Adresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adresa>
 */
class AdresaFactory extends Factory
{
    protected $model = Adresa::class;

    public function definition(): array
    {
        return [
            'ulica' => $this->faker->streetName(),
            'broj' => (string) $this->faker->numberBetween(1, 200),
            'mesto' => $this->faker->city(),
            'opstina' => $this->faker->city(),
            'grad' => $this->faker->city(),
            'postanski_broj' => $this->faker->postcode(),
            'trajanje_prebivalista' => $this->faker->randomElement(['trajna', 'privremena']),
            'uloga_adrese' => $this->faker->randomElement(['stara', 'nova']),
            'zahtev_id' => null, // popunićemo kroz ZahtevFactory
        ];
    }

    public function stara(): static
    {
        return $this->state(fn () => ['uloga_adrese' => 'stara']);
    }

    public function nova(): static
    {
        return $this->state(fn () => ['uloga_adrese' => 'nova']);
    }
}

