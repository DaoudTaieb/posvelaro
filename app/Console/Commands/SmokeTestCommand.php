<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SmokeTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:smoke-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste toutes les routes GET de l\'application pour détecter les erreurs 500 (Bugs)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Démarrage du script de test global (Smoke Test)...");

        // 1. Trouver un utilisateur administrateur pour s'authentifier
        // Comme le modèle User peut être configuré différemment, on le récupère via DB
        $userRecord = DB::table('users')->first();
        
        if (!$userRecord) {
            $this->error("Aucun utilisateur trouvé dans la base de données. Impossible de tester les routes protégées.");
            return;
        }

        $this->info("Connexion simulée avec l'utilisateur ID: " . $userRecord->userid);
        
        // Simuler l'authentification avec cet utilisateur
        // Si App\Models\User utilise 'userid' comme clé primaire, on le trouve :
        $user = User::find($userRecord->userid) ?? new User((array) $userRecord);
        $user->userid = $userRecord->userid; // Force ID just in case
        auth()->login($user);

        $this->info("Utilisateur connecté. Début du scan des routes...");

        $routes = Route::getRoutes();
        $results = [];
        $errors = [];
        
        $bar = $this->output->createProgressBar(count($routes));
        $bar->start();

        foreach ($routes as $route) {
            $bar->advance();

            // On ne teste que les routes GET
            if (!in_array('GET', $route->methods())) {
                continue;
            }

            $uri = $route->uri();
            
            // Ignorer les routes d'API externes, de debug ou inutiles à tester automatiquement
            if (str_starts_with($uri, '_ignition') || str_starts_with($uri, 'api/') || str_starts_with($uri, 'sanctum/')) {
                continue;
            }

            // Remplacer les paramètres dynamiques {id}, {produit2id}, etc. par une valeur bidon (ex: 1)
            // afin de pouvoir tester l'URL
            $testUrl = preg_replace('/\{[a-zA-Z0-9_]+\??\}/', '1', $uri);

            try {
                // Créer une requête interne factice
                $request = \Illuminate\Http\Request::create('/' . ltrim($testUrl, '/'), 'GET');
                
                // Exécuter la route via l'application
                $response = app()->handle($request);
                $statusCode = $response->getStatusCode();
                
                if ($statusCode == 200 || $statusCode == 302 || $statusCode == 201) {
                    $results[] = ['uri' => $uri, 'status' => '<fg=green>OK ('.$statusCode.')</>'];
                } elseif ($statusCode >= 500) {
                    $results[] = ['uri' => $uri, 'status' => '<fg=red>BUG ('.$statusCode.')</>'];
                    $errors[] = "Route $uri a généré une erreur $statusCode.";
                } else {
                    $results[] = ['uri' => $uri, 'status' => '<fg=yellow>Avertissement ('.$statusCode.')</>'];
                }
            } catch (\Exception $e) {
                // S'il y a un crash PHP
                $results[] = ['uri' => $uri, 'status' => '<fg=red>CRASH</>'];
                $errors[] = "La route $uri a crashé avec l'erreur: " . $e->getMessage();
            }
        }

        $bar->finish();
        $this->line('');
        
        // Afficher le tableau des résultats
        $this->table(['Route (URI)', 'Statut'], $results);

        if (count($errors) > 0) {
            $this->error("\nNous avons trouvé " . count($errors) . " bugs critiques !");
            foreach ($errors as $error) {
                $this->line("- $error");
            }
            $this->info("Astuce : Vérifiez le fichier storage/logs/laravel.log pour voir les détails de ces bugs.");
        } else {
            $this->info("\nFélicitations ! Aucun bug critique n'a été détecté sur l'ouverture des pages.");
        }
    }
}
