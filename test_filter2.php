<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/vente/tickets', 'GET', [
    'f_client' => 'afef'
]);

$query = \Illuminate\Support\Facades\DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->leftJoin('users as caissiers', 'ctickets.caissierid', '=', 'caissiers.userid')
            ->leftJoin('employees as vendeurs', 'ctickets.employeeid', '=', 'vendeurs.employeeid')
            ->leftJoin('statutdocuments', 'ctickets.statutdocumentid', '=', 'statutdocuments.statutdocumentid')
            ->select(
                'ctickets.*',
                'clients.nom as client_nom',
                'clients.code as client_code',
                'caissiers.login as caissier_nom',
                'vendeurs.nom as vendeur_nom',
                'vendeurs.prenom as vendeur_prenom',
                'vendeurs.code as vendeur_code',
                'statutdocuments.libelle as statut_libelle',
                'statutdocuments.couleurcode as statut_couleur'
            )
            ->orderBy('ctickets.cticketdate', 'desc');

$query->where(function($q) use ($request) {
    $q->where('clients.nom', 'ilike', '%' . $request->f_client . '%')
      ->orWhere('clients.prenom', 'ilike', '%' . $request->f_client . '%');
});

try {
    $totalQte = (clone $query)->sum('ctickets.totalqte');
    $tickets = $query->paginate(15);
    echo "Success! Count: " . count($tickets) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
