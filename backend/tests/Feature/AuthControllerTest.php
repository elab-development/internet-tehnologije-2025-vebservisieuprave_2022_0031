<?php

namespace Tests\Feature;//Feature testove koristimo

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;//aktivira RefreshDatabase -> pre svakog testa baza se resetuje (krene od nule)

    private function makeUser(array $override = []): User
    {
        return User::factory()->create(array_merge([//pravljenje User objekta pomoću factory-ja
            'pol' => 'M',
            'tip_korisnika' => 'domaci',  // default
            'datum_rodjenja' => '2000-01-01',
        ], $override));//kreira se user u bazi
    }

    // REGISTER

    public function test_register_validation_fails_with_empty_data(): void//validacija pada kada nema obaveznih polja
    {
        $this->postJson('/api/register', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validacija nije prosla.')
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_register_creates_user_and_returns_token(): void//uspesna registracija + token
    {
        $email = 'user_' . uniqid() . '@test.com';//generise unikatan email da ne bi doslo do unique constraint-a

        $this->postJson('/api/register', [
            'ime' => 'Petar',
            'prezime' => 'Petrovic',
            'email' => $email,
            'password' => '123456',
            'password_confirmation' => '123456',
        ])
            ->assertStatus(201)
            ->assertJsonPath('message', 'Registracija uspesna.')
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('user.email', $email)
            ->assertJsonPath('user.ime', 'Petar')
            ->assertJsonPath('user.prezime', 'Petrovic');

        $this->assertDatabaseHas('users', [//provera da li je red stvarno upisan u tabelu
            'email' => $email,
            'ime' => 'Petar',
            'prezime' => 'Petrovic',
        ]);
    }

    // LOGIN / ME / LOGOUT

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
            ->assertJsonPath('message', 'Pogresan email ili lozinka.');
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
            ->assertJsonPath('message', 'Uspesno ste prijavljeni.');
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
}
