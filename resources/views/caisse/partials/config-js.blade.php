{{--
    Valeurs serveur utilisées par le JavaScript de la caisse (public/js/caisse/*.js).
    Les scripts sont des scripts classiques : ils lisent cet objet via la variable globale GP.
--}}
@php
    $routesCaisse = [
        'vente.caisse.store', 'vente.caisse.en_attente', 'vente.caisse.journal_data', 'vente.caisse.mouvements',
        'vente.caisse.pos.advanced_check_stock', 'vente.caisse.pos.article_history', 'vente.caisse.pos.check_bon_achat',
        'vente.caisse.pos.scan_product', 'vente.caisse.pos.search_clients', 'vente.caisse.pos.search_products',
        'vente.caisse.pos.search_vendeurs', 'vente.caisse.pos.send_sms', 'vente.caisse.pos.store_client',
        'vente.caisse.pos.variants',
    ];
    $urlsCaisse = [
        'vente/caisse/pos/client', 'vente/caisse/pos/client-history', 'vente/caisse/pos/bons-client',
        'vente/caisse/reprise', 'vente/tickets', '/vente/caisse/ticket-details',
    ];
    $configCaisse = [
        'csrf' => csrf_token(),
        'siteNom' => strtoupper($siteName),
        'clientPassagerId' => (int) config('pos.client_passager_id'),
        'clientParDefautId' => $client ? (int) $client->clientid : 1,
        'vendeurParDefaut' => [
            'id' => $defaultVendeur ? (int) $defaultVendeur->employeeid : null,
            'nom' => $defaultVendeur ? $defaultVendeur->nom . ' ' . $defaultVendeur->prenom : 'Non spécifié',
        ],
        'routes' => collect($routesCaisse)->mapWithKeys(fn ($nom) => [$nom => route($nom)])
            // Route à paramètre : l'identifiant du ticket est ajouté par le script (…/imprimer/{id})
            ->put('vente.caisse.imprimer', route('vente.caisse.imprimer', ['id' => 0]))
            ->all(),
        'urls' => collect($urlsCaisse)->mapWithKeys(fn ($chemin) => [$chemin => url($chemin)])->all(),
        'impression' => $impression ?? ['mode' => 'navigateur'],
    ];
@endphp
<script>
    window.GoldenPos = {!! json_encode($configCaisse, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    var GP = window.GoldenPos;
</script>
