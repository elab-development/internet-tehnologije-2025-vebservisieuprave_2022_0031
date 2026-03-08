<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
// use App\Mail\VerifyEmail;
use App\Models\Zahtev;
use App\Models\Termin;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/check-jmbg",
        summary: "Provera da li JMBG postoji u bazi drzavljana",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["jmbg"],
                properties: [
                    new OA\Property(property: "jmbg", type: "string", example: "1234567890123")
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Drzavljanin pronađen, vraća osnovne podatke"),
            new OA\Response(response: 404, description: "Korisnik sa tim JMBG-om ne postoji"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

        return response()->json([
            'drzavljanin' => [
                'ime' => $drzavljanin->ime,
                'prezime' => $drzavljanin->prezime,
                'datum_rodjenja' => $drzavljanin->datum_rodjenja,
                'pol' => $drzavljanin->pol
            ]
        ]);
    }

    #[OA\Post(
        path: "/api/register-domaci",
        summary: "Registracija domaćeg korisnika pomoću JMBG-a + slanje verifikacionog emaila",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["jmbg", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "jmbg", type: "string", example: "1234567890123"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "marko@email.com"),
                    new OA\Property(property: "password", type: "string", example: "123456"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "123456"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Registracija uspešna"),
            new OA\Response(response: 404, description: "JMBG ne postoji u bazi"),
            new OA\Response(response: 409, description: "Korisnik sa tim JMBG-om je već registrovan"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

        $drzavljanin = \App\Models\Drzavljanin::where('jmbg', $data['jmbg'])->first();
        if (!$drzavljanin) {
            return response()->json([
                'message' => 'Korisnik sa tim JMBG-om ne postoji u bazi.'
            ], 404);
        }

        $postojećiUser = User::where('jmbg', $data['jmbg'])->first();
        if ($postojećiUser) {
            return response()->json([
                'message' => 'Korisnik sa tim JMBG-om je već registrovan.'
            ], 409);
        }

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

        // $url = URL::temporarySignedRoute(
        //     'verification.verify',
        //     now()->addMinutes(60),
        //     ['id' => $user->id]
        // );

        // Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return response()->json([
            'message' => 'Registracija uspešna. Proverite email za verifikaciju.',
            'user' => $user
        ], 201);
    }

    #[OA\Post(
        path: "/api/register-strani",
        summary: "Registracija stranog korisnika (uz opcioni upload profilne slike) + slanje verifikacionog emaila",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["ime", "prezime", "email", "password", "password_confirmation", "broj_pasosa", "drzavljanstvo"],
                    properties: [
                        new OA\Property(property: "ime", type: "string", example: "John"),
                        new OA\Property(property: "prezime", type: "string", example: "Doe"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "john@email.com"),
                        new OA\Property(property: "password", type: "string", example: "123456"),
                        new OA\Property(property: "password_confirmation", type: "string", example: "123456"),
                        new OA\Property(property: "broj_pasosa", type: "string", example: "PA1234567"),
                        new OA\Property(property: "drzavljanstvo", type: "string", example: "Nemacka"),
                        new OA\Property(
                            property: "slika",
                            type: "string",
                            format: "binary",
                            description: "Profilna slika (jpg/jpeg/png, max 2MB)"
                        ),
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Registracija uspešna"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

        // $url = URL::temporarySignedRoute(
        //     'verification.verify',
        //     now()->addMinutes(60),
        //     ['id' => $user->id]
        // );

        // Mail::to($user->email)->send(new VerifyEmail($user, $url));

        return response()->json([
            'message' => 'Registracija uspešna. Proverite email za verifikaciju.',
            'user' => $user
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "Prijava korisnika (Sanctum token)",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "marko@email.com"),
                    new OA\Property(property: "password", type: "string", example: "123456"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Uspešna prijava"),
            new OA\Response(response: 401, description: "Pogrešan email ili lozinka"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

    #[OA\Post(
        path: "/api/logout",
        summary: "Odjava korisnika (briše trenutni token)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Uspešna odjava"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Uspesno ste odjavljeni.',
        ], 200);
    }

    #[OA\Get(
        path: "/api/me",
        summary: "Podaci o trenutno ulogovanom korisniku",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Podaci o korisniku"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // public function verifyEmail(Request $request, $id)
    // {
    //     if (!$request->hasValidSignature()) {
    //         return response()->json([
    //             'message' => 'Link za verifikaciju je nevažeći ili je istekao.',
    //         ], 401);
    //     }

    //     $user = User::findOrFail($id);

    //     if ($user->email_verified_at) {
    //         return response()->json([
    //             'message' => 'Email je već verifikovan.',
    //         ], 200);
    //     }

    //     $user->email_verified_at = now();
    //     $user->save();

    //     return response()->json([
    //         'message' => 'Email je uspesno verifikovan.',
    //     ], 200);
    // }

    #[OA\Put(
        path: "/api/profile",
        summary: "Ažuriranje profila korisnika (email, slika, lozinka)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "email", type: "string", format: "email", example: "novi@email.com"),
                        new OA\Property(
                            property: "profile_photo",
                            type: "string",
                            format: "binary",
                            description: "Nova profilna slika (jpg/jpeg/png, max 2MB)"
                        ),
                        new OA\Property(property: "current_password", type: "string", example: "stara123"),
                        new OA\Property(property: "new_password", type: "string", example: "nova123"),
                        new OA\Property(property: "new_password_confirmation", type: "string", example: "nova123"),
                    ],
                    type: "object"
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profil uspešno ažuriran"),
            new OA\Response(response: 400, description: "Trenutna lozinka nije tačna"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validaciona greška")
        ]
    )]
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

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo_path = $path;
        }

        if ($request->filled('new_password')) {
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

    #[OA\Get(
        path: "/api/admin/statistika",
        summary: "Statistika korisnika (ukupno, domaći, strani) - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Statistika korisnika"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin")
        ]
    )]
    public function statistika()
    {
        $totalUsers = \App\Models\User::count();
        $totalDomaci = \App\Models\User::where('tip_korisnika', 'domaci')->count();
        $totalStrani = \App\Models\User::where('tip_korisnika', 'strani')->count();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalDomaci' => $totalDomaci,
            'totalStrani' => $totalStrani
        ]);
    }

    #[OA\Get(
        path: "/api/admin/korisnici",
        summary: "Lista svih korisnika koji nisu admini - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Lista korisnika"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin")
        ]
    )]
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

    #[OA\Get(
        path: "/api/admin/korisnici/{id}",
        summary: "Prikaz jednog korisnika sa zahtevima i terminima - samo admin",
        tags: ["Admin"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID korisnika",
                schema: new OA\Schema(type: "integer"),
                example: 1
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Podaci o korisniku sa zahtevima i terminima"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Forbidden - nije admin"),
            new OA\Response(response: 404, description: "Korisnik ne postoji")
        ]
    )]
    public function prikaziKorisnika($id)
    {
        $user = User::where('id', $id)
            ->where('tip_korisnika', '!=', 'admin')
            ->with([
                'zahtevi' => function($q) {
                    $q->with(['staraAdresa', 'novaAdresa', 'dokumenti']);
                },
                'termini'
            ])
            ->firstOrFail();

        return response()->json($user);
    }
}