<?php

namespace Tests\Feature;//Feature testove koristimo

use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;//aktivira RefreshDatabase -> pre svakog testa baza se resetuje (krene od nule)

    private function seedDrzavljanin(array $override = []): array//ubacuje test drzavljanina u DB
    {
        $data = array_merge([//spaja default podatke sa ovim sto je nabrojano
            'jmbg' => '1234567890123',
            'ime' => 'Petar',
            'prezime' => 'Petrovic',
            'datum_rodjenja' => '2000-01-01',
            'pol' => 'M', // bitno zbog CHECK constraint-a
        ], $override);

        DB::table('drzavljani')->insert($data);//ubacuje red u tabelu drzavljani

        return $data;//vraća ubacene podatke da test može da koristi jmbg, ime...
    }
    private function makeUser(array $override = []): User
    {
        return User::factory()->create(array_merge([//pravljenje User objekta pomoću factory-ja
            'pol' => 'M',                 
            'tip_korisnika' => 'domaci',  // default
            'datum_rodjenja' => '2000-01-01',
        ], $override));//kreira se user u bazi
    }

    // CHECK JMBG 
//svaka metoda koja pocinje sa test... je 1 test
    public function test_check_jmbg_validation_fails(): void//validacija pada
    {
        $this->postJson('/api/check-jmbg', ['jmbg' => '123'])//salje POST zahtev na rutu /api/check-jmbg sa nevalidnim jmbg (nije 13 cifara)
            ->assertStatus(422)//vraca se 422 (validation error) kada je jmbg pogresan
            ->assertJsonStructure(['message', 'errors']);//ocekuje json koji ima message i error
    }

    public function test_check_jmbg_returns_404_if_not_found(): void//salje se validan jmbg ali njega nema u drzavljani
    {
        $this->postJson('/api/check-jmbg', ['jmbg' => '1234567890123'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Korisnik sa tim JMBG-om ne postoji u bazi.');
    }

    public function test_check_jmbg_returns_data_if_exists(): void//postoji drzavljanin
    {
        $drz = $this->seedDrzavljanin();//ubacuje drzavljanina u bazu i smesta podatke u $drz

        $this->postJson('/api/check-jmbg', ['jmbg' => $drz['jmbg']])//salje request sa tim jmbg
            ->assertOk()//200 OK
            ->assertJsonStructure(['drzavljanin' => ['ime', 'prezime', 'datum_rodjenja', 'pol']])//ocekuje u JSON-u objekat drzavljanin sa tim poljima
            ->assertJsonPath('drzavljanin.ime', $drz['ime'])//provera da li vraceni podaci odg ubacenim
            ->assertJsonPath('drzavljanin.prezime', $drz['prezime']);
    }

    // REGISTER DOMACI

    public function test_register_domaci_creates_user_and_sends_email(): void//uspešna registracija + poslat email
    {
        Mail::fake();//ne šalje stvarne email-ove, nego ih fejkuje da možemo posle proveriti da li je nešto poslato

        $drz = $this->seedDrzavljanin([//ubacujemo drzavljanina
            'jmbg' => '1111111111111',
            'ime' => 'Marko',
            'prezime' => 'Markovic',
        ]);

        $email = 'domaci_' . uniqid() . '@test.com';//generise unikatan email da ne bi doslo do unique constraint-a

        $this->postJson('/api/register-domaci', [//ruta za domacu registraciju
            'jmbg' => $drz['jmbg'],
            'email' => $email,
            'password' => '123456',
            'password_confirmation' => '123456',
        ])
            ->assertStatus(201)//ocekuje se 201 Created
            ->assertJsonStructure(['message', 'user'])//ocekujemo da se vrati poruka i korisnik
            //provera kljucnih vrednosti vracenog usera
            ->assertJsonPath('user.email', $email)
            ->assertJsonPath('user.tip_korisnika', 'domaci')
            ->assertJsonPath('user.jmbg', $drz['jmbg']);

        $this->assertDatabaseHas('users', [//provera da li je red stvarno upisan u tabelu
            'email' => $email,
            'tip_korisnika' => 'domaci',
            'jmbg' => $drz['jmbg'],
        ]);

        Mail::assertSent(VerifyEmail::class);//provera da li je poslat email tipa VerifyEmail
    }

    public function test_register_domaci_fails_if_jmbg_not_in_drzavljani(): void//registracija ne uspe jer drzavljanin ne postoji
    {
        $this->postJson('/api/register-domaci', [
            'jmbg' => '9999999999999',//salje se jmbg koji nije u tabeli
            'email' => 'x@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])
            ->assertStatus(404)
            ->assertJsonPath('message', 'Korisnik sa tim JMBG-om ne postoji u bazi.');
    }

    public function test_register_domaci_fails_if_user_with_jmbg_already_registered(): void
    {//registracija ne uspe jer je drzavljanin sa tim jmbg vec registrovan
        $drz = $this->seedDrzavljanin(['jmbg' => '2222222222222']);//ubacujemo drzavljanina

        $this->makeUser([//kreira već postojećeg user-a sa tim jmbg
            'jmbg' => $drz['jmbg'],
            'tip_korisnika' => 'domaci',
            'email' => 'old@test.com',
        ]);

        $this->postJson('/api/register-domaci', [
            'jmbg' => $drz['jmbg'],
            'email' => 'new@test.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Korisnik sa tim JMBG-om je već registrovan.');
    }

    //REGISTER STRANI 

    public function test_register_strani_creates_user_and_sends_email_with_optional_photo(): void
    {//uspešna registracija stranog korisnika + upload + email
        Mail::fake();
        Storage::fake('public');//fejkujemo mail i public storage disk da se upload ne snima na stvarni disk

        $email = 'strani_' . uniqid() . '@test.com';//unikatan mail

        $fakeJpg = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');//pravimo fake fajl za upload

        $this->postJson('/api/register-strani', [//salje se request za stranu registraciju
            'ime' => 'John',
            'prezime' => 'Doe',
            'email' => $email,
            'password' => '123456',
            'password_confirmation' => '123456',
            'broj_pasosa' => 'AB1234567',
            'drzavljanstvo' => 'USA',
            'slika' => $fakeJpg,
        ])
            ->assertStatus(201)
            ->assertJsonStructure(['message', 'user'])
            ->assertJsonPath('user.email', $email)
            ->assertJsonPath('user.tip_korisnika', 'strani');

        $this->assertDatabaseHas('users', [//provera da li je korisnik upisan u bazu
            'email' => $email,
            'tip_korisnika' => 'strani',
            'broj_pasosa' => 'AB1234567',
            'drzavljanstvo' => 'USA',
        ]);

        Mail::assertSent(VerifyEmail::class);//provera da li je poslat mail
    }

    //LOGIN / ME / LOGOUT 

    public function test_login_fails_with_wrong_credentials(): void//login fail(401)
    {
        $this->makeUser([//ubacuje usera u bazu sa poznatom lozinkom
            'email' => 'a@test.com',
            'password' => Hash::make('correct'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'a@test.com',
            'password' => 'wrong',//pokusava login sa pogresnom lozinkom
        ])
            ->assertStatus(401)//Unauthorized
            ->assertJsonPath('message', 'Pogrešan email ili lozinka.');
    }

    public function test_login_returns_token_on_success(): void//login success (200+token)
    {
        $user = $this->makeUser([//kreira korisnika
            'email' => 'a@test.com',
            'password' => Hash::make('123456'),
        ]);

        $this->postJson('/api/login', [//ocekuje da API vrati token (Sanctum token)
            'email' => $user->email,
            'password' => '123456',
        ])
            ->assertOk()
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('message', 'Uspešno ste prijavljeni.');
    }

    public function test_me_requires_auth(): void//bez tokena ne moze na me
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_user_when_authenticated(): void//vraca usera kad si ulogovan
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);//actingAs simulira autentifikovanog korisnika u testu

        $this->getJson('/api/me')//proverava da API vraća tog korisnika
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('email', $user->email);
    }

    public function test_logout_requires_auth(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);//bez tokena logout ne radi
    }

    public function test_logout_works_when_authenticated(): void//logout radi ako ima token
    {
        $user = $this->makeUser();
        $token = $user->createToken('api_token')->plainTextToken;//kreira pravi Sanctum token koji se salje

        $this->withHeader('Authorization', 'Bearer ' . $token)//salje logout request sa tokenom i ocekuje uspeh
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Uspesno ste odjavljeni.');
    }

    // EMAIL VERIFY

    public function test_verify_email_marks_verified(): void
    {
        $user = $this->makeUser(['email_verified_at' => null]);//kreira usera koji nije verifikovan

        $signedUrl = URL::temporarySignedRoute(//pravi signed link za rutu verification.verify (/api/email/verify/{id}) sa rokom 60 minuta

        //taj link ima signature parametar i Laravel ga proverava preko hasValidSignature()
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id]
        );

        $this->getJson($signedUrl)//poziva taj link i očekuje uspešnu verifikaciju
            ->assertOk()
            ->assertJsonPath('message', 'Email je uspesno verifikovan.');

        $this->assertNotNull($user->fresh()->email_verified_at);//proverava da je u bazi upisan datum verifikacije
    }

    // UPDATE PROFILE

    public function test_update_profile_requires_auth(): void
    {
        $this->putJson('/api/profile', ['email' => 'x@test.com'])//bez tokena se ne moze menjati profil
            ->assertStatus(401);
    }

    public function test_update_profile_can_update_email_photo_and_password(): void
    {//Update profila: email + slika + lozinka
        Storage::fake('public');//fejkuje storage

        $user = $this->makeUser([//kreira usera
            'email' => 'old@test.com',
            'password' => Hash::make('oldpass'),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;//kreira token

        $fakePng = UploadedFile::fake()->create('p.png', 100, 'image/png'); // fejk upload fajl

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/profile', [//salje PUT na /api/profile
                'email' => 'new@test.com',
                'profile_photo' => $fakePng,
                'current_password' => 'oldpass',
                'new_password' => 'newpass1',
                'new_password_confirmation' => 'newpass1',
            ])
            ->assertOk()//proverava odgovor
            ->assertJsonPath('message', 'Profil uspešno ažuriran.')
            ->assertJsonPath('user.email', 'new@test.com');

        $fresh = $user->fresh();//ucitava sveze podatke iz baze jer user mozda nije updateovan
        $this->assertEquals('new@test.com', $fresh->email);//proverava da su email, lozinka i slika stvarno promenjeni
        $this->assertTrue(Hash::check('newpass1', $fresh->password));
        $this->assertNotNull($fresh->profile_photo_path);
    }

    public function test_update_profile_rejects_wrong_current_password(): void//update profila odbija pogrešnu staru lozinku
    {//Kreira user-a sa oldpass, salje current_password=wrong, očekuje 400 i poruku Trenutna lozinka nije tačna
        $user = $this->makeUser([
            'password' => Hash::make('oldpass'),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/profile', [
                'current_password' => 'wrong',
                'new_password' => 'newpass1',
                'new_password_confirmation' => 'newpass1',
            ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Trenutna lozinka nije tačna.');
    }

    // ADMIN 

    public function test_admin_routes_forbidden_for_non_admin(): void//non-admin ne može
    {
        $user = $this->makeUser(['tip_korisnika' => 'domaci']);//uloguje običnog korisnika
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/statistika')
            ->assertStatus(403)//ocekuje 403 jer middleware vraca tu poruku
            ->assertJsonPath('message', 'Samo admin može pristupiti.');
    }

    public function test_admin_statistika_returns_counts_for_admin(): void
    {
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);//uloguje admin, kreira 2 domaca, 3 strana
        Sanctum::actingAs($admin);

        $this->makeUser(['tip_korisnika' => 'domaci']);
        $this->makeUser(['tip_korisnika' => 'domaci']);
        $this->makeUser(['tip_korisnika' => 'strani']);
        $this->makeUser(['tip_korisnika' => 'strani']);
        $this->makeUser(['tip_korisnika' => 'strani']);

        $this->getJson('/api/admin/statistika')//provera da li se vracaju pravi brojevi
            ->assertOk()
            ->assertJsonStructure(['totalUsers', 'totalDomaci', 'totalStrani'])
            ->assertJsonPath('totalDomaci', 2)
            ->assertJsonPath('totalStrani', 3);
    }

    public function test_admin_svi_korisnici_excludes_admins(): void
    {//Uloguje admina, napravi još jednog admina + domaci + strani
     //pozove /api/admin/korisnici, proveri JSON strukturu, proveri da ni jedan vraćeni korisnik nema tip_korisnika=admin
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);

        $this->makeUser(['tip_korisnika' => 'admin']);
        $this->makeUser(['tip_korisnika' => 'domaci']);
        $this->makeUser(['tip_korisnika' => 'strani']);

        $response = $this->getJson('/api/admin/korisnici');

        $response->assertOk()
            ->assertJsonStructure([
                'total',
                'users' => [
                    '*' => ['id', 'ime', 'prezime', 'email', 'tip_korisnika', 'datum_rodjenja'],
                ],
            ]);

        foreach ($response->json('users') as $u) {
            $this->assertNotEquals('admin', $u['tip_korisnika']);
        }
    }

    public function test_admin_prikazi_korisnika_returns_user_with_relations(): void
    {//Uloguje admina, napravi domaceg user-a, pozove /api/admin/korisnici/{id}, očekuje da JSON ima:
     //osnovna polja, zahtevi, termini
        $admin = $this->makeUser(['tip_korisnika' => 'admin']);
        Sanctum::actingAs($admin);

        $user = $this->makeUser(['tip_korisnika' => 'domaci']);

        $this->getJson('/api/admin/korisnici/' . $user->id)
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonStructure([
                'id',
                'ime',
                'prezime',
                'email',
                'tip_korisnika',
                'zahtevi',
                'termini',
            ]);
    }
}
