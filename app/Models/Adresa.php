<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adresa extends Model
{
    protected $fillable = [
        'ulica',
        'broj',
        'mesto', 
        'opstina',
        'grad', 
        'postanski_broj',
        'tip_adrese',
        'zahtev_id'
    ];
     protected $casts = [
        'tip_adrese' => 'string',
    ];

    
}
