<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Liste des tickets de caisse.
     */
    public function index(Request $request)
    {
        $query = \Illuminate\Support\Facades\DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->leftJoin('users as caissiers', 'ctickets.caissierid', '=', 'caissiers.userid')
            ->leftJoin('employees as vendeurs', 'ctickets.employeeid', '=', 'vendeurs.employeeid')
            ->leftJoin('statutdocuments', 'ctickets.statutdocumentid', '=', 'statutdocuments.statutdocumentid')
            ->select(
                'ctickets.*',
                'clients.nom as client_nom',
                'clients.clientcode as client_code',
                'caissiers.login as caissier_nom',
                'vendeurs.nom as vendeur_nom',
                'vendeurs.prenom as vendeur_prenom',
                'vendeurs.matricule as vendeur_code',
                'statutdocuments.libelle as statut_libelle',
                'statutdocuments.couleurcode as statut_couleur'
            )
            ->orderBy('ctickets.cticketdate', 'desc');

        // Apply filters
        if ($request->filled('date_du')) {
            $query->whereDate('ctickets.cticketdate', '>=', $request->date_du);
        }
        if ($request->filled('date_au')) {
            $query->whereDate('ctickets.cticketdate', '<=', $request->date_au);
        }
        if ($request->filled('statut')) {
            $query->where('ctickets.statutdocumentid', $request->statut);
        }
        if ($request->filled('client')) {
            $query->where('ctickets.clientid', $request->client);
        }
        if ($request->filled('caissier')) {
            $query->where(function($q) use ($request) {
                $q->where('ctickets.caissierid', $request->caissier)
                  ->orWhere('ctickets.userid', $request->caissier);
            });
        }
        if ($request->filled('vendeur')) {
            $query->where('ctickets.employeeid', $request->vendeur);
        }

        // Apply column filters
        $colMap = [
            'f_date' => 'ctickets.cticketdate',
            'f_numero' => 'ctickets.numerointerne', // or cticketnumero
            'f_statut' => 'statutdocuments.libelle',
            'f_code' => 'clients.clientcode',
            'f_client' => 'clients.nom',
            'f_code_vendeur' => 'vendeurs.matricule',
            'f_vendeur' => 'vendeurs.nom',
        ];

        foreach ($colMap as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) {
                if ($reqKey == 'f_date') {
                    $query->whereDate($dbCol, $request->{$reqKey});
                } elseif ($reqKey == 'f_client') {
                    $query->where(function($q) use ($request, $reqKey) {
                        $q->where('clients.nom', 'ilike', '%' . $request->{$reqKey} . '%')
                          ->orWhere('clients.prenom', 'ilike', '%' . $request->{$reqKey} . '%');
                    });
                } elseif ($reqKey == 'f_vendeur') {
                    $query->where(function($q) use ($request, $reqKey) {
                        $q->where('vendeurs.nom', 'ilike', '%' . $request->{$reqKey} . '%')
                          ->orWhere('vendeurs.prenom', 'ilike', '%' . $request->{$reqKey} . '%');
                    });
                } else {
                    $query->where($dbCol, 'ilike', '%' . $request->{$reqKey} . '%');
                }
            }
        }

        // Check if any filter is applied
        $hasFilters = $request->filled('date_du') || $request->filled('date_au') || 
                      $request->filled('statut') || $request->filled('client') || 
                      $request->filled('caissier') || $request->filled('vendeur') || 
                      $request->filled('q');

        foreach ($colMap as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) {
                $hasFilters = true;
                break;
            }
        }

        if ($hasFilters) {
            if ($request->ajax()) {
                \Illuminate\Support\Facades\Log::info('AJAX Search Query: ' . $query->toSql(), $query->getBindings());
                \Illuminate\Support\Facades\Log::info('Request Filters: ', $request->all());

                $totalQte = (clone $query)->sum('ctickets.totalqte');
                $totalBrutHt = (clone $query)->sum('ctickets.totalbrutht');
                $totalRemise = (clone $query)->sum(\Illuminate\Support\Facades\DB::raw('ctickets.totalbrutht - ctickets.totalnetht'));
                $totalNetHt = (clone $query)->sum('ctickets.totalnetht');
                $totalTva = (clone $query)->sum('ctickets.totaltva');
                $totalTtc = (clone $query)->sum('ctickets.totalttc');
                $totalAcompte = (clone $query)->sum('ctickets.acompte');
                $totalReste = (clone $query)->sum('ctickets.netapayer');

                $tickets = $query->paginate(15);
                $html = view('vente.tickets.partials.table_body', compact('tickets'))->render();

                return response()->json([
                    'html' => $html,
                    'pagination' => $tickets->links()->toHtml(),
                    'totals' => [
                        'qte' => number_format($totalQte ?? 0, 0, ',', ' '),
                        'brut_ht' => number_format($totalBrutHt ?? 0, 3, ',', ' '),
                        'remise' => number_format($totalRemise ?? 0, 3, ',', ' '),
                        'net_ht' => number_format($totalNetHt ?? 0, 3, ',', ' '),
                        'tva' => number_format($totalTva ?? 0, 3, ',', ' '),
                        'ttc' => number_format($totalTtc ?? 0, 3, ',', ' '),
                        'acompte' => number_format($totalAcompte ?? 0, 3, ',', ' '),
                        'reste' => number_format($totalReste ?? 0, 3, ',', ' ')
                    ]
                ]);
            }

            $tickets = $query->paginate(15);
        } else {
            // Return empty paginator if no filters
            $tickets = \Illuminate\Pagination\Paginator::resolveCurrentPage() == 1 
                ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) 
                : $query->whereRaw('1 = 0')->paginate(15);
        }
        
        $clients = \Illuminate\Support\Facades\DB::table('clients')->select('clientid', 'nom', 'code')->orderBy('nom')->get();
        $statuts = \Illuminate\Support\Facades\DB::table('statutdocuments')
            ->select('statutdocumentid', 'libelle')
            ->where('statutdocumentid', '>', 0)
            ->get();
        $caissiers = \Illuminate\Support\Facades\DB::table('users')->select('userid', 'login')->get();
        $vendeurs = \Illuminate\Support\Facades\DB::table('employees')->select('employeeid', 'nom', 'prenom')->where('isvendeur', true)->get();

        return view('vente.tickets.index', compact('tickets', 'clients', 'statuts', 'caissiers', 'vendeurs'));
    }

    /**
     * Affiche les détails d'un ticket spécifique (receipt format).
     */
    public function show($id)
    {
        $ticket = \Illuminate\Support\Facades\DB::table('ctickets')
            ->where('cticketid', $id)
            ->first();

        if (!$ticket) {
            abort(404, 'Ticket non trouvé');
        }

        // Get company info
        $societe = \Illuminate\Support\Facades\DB::table('societes')->first();

        // Get caisse
        $caisse = \Illuminate\Support\Facades\DB::table('caisses')
            ->where('caisseid', $ticket->caisseid)
            ->first();

        // Get caissier name
        $caissier = \Illuminate\Support\Facades\DB::table('users')
            ->where('userid', $ticket->caissierid)
            ->first();
        $caissier_nom = $caissier->login ?? '';

        // If caissierid is 0, try userid
        if (empty($caissier_nom) || $ticket->caissierid == 0) {
            $userCreator = \Illuminate\Support\Facades\DB::table('users')
                ->where('userid', $ticket->userid)
                ->first();
            $caissier_nom = $userCreator->login ?? '';
        }

        // Get vendeur name
        $vendeur = \Illuminate\Support\Facades\DB::table('employees')
            ->where('employeeid', $ticket->employeeid)
            ->first();
        // Avoid duplicate if nom == prenom
        $vNom = $vendeur->nom ?? '';
        $vPrenom = $vendeur->prenom ?? '';
        $vendeur_nom = (strtolower(trim($vNom)) === strtolower(trim($vPrenom)))
            ? trim($vNom)
            : trim("$vNom $vPrenom");

        // Get client name
        $client = \Illuminate\Support\Facades\DB::table('clients')
            ->where('clientid', $ticket->clientid)
            ->first();
        $client_nom = trim(($client->nom ?? '') . ' ' . ($client->prenom ?? ''));
        if (empty(trim($client_nom))) {
            $client_nom = 'PASSAGER';
        }

        // Get detail lines with product info + variant (produit2s: couleur/taille)
        $lignes = \Illuminate\Support\Facades\DB::table('detctickets')
            ->leftJoin('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->where('detctickets.cticketid', $id)
            ->select(
                'detctickets.*',
                'produits.produitlibelle as produit_libelle',
                'produits.reference as produit_ref',
                'produits.produitcode as produit_code',
                'couleurs.couleurlibelle as couleur_libelle',
                'tailles.taillelibelle as taille_libelle'
            )
            ->get();

        $total_articles = $lignes->count();

        // Compute remise amount (remise field is a PERCENTAGE, not an amount)
        $remise_montant = (float)($ticket->totalbrutht ?? 0) - (float)($ticket->totalttc ?? 0);

        // Get payment details
        $reglements = \Illuminate\Support\Facades\DB::table('creglementdets')
            ->leftJoin('creglements', 'creglementdets.creglementid', '=', 'creglements.creglementid')
            ->leftJoin('modereglements', 'creglements.modereglementid', '=', 'modereglements.modereglementid')
            ->where('creglementdets.documentid', $id)
            ->where('creglementdets.typeid', 1)
            ->select(
                'creglementdets.montant',
                'creglements.date',
                'modereglements.libelle as mode_libelle'
            )
            ->get();

        return view('vente.tickets.show', compact(
            'ticket', 'societe', 'caisse', 'caissier_nom', 'vendeur_nom',
            'client_nom', 'lignes', 'total_articles', 'reglements', 'remise_montant'
        ));
    }
}
