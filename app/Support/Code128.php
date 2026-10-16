<?php

namespace App\Support;

/**
 * Générateur de code-barres Code 128 (jeu B) en SVG inline, sans dépendance externe,
 * pour l'impression des bons d'achat sur le ticket (poste de caisse souvent hors ligne).
 */
class Code128
{
    /** Motifs barres/espaces des 107 symboles Code 128 (largeurs en modules). */
    private const MOTIFS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_B = 104;
    private const STOP = 106;

    /**
     * SVG du code-barres. Les caractères hors du jeu B (32..126) sont remplacés par un espace.
     */
    public static function svg(string $texte, int $hauteur = 40, int $module = 2, bool $afficherTexte = false): string
    {
        $texte = $texte === '' ? ' ' : $texte;
        $valeurs = [self::START_B];
        $somme = self::START_B;
        $i = 1;
        foreach (str_split($texte) as $car) {
            $code = ord($car);
            $valeur = ($code >= 32 && $code <= 126) ? $code - 32 : 0;
            $valeurs[] = $valeur;
            $somme += $valeur * $i++;
        }
        $valeurs[] = $somme % 103;
        $valeurs[] = self::STOP;

        $rects = '';
        $x = 10 * $module; // zone de silence
        foreach ($valeurs as $v) {
            $motif = self::MOTIFS[$v];
            $barre = true;
            foreach (str_split($motif) as $largeur) {
                $w = (int) $largeur * $module;
                if ($barre) {
                    $rects .= sprintf('<rect x="%d" y="0" width="%d" height="%d" fill="#000"/>', $x, $w, $hauteur);
                }
                $x += $w;
                $barre = !$barre;
            }
        }
        $largeurTotale = $x + 10 * $module;
        $hauteurTotale = $afficherTexte ? $hauteur + 14 : $hauteur;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" shape-rendering="crispEdges">%s',
            $largeurTotale, $hauteurTotale, $largeurTotale, $hauteurTotale, $rects
        );
        if ($afficherTexte) {
            $svg .= sprintf(
                '<text x="%d" y="%d" text-anchor="middle" font-family="monospace" font-size="11">%s</text>',
                (int) ($largeurTotale / 2), $hauteur + 11, htmlspecialchars($texte, ENT_QUOTES)
            );
        }

        return $svg . '</svg>';
    }
}
