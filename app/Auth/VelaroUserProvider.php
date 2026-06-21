<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

use Illuminate\Support\Facades\Hash;

class VelaroUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     * Some users have plain text passwords, others have hashed passwords.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'];
        $storedPassword = $user->getAuthPassword();

        // Check if the stored password looks like a bcrypt hash
        if (strpos($storedPassword, '$2y$') === 0) {
            return Hash::check($plain, $storedPassword);
        }

        // Fallback to plain text comparison
        return $plain === $storedPassword;
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
