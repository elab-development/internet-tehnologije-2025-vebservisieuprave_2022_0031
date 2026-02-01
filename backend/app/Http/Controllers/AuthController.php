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
    // POST /api/register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ime' => 'required|string|max:255',
            'prezime' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            // ✅ bez razmaka u mimes
            'slika' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validacija nije prosla.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // ✅ uvek obezbedi ključ (da ne puca kad nema slike)
        $data['slika'] = null;

        // ✅ ako postoji slika, sacuvaj i upisi putanju
        if ($request->hasFile('slika')) {
            $path = $request->file('slika')->store('profile_photos', 'public');
            $data['slika'] = $path;
        }

        $user = User::create([
            'ime' => $data['ime'],
            'prezime' => $data['prezime'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            // ✅ sad je ili putanja ili null
            'profile_photo_path' => $data['slika'],
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id]
        );

        Mail::to($user->email)->send(new VerifyEmail($user, $url));

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Registracija uspesna.',
            'user' => $user,
            'token' => $token,
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
        ], 200);
    }
}

