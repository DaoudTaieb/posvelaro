<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class VelaroUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     * Passwords are stored in plain text (shared with the legacy desktop app),
     * but some rows may contain a bcrypt hash.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = (string) ($credentials['password'] ?? '');
        $storedPassword = (string) $user->getAuthPassword();

        // Empty / NULL stored password never authenticates
        if ($storedPassword === '' || $plain === '') {
            return false;
        }

        // bcrypt hash ($2y$, $2a$, $2b$)
        if (preg_match('/^\$2[aby]\$/', $storedPassword)) {
            return password_verify($plain, $storedPassword);
        }

        // Fallback to plain text comparison (constant-time)
        return hash_equals($storedPassword, $plain);
    }

    /**
     * Disable automatic password rehashing on login.
     * This prevents Laravel from converting plain text passwords to bcrypt hashes.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // Do nothing to prevent password from being modified in the database
    }
}
