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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('imeprezime');

            $table->date('datum_rodjenja')->nullable();
            $table->enum('pol', ['M', 'Z'])->nullable();

            $table->enum('tip_korisnika', ['domaci', 'strani', 'admin']);

            $table->string('jmbg')->nullable();
            $table->string('broj_pasosa')->nullable();
            $table->string('drzavljanstvo')->nullable();
            $table->string('broj_zaposlenog')->nullable();

            $table->timestamp('datum_kreiranja_naloga')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // samo ako koristiš verifikaciju
            $table->string('password');
            $table->rememberToken();

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
