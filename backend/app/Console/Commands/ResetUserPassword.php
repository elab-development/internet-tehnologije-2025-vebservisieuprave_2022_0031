<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resetuje lozinku korisnika na novu';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Korisnik sa emailom $email nije pronađen.");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Lozinka korisnika $email je uspešno resetovana na: $password");

        return 0;
    }
}
