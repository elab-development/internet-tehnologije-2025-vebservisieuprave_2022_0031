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

public function checkJmbg(Request $request)
{
    $validator = Validator::make($request->all(), [
        'jmbg' => 'required|string|size:13',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija JMBG-a nije prošla.',
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
    // POST /api/register/domaci
public function registerDomaci(Request $request)
{
    $validator = Validator::make($request->all(), [
        'jmbg' => 'required|string|size:13',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validacija nije prosla.',
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
        'message' => 'Корисник са тим JMBG-ом је већ регистрован.'
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

// POST /api/register/strani
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

    // POST /api/login
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

    // POST /api/logout
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

    // verifikacija mejla
    public function verifyEmail(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Link za verifikaciju je nevazeci ili je istekao.',
            ], 401);
        }

        $user = User::findOrFail($id);

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email je vec verifikovan.',
            ], 200);
        }

        $user->email_verified_at = now();
        $user->save();

        return response()->json([
            'message' => 'Email je uspesno verifikovan.',
        ], 200);    }

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
            'message' => 'Validacija nije prošla.',
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
}

