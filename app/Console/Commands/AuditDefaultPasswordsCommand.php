<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Finds accounts that still use one of the old shared default passwords
 * (password123 / Basecamp2026! / Ajath2026!) and optionally replaces them.
 *
 *   php artisan security:default-passwords            # list only
 *   php artisan security:default-passwords --reset    # give those accounts a random password
 */
class AuditDefaultPasswordsCommand extends Command
{
    protected $signature = 'security:default-passwords {--reset : Replace default passwords with random ones}';

    protected $description = 'List (and optionally reset) accounts still using the old shared default passwords.';

    private const DEFAULTS = ['password123', 'Basecamp2026!', 'Ajath2026!'];

    public function handle(): int
    {
        $found = [];
        User::orderBy('id')->chunk(100, function ($users) use (&$found) {
            foreach ($users as $user) {
                foreach (self::DEFAULTS as $pwd) {
                    if (Hash::check($pwd, (string) $user->password)) {
                        $found[] = $user;
                        break;
                    }
                }
            }
        });

        if (empty($found)) {
            $this->info('No accounts use a default password.');
            return self::SUCCESS;
        }

        $this->table(['ID', 'Name', 'Email', 'Joined?'], array_map(fn($u) => [
            $u->id, $u->name, $u->email, $u->email_verified_at ? 'yes' : 'pending invite',
        ], $found));

        if (!$this->option('reset')) {
            $this->warn(count($found) . ' account(s) use a default password. Re-run with --reset to replace them with random passwords.');
            $this->line('Affected people can then set a new password via their invitation link (pending invites), Google sign-in, or `php artisan user:set-password <email>`.');
            return self::SUCCESS;
        }

        foreach ($found as $user) {
            $user->forceFill(['password' => Hash::make(Str::random(40))])->save();
        }
        $this->info('Reset ' . count($found) . ' account(s) to random passwords.');

        return self::SUCCESS;
    }
}
