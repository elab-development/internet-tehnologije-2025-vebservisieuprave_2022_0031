<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Zahtev;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ZahtevControllerTest extends TestCase
{
    use RefreshDatabase;//svaki test se izvrsava na svezoj bazi

    private function makeUser(array $override = []): User//pravi usera u bazi, override sluzi za menjanje default vrednosti kad nam zatreba admin
    {
        return User::factory()->create(array_merge([//kreira usera preko factorya, array_merge spaja default podatke sa override
            'pol' => 'M',//default podesavamo
            'tip_korisnika' => 'domaci',
            'datum_rodjenja' => '2000-01-01',
            'password' => Hash::make('123456'),//hashovana, korisno ako nekad testiramo login
        ], $override));
    }

    private function payloadBracniStatus(array $override = []): array//pravi request body za tipzahteva-bracni status
    {
        return array_merge([
            //obavezna polja
            'tip_zahteva' => 'bracni_status',
            'broj_licnog_dokumenta' => 'LK-12345',
            'datum_promene' => now()->subDays(2)->toDateString(),//mora biti najkasnije juce, zato se stavlja 2 dana ranije
            //podaci o partneru, obavezni za ovaj zahtev
            'tip_promene' => 'razvod',
            'ime_partnera' => 'Ana',
            'prezime_partnera' => 'Anic',
            'datum_rodjenja_partnera' => now()->subYears(20)->toDateString(),
            'partner_pol' => 'Z',
            'broj_licnog_dokumenta_partnera' => 'LK-P-999',
        ], $override);
    }

    private function payloadPrebivaliste(array $override = []): array//pravimo request body za prebivaliste
    {
        return array_merge([
            'tip_zahteva' => 'prebivaliste',
            'broj_licnog_dokumenta' => 'LK-7777',
            'datum_promene' => now()->subDays(2)->toDateString(),

            'stara_adresa' => [
                'ulica' => 'Stara ulica',
                'broj' => '1',
                'mesto' => 'Beograd',
                'opstina' => 'Vracar',
                'grad' => 'Beograd',
                'postanski_broj' => '11000',
            ],
            'nova_adresa' => [
                'ulica' => 'Nova ulica',
                'broj' => '2',
                'mesto' => 'Novi Sad',
                'opstina' => 'Novi Sad',
                'grad' => 'Novi Sad',
                'postanski_broj' => '21000',
            ],
        ], $override);
    }

    // PUBLIC

    public function test_index_returns_ok(): void//pozove GET /api/zahtev
    {
        $this->getJson('/api/zahtev')->assertOk();//ocekuje http 200
    }

    public function test_show_returns_404_if_not_found(): void//trazi nepostojeci zahtev
    {
        $this->getJson('/api/zahtev/999999')->assertStatus(404);//ocekuje 404
    }

    // STORE 

    public function test_store_requires_auth(): void//pokuša da kreira zahtev bez tokena -> mora 401 (nije ulogovan)
    {
        $this->postJson('/api/zahtev', $this->payloadBracniStatus())
            ->assertStatus(401);
    }

    public function test_store_bracni_status_creates_zahtev(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);//Kreira user-a i uloguje ga u testu

        $res = $this->postJson('/api/zahtev', $this->payloadBracniStatus());
        $res->assertStatus(201);//salje POST i očekuje 201 (created)

        $this->assertDatabaseHas('zahtevi', [//proverava da je stvarno upisan red u tabelu zahtevi sa tačnim poljima
            'korisnik_id' => $user->id,
            'tip_zahteva' => 'bracni_status',
            'status' => 'kreiran',
            'broj_licnog_dokumenta' => 'LK-12345',
        ]);
    }

    public function test_store_prebivaliste_creates_zahtev_and_adrese(): void
    {//ista logika samo se proveraju i adrese
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/zahtev', $this->payloadPrebivaliste());
        $res->assertStatus(201);

        $zahtevId = Zahtev::latest('id')->value('id');//uzima poslednji id iz baze
        $this->assertNotNull($zahtevId);

        $this->assertDatabaseHas('zahtevi', [
            'id' => $zahtevId,
            'korisnik_id' => $user->id,
            'tip_zahteva' => 'prebivaliste',
            'status' => 'kreiran',
            'broj_licnog_dokumenta' => 'LK-7777',
        ]);

        $this->assertDatabaseHas('adrese', [//provera da je napravljena stara adresa
            'zahtev_id' => $zahtevId,
            'uloga_adrese' => 'stara',
        ]);

        $this->assertDatabaseHas('adrese', [//provera za novu
            'zahtev_id' => $zahtevId,
            'uloga_adrese' => 'nova',
        ]);
    }

    public function test_store_fails_if_datum_promene_is_today(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $payload = $this->payloadBracniStatus([
            'datum_promene' => now()->toDateString(),
        ]);

        $this->postJson('/api/zahtev', $payload)
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    // MOJI ZAHTEVI 

    public function test_moji_zahtevi_returns_only_users_requests(): void//ruta /api/zahtev/moje stvarno filtrira po ulogovanom korisniku
    {
        $user1 = $this->makeUser(['email' => 'u1@test.com']);//kreiramo 2 razlicita korisnika u test bazi
        $user2 = $this->makeUser(['email' => 'u2@test.com']);//menjamo emailove da budu razliciti da ne pukne unique constraint

        Zahtev::create(array_merge($this->payloadBracniStatus(), [//daje osnovna polja bracnog statusa, array_merge dodaje navedena polja
            'korisnik_id' => $user1->id,//rucno ubacujemo u bazu 1 zahtev koji pripada user1
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));//sa create se odma upise taj red u tabelu zahtevi

        Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $user2->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));
        //u bazi sad postoje zahtevi za razlicite korisnike
        Sanctum::actingAs($user1);//uloguje user1 za ovaj test

        $res = $this->getJson('/api/zahtev/moje');//pozovemo API rutu /api/zahtev/moje kao json request, ona treba da vrati samo zahteve prijavljenog usera
        $res->assertOk();//provera da je status 200

        $items = $res->json('data') ?? [];//pokusava da iz json odgovora uzme data polje, ako data ne postoji neka bude prazno
        foreach ($items as $item) {//prolazi kroz sve vracene zahteve
            $this->assertEquals($user1->id, $item['korisnik_id'] ?? null);//korisnik_id mora biti user1 id u suprotnom test pada
        }
    }

    public function test_moji_bracni_status_returns_only_bracni_status(): void//dokazuje da ruta /api/zahtev/moje/bracni_status filtrira po tipu zahteva
    {//Testira rutu koja treba da vrati samo zahteve tipa bracni_status
        $user = $this->makeUser();//kreira i loguje jednog usera
        Sanctum::actingAs($user);

        Zahtev::create(array_merge($this->payloadBracniStatus(), [//pravi zahtev tipa bracni_status za tog korisnika
            'korisnik_id' => $user->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        Zahtev::create(array_merge($this->payloadPrebivaliste(), [//pravi drugi zahtev za istog korisnika ali tip prebivaliste, sada user ima oba zahteva u bazi
            'korisnik_id' => $user->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $res = $this->getJson('/api/zahtev/moje/bracni_status');//poziva rutu koja treba da vrati samo bracni status
        $res->assertOk();

        $items = $res->json('data') ?? [];
        foreach ($items as $item) {//prolazi kroz sve rezultate i proverava da je tip_zahteva uvek bracni_status
            $this->assertEquals('bracni_status', $item['tip_zahteva'] ?? null);
        }
    }

    public function test_moji_prebivaliste_returns_only_prebivaliste(): void//dokazuje da ruta vraca samo zahteve za prebivaliste
    {//isto kao prethodno samo za prebivaliste
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $user->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        Zahtev::create(array_merge($this->payloadPrebivaliste(), [
            'korisnik_id' => $user->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $res = $this->getJson('/api/zahtev/moje/prebivaliste');
        $res->assertOk();

        $items = $res->json('data') ?? [];
        foreach ($items as $item) {
            $this->assertEquals('prebivaliste', $item['tip_zahteva'] ?? null);
        }
    }

    // DELETE 

    public function test_destroy_requires_auth(): void//ruta je zaštićena i ne može bez tokena
    {//pozivanje delete bez autentifikacije, ocekujemo 401 (unauthorized), 1 nije bitno (poenta je da middleware presece pre nego sto dodje do kontrolera)
        $this->deleteJson('/api/zahtev/1')->assertStatus(401);
    }

    public function test_destroy_deletes_zahtev_when_exists(): void//delete radi i stvarno brise iz baze
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);//kreira i uloguje usera

        $zahtev = Zahtev::create(array_merge($this->payloadBracniStatus(), [//Kreira zahtev u bazi i čuva ga u promenljivoj $zahtev (znamo id)
            'korisnik_id' => $user->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $this->deleteJson('/api/zahtev/' . $zahtev->id)//salje delete za taj id
            ->assertOk()//ocekuje 200 OK
            ->assertJsonPath('message', 'Zahtev je obrisan.');//ocekuje da JSON ima ovu poruku

        $this->assertDatabaseMissing('zahtevi', [
            'id' => $zahtev->id,//provera da taj red vise ne postoji u tabeli
        ]);
    }

    /* ADMIN 

    public function test_admin_routes_forbidden_for_non_admin(): void//Admin MiddleWare radi i blokira ne-admin korisnike
    {
        $user = $this->makeUser(['tip_korisnika' => 'domaci']);//kreira usera koji nije admin, loguje ga
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/neobradjeniZahtevi')//poziva admin rutu
            ->assertStatus(403)//ocekuje 403 (forbidden) - zabranjeno
            ->assertJsonPath('message', 'Samo admin može pristupiti.');//ocekuje ovu poruku (nju imamo iz MiddleWare)
    }

    public function test_admin_neobradjeni_returns_only_kreiran(): void
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);//kreira admina i loguje ga

        $u = $this->makeUser(['email' => 'u@test.com']);//kreira obicnog korisnika (vlasnika zahteva)

        Zahtev::create(array_merge($this->payloadBracniStatus(), [//pravi zahtev kreiran
            'korisnik_id' => $u->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        Zahtev::create(array_merge($this->payloadBracniStatus(), [//pravi zahtev odobren
            'korisnik_id' => $u->id,
            'status' => 'odobren',
            'datum_kreiranja' => now(),
        ]));

        $res = $this->getJson('/api/admin/neobradjeniZahtevi');//pozove rutu i ocekuje ok 200
        $res->assertOk();

        foreach ($res->json() as $item) {//proverava da li svi vraceni zahtevi imaju status kreiran
            $this->assertEquals('kreiran', $item['status'] ?? null);//API vraca samo neobradjene
        }
    }

    public function test_admin_prikazi_zahtev_returns_zahtev(): void
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);//kreira admina
        Sanctum::actingAs($admin);

        $u = $this->makeUser(['email' => 'u@test.com']);//kreira obicnog usera

        $zahtev = Zahtev::create(array_merge($this->payloadBracniStatus(), [//kreira zahtev
            'korisnik_id' => $u->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $this->getJson('/api/admin/zahtevi/' . $zahtev->id)//poziva ovu rutu
            ->assertOk()
            ->assertJsonPath('id', $zahtev->id);//proverava da li je vracen bas ovaj zahtev
    }

    public function test_admin_odobri_zahtev_changes_status(): void//odobravanje radi i upisuje status
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);

        $u = $this->makeUser(['email' => 'u@test.com']);

        $zahtev = Zahtev::create(array_merge($this->payloadBracniStatus(), [//kreira zahtev za status kreiran
            'korisnik_id' => $u->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $this->postJson('/api/admin/zahtevi/' . $zahtev->id . '/odobri')//ovo se ocekuje:
            ->assertOk()
            ->assertJsonPath('message', 'Zahtev je odobren.')
            ->assertJsonPath('zahtev.status', 'odobren');

        $this->assertDatabaseHas('zahtevi', [//proverava bazu
            'id' => $zahtev->id,
            'status' => 'odobren',
        ]);
    }

    public function test_admin_odbij_zahtev_changes_status(): void//isto kao odobri, samo status odbijen i ruta /odbijen
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);

        $u = $this->makeUser(['email' => 'u@test.com']);

        $zahtev = Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $u->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));

        $this->postJson('/api/admin/zahtevi/' . $zahtev->id . '/odbij')
            ->assertOk()
            ->assertJsonPath('message', 'Zahtev je odbijen.')
            ->assertJsonPath('zahtev.status', 'odbijen');

        $this->assertDatabaseHas('zahtevi', [
            'id' => $zahtev->id,
            'status' => 'odbijen',
        ]);
    }

    public function test_admin_statistika_zahteva_returns_counts(): void//statistika radi i vraca tacne brojeve
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);

        $u = $this->makeUser(['email' => 'u@test.com']);
        //pravi 3 zahteva za 1 usera
        Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $u->id,
            'status' => 'kreiran',
            'datum_kreiranja' => now(),
        ]));
        Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $u->id,
            'status' => 'odobren',
            'datum_kreiranja' => now(),
        ]));
        Zahtev::create(array_merge($this->payloadBracniStatus(), [
            'korisnik_id' => $u->id,
            'status' => 'odbijen',
            'datum_kreiranja' => now(),
        ]));

        $this->getJson('/api/admin/statistikaZahteva')//poziva ovu rutu
            ->assertOk()
            ->assertJsonStructure(['cekajuci', 'odobreni', 'odbijeni'])
            ->assertJsonPath('cekajuci', 1)//provera koliko ima cega
            ->assertJsonPath('odobreni', 1)
            ->assertJsonPath('odbijeni', 1);
    }
            */
}