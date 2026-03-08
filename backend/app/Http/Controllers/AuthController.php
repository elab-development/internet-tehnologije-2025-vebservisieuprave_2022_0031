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
use App\Models\Zahtev;
use App\Models\Termin;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{


//PROVERA DA LI JE JMBG ISPRAVAN, I DA LI POSTOJI U BAZI DRZAVLJANINA
public function checkJmbg(Request $request)
{
    $validator = Validator::make($request->all(), [
        'jmbg' => 'required|string|size:13',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija JMBG-a nije uspela.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $drzavljanin = \App\Models\Drzavljanin::where('jmbg', $request->jmbg)->first();

    if (!$drzavljanin) {
        return response()->json([
            'message' => 'Korisnik sa tim JMBG-om ne postoji u bazi.'
        ], 404);
    }

    // ako postoji, vraća osnovne podatke
    return response()->json([
        'drzavljanin' => [
            'ime' => $drzavljanin->ime,
            'prezime' => $drzavljanin->prezime,
            'datum_rodjenja' => $drzavljanin->datum_rodjenja,
            'pol' => $drzavljanin->pol
        ]
    ]);
}

    
    //FUNKCIJA REGISTRUJE DOMACEG KORISNIKA (POST /api/register/domaci)
public function registerDomaci(Request $request)
{
    $validator = Validator::make($request->all(), [
        'jmbg' => 'required|string|size:13',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija nije uspela.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $data = $validator->validated();

    // Provera da li domaći korisnik postoji u tabeli Drzavljanin
    $drzavljanin = \App\Models\Drzavljanin::where('jmbg', $data['jmbg'])->first();
    if (!$drzavljanin) {
        return response()->json([
            'message' => 'Korisnik sa tim JMBG-om ne postoji u bazi.'
        ], 404);
    }
    //Proveravamo da li je korisnik vec regostrovan
    $postojećiUser = User::where('jmbg', $data['jmbg'])->first();
    if ($postojećiUser) {
    return response()->json([
        'message' => 'Korisnik sa tim JMBG-om je već registrovan.'
    ], 409); // 409 Conflict
    }

    // Kreiramo User sa podacima iz Drzavljanin tabele + email i šifra koju je korisnik uneo
    $user = User::create([
        'ime' => $drzavljanin->ime,
        'prezime' => $drzavljanin->prezime,
        'datum_rodjenja' => $drzavljanin->datum_rodjenja,
        'pol' => $drzavljanin->pol,
        'tip_korisnika' => 'domaci',
        'jmbg' => $drzavljanin->jmbg,
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id]
    );

    Mail::to($user->email)->send(new VerifyEmail($user, $url));

    return response()->json([
        'message' => 'Registracija uspešna. Proverite email za verifikaciju.',
        'user' => $user
    ], 201);
}


//FUNKCIJA REGISTRUJE STRANOG KORISNIKA ( POST /api/register/strani)
public function registerStrani(Request $request)
{
    $validator = Validator::make($request->all(), [
        'ime' => 'required|string|max:255',
        'prezime' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'broj_pasosa' => 'required|string|max:20',
        'drzavljanstvo' => 'required|string|max:100',
        'slika' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija nije prosla.',
            'errors' => $validator->errors(),
        ], 422);
    }

    $data = $validator->validated();
    $data['slika'] = null;

    if ($request->hasFile('slika')) {
        $path = $request->file('slika')->store('profile_photos', 'public');
        $data['slika'] = $path;
    }

    $user = User::create([
        'ime' => $data['ime'],
        'prezime' => $data['prezime'],
        'tip_korisnika' => 'strani',
        'broj_pasosa' => $data['broj_pasosa'],
        'drzavljanstvo' => $data['drzavljanstvo'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'profile_photo_path' => $data['slika'],
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id]
    );

    Mail::to($user->email)->send(new VerifyEmail($user, $url));

    return response()->json([
        'message' => 'Registracija uspešna. Proverite email za verifikaciju.',
        'user' => $user
    ], 201);
    }


    // LOGIN KORISNIKA (POST /api/login)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije uspela.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Pogrešan email ili lozinka.',
            ], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Uspešno ste prijavljeni.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // LOGOUT KORISNIKA (POST /api/logout)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Uspesno ste odjavljeni.',
        ], 200);
    }

    // GET /api/me
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    //VERIFIKACIJA MEJLA
    public function verifyEmail(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Link za verifikaciju je nevažeći ili je istekao.',
            ], 401);
        }

        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email je već verifikovan.',
            ], 200);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email je uspesno verifikovan.',
        ], 200);    }


    //UPDATE PROFILA KORISNIKA
    public function updateProfile(Request $request)
    {
    $user = $request->user();

    $validator = Validator::make($request->all(), [
        'email' => 'sometimes|email|unique:users,email,' . $user->id,
        'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'current_password' => 'nullable|required_with:new_password',
        'new_password' => 'nullable|min:6|confirmed'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija nije uspela.',
            'errors' => $validator->errors()
        ], 422);
    }

    // EMAIL
    if ($request->has('email')) {
        $user->email = $request->email;
    }

    // PROFILNA SLIKA
    if ($request->hasFile('profile_photo')) {
        $path = $request->file('profile_photo')->store('profile_photos', 'public');
        $user->profile_photo_path = $path;
    }

    // PROMENA LOZINKE
    if ($request->filled('new_password')) {

        // Provera stare lozinke
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Trenutna lozinka nije tačna.'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
    }

    $user->save();

    return response()->json([
        'message' => 'Profil uspešno ažuriran.',
        'user' => $user
    ]);
}


//FUNKCIJA VRACA UKUPAN BROJ KORISNIKA(GET /api/admin/stats)
public function statistika()
{
    // Ukupan broj korisnika
    $totalUsers = \App\Models\User::count();

    // Možeš dodati i broj domaćih i stranih korisnika
    $totalDomaci = \App\Models\User::where('tip_korisnika', 'domaci')->count();
    $totalStrani = \App\Models\User::where('tip_korisnika', 'strani')->count();

    return response()->json([
        'totalUsers' => $totalUsers,
        'totalDomaci' => $totalDomaci,
        'totalStrani' => $totalStrani
    ]);
}

// FUNKCIJA VRACA SVE KORISNIKE KOJI NISU ADMINI (GET /api/admin/korisnici)
public function sviKorisnici()
{
    
    $users = User::where('tip_korisnika', '!=', 'admin')
        ->select('id', 'ime', 'prezime', 'email', 'tip_korisnika', 'datum_rodjenja') 
        ->orderBy('ime')
        ->get();

    return response()->json([
        'total' => $users->count(),
        'users' => $users
    ]);
}


// FUNKCIJA VRACA TACNO ODREDJENOG KORISNIKA(GET /api/admin/users/{id})
public function prikaziKorisnika($id)
{
    $user = User::where('id', $id)
        ->where('tip_korisnika', '!=', 'admin')
        ->with([
            'zahtevi' => function($q) {
                $q->with(['staraAdresa', 'novaAdresa', 'dokumenti']);// kako bi vratio sve podatke
            },
            'termini'
        ])
        ->firstOrFail();

    return response()->json($user);
}
}