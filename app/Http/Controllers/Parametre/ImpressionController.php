<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Services\ImpressionTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Paramètres > Imprimantes et douchettes : imprimante thermique et tiroir-caisse de chaque caisse
 * du site, liste des imprimantes Windows du poste, test de la douchette (lecteur de codes-barres).
 */
class ImpressionController extends Controller
{
    use ParametreHelpers;

    public function __construct(private readonly ImpressionTicketService $impression)
    {
    }

    public function index()
    {
        $caisses = DB::table('caisses')->where('siteid', $this->userSiteId())->orderBy('caisseid')->get(['caisseid', 'libelle', 'numero']);
        $configs = $caisses->mapWithKeys(fn ($c) => [$c->caisseid => $this->impression->configuration((int) $c->caisseid)]);

        return view('parametre.impression.index', [
            'caisses' => $caisses,
            'configs' => $configs,
            'modes' => ImpressionTicketService::MODES,
        ]);
    }

    public function update(Request $request, $id)
    {
        $id = $this->caisseDuSite($id);
        $request->validate([
            'mode' => ['required', Rule::in(array_keys(ImpressionTicketService::MODES))],
            'imprimante' => 'nullable|string|max:200|required_if:mode,windows',
            'reseau' => ['nullable', 'string', 'max:100', 'required_if:mode,reseau', 'regex:/^[A-Za-z0-9.\-]+(:\d{2,5})?$/'],
            'colonnes' => 'required|integer|min:24|max:64',
        ], [
            'imprimante.required_if' => 'Indiquez le nom de l\'imprimante Windows (tel qu\'il apparaît dans Windows).',
            'reseau.required_if' => 'Indiquez l\'adresse IP de l\'imprimante réseau.',
            'reseau.regex' => 'Adresse attendue : IP ou nom, avec le port éventuel (ex. 192.168.1.50:9100).',
        ]);

        $this->impression->enregistrerConfiguration($id, [
            'mode' => $request->mode,
            'imprimante' => $request->imprimante,
            'reseau' => $request->reseau,
            'colonnes' => $request->colonnes,
            'accents' => $request->boolean('accents'),
            'tiroir' => $request->boolean('tiroir'),
        ]);

        return redirect()->route('parametre.impression.index')->with('success', 'Réglage d\'impression enregistré.');
    }

    public function test($id)
    {
        $resultat = $this->impression->imprimerTest($this->caisseDuSite($id));

        return redirect()->route('parametre.impression.index')->with($resultat['ok'] ? 'success' : 'error', $resultat['message']);
    }

    public function tiroir($id)
    {
        $resultat = $this->impression->testerTiroir($this->caisseDuSite($id));

        return redirect()->route('parametre.impression.index')->with($resultat['ok'] ? 'success' : 'error', $resultat['message']);
    }

    /** Imprimantes installées sur le poste serveur (chargées après l'affichage de la page). */
    public function imprimantes(Request $request)
    {
        return response()->json($this->impression->imprimantesWindows($request->boolean('actualiser')));
    }

    /** Test de la douchette : ce que la caisse ferait du code lu. */
    public function douchette(Request $request, \App\Services\DouchetteService $douchette)
    {
        $recu = \App\Support\CodeBarre::nettoyer($request->input('code', ''));
        if ($recu === '' || mb_strlen($recu) > 64) {
            return response()->json(['type' => null, 'code' => $recu, 'message' => 'Code vide ou trop long.']);
        }
        $resultat = $douchette->identifier($recu, $this->userSiteId());

        // Réponse réduite à ce que la page affiche
        return response()->json([
            'type' => $resultat['type'],
            'code' => $resultat['code'],
            'corrige' => $resultat['corrige'] ?? false,
            'qwerty' => \App\Support\CodeBarre::semblePermute($recu),
            'libelle' => match ($resultat['type']) {
                'produit' => trim(($resultat['produit']->reference ?? '') . ' ' . ($resultat['produit']->produitlibelle ?? '') . ' ' . ($resultat['produit']->taillelibelle ?? '') . ' ' . ($resultat['produit']->couleurlibelle ?? '')),
                'client' => trim(($resultat['client']->nom ?? '') . ' ' . ($resultat['client']->prenom ?? '')),
                'bon' => $resultat['bon']['numero'] . ' : ' . number_format($resultat['bon']['montant'], 3, '.', '') . ' DT',
                default => $resultat['message'] ?? 'Code inconnu',
            },
        ]);
    }

    private function caisseDuSite($id): int
    {
        $id = $this->routeId($id);
        abort_unless(DB::table('caisses')->where('caisseid', $id)->where('siteid', $this->userSiteId())->exists(), 404);

        return $id;
    }
}
