<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Support\SiteCourant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Articles dont le stock calculé du site est négatif (ventes ou transferts sans entrée suffisante).
 * À régulariser par une entrée (bon d'entrée, réception de transfert) ou un inventaire.
 */
class StockNegatifController extends Controller
{
    public function index(Request $request)
    {
        $siteid = SiteCourant::id();

        $query = DB::table('stock2s as s')
            ->join('produits as p', 'p.produitid', '=', 's.produitid')
            ->join('produit2s as v', 'v.produit2id', '=', 's.produit2id')
            ->leftJoin('couleurs as c', 'c.couleurid', '=', 'v.couleurid')
            ->leftJoin('tailles as t', 't.tailleid', '=', 'v.tailleid')
            ->leftJoin('sousfamilles as sf', 'sf.sousfamilleid', '=', 'p.sousfamilleid')
            ->where('s.siteid', $siteid)
            ->where('s.qtestock', '<', 0);

        if ($request->filled('q')) {
            $q = '%' . trim((string) $request->q) . '%';
            $query->where(fn ($w) => $w->where('p.reference', 'ilike', $q)
                ->orWhere('p.produitcode', 'ilike', $q)
                ->orWhere('p.produitlibelle', 'ilike', $q));
        }

        $totaux = (clone $query)->selectRaw('COUNT(*) as lignes, COALESCE(SUM(s.qtestock), 0) as quantite')->first();

        $query->select(
                's.produit2id',
                'p.produitcode',
                'p.reference',
                'p.produitlibelle',
                'sf.sousfamillelibelle',
                'c.couleurlibelle',
                't.taillelibelle',
                's.qtestock',
                'p.ttc_vente',
                // Dernière vente de la variante sur le site (pour retrouver l'origine de l'écart)
                DB::raw("(SELECT MAX(ct.cticketdate) FROM detctickets d JOIN ctickets ct ON ct.cticketid = d.cticketid
                          WHERE d.produit2id = s.produit2id AND ct.siteid = s.siteid AND ct.enattente = false) as derniere_vente")
            );

        $stocks = $query->orderBy('s.qtestock')->orderBy('p.reference')->paginate(50)->withQueryString();
        $site = DB::table('sites')->where('siteid', $siteid)->value('libelle');

        return view('stock.negatifs.index', compact('stocks', 'totaux', 'site'));
    }
}
