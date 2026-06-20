<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\Vente\CaisseController;
use App\Http\Controllers\Vente\TicketController;
use App\Http\Controllers\Vente\CommissionController;
use App\Http\Controllers\Vente\JourneeController;
use App\Http\Controllers\Vente\ClientController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Menu Vente
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
        Route::get('/journee/ouverture', [JourneeController::class, 'ouverture'])->name('journee.ouverture');
        Route::post('/journee/ouverture', [JourneeController::class, 'storeOuverture'])->name('journee.ouverture.store');
        Route::get('/journee/cloture', [JourneeController::class, 'cloture'])->name('journee.cloture');
        Route::post('/journee/cloture', [JourneeController::class, 'storeCloture'])->name('journee.cloture.store');
        
        // Clients
        Route::resource('clients', ClientController::class);
    });
});
