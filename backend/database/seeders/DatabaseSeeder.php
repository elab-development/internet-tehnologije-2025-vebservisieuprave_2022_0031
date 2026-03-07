<?php

namespace Database\Seeders;

use App\Models\Adresa;
use App\Models\Dokument;
use App\Models\Termin;
use App\Models\User;
use App\Models\Zahtev;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder//direktno popunjavaju bazu
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');//db omogucava direktno pisanje upita nad bazom, napredan koncept, da se ne vrsi provera spoljnih kljuceva
        Adresa::truncate();//brisemo prethodne svaki put kad ponovo seedujemo
        Dokument::truncate();
        Zahtev::truncate();
        Termin::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        User::factory(10)->create();
        Termin::factory(10)->create();
        Zahtev::factory(20)->create();
        Dokument::factory(10)->create();
        Adresa::factory(15)->create();
    }
}
