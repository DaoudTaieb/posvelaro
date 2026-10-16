<?php

namespace App\Services;

use App\Exceptions\VenteRefusee;
use Illuminate\Support\Facades\DB;

/**
 * Préparation des lignes et des totaux d'un ticket de caisse.
 *
 * Chaque ligne reçue de la caisse est rattachée à sa variante (produit2s), reçoit la TVA réelle
 * de l'article (taxefamilles.tvavente) et des montants arrondis au millime. Une ligne incohérente
 * lève VenteRefusee : un article ne doit jamais sortir du stock sans être payé.
 */
class TicketService
{
    /**
     * @param  array<int, array<string, mixed>> $lignes lignes envoyées par la caisse
     * @return array<int, array<string, mixed>> lignes prêtes pour detctickets (hors colonnes fixes)
     */
    public function preparerLignes(array $lignes): array
    {
        // Articles de toutes les lignes en UNE requête (au lieu de deux par ligne) : la caisse envoie
        // l'identifiant de variante ; les autres cas (code, produitid) passent par resoudreVariante().
        $ids = [];
        foreach ($lignes as $ligne) {
            $id = (string) ($ligne['produit2id'] ?? '');
            if ($id !== '' && ctype_digit($id) && strlen($id) <= 18) {
                $ids[$id] = true;
            }
        }
        $produits = $ids ? $this->infosProduits(array_keys($ids)) : [];

        $detLignes = [];
        foreach ($lignes as $ligne) {
            $libelle = $ligne['designation'] ?? $ligne['reference'] ?? '';
            $id = (string) ($ligne['produit2id'] ?? '');
            if (isset($produits[$id])) {
                $produit2id = (int) $id;
                $produit = $produits[$id];
            } else {
                $produit2id = $this->resoudreVariante($ligne);
                if (!$produit2id) {
                    throw new VenteRefusee("Erreur : La variante de l'article " . ($ligne['reference'] ?? '') . ' est introuvable.');
                }
                $produit = $this->infosProduits([$produit2id])[(string) $produit2id] ?? null;
            }

            $tva = (float) ($produit->tvavente ?? 0);
            $qte = (float) ($ligne['qte'] ?? 0);
            $prix = (float) ($ligne['prix'] ?? 0);
            $total = round((float) ($ligne['total'] ?? 0), 3);
            $prixNet = round((float) ($ligne['prixNet'] ?? $prix), 3);

            if (abs($qte) < 0.0005) {
                throw new VenteRefusee("La ligne {$libelle} a une quantité nulle : supprimez-la du ticket.");
            }
            if (abs($total - $qte * $prixNet) > 0.01 + 0.0005 * abs($qte)) {
                throw new VenteRefusee("Montant incohérent sur la ligne {$libelle} : le total ne correspond pas à quantité × prix net.");
            }

            $brut = round($prix * $qte, 3);
            $htNet = round($total / (1 + $tva / 100), 3);
            $htBrut = round($brut / (1 + $tva / 100), 3);

            $detLignes[] = [
                'produitid' => $produit->produitid,
                'produit2id' => $produit2id,
                'taxefamilleid' => $produit->taxefamilleid ?? 7,
                'ht' => $htBrut / $qte,
                'ttc' => $prix,
                'qte' => $qte,
                'totalht' => $htBrut,
                'remise' => (float) ($ligne['remise'] ?? 0),
                'totalhtnet' => $htNet,
                'tva' => $tva,
                'vtva' => $total - $htNet,
                'totalttc' => $brut,
                'totalttcnet' => $total,
                'puttcnet' => $prixNet,
                'htachat' => (float) ($produit->ht_achat ?? 0),
                'ttcachat' => (float) ($produit->ttc_achat ?? 0),
                'detventeid' => ($qte < 0 && !empty($ligne['detventeid'])) ? (int) $ligne['detventeid'] : 0,
            ];
        }

        return $detLignes;
    }

    /**
     * Totaux de l'entête (le trigger cticket_calc les recalcule aussi à partir des lignes).
     *
     * @return array{totalqte: float, totalttc: float, totalnetht: float, totaltva: float, totalbrutht: float, vremise: float}
     */
    public function totaux(array $detLignes): array
    {
        $totalttc = round(array_sum(array_column($detLignes, 'totalttcnet')), 3);

        return [
            'totalqte' => array_sum(array_column($detLignes, 'qte')),
            'totalttc' => $totalttc,
            'totalnetht' => array_sum(array_column($detLignes, 'totalhtnet')),
            'totaltva' => array_sum(array_column($detLignes, 'vtva')),
            'totalbrutht' => array_sum(array_column($detLignes, 'totalht')),
            'vremise' => array_sum(array_column($detLignes, 'totalttc')) - $totalttc,
        ];
    }

    /**
     * Article, famille de taxe, prix d'achat et TVA de plusieurs variantes, indexés par produit2id (texte).
     *
     * @param  array<int, int|string> $produit2ids
     * @return array<string, object>
     */
    private function infosProduits(array $produit2ids): array
    {
        $resultat = [];
        $lignes = DB::table('produit2s')
            ->join('produits', 'produit2s.produitid', '=', 'produits.produitid')
            ->leftJoin('taxefamilles', 'produits.taxefamilleid', '=', 'taxefamilles.taxefamilleid')
            ->whereIn('produit2s.produit2id', $produit2ids)
            ->select('produit2s.produit2id', 'produits.produitid', 'produits.taxefamilleid', 'produits.ht_achat', 'produits.ttc_achat', 'taxefamilles.tvavente')
            ->get();
        foreach ($lignes as $ligne) {
            $resultat[(string) $ligne->produit2id] = $ligne;
        }

        return $resultat;
    }

    /** Variante (produit2id) d'une ligne du panier, à partir de l'identifiant ou du code saisi. */
    public function resoudreVariante(array $ligne): ?int
    {
        if (!empty($ligne['produit2id']) && DB::table('produit2s')->where('produit2id', $ligne['produit2id'])->exists()) {
            return (int) $ligne['produit2id'];
        }
        foreach (['produitid', 'articleid'] as $cle) {
            if (!empty($ligne[$cle])) {
                $id = DB::table('produit2s')->where('produitid', $ligne[$cle])->value('produit2id');
                if ($id) {
                    return (int) $id;
                }
            }
        }
        if (!empty($ligne['code'])) {
            $id = DB::table('produit2s')
                ->join('produits', 'produit2s.produitid', '=', 'produits.produitid')
                ->where(fn ($q) => $q->where('produits.reference', $ligne['code'])->orWhere('produit2s.barcode2', $ligne['code']))
                ->value('produit2s.produit2id');

            return $id ? (int) $id : null;
        }

        return null;
    }
}
