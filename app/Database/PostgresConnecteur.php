<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Connecteur PostgreSQL optimisé pour une base distante.
 *
 * Laravel envoie, après l'ouverture de la connexion, une requête « SET time zone » puis une requête
 * « SET search_path » : deux allers-retours réseau de plus à chaque page. Ici ces réglages partent
 * dans le paquet d'ouverture de la connexion (paramètre libpq « options »), sans aller-retour.
 *
 * Les « keepalives » TCP détectent une connexion coupée (box, NAT) au lieu de la laisser pendre :
 * utile avec les connexions persistantes (DB_PERSISTENT).
 */
class PostgresConnecteur extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $options = [];
        if (!empty($config['timezone'])) {
            $options[] = '-c TimeZone=' . $this->valeurSure($config['timezone']);
        }
        $searchPath = $config['search_path'] ?? $config['schema'] ?? null;
        if (is_string($searchPath) && $searchPath !== '') {
            $options[] = '-c search_path=' . $this->valeurSure($searchPath);
        }
        if ($options) {
            $dsn .= ";options='" . implode(' ', $options) . "'";
        }

        if (!empty($config['keepalives'])) {
            $dsn .= ';keepalives=1;keepalives_idle=60;keepalives_interval=10;keepalives_count=3';
        }

        if (!empty($config['connect_timeout'])) {
            $dsn .= ';connect_timeout=' . (int) $config['connect_timeout'];
        }

        return $dsn;
    }

    /** Fuseau et schéma déjà transmis à l'ouverture (getDsn) : pas de requête SET. */
    protected function configureTimezone($connection, array $config)
    {
    }

    protected function configureSearchPath($connection, $config)
    {
    }

    /** Valeur d'option libpq : lettres, chiffres, « _ / + - , » uniquement (pas d'espace ni de guillemet). */
    private function valeurSure(string $valeur): string
    {
        if (!preg_match('#^[A-Za-z0-9_/+\-,]+$#', $valeur)) {
            throw new \InvalidArgumentException("Valeur de connexion PostgreSQL invalide : {$valeur}");
        }

        return $valeur;
    }
}
