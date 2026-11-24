<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Server-side recovery: set a user's password from the command line.
 *
 *   php artisan user:set-password manjot@ajath.com
 */
class SetUserPasswordCommand extends Command
{
    protected $signature = 'user:set-password {email : Email address of the account}';

    protected $description = 'Set a new password for a user account (prompts for the password).';

    public function handle(): int
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($this->argument('email')))])->first();
        if (!$user) {
            $this->error('No user with that email.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('New password (min 8 characters)');
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }
        if ($password !== (string) $this->secret('Confirm password')) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        $user->forceFill([
            'password' => Hash::make($password),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->info("Password updated for {$user->name} <{$user->email}>.");
        return self::SUCCESS;
    }
}
