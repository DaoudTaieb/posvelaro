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
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // -- Section STOCK --
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/articles', [\App\Http\Controllers\Stock\ArticleController::class, 'index'])->name('articles.index');
        Route::get('/consultation', [\App\Http\Controllers\Stock\ConsultationStockController::class, 'index'])->name('consultation.index');
        Route::get('/detaille', [\App\Http\Controllers\Stock\StockDetailleController::class, 'index'])->name('detaille.index');
        Route::get('/mouvements', [\App\Http\Controllers\Stock\MouvementsArticlesController::class, 'index'])->name('mouvements.index');
        Route::get('/etat', [\App\Http\Controllers\Stock\EtatStockController::class, 'index'])->name('etat.index');
    });

    // -- Section TRANSFERT --
    Route::prefix('transfert')->group(function () {
        Route::get('/demande-envoye', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'index'])->name('transfert.demande_envoye.index');
        Route::get('/demande-envoye/create', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'create'])->name('transfert.demande_envoye.create');
        Route::post('/demande-envoye', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'store'])->name('transfert.demande_envoye.store');
        Route::get('/demande-envoye/{id}/edit', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'edit'])->name('transfert.demande_envoye.edit');
        Route::delete('/demande-envoye/{id}', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'destroy'])->name('transfert.demande_envoye.destroy');
        Route::get('/demande-envoye/search-products', [\App\Http\Controllers\Transfert\DemandeTransfertEnvoyeController::class, 'searchProducts'])->name('transfert.demande_envoye.search_products');
        Route::get('/demande-recu', [\App\Http\Controllers\Transfert\DemandeTransfertRecuController::class, 'index'])->name('transfert.demande_recu.index');
        Route::post('/demande-recu/pointer', [\App\Http\Controllers\Transfert\DemandeTransfertRecuController::class, 'pointer'])->name('transfert.demande_recu.pointer');
        
        // Transfert Envoyé
        Route::get('/envoye', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'index'])->name('transfert.envoye.index');
        Route::get('/envoye/create', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'create'])->name('transfert.envoye.create');
        Route::get('/envoye/impression-multiple', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'impressionMultiple'])->name('transfert.envoye.impression_multiple');
        Route::post('/envoye', [\App\Http\Controllers\Transfert\TransfertEnvoyeController::class, 'store'])->name('transfert.envoye.store');
        
        // Transfert Reçu
        Route::get('/recu', [\App\Http\Controllers\Transfert\TransfertRecuController::class, 'index'])->name('transfert.recu.index');
    });

    // -- Section VENTE --
    Route::prefix('vente')->name('vente.')->group(function () {
        // Caisse
        Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.index');
        Route::post('/caisse', [CaisseController::class, 'store'])->name('caisse.store');
        
        // Consultation Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
        
        // Calcul Commissions
        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/calculate', [CommissionController::class, 'calculate'])->name('commissions.calculate');
        
        // Journée
        Route::get('/journees', [JourneeController::class, 'index'])->name('journee.index');
        Route::get('/journees/{id}', [JourneeController::class, 'show'])->name('journee.show');
        Route::get('/journees/{id}/details', [JourneeController::class, 'details'])->name('journee.details');
        Route::get('/journee/ouverture', [JourneeController::class, 'ouverture'])->name('journee.ouverture');
        Route::post('/journee/ouverture', [JourneeController::class, 'storeOuverture'])->name('journee.ouverture.store');
        Route::get('/journee/etat', [JourneeController::class, 'etat'])->name('journee.etat');
        Route::get('/journee/etat/filter', [JourneeController::class, 'etatFilter'])->name('journee.etat.filter');
        Route::get('/journee/cloture', [JourneeController::class, 'cloture'])->name('journee.cloture');
        Route::post('/journee/cloture', [JourneeController::class, 'storeCloture'])->name('journee.cloture.store');
        
        // Clients
        Route::resource('clients', ClientController::class);
    });
});
