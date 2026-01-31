<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ForgotPasswordController extends Controller
{
    // Post/api/password/forgot
    // korisnik posalje mejl
    //mi generisemo token, sacuvamo u bazi i posaljemo mejl
    public function sendResetLink(Request $request){

    $validator= Validator::make($request->all(),[
        'email' => 'required|string|email'
    ]);

        if($validator->fails()){ //proveravamo mejl
            return response()->json([
                'message'=> 'Validacija nije prosla.',
                'errors'=> $validator->errors(),

            ], 422);
        }
        $email=$validator->validated()['email'];
        $user=User::where('email', $email)->first();//uzimamo usera sa tim emailom

        if(! $user){
        //zbog sigurnosti ne otkrivamo da li postoji user sa tim emailom
        return response()->json([
            'message'=> 'Ako nalog postoji, poslali smo mejl sa instrukcijama za reset lozinke.',
        ],200);
        }

        //generisemo token
        $token=Str::random(64);

        //upisemo token u password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
        ['email'=>$user->email],
        [
            'token' => Hash::make($token), //cuvamo hash tokena
            'created_at' => Carbon::now(),
        ]
        );
        //napravimo url koji bismo koristili na frontu 
        $resetUrl=config('app.frontend_url', config('app.url')) .
        '/reset-password?token=' . urlencode($token) . 
        '&email=' . urlencode($user->email);

        //posaljemo mejl preko Mailtrape


        Mail::to($user->email)->send(new ResetPasswordMail($user, $token, $resetUrl));
        
        return response()-> json([
        'message'=> 'Ako nalog postoji, poslali smo instrukcije za reset lozinke na email.',
        
        ],200);
    }

    //post/api/password/reset
    //korisnik posalje email, token, novu lozinku

    public function resetPassword(Request $request){
        $validator= Validator::make($request->all(),[
        'email' => 'required|string|email',
        'token' => 'required|string',
        'password' => 'required|string|min:6|confirmed', //password confirmation
    ]);
    if($validator->fails()){ //proveravamo mejl
            return response()->json([
                'message'=> 'Validacija nije prosla.',
                'errors'=> $validator->errors(),

            ], 422);
        }
        $data=$validator-> validated();

        //proveravamo da li je token ispravan, uzimamo iz baze tabele pas reset tokens
        $record=DB::table('password_reset_tokens')
        ->where('email', $data['email'])
        -> first();

        if(!$record){//ako token nije ispravan
         return response()->json([
                'message'=> 'Neispravan token ili email.',
            ], 400);

        }
        //proveravamo da li je token istekao(nprm 60 minuta)
        $createdAt=Carbon::parse($record->created_at);
        if($createdAt->addMinutes(60)->isPast()){
             return response()->json([
                'message'=> 'TOken je istekao.Posaljite novi zahtev za reset lozinke',
            ], 400);
        }
        // da li se token pokkapa sa tokenom iz baze
        if(!Hash::check($data['token'], $record->token)){
         return response()->json([
                'message'=> 'Neispravan token.',
            ], 400);
        }

        //sve je u redu, menjamo lozinku
        $user=User::where('email', $data['email'])->firstOrFail();

        $user->password=$data['password'];
        $user->save();

        //obrisemo token nakon resetovanja
        DB::table('password_reset_tokens')
        -> where('email', $data['email'])
        -> delete();
         return response()->json([
                'message'=> 'Lozinka je uspesno resetovana.',
            ], 200);
    }
}
