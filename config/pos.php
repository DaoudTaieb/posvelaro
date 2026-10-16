<?php

/**
 * Constantes métier de la caisse Golden Pos.
 *
 * Les valeurs de fidélité sont des valeurs par défaut : si une ligne de même nom existe dans
 * retailconfigs (page « Général »), c'est elle qui l'emporte (voir App\Support\RetailConfig).
 */
return [

    // Client « PASSAGER » : vente comptoir sans fiche client
    'client_passager_id' => (int) env('POS_CLIENT_PASSAGER_ID', 1),

    // Identifiants de la table modereglements
    'modes_reglement' => [
        'espece' => 1,
        'cheque' => 2,
        'traite' => 3,
        'carte_bancaire' => 4,
        'cheque_cadeau' => 5,
        'ticket_resto' => 6,
        'offre' => 7,
        'bon_convention' => 8,
        'avoir' => 9,
        'bon_achat' => 10,
        'virement' => 11,
        'autre' => 12,
        'coupon' => 13,
    ],

    // familledepenses « Retour Argent » (remboursement en espèces d'un retour)
    'famille_depense_retour_argent' => 10,

    'fidelite' => [
        // Remise appliquée aux articles des sous-familles fidélité : 1ère vente, 2ème vente, 3ème et suivantes
        'paliers' => [10, 20, 30],
        // À partir de cette vente (incluse), un bon d'achat est émis
        'seuil_bon' => 4,
        // Montant du bon par paire (article d'une sous-famille fidélité)
        'montant_bon' => 10.000,
        // Validité du bon d'achat, en mois
        'validite_bon_mois' => 3,
        // Nombre de ventes minimum par année (à compter de la 1ère vente) pour conserver la carte
        'ventes_par_an' => 2,
        // Préfixe du numéro imprimé / scanné d'un bon d'achat
        'prefixe_bon' => 'BA',
    ],
];
