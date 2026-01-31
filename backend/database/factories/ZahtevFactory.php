<?php

namespace Database\Factories;

use App\Models\Adresa;
use App\Models\User;
use App\Models\Zahtev;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zahtev>
 */
class ZahtevFactory extends Factory
//sluzi da automatski pravi zahteve u bazi, kao da ih pravi e uprava, inace bi sve rucno
{//automatski kreira 2 adrese (stara + nova) kad je tip_zahteva = prebivaliste.
    protected $model = Zahtev::class;//ovaj factory proizvodi zahtev modele

    public function definition(): array//vraca 1 red u zahtevi tabeli
    {
        $tipZahteva = $this->faker->randomElement([Zahtev::PREBIVALISTE, Zahtev::BRACNI_STATUS]);

        $base = [//zajednicka, osnovna polja, nevezano za tip zahteva
            'tip_zahteva' => $tipZahteva,
            'status' => $this->faker->randomElement(['kreiran', 'odobren', 'odbijen']),
            'datum_kreiranja' => now(),
            'korisnik_id' => User::factory(),//ko je poslao zahtev
            'datum_promene' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
        ];

        if ($tipZahteva === Zahtev::BRACNI_STATUS) {
            $partnerPol = $this->faker->randomElement(['M', 'Ž']);

            return $base + [//samo tad nam potrebni podaci o partneru
                'tip_promene' => $this->faker->randomElement(['sklapanje_braka', 'razvod']),
                'ime_partnera' => $partnerPol === 'M' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale(),
                'prezime_partnera' => $this->faker->lastName(),
                'datum_rodjenja_partnera' => $this->faker->date('Y-m-d', '-18 years'),
                'partner_pol' => $partnerPol,
                'broj_licnog_dokumenta' => strtoupper($this->faker->bothify('ID#######')),//bothify je faker metoda koja # zamenjuje ciframa od 0 do 9
                'broj_licnog_dokumenta_partnera' => strtoupper($this->faker->bothify('ID#######')),
            ];
        }

        // PREBIVALISTE: partner podaci null
        return $base + [
            'tip_promene' => null,
            'ime_partnera' => null,
            'prezime_partnera' => null,
            'datum_rodjenja_partnera' => null,
            'partner_pol' => null,
            'broj_licnog_dokumenta' => strtoupper($this->faker->bothify('ID#######')),
            'broj_licnog_dokumenta_partnera' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Zahtev $zahtev) {//kad se zahtev snimi u bazi -> uradi jos nesto
            // samo za prebivaliste pravimo stare/nove adrese
            if ($zahtev->tip_zahteva !== Zahtev::PREBIVALISTE) {
                return;
            }
            //popunjavanje stare i nove adrese
            Adresa::factory()->stara()->create([
                'zahtev_id' => $zahtev->id,
            ]);

            Adresa::factory()->nova()->create([
                'zahtev_id' => $zahtev->id,
            ]);
        });
    }

    // states -> definition je random slucaj a state namerni
    public function prebivaliste(): static //Zahtev::factory()->prebivaliste()->create(); -> hocu zahtev za prebivaliste
    {
        return $this->state(fn () => [//vraca factory ali sa izmenjenim poljima, sve sto ovde navedemo pregazice ono iz definition
            'tip_zahteva' => Zahtev::PREBIVALISTE,
            'tip_promene' => null,
            'ime_partnera' => null,
            'prezime_partnera' => null,
            'datum_rodjenja_partnera' => null,
            'partner_pol' => null,
            'broj_licnog_dokumenta_partnera' => null,
        ]);
    }

    public function bracniStatus(): static//Zahtev::factory()->bracniStatus()->create();
    {
        return $this->state(fn () => ['tip_zahteva' => Zahtev::BRACNI_STATUS]);//samo se forsira tip zahteva, ostala polja ce doci iz definition()
    }
}

