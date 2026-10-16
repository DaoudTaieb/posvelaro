<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class VelaroUserProvider extends EloquentUserProvider
{
    /**
     * Charge l'utilisateur de la session avec son rôle, ses droits bloqués et son site en UNE seule
     * requête (au lieu de quatre à chaque page) : avec une base distante, chaque aller-retour coûte
     * plusieurs dizaines de millisecondes. Les données restent lues à chaque requête (pas de cache),
     * donc un changement fait depuis le logiciel de bureau s'applique immédiatement.
     */
    public function retrieveById($identifier)
    {
        if (!is_numeric($identifier)) {
            return null;
        }

        $ligne = DB::selectOne(<<<'SQL'
            SELECT u.*,
                   row_to_json(r)::text AS gp_role,
                   CASE WHEN s.siteid IS NULL THEN NULL
                        ELSE json_build_object('siteid', s.siteid, 'libelle', s.libelle)::text END AS gp_site,
                   (SELECT COALESCE(json_object_agg(t.abreviation, t.libelle), '{}')::text
                      FROM userdroitdets d
                      JOIN typedroits t ON t.typedroitid = d.typedroitid
                     WHERE d.userdroitid = u.userdroitid AND d.bloque AND t.abreviation IS NOT NULL) AS gp_bloques
              FROM users u
              LEFT JOIN userdroits r ON r.userdroitid = u.userdroitid
              LEFT JOIN sites s ON s.siteid = u.siteid
             WHERE u.userid = ?
            SQL, [(int) $identifier]);

        if (!$ligne) {
            return null;
        }

        $attributs = (array) $ligne;
        $role = json_decode($attributs['gp_role'] ?? 'null');
        $site = json_decode($attributs['gp_site'] ?? 'null');
        $bloques = json_decode($attributs['gp_bloques'] ?? '{}', true) ?: [];
        unset($attributs['gp_role'], $attributs['gp_site'], $attributs['gp_bloques']);

        $user = $this->createModel()->newFromBuilder($attributs);
        $user->precharge = ['role' => $role, 'bloques' => $bloques, 'site' => $site];

        return $user;
    }

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
