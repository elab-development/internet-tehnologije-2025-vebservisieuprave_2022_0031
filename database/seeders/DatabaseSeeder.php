<?php

namespace Database\Seeders;

use App\Models\Adresa;
use App\Models\Dokument;
use App\Models\Termin;
use App\Models\User;
use App\Models\Zahtev;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder//direktno popunjavaju bazu
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Termin::factory(10)->create();
        Zahtev::factory(20)->create();
        Dokument::factory(10)->create();
        Adresa::factory(15)->create();
    }
}
