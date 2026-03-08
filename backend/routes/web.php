<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs/asset/{asset}', function ($asset) {
    $path = base_path('swagger-assets/' . $asset);
    if (!file_exists($path)) {
        abort(404);
    }
    $extension = pathinfo($asset, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'html' => 'text/html',
    ];
    $mime = $mimeTypes[$extension] ?? 'text/plain';
    return response()->file($path, ['Content-Type' => $mime]);
})->where('asset', '.*');