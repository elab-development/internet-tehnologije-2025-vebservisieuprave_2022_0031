<?php


use App\Http\Controllers\ZahtevController;
use App\Http\Controllers\TerminController;
use App\Http\Controllers\AdresaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DokumentController;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;

//REGISTRACIJA I LOGIN(mogu da im pristupe svi)
Route::post('/register-domaci', [AuthController::class, 'registerDomaci']);
Route::post('/check-jmbg', [AuthController::class, 'checkJmbg']); 
Route::post('/register-strani', [AuthController::class, 'registerStrani']); 
Route::post('/login', [AuthController::class, 'login']);

//sta se desava kada korisnik ode na rutu verification.verify
Route::get('/email/verify/{id}', [AuthController::class, 'verifyEmail'])//pozivamo iz AuthControllera metodu verifyEmail
    ->name('verification.verify');

//Middleware filtrira rute,proverava da li postoji token, vrsi autentifikaciju
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
      
    //Zahtevi i termini korisnika
     Route::get('/zahtev/moje', [ZahtevController::class, 'mojiZahteviPaginatedFiltered']);
     Route::get('/zahtev/moje/bracni_status', [ZahtevController::class, 'mojiBracniStatusPaginatedFiltered']);
     Route::get('/zahtev/moje/prebivaliste', [ZahtevController::class, 'mojiPromenaPrebivalistaPaginatedFiltered']);
    Route::get('/termin/moje', [TerminController::class, 'mojiTerminiPaginatedFiltered']);

     Route::get('/zahtev/export/csv', [ZahtevController::class, 'exportCsv']);
     Route::post('/zahtev', [ZahtevController::class, 'store']);
     Route::delete('/zahtev/{id}', [ZahtevController::class, 'destroy']);
     
});
//Middleware  koji proverava da li je ulogovan korisnik admin
Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function (){
    //Svi korisnici i pojedinacan korisnik
     Route::get('/admin/korisnici', [AuthController::class, 'sviKorisnici']);
     Route::get('/admin/korisnici/{id}', [AuthController::class, 'prikaziKorisnika']);

    // Statistika i vizualizacija
    Route::get('/admin/statistika', [AuthController::class, 'statistika']);
    Route::get('/admin/statistikaZahteva', [ZahtevController::class, 'statistikaZahteva']);
});

//praksa je da rute koje rade store, destroy, update zastitimo; ove koje rade get ne moramo

Route::get('/zahtev', [ZahtevController::class, 'index']);
Route::get('/zahtev/{id}', [ZahtevController::class, 'show']);
Route::put('/zahtev/{id}', [ZahtevController::class, 'update']);

Route::get('/termin', [TerminController::class, 'index']);
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




