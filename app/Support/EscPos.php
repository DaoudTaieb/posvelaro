<?php

namespace App\Support;

/**
 * Construction d'un flux ESC/POS (jeu de commandes Epson, reconnu par les imprimantes ticket 80 mm
 * courantes : Epson TM-T20, Xprinter, « 80mm Series Printer »...).
 *
 * Le texte est converti soit en page de code PC858 (accents français, commande ESC t 19),
 * soit en ASCII sans accents (option la plus sûre pour une imprimante générique).
 */
class EscPos
{
    private const ESC = "\x1B";
    private const GS = "\x1D";

    private string $flux = '';

    public function __construct(private readonly int $colonnes = 48, private readonly bool $accents = false)
    {
        $this->flux = self::ESC . '@'; // initialisation
        $this->flux .= $accents ? self::ESC . 't' . chr(19) : self::ESC . 't' . chr(0); // PC858 ou PC437
    }

    public function colonnes(): int
    {
        return $this->colonnes;
    }

    public function texte(string $texte): static
    {
        $this->flux .= $this->encoder($texte);
        return $this;
    }

    public function ligne(string $texte = ''): static
    {
        return $this->texte($texte)->retour();
    }

    public function retour(int $n = 1): static
    {
        $this->flux .= str_repeat("\n", max(1, $n));
        return $this;
    }

    /** 0 gauche, 1 centre, 2 droite */
    public function alignement(int $a): static
    {
        $this->flux .= self::ESC . 'a' . chr(max(0, min(2, $a)));
        return $this;
    }

    public function gras(bool $actif = true): static
    {
        $this->flux .= self::ESC . 'E' . chr($actif ? 1 : 0);
        return $this;
    }

    /** Taille des caractères : 1 = normale, 2 = double largeur et double hauteur */
    public function taille(int $facteur = 1): static
    {
        $f = max(1, min(8, $facteur)) - 1;
        $this->flux .= self::GS . '!' . chr(($f << 4) | $f);
        return $this;
    }

    public function separateur(string $car = '-'): static
    {
        return $this->ligne(str_repeat($car, $this->colonnes));
    }

    /**
     * Texte à gauche et à droite sur la même ligne (le texte de gauche est tronqué si besoin).
     * $facteur : taille des caractères en cours (2 en double largeur : deux fois moins de colonnes).
     */
    public function gaucheDroite(string $gauche, string $droite, int $facteur = 1): static
    {
        $largeur = intdiv($this->colonnes, max(1, $facteur));
        $droite = $this->ascii($droite);
        $place = max(1, $largeur - mb_strlen($droite) - 1);
        $gauche = mb_strimwidth($this->ascii($gauche), 0, $place, '');

        return $this->ligne($gauche . str_repeat(' ', max(1, $largeur - mb_strlen($gauche) - mb_strlen($droite))) . $droite);
    }

    /** Code-barres CODE128 (jeu B) avec le texte lisible dessous. */
    public function codeBarres(string $donnees, int $hauteur = 60): static
    {
        $donnees = preg_replace('/[^\x20-\x7E]/', '', $donnees) ?? '';
        if ($donnees === '') {
            return $this;
        }
        $donnees = '{B' . $donnees;
        $this->flux .= self::GS . 'h' . chr(max(1, min(255, $hauteur)))
            . self::GS . 'w' . chr(2)
            . self::GS . 'H' . chr(2)
            . self::GS . 'k' . chr(73) . chr(strlen($donnees)) . $donnees;

        return $this->retour();
    }

    /** Ouverture du tiroir-caisse (impulsion broche 2). */
    public function tiroir(): static
    {
        $this->flux .= self::ESC . 'p' . chr(0) . chr(25) . chr(250);
        return $this;
    }

    /** Avance du papier puis coupe partielle. */
    public function couper(): static
    {
        $this->flux .= self::GS . 'V' . chr(66) . chr(3);
        return $this;
    }

    public function flux(): string
    {
        return $this->flux;
    }

    private function encoder(string $texte): string
    {
        if ($this->accents) {
            $converti = @iconv('UTF-8', 'CP858//TRANSLIT', $texte);
            if ($converti !== false) {
                return $converti;
            }
        }

        return $this->ascii($texte);
    }

    private const TRANSLITTERATION = [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Á' => 'A',
        'ç' => 'c', 'Ç' => 'C',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'î' => 'i', 'ï' => 'i', 'í' => 'i', 'Î' => 'I', 'Ï' => 'I', 'Í' => 'I',
        'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'õ' => 'o', 'Ô' => 'O', 'Ö' => 'O', 'Ó' => 'O',
        'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ú' => 'U',
        'ÿ' => 'y', 'ñ' => 'n', 'Ñ' => 'N', 'œ' => 'oe', 'Œ' => 'OE', 'æ' => 'ae', 'Æ' => 'AE',
        '€' => 'EUR', '«' => '"', '»' => '"', '’' => "'", '‘' => "'", '“' => '"', '”' => '"',
        '–' => '-', '—' => '-', '…' => '...', '°' => 'o', "\u{00A0}" => ' ', "\u{202F}" => ' ', '×' => 'x',
    ];

    /** Translittération en ASCII imprimable (é -> e, « -> ", € -> EUR) ; tout autre caractère devient « ? ». */
    private function ascii(string $texte): string
    {
        $texte = strtr($texte, self::TRANSLITTERATION);

        return preg_replace('/[^\x0A\x20-\x7E]/u', '?', $texte) ?? $texte;
    }
}
