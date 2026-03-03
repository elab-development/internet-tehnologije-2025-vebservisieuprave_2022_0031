<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drzavljanin extends Model
{
    use HasFactory;

    protected $table = 'drzavljani';

    protected $fillable = [
        'ime',
        'prezime',
        'datum_rodjenja',
        'pol',
        'jmbg',
    ];

    protected $casts = [
        'datum_rodjenja' => 'date',
    ];
}