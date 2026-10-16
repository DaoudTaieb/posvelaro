<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Pagination en UNE requête : ->paginerVite(20) remplace ->paginate(20).
 *
 * paginate() fait deux allers-retours (COUNT puis la page). Avec une base distante, chacun coûte
 * plusieurs dizaines de millisecondes. Ici le total est lu avec la page (count(*) OVER ()).
 * Résultat identique à paginate() : même LengthAwarePaginator, même nom de paramètre de page.
 *
 * À ne pas utiliser sur une requête DISTINCT (le total compterait les lignes avant dédoublonnage).
 * Les requêtes GROUP BY sont correctes : la fenêtre est calculée après le regroupement.
 */
class PaginationRapide
{
    public static function installer(): void
    {
        Builder::macro('paginerVite', function ($perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator {
            /** @var Builder $this */
            $perPage = max(1, (int) $perPage ?: 15);
            $page = $page ?: Paginator::resolveCurrentPage($pageName);

            $requete = clone $this;
            if (empty($requete->columns)) {
                $requete->columns = ['*'];
            }
            $lignes = $requete->selectRaw('count(*) OVER () AS gp_total')->forPage($page, $perPage)->get();

            if ($lignes->isNotEmpty()) {
                $total = (int) $lignes->first()->gp_total;
                foreach ($lignes as $ligne) {
                    unset($ligne->gp_total);
                }
            } else {
                // Page vide : au-delà de la dernière page (total réel nécessaire) ou aucun résultat
                $total = $page > 1 ? $this->getCountForPagination() : 0;
            }

            return new LengthAwarePaginator($lignes, $total, $perPage, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });
    }
}
