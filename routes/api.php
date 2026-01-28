<?php


use App\Http\Controllers\ZahtevController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/zahtev', [ZahtevController::class, 'index']);
Route::get('/zahtev/{id}', [ZahtevController::class, 'show']);
Route::post('/zahtev', [ZahtevController::class, 'store']);
Route::delete('/zahtev/{id}', [ZahtevController::class, 'destroy']);

