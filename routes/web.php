<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Vente\CaisseController;
use App\Http\Controllers\Vente\TicketController;
use App\Http\Controllers\Vente\CommissionController;
use App\Http\Controllers\Vente\JourneeController;
use App\Http\Controllers\Vente\ClientController;

// Auth routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/login/sites', [AuthController::class, 'getSites'])->middleware('throttle:30,1')->name('login.sites');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
// Les droits ('droit:...') correspondent aux abréviations de la table typedroits,
// plus 'caisse' (userdroits.notcaisse) et 'backoffice' (userdroits.notbackoffice).
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // -- Section STOCK --
    Route::prefix('stock')->name('stock.')->middleware('droit:backoffice,cons.stock')->group(function () {
        Route::get('/articles', [\App\Http\Controllers\Stock\ArticleController::class, 'index'])->name('articles.index');
        Route::get('/consultation', [\App\Http\Controllers\Stock\ConsultationStockController::class, 'index'])->name('consultation.index');
        Route::get('/detaille', [\App\Http\Controllers\Stock\StockDetailleController::class, 'index'])->name('detaille.index');
        Route::get('/mouvements', [\App\Http\Controllers\Stock\MouvementsArticlesController::class, 'index'])->name('mouvements.index');
        Route::get('/etat', [\App\Http\Controllers\Stock\EtatStockController::class, 'index'])->name('etat.index');
    });

    // -- Section TRANSFERT --
    Route::prefix('transfert')->middleware('droit:backoffice')->group(function () {
        // Demandes de transfert
        Route::middleware('droit:cons.demandebontransfert')->group(function () {
            Route::get('/demande-envoye', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'index'])->name('transfert.demande_envoye.index');
            Route::get('/demande-envoye/search-products', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'searchProducts'])->name('transfert.demande_envoye.search_products');
            Route::get('/demande-recu', [\App\Http\Controllers\Transfert\DemandeTransfertRecuController::class, 'index'])->name('transfert.demande_recu.index');
        });
        Route::middleware('droit:aj.demandebontransfert')->group(function () {
            Route::get('/demande-envoye/create', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'create'])->name('transfert.demande_envoye.create');
            Route::post('/demande-envoye', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'store'])->name('transfert.demande_envoye.store');
            Route::get('/demande-envoye/{id}/edit', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'edit'])->whereNumber('id')->name('transfert.demande_envoye.edit');
        });
        Route::delete('/demande-envoye/{id}', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'destroy'])->whereNumber('id')->middleware('droit:supp.demandebontransfert')->name('transfert.demande_envoye.destroy');
        Route::post('/demande-recu/pointer', [\App\Http\Controllers\Transfert\DemandeTransfertRecuController::class, 'pointer'])->middleware('droit:valider.demandebontransfert')->name('transfert.demande_recu.pointer');

        // Transfert Envoyé
        Route::middleware('droit:cons.bontransfert')->group(function () {
            Route::get('/envoye', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'index'])->name('transfert.envoye.index');
            Route::get('/envoye/api/demande-lignes/{id}', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'getDemandeLignes'])->whereNumber('id')->name('transfert.envoye.get_demande_lignes');
            Route::get('/envoye/impression-multiple', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'impressionMultiple'])->name('transfert.envoye.impression_multiple');
        });
        Route::middleware('droit:aj.bontransfert')->group(function () {
            Route::get('/envoye/create', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'create'])->name('transfert.envoye.create');
            Route::post('/envoye', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'store'])->name('transfert.envoye.store');
        });
        Route::middleware('droit:mod.bontransfert')->group(function () {
            Route::get('/envoye/{id}/edit', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'edit'])->whereNumber('id')->name('transfert.envoye.edit');
            Route::put('/envoye/{id}', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'update'])->whereNumber('id')->name('transfert.envoye.update');
        });
        Route::delete('/envoye/{id}', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'destroy'])->whereNumber('id')->middleware('droit:supp.bontransfert')->name('transfert.envoye.destroy');

        // Transfert Reçu
        Route::get('/recu', [\App\Http\Controllers\Transfert\TransfertRecuController::class, 'index'])->middleware('droit:cons.bontransfert')->name('transfert.recu.index');
        Route::middleware('droit:valider.bontransfert')->group(function () {
            Route::get('/recu/{id}/receptionner', [\App\Http\Controllers\Transfert\TransfertRecuController::class, 'receptionner'])->whereNumber('id')->name('transfert.recu.receptionner');
            Route::post('/recu/{id}/receptionner', [\App\Http\Controllers\Transfert\TransfertRecuController::class, 'storeReception'])->whereNumber('id')->name('transfert.recu.store_reception');
        });
    });

    // -- Section PARAMETRES --
    Route::prefix('parametre')->name('parametre.')->middleware('droit:backoffice')->group(function () {
        Route::middleware('droit:config.caisses')->group(function () {
            Route::post('/caisse/liberer/{id}', [\App\Http\Controllers\Parametre\CaisseController::class, 'liberer'])->name('caisse.liberer');
            Route::get('/caisse/configuration', [\App\Http\Controllers\Parametre\CaisseController::class, 'index'])->name('caisse.index');
            Route::post('/caisse/configuration', [\App\Http\Controllers\Parametre\CaisseController::class, 'store'])->name('caisse.store');
            Route::put('/caisse/configuration/{id}', [\App\Http\Controllers\Parametre\CaisseController::class, 'update'])->name('caisse.update');
            Route::delete('/caisse/configuration/{id}', [\App\Http\Controllers\Parametre\CaisseController::class, 'destroy'])->name('caisse.destroy');
            Route::get('/caisse/liberation', [\App\Http\Controllers\Parametre\CaisseController::class, 'liberation'])->name('caisse.liberation');
        });

        Route::middleware('droit:gestion.vendeur')->group(function () {
            Route::get('/vendeur', [\App\Http\Controllers\Parametre\VendeurController::class, 'index'])->name('vendeur.index');
            Route::post('/vendeur', [\App\Http\Controllers\Parametre\VendeurController::class, 'store'])->name('vendeur.store');
            Route::put('/vendeur/{id}', [\App\Http\Controllers\Parametre\VendeurController::class, 'update'])->name('vendeur.update');
            Route::delete('/vendeur/{id}', [\App\Http\Controllers\Parametre\VendeurController::class, 'destroy'])->name('vendeur.destroy');
        });

        Route::middleware('droit:config')->group(function () {
            Route::get('/configuration/general', [\App\Http\Controllers\Parametre\ConfigurationController::class, 'general'])->name('configuration.general');
            Route::post('/configuration/general', [\App\Http\Controllers\Parametre\ConfigurationController::class, 'updateGeneral'])->name('configuration.general.update');
        });

        Route::middleware('droit:gestion.droits')->group(function () {
            Route::get('/droit', [\App\Http\Controllers\Parametre\DroitController::class, 'index'])->name('droit.index');
            Route::post('/droit/role', [\App\Http\Controllers\Parametre\DroitController::class, 'storeRole'])->name('droit.role.store');
            Route::put('/droit/role/{id}', [\App\Http\Controllers\Parametre\DroitController::class, 'updateRole'])->name('droit.role.update');
            Route::delete('/droit/role/{id}', [\App\Http\Controllers\Parametre\DroitController::class, 'destroyRole'])->name('droit.role.destroy');
            Route::post('/droit/permissions/{id}', [\App\Http\Controllers\Parametre\DroitController::class, 'updatePermissions'])->name('droit.permissions.update');
        });

        Route::middleware('droit:gestion.users')->group(function () {
            Route::get('/utilisateur', [\App\Http\Controllers\Parametre\UtilisateurController::class, 'index'])->name('utilisateur.index');
            Route::post('/utilisateur', [\App\Http\Controllers\Parametre\UtilisateurController::class, 'store'])->name('utilisateur.store');
            Route::put('/utilisateur/{id}', [\App\Http\Controllers\Parametre\UtilisateurController::class, 'update'])->name('utilisateur.update');
            Route::delete('/utilisateur/{id}', [\App\Http\Controllers\Parametre\UtilisateurController::class, 'destroy'])->name('utilisateur.destroy');
        });
    });

    // -- Section VENTE --
    Route::prefix('vente')->name('vente.')->group(function () {
        // Caisse (POS) : réservée aux rôles autorisés en caisse
        Route::middleware('droit:caisse')->group(function () {
            Route::get('/caisse/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('caisse.pos');
            Route::get('/caisse/pos/search-products', [\App\Http\Controllers\PosController::class, 'searchProducts'])->name('caisse.pos.search_products');
            Route::get('/caisse/pos/scan-product', [\App\Http\Controllers\PosController::class, 'scanProduct'])->name('caisse.pos.scan_product');
            Route::get('/caisse/pos/variants', [\App\Http\Controllers\PosController::class, 'getProductVariants'])->name('caisse.pos.variants');
            Route::get('/caisse/pos/search-clients', [\App\Http\Controllers\PosController::class, 'searchClients'])->name('caisse.pos.search_clients');
            Route::post('/caisse/pos/store-client', [\App\Http\Controllers\PosController::class, 'storeClient'])->middleware('droit:aj.client')->name('caisse.pos.store_client');
            Route::get('/caisse/pos/client/{id}', [\App\Http\Controllers\PosController::class, 'getClient'])->whereNumber('id')->name('caisse.pos.get_client');
            Route::post('/caisse/pos/client/{id}', [\App\Http\Controllers\PosController::class, 'updateClient'])->whereNumber('id')->middleware('droit:mod.client')->name('caisse.pos.update_client');
            Route::get('/caisse/pos/search-vendeurs', [\App\Http\Controllers\PosController::class, 'searchVendeurs'])->name('caisse.pos.search_vendeurs');
            Route::get('/caisse/pos/client-history/{id}', [\App\Http\Controllers\PosController::class, 'clientHistory'])->whereNumber('id')->name('caisse.pos.client_history');
            Route::get('/caisse/pos/article-history', [\App\Http\Controllers\PosController::class, 'searchArticleHistory'])->name('caisse.pos.article_history');
            Route::post('/caisse/pos/send-sms', [\App\Http\Controllers\PosController::class, 'sendSms'])->middleware('throttle:20,1')->name('caisse.pos.send_sms');
            Route::get('/caisse/pos/check-stock/{produit2id}', [\App\Http\Controllers\PosController::class, 'checkStock'])->whereNumber('produit2id')->name('caisse.pos.check_stock');
            Route::get('/caisse/pos/advanced-check-stock', [\App\Http\Controllers\PosController::class, 'advancedCheckStock'])->name('caisse.pos.advanced_check_stock');
            Route::get('/caisse/pos/check-avoir', [\App\Http\Controllers\PosController::class, 'checkAvoir'])->name('caisse.pos.check_avoir');
            Route::post('/caisse', [CaisseController::class, 'store'])->name('caisse.store');
            Route::get('/caisse/en-attente', [CaisseController::class, 'getEnAttente'])->name('caisse.en_attente');
            Route::post('/caisse/reprise/{id}', [CaisseController::class, 'reprise'])->whereNumber('id')->name('caisse.reprise');
            Route::get('/caisse/journal-data', [CaisseController::class, 'journalVenteData'])->name('caisse.journal_data');
            Route::get('/caisse/ticket-details/{numero}', [CaisseController::class, 'ticketDetails'])->whereNumber('numero')->name('caisse.ticket_details');
            Route::get('/caisse/mouvements', [CaisseController::class, 'getMouvements'])->name('caisse.mouvements');
            Route::get('/caisse/tickets-reste', [CaisseController::class, 'getTicketsWithReste'])->middleware('droit:comp.acompte')->name('caisse.tickets_reste');
            Route::post('/caisse/store-complement-acompte', [CaisseController::class, 'storeComplementAcompte'])->middleware('droit:comp.acompte')->name('caisse.store_complement_acompte');
        });

        // Consultation Tickets
        Route::middleware('droit:backoffice')->group(function () {
            Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{id}', [TicketController::class, 'show'])->whereNumber('id')->name('tickets.show');
        });

        // Calcul Commissions
        Route::middleware('droit:backoffice,cons.commission.vendeur')->group(function () {
            Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
            Route::post('/commissions/calculate', [CommissionController::class, 'calculate'])->name('commissions.calculate');
        });

        // Journée
        Route::middleware('droit:cons.journeecaisse')->group(function () {
            Route::get('/journees', [JourneeController::class, 'index'])->name('journee.index');
            Route::get('/journees/{id}', [JourneeController::class, 'show'])->whereNumber('id')->name('journee.show');
            Route::get('/journees/{id}/details', [JourneeController::class, 'details'])->whereNumber('id')->name('journee.details');
        });
        Route::middleware('droit:caisse')->group(function () {
            Route::get('/journee/ouverture', [JourneeController::class, 'ouverture'])->name('journee.ouverture');
            Route::post('/journee/ouverture', [JourneeController::class, 'storeOuverture'])->name('journee.ouverture.store');
        });
        Route::middleware('droit:cons.etatjourneecaisse')->group(function () {
            Route::get('/journee/etat', [JourneeController::class, 'etat'])->name('journee.etat');
            Route::get('/journee/etat/filter', [JourneeController::class, 'etatFilter'])->name('journee.etat.filter');
        });
        Route::middleware('droit:caisse,clot.caisse')->group(function () {
            Route::get('/journee/cloture', [JourneeController::class, 'cloture'])->name('journee.cloture');
            Route::post('/journee/cloture', [JourneeController::class, 'storeCloture'])->name('journee.cloture.store');
        });

        // Clients (mêmes noms que l'ancien Route::resource, avec un droit par action)
        Route::middleware('droit:backoffice')->group(function () {
            Route::get('/clients', [ClientController::class, 'index'])->middleware('droit:cons.client')->name('clients.index');
            Route::get('/clients/{client}', [ClientController::class, 'show'])->whereNumber('client')->middleware('droit:cons.client')->name('clients.show');
            Route::post('/clients', [ClientController::class, 'store'])->middleware('droit:aj.client')->name('clients.store');
            Route::match(['put', 'patch'], '/clients/{client}', [ClientController::class, 'update'])->whereNumber('client')->middleware('droit:mod.client')->name('clients.update');
            Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->whereNumber('client')->middleware('droit:supp.client')->name('clients.destroy');
        });
    });
});
