<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dokumenta', function (Blueprint $table) {
            $table->id();
            $table->string('nazivFajla'); 
            $table->string('putanja');    
            $table->enum('tipDokumeta', ['izvod_iz_knjige_vecnacih', 'presuda', 'licna_karta', 'pasos']); 
            $table->string('broj_dokumenta')->nullable(); 
            $table->string('organ_izdavanja')->nullable(); 
            $table->foreignId('zahtev_id')->constrained('zahtevi')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumenta');
    }
};
