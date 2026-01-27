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
        Schema::create('zahtevi', function (Blueprint $table) {
            $table->id();
            
            $table->enum('tip_zahteva', ['prebivaliste', 'bracni_status']);          

            $table->string('status');

            $table->timestamp('datum_kreiranja')->nullable();

           $table->foreignId('korisnik_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            
            $table->enum('tip_promene', ['razvod', 'sklapanje_braka'])->nullable();
            $table->string('ime_partnera')->nullable();
            $table->string('prezime_partnera')->nullable();
            $table->date('datum_rodjenja_partnera')->nullable();
            $table->enum('partner_pol', ['M', 'Z'])->nullable(); 
            $table->string('broj_licnog_dokumenta')->nullable();
            $table->string('broj_licnog_dokumenta_partnera')->nullable();
            $table->date('datum_promene')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zahtevi');
    }
};
