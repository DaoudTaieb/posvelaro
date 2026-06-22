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
        $dateDu = $request->input('date_du');
        if ($dateDu) {
            $query->whereDate('ctickets.cticketdate', '>=', $dateDu);
        }
        $dateAu = $request->input('date_au', now()->format('Y-m-d'));
        if ($dateAu) {
            $query->whereDate('ctickets.cticketdate', '<=', $dateAu);
        }

        if ($request->filled('statut')) {
            $statutId = $request->statut;
            $statutObj = \Illuminate\Support\Facades\DB::table('statutdocuments')->where('statutdocumentid', $statutId)->first();
            
            if ($statutObj) {
                $term = $statutObj->libelle;
                $query->where(function($q) use ($statutId, $term) {
                    $q->where('ctickets.statutdocumentid', $statutId);
                    
                    $termLower = mb_strtolower($term, 'UTF-8');
                    $isNonPaye = str_contains($termLower, 'non') || str_contains($termLower, 'imp');
                    $isPaye = !$isNonPaye && (str_contains($termLower, 'pay') || $termLower === 'payé');
                    $isAcompte = str_contains($termLower, 'acomp');

                    if ($isPaye) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('ctickets.statutdocumentid')
                                ->where('ctickets.netapayer', '<=', 0)
                                ->where('ctickets.totalttc', '>', 0);
                        });
                    }
                    if ($isAcompte) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('ctickets.statutdocumentid')
                                ->where('ctickets.acompte', '>', 0)
                                ->where(function($s2) {
                                    $s2->where('ctickets.netapayer', '>', 0)
                                       ->orWhere('ctickets.totalttc', '<=', 0);
                                });
                        });
                    }
                    if ($isNonPaye) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('ctickets.statutdocumentid')
                                ->where(function($s2) {
                                    $s2->where('ctickets.netapayer', '>', 0)
                                       ->orWhere('ctickets.totalttc', '<=', 0);
                                })
                                ->where(function($s2) {
                                    $s2->where('ctickets.acompte', '<=', 0)
                                       ->orWhereNull('ctickets.acompte');
                                });
                        });
                    }
                });
            } else {
                $query->where('ctickets.statutdocumentid', $statutId);
            }
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
            'f_numero' => 'ctickets.numerointerne',
            'f_statut' => 'statutdocuments.libelle',
            'f_code' => 'clients.clientcode',
            'f_client' => 'clients.nom',
            'f_code_vendeur' => 'vendeurs.matricule',
            'f_vendeur' => 'vendeurs.nom',
            'f_qte' => 'ctickets.totalqte',
            'f_brutht' => 'ctickets.totalbrutht',
            'f_remise' => 'ctickets.remise',
            'f_netht' => 'ctickets.totalnetht',
            'f_tva' => 'ctickets.totaltva',
            'f_ttc' => 'ctickets.totalttc',
            'f_brutttc' => 'ctickets.totalttc',
            'f_acompte' => 'ctickets.acompte',
            'f_reste' => 'ctickets.netapayer',
        ];

        foreach ($colMap as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) {
                $term = $request->{$reqKey};
                if ($reqKey == 'f_date') {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $term)) {
                        $query->whereDate('ctickets.cticketdate', $term);
                    } else {
                        $query->where(\Illuminate\Support\Facades\DB::raw("to_char(ctickets.cticketdate, 'DD/MM/YYYY')"), 'like', '%' . $term . '%');
                    }
                } elseif ($reqKey == 'f_numero') {
                    $query->where(function($q) use ($term) {
                        $q->where('ctickets.numerointerne', 'ilike', '%' . $term . '%')
                          ->orWhere('ctickets.cticketnumero', 'ilike', '%' . $term . '%');
                    });
                } elseif ($reqKey == 'f_statut') {
                    $query->where(function($q) use ($term) {
                        $termLower = mb_strtolower($term, 'UTF-8');
                        $isNonPaye = str_contains($termLower, 'non') || str_contains($termLower, 'imp');
                        $isPaye = !$isNonPaye && (str_contains($termLower, 'pay') || $termLower === 'payé');
                        $isAcompte = str_contains($termLower, 'acomp');

                        if ($isPaye) {
                            $q->where('statutdocuments.libelle', 'ilike', 'Payé')
                              ->orWhere(function($sub) {
                                  $sub->whereNull('ctickets.statutdocumentid')
                                      ->where('ctickets.netapayer', '<=', 0)
                                      ->where('ctickets.totalttc', '>', 0);
                              });
                        } elseif ($isNonPaye) {
                            $q->where('statutdocuments.libelle', 'ilike', 'Non Payé')
                              ->orWhere(function($sub) {
                                  $sub->whereNull('ctickets.statutdocumentid')
                                      ->where(function($s2) {
                                          $s2->where('ctickets.netapayer', '>', 0)
                                             ->orWhere('ctickets.totalttc', '<=', 0);
                                      })
                                      ->where(function($s2) {
                                          $s2->where('ctickets.acompte', '<=', 0)
                                             ->orWhereNull('ctickets.acompte');
                                      });
                              });
                        } elseif ($isAcompte) {
                            $q->where('statutdocuments.libelle', 'ilike', 'Acompte')
                              ->orWhere(function($sub) {
                                  $sub->whereNull('ctickets.statutdocumentid')
                                      ->where('ctickets.acompte', '>', 0)
                                      ->where(function($s2) {
                                          $s2->where('ctickets.netapayer', '>', 0)
                                             ->orWhere('ctickets.totalttc', '<=', 0);
                                      });
                              });
                        } else {
                            $q->where('statutdocuments.libelle', 'ilike', '%' . $term . '%');
                        }
                    });
                } elseif ($reqKey == 'f_client') {
                    $query->where(function($q) use ($term) {
                        $q->where('clients.nom', 'ilike', '%' . $term . '%')
                          ->orWhere('clients.prenom', 'ilike', '%' . $term . '%');
                    });
                } elseif ($reqKey == 'f_vendeur') {
                    $query->where(function($q) use ($term) {
                        $q->where('vendeurs.nom', 'ilike', '%' . $term . '%')
                          ->orWhere('vendeurs.prenom', 'ilike', '%' . $term . '%');
                    });
                } elseif (in_array($reqKey, ['f_qte', 'f_brutht', 'f_remise', 'f_netht', 'f_tva', 'f_ttc', 'f_brutttc', 'f_acompte', 'f_reste'])) {
                    $cleanTerm = str_replace(',', '.', $term);
                    if ($reqKey == 'f_qte') {
                        $query->where(\Illuminate\Support\Facades\DB::raw("CAST(ROUND(" . $dbCol . "::numeric, 0) AS varchar)"), 'like', '%' . $cleanTerm . '%');
                    } else {
                        $query->where(\Illuminate\Support\Facades\DB::raw("CAST(ROUND(" . $dbCol . "::numeric, 3) AS varchar)"), 'like', '%' . $cleanTerm . '%');
                    }
                } else {
                    $query->where($dbCol, 'ilike', '%' . $term . '%');
                }
            }
        }

        // Apply Global Search
        if ($request->filled('q')) {
            $searchTerm = '%' . $request->q . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('ctickets.numerointerne', 'ilike', $searchTerm)
                  ->orWhere('ctickets.cticketnumero', 'ilike', $searchTerm)
                  ->orWhere('clients.nom', 'ilike', $searchTerm)
                  ->orWhere('clients.prenom', 'ilike', $searchTerm)
                  ->orWhere('clients.clientcode', 'ilike', $searchTerm)
                  ->orWhere('caissiers.login', 'ilike', $searchTerm)
                  ->orWhere('vendeurs.nom', 'ilike', $searchTerm)
                  ->orWhere('vendeurs.prenom', 'ilike', $searchTerm)
                  ->orWhere('vendeurs.matricule', 'ilike', $searchTerm);
            });
        }

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

            // KPI Calculations
            $nbTickets = (clone $query)->count();
            // A ticket is paid if netapayer <= 0 and totalttc > 0 (or simply we can sum the amounts paid: totalttc - netapayer)
            // But let's use standard logic: total payé = totalttc - netapayer. Unpaid = netapayer.
            $kpiTotalPaye = (clone $query)->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(ctickets.totalttc, 0) - COALESCE(ctickets.netapayer, 0)'));
            $kpiTotalNonPaye = $totalReste;

            $tickets = $query->paginate(15);
            $html = view('vente.tickets.partials.table_body', compact('tickets'))->render();

            return response()->json([
                'html' => $html,
                'pagination' => $tickets->links()->toHtml(),
                'totals' => [
                    'qte' => number_format((float)($totalQte ?? 0), 0, ',', ' '),
                    'brut_ht' => number_format((float)($totalBrutHt ?? 0), 3, ',', ' '),
                    'remise' => number_format((float)($totalRemise ?? 0), 3, ',', ' '),
                    'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
                    'tva' => number_format((float)($totalTva ?? 0), 3, ',', ' '),
                    'ttc' => number_format((float)($totalTtc ?? 0), 3, ',', ' '),
                    'acompte' => number_format((float)($totalAcompte ?? 0), 3, ',', ' '),
                    'reste' => number_format((float)($totalReste ?? 0), 3, ',', ' ')
                ],
                'kpis' => [
                    'nb_tickets' => number_format((float)($nbTickets ?? 0), 0, ',', ' '),
                    'ca_ttc' => number_format((float)($totalTtc ?? 0), 3, ',', ' '),
                    'total_paye' => number_format((float)($kpiTotalPaye ?? 0), 3, ',', ' '),
                    'total_non_paye' => number_format((float)($kpiTotalNonPaye ?? 0), 3, ',', ' ')
                ]
            ]);
        }

        // Initial Load (Non-AJAX) -> Just load tickets, no empty paginator logic.
        $tickets = $query->paginate(15);
        
        // Calculate initial totals to pass to the view to avoid layout flickers
        $totalQte = (clone $query)->sum('ctickets.totalqte');
        $totalBrutHt = (clone $query)->sum('ctickets.totalbrutht');
        $totalRemise = (clone $query)->sum(\Illuminate\Support\Facades\DB::raw('ctickets.totalbrutht - ctickets.totalnetht'));
        $totalNetHt = (clone $query)->sum('ctickets.totalnetht');
        $totalTva = (clone $query)->sum('ctickets.totaltva');
        $totalTtc = (clone $query)->sum('ctickets.totalttc');
        $totalAcompte = (clone $query)->sum('ctickets.acompte');
        $totalReste = (clone $query)->sum('ctickets.netapayer');

        // KPI Calculations
        $nbTickets = (clone $query)->count();
        $kpiTotalPaye = (clone $query)->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(ctickets.totalttc, 0) - COALESCE(ctickets.netapayer, 0)'));
        $kpiTotalNonPaye = $totalReste;

        $totals = [
            'qte' => number_format((float)($totalQte ?? 0), 0, ',', ' '),
            'brut_ht' => number_format((float)($totalBrutHt ?? 0), 3, ',', ' '),
            'remise' => number_format((float)($totalRemise ?? 0), 3, ',', ' '),
            'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
            'tva' => number_format((float)($totalTva ?? 0), 3, ',', ' '),
            'ttc' => number_format((float)($totalTtc ?? 0), 3, ',', ' '),
            'acompte' => number_format((float)($totalAcompte ?? 0), 3, ',', ' '),
            'reste' => number_format((float)($totalReste ?? 0), 3, ',', ' ')
        ];

        $kpis = [
            'nb_tickets' => number_format((float)($nbTickets ?? 0), 0, ',', ' '),
            'ca_ttc' => number_format((float)($totalTtc ?? 0), 3, ',', ' '),
            'total_paye' => number_format((float)($kpiTotalPaye ?? 0), 3, ',', ' '),
            'total_non_paye' => number_format((float)($kpiTotalNonPaye ?? 0), 3, ',', ' ')
        ];
        $clients = \Illuminate\Support\Facades\DB::table('clients')->select('clientid', 'nom', 'code')->orderBy('nom')->get();
        $statuts = \Illuminate\Support\Facades\DB::table('statutdocuments')
            ->select('statutdocumentid', 'libelle')
            ->where('statutdocumentid', '>', 0)
            ->get();
        $caissiers = \Illuminate\Support\Facades\DB::table('users')->select('userid', 'login')->get();
        $vendeurs = \Illuminate\Support\Facades\DB::table('employees')->select('employeeid', 'nom', 'prenom')->where('isvendeur', true)->get();

        return view('vente.tickets.index', compact('tickets', 'clients', 'statuts', 'caissiers', 'vendeurs', 'totals', 'kpis'));
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
