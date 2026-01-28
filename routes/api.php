<?php


use App\Http\Controllers\ZahtevController;
use App\Http\Controllers\TerminController;
use App\Http\Controllers\AdresaController;
use App\Http\Controllers\DokumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/zahtev', [ZahtevController::class, 'index']);
Route::get('/zahtev/{id}', [ZahtevController::class, 'show']);
Route::post('/zahtev', [ZahtevController::class, 'store']);
Route::delete('/zahtev/{id}', [ZahtevController::class, 'destroy']);
Route::put('/zahtev/{id}', [ZahtevController::class, 'update']);

Route::get('/termin', [TerminController::class, 'index']);
Route::get('/termin/{id}', [TerminController::class, 'show']);
Route::post('/termin', [TerminController::class, 'store']);
Route::delete('/termin/{id}', [TerminController::class, 'destroy']);
Route::put('/termin/{id}', [TerminController::class, 'update']);

Route::get('/dokument', [DokumentController::class, 'index']);
Route::get('/dokument/{id}', [DokumentController::class, 'show']);
Route::post('/dokument', [DokumentController::class, 'store']);
Route::delete('/dokument/{id}', [DokumentController::class, 'destroy']);
Route::put('/dokument/{id}', [DokumentController::class, 'update']);

Route::get('/adresa', [AdresaController::class, 'index']);
Route::get('/adresa/{id}', [AdresaController::class, 'show']);
Route::post('/adresa', [AdresaController::class, 'store']);
Route::delete('/adresa/{id}', [AdresaController::class, 'destroy']);
Route::put('/adresa/{id}', [AdresaController::class, 'update']);


