<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drzavljani', function (Blueprint $table) {
            $table->id();
            $table->string('ime');
            $table->string('prezime');
            $table->date('datum_rodjenja')->nullable();
            $table->enum('pol', ['M', 'Z'])->nullable();
            $table->string('jmbg', 13)->unique(); // JMBG je jedinstven
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drzavljani');
    }
};