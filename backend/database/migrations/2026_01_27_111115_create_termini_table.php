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
        Schema::create('termini', function (Blueprint $table) {
            $table->id();
            $table->enum('tip_dokumenta', ['licna_karta', 'pasos']); 
            $table->string('lokacija');
            $table->dateTime('datum_vreme');  
            $table->foreignId('korisnik_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termini');
    }
};
