<?php

namespace App\Support;

/**
 * Codes lus par une douchette (lecteur de codes-barres USB en émulation clavier).
 *
 * La plupart des douchettes sortent d'usine en clavier QWERTY américain. Branchées sur un poste
 * Windows en AZERTY (cas normal en Tunisie et en France), elles « tapent » les touches QWERTY :
 * le code 123456 arrive sous la forme &é"'(- et la lettre A devient Q. Le lecteur n'est pas en
 * cause : il suffit de relire le code comme s'il avait été tapé sur un clavier QWERTY.
 *
 * variantes() donne le code tel quel puis ses corrections plausibles, dans l'ordre où il faut les
 * essayer. Le code reçu est toujours essayé en premier : une référence contenant réellement
 * un tiret ou un « è » n'est jamais modifiée si elle existe telle quelle.
 */
class CodeBarre
{
    /** Rangée des chiffres : caractère AZERTY obtenu (sans Maj) => chiffre voulu. */
    private const CHIFFRES_AZERTY = [
        '&' => '1', 'é' => '2', '"' => '3', "'" => '4', '(' => '5',
        '-' => '6', 'è' => '7', '_' => '8', 'ç' => '9', 'à' => '0',
    ];

    /** Lettres et ponctuation échangées entre QWERTY et AZERTY : caractère reçu => caractère voulu. */
    private const LETTRES_AZERTY = [
        'A' => 'Q', 'Q' => 'A', 'Z' => 'W', 'W' => 'Z',
        'a' => 'q', 'q' => 'a', 'z' => 'w', 'w' => 'z',
        ',' => 'M', ';' => ',', ':' => '.', '!' => '/', 'M' => ':', 'm' => ';',
    ];

    /** Code nettoyé : espaces de début et de fin, caractères de contrôle (suffixe Tab, CR, LF) retirés. */
    public static function nettoyer(?string $code): string
    {
        $code = preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $code) ?? '';

        return trim($code);
    }

    /**
     * Le code reçu, puis le même code relu en QWERTY (chiffres seuls, puis chiffres et lettres).
     *
     * @return array<int, string> sans doublon, le code reçu en premier
     */
    public static function variantes(?string $code): array
    {
        $code = self::nettoyer($code);
        if ($code === '') {
            return [];
        }

        $chiffres = strtr($code, self::CHIFFRES_AZERTY);
        $complet = strtr($chiffres, self::LETTRES_AZERTY);

        return array_values(array_unique([$code, $chiffres, $complet]));
    }

    /** Vrai si le code ressemble à une saisie QWERTY reçue sur un poste AZERTY. */
    public static function semblePermute(?string $code): bool
    {
        $code = self::nettoyer($code);

        return $code !== '' && strtr($code, self::CHIFFRES_AZERTY) !== $code;
    }
}
