<?php


use App\Http\Controllers\ZahtevController;
use App\Http\Controllers\TerminController;
use App\Http\Controllers\AdresaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DokumentController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//sta se desava kada korisnik ode na rutu verification.verify
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])//pozivamo iz AuthControllera metodu verifyEmail
    ->name('verification.verify');

//kada pise Route::.... tim rutama mogu da pristupe svi (i neulogovani korisnici)
//korisnik koji nije ulogovan treba da pristupi metodama register (ako nema nalog) i login (ako ima nalog ali nije prijavljen)
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
      

     Route::get('/zahtev/moje', [ZahtevController::class, 'mojiZahteviPaginatedFiltered']);

     Route::get('/zahtev/moje/bracni_status', [ZahtevController::class, 'mojiBracniStatusPaginatedFiltered']);

     Route::get('/zahtev/moje/prebivaliste', [ZahtevController::class, 'mojiPromenaPrebivalistaPaginatedFiltered']);

      Route::get('/termin/moje', [TerminController::class, 'mojiTerminiPaginatedFiltered']);
     Route::get('/zahtev/export/csv', [ZahtevController::class, 'exportCsv']);
     Route::post('/zahtev', [ZahtevController::class, 'store']);
     Route::delete('/zahtev/{id}', [ZahtevController::class, 'destroy']);
     
});
//ako neke rute zelimo da zastitimo pakujemo ih u grupu ruta
//sanctum je biblioteka koju instaliramo da bi mogli da radimo autent.
//middleware filtrira rute, ako postoji token, dace nam da udjemo u ovu grupu ruta (mozemo da radimo odjavu ili da vidimo svoj nalog)
//ako nismo registrovani nemamo token, middleware daje da ide po ostalim nezasticenim rutama i da nadje register

//praksa je da rute koje rade store, destroy, update zastitimo; ove koje rade get ne moramo

Route::get('/zahtev', [ZahtevController::class, 'index']);
Route::get('/zahtev/{id}', [ZahtevController::class, 'show']);


Route::put('/zahtev/{id}', [ZahtevController::class, 'update']);
   
Route::get('/termin/{id}', [TerminController::class, 'show']);
Route::post('/termin', [TerminController::class, 'store']);
Route::delete('/termin/{id}', [TerminController::class, 'destroy']);
Route::put('/termin/{id}', [TerminController::class, 'update']);

Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/password/reset', [ForgotPasswordController::class, 'resetPassword']);


Route::resource('/dokument', DokumentController::class);//ova linija menja donjih 5, zahtev - da imamo resource rutu
//Route::get('/dokument', [DokumentController::class, 'index']);
//Route::get('/dokument/{id}', [DokumentController::class, 'show']);
//Route::post('/dokument', [DokumentController::class, 'store']);
//Route::delete('/dokument/{id}', [DokumentController::class, 'destroy']);
//Route::put('/dokument/{id}', [DokumentController::class, 'update']);

Route::get('/adresa', [AdresaController::class, 'index']);
Route::get('/adresa/{id}', [AdresaController::class, 'show']);
Route::post('/adresa', [AdresaController::class, 'store']);
Route::delete('/adresa/{id}', [AdresaController::class, 'destroy']);
Route::put('/adresa/{id}', [AdresaController::class, 'update']);
Route::get('/termin', [TerminController::class, 'index']);



