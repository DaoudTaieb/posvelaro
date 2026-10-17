<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Lecture des codes-barres (douchette) : un même code peut être un article, une carte fidélité
 * (clients.barcode) ou un bon d'achat Golden Pos. Utilisé par la caisse et par le test de
 * douchette de Paramètres > Imprimantes et douchettes.
 */
class DouchetteService
{
    public function __construct(private readonly FideliteService $fidelite)
    {
    }

    /**
     * Identifie un code lu : article, carte fidélité ou bon d'achat. Le code reçu est essayé en premier,
     * puis ses relectures QWERTY (CodeBarre::variantes).
     *
     * @return array{type: ?string, code: string, corrige: bool}
     */
    public function identifier(string $recu, int $siteid): array
    {
        $prefixeBon = strtoupper((string) $this->fidelite->parametres()['prefixe_bon']);

        foreach (\App\Support\CodeBarre::variantes($recu) as $code) {
            $corrige = $code !== $recu;

            if ($produit = $this->produit($code, $siteid)) {
                return ['type' => 'produit', 'code' => $code, 'corrige' => $corrige, 'produit' => $produit];
            }

            // Carte fidélité : n° de carte = clients.barcode (colonne partagée avec le bureau)
            $client = DB::table('clients')->where('barcode', $code)->first();
            if ($client) {
                return ['type' => 'client', 'code' => $code, 'corrige' => $corrige, 'client' => $this->enrichirClient($client)];
            }

            // Bon d'achat imprimé par Golden Pos (préfixe obligatoire : un nombre seul reste un article inconnu)
            if ($prefixeBon !== '' && str_starts_with(strtoupper($code), $prefixeBon) && ($bon = $this->fidelite->trouverBon($code))) {
                return ['type' => 'bon', 'code' => $code, 'corrige' => $corrige, 'bon' => [
                    'numero' => $bon->numero,
                    'montant' => (float) $bon->montant,
                    'date_expiration' => $bon->date_expiration,
                    'utilisable' => !$bon->utilise && !$bon->annule && $bon->date_expiration >= now()->toDateString(),
                ]];
            }
        }

        return ['type' => null, 'code' => $recu, 'corrige' => false, 'message' => 'Code inconnu : ' . $recu];
    }

    /** Article (variante) correspondant exactement à un code-barres, un code ou une référence. */
    public function produit(string $code, int $siteid): ?object
    {
        // Chercher par barcode2, produitcode, ou reference (correspondance exacte)
        $product = DB::table('produit2s')
            ->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('vproduit2stocks', function($join) use ($siteid) {
                $join->on('vproduit2stocks.produit2id', '=', 'produit2s.produit2id')
                     ->where('vproduit2stocks.siteid', '=', $siteid);
            })
            ->where(function($q) use ($code) {
                $q->where('produits.barcode2', $code)
                  ->orWhere('produits.produitcode', $code)
                  ->orWhere('produits.reference', $code)
                  ->orWhere('produit2s.barcode2', $code)
                  ->orWhere('produit2s.produit2code', $code);
            })
            ->select(
                'produit2s.produit2id',
                'produits.produitid',
                'produits.reference',
                'produits.produitcode',
                'produits.produitlibelle',
                'produits.familleid',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'couleurs.couleurlibelle',
                'tailles.taillelibelle',
                'produits.ttc_vente',
                DB::raw('COALESCE(vproduit2stocks.qtestock, 0) as total_stock'),
                'sousfamilles.is_loyalty_enabled'
            )
            // Variante exacte et nombre de variantes lus dans la même requête (un seul aller-retour par scan)
            ->selectRaw('COALESCE(produit2s.barcode2 = ? OR produit2s.produit2code = ?, false) AS gp_variante_exacte', [$code, $code])
            ->selectRaw('(SELECT count(*) FROM produit2s v WHERE v.produitid = produits.produitid) AS gp_nb_variantes')
            ->orderByRaw('CASE WHEN produit2s.barcode2 = ? OR produit2s.produit2code = ? THEN 0 ELSE 1 END', [$code, $code])
            ->first();

        // Code commun à toutes les variantes (référence / code article) : laisser le caissier choisir
        if ($product) {
            if ($product->produit2id && !$product->gp_variante_exacte && (int) $product->gp_nb_variantes > 1) {
                $product->choisir_variante = true;
            }
            unset($product->gp_variante_exacte, $product->gp_nb_variantes);
        }

        return $product;
    }

    /** Fiche client complétée des informations fidélité attendues par la caisse. */
    public function enrichirClient(object $client): object
    {
        $etat = $this->fidelite->etat($client);
        $client->loyalty_tier = $etat['palier'];
        $client->fidelite_ventes = $etat['ventes'];
        $client->fidelite_expiree = $etat['expiree'];
        $client->num_fidelite = $client->barcode ?? null; // n° de carte = colonne barcode (partagée avec le bureau)
        $client->soldefidelite = $etat['bons_disponibles'];
        $client->bons_disponibles = $etat['eligible'] ? $this->fidelite->bonsDisponibles((int) $client->clientid)->values() : [];

        return $client;
    }
}
