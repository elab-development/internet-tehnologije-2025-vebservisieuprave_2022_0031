<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;




class AuthController extends Controller
{
    //postupkom registracije kreiramo novi objekat u bazi, kada se novi korisnik registruje=upisemo ga u bazu
    //sve metode ovde koriste post zahtev
    //POST /api/register ovako pozivamo ovu metodu
    public function register(Request $request)//za kada se prvi put logujemo
    {
        $validator=Validator::make($request->all(), [
            'ime' => 'required|string|max:255',//required - da bi bilo obavezno polje
            'prezime' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',//mora da bude jedinstven u tabeli users u koloni email
            'password' => 'required|string|min:6|confirmed', //password_confirmation, minimum 6 karaktera, lozinka mora da bude potvrdjena
        ]);
        //ako validator negde failuje ispisuje se greska 422
        if($validator->fails()){
            return response()->json([
                'message' => 'Validacija nije prosla.',
                'errors' => $validator->errors(),
            ], 422);
        }
        //ako bude sve okej sa validatorom, kreiramo usera
        $data = $validator->validated();
        $user=User::create([
            'ime' => $data['ime'],
            'prezime' => $data['prezime'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        //logika za slanje verifikacionog mejla, taj mejl moramo da napravimo kroz terminal
        //pravimo da link bude privremen, vazi npr 60min, za pravljenje linka koristimo ugradjene metode
        $url=URL::temporarySignedRoute(
            'verification.verify',//korisnik kad klikne dugme otvara mu se i ova ruta, ruta ima token koji vazi 60min
            now()->addMinutes(60),
            ['id' => $user->id]
        );
        Mail::to($user->email)->send(new VerifyEmail( $user, $url));
        $token = $user->createToken('api_token')->plainTextToken;

        
        return response()->json([
            'message' => 'Registracija uspesna.',
            'user' => $user,
            'token' => $token,
        ], 201);

    }

    //POST /api/login
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija nije prosla.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Pogresan email ili lozinka.',
        ], 401);
    }

    $token = $user->createToken('api_token')->plainTextToken;

    return response()->json([
        'message' => 'Uspesno ste prijavljeni.',
        'user' => $user,
        'token' => $token,
    ], 200);
}


    //kada radimo logout ne saljemo nista u requestu, vidimo koji nam je token
    //da bi se odjavili moramo biti prijavljeni (imamo token)!
    //POST /api/logout
    public function logout(Request $request){
        $user = $request->user();//kada je korisnik ulogovan, na osnovu tokena dobijamo ulogovanog usera
        //obrisemo samo trenutni token
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Uspesno ste odjavljeni.',
        ], 200);
    }

    //kada je korisnik prijavljen, on moze iz baze da izvuce podatke o sebi
    //kada hocemo da prikazemo profil korisnika: GET /api/me
    public function me(Request $request){
        return response()->json($request->user());
    }

    //metoda za verifikaciju mejla
    public function verifyEmail(Request $request, $id)
    {
        //da li je link potpisan i da li je vazeci (nije istekao)
        //uzimamo link za verifikaciju koji je korisnik kliknuo i proveravamo da li je vazeci (60min)
        if(! $request->hasValidSignature()){
            return response()->json([
                'message' => 'Link za verifikaciju je nevazeci ili je istekao.',
            ],401);
        }
        $user = User::findOrFail($id);//trazimo korisnika sa id koji saljemo
        if($user->email_verified_at){//ako je ovo polje null dole ga postavlja na now
            return response()->json([//ako je to polje razlicito od null
                'message' => 'Email je vec verifikovan.',
            ], 200);
        }
        $user->email_verified_at=now();
        $user->save();//azuriranje usera
        return response()->json([
            'message' => 'Email je uspesno verifikovan.',
        ], 200);
    }
}
