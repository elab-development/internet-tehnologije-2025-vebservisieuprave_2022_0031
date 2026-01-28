<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void // desava se kada radimo commit
    {
        Schema::create('adrese', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zahtev_id')->constrained('zahtevi');
            $table-> string('ulica');
            $table-> integer('broj');
            $table->string('mesto');
            $table->string('opstina');
            $table->string('grad');
            $table->string('postanski_broj');
            $table->enum('trajanje_prebivalista' , ['stalna', 'privremena']);
                
            $table->enum('uloga_adrese' , ['nova', 'stara']);
            
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void //kada radimo rollback, uvek moraju da budu suprotne
    {
        Schema::dropIfExists('adrese');
    }
};
