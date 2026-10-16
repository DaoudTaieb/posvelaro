<?php

use App\Support\Identifiants;
use Illuminate\Database\Migrations\Migration;

/**
 * Réaligne les séquences PostgreSQL des tables où la caisse web a écrit des identifiants en
 * max()+1 (avant l'utilisation de App\Support\Identifiants). Sans cela, la prochaine insertion
 * du logiciel de bureau (nextval) pourrait réutiliser un identifiant existant.
 *
 * Idempotente : une séquence n'est modifiée que si elle est en retard sur max(identifiant).
 */
return new class extends Migration
{
    private const TABLES = [
        ['retailconfigs', 'retailconfigid'],
        ['caisseconfigs', 'caisseconfigid'],
        ['caisses', 'caisseid'],
        ['users', 'userid'],
        ['employees', 'employeeid'],
        ['userdroits', 'userdroitid'],
        ['userdroitdets', 'userdroitdetid'],
        ['bontransferts', 'bontransfertid'],
        ['detbontransferts', 'detbontransfertid'],
        ['demandetransferts', 'demandetransfertid'],
        ['detdemandetransferts', 'detdemandetransfertid'],
        ['clients', 'clientid'],
    ];

    public function up(): void
    {
        foreach (self::TABLES as [$table, $colonne]) {
            Identifiants::synchroniserSequence($table, $colonne);
        }
    }

    public function down(): void
    {
        // Rien à défaire : une séquence réalignée reste valide.
    }
};
