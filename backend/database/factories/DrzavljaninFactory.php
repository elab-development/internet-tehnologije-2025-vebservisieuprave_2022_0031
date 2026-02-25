<?php

namespace Database\Factories;

use App\Models\Drzavljanin;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrzavljaninFactory extends Factory
{
    protected $model = Drzavljanin::class;

    public function definition()
    {
        return [
            'ime' => $this->faker->firstName(),
            'prezime' => $this->faker->lastName(),
            // datum rodjenja: bilo koji datum pre 18 godina
            'datum_rodjenja' => $this->faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d'),
            'pol' => $this->faker->randomElement(['M', 'Z']),
            'jmbg' => $this->faker->unique()->numerify('#############'), // 13 cifara
        ];
    }
}