<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "MojaEUprava",
    description: "Aplikacija koja omogućava građanima da elektronski podnose zahteve za administrativne usluge i zakazuju termine bez potrebe za odlaskom na šalter."
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}