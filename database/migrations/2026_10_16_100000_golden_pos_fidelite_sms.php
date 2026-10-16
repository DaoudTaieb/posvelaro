<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schéma additionnel Golden Pos sur la base « velaro » partagée avec le logiciel de bureau.
 *
 * Idempotente : chaque ajout est conditionné par hasTable / hasColumn, afin de pouvoir être
 * rejouée après une restauration du dump du logiciel de bureau sans rien casser.
 *
 *  - sousfamilles.is_loyalty_enabled : sous-familles ouvrant droit à la remise et aux bons fidélité
 *  - clients.prenom                  : prénom du client (le bureau ne connaît que « nom »)
 *  - clients.fidelite_debut          : début du cycle fidélité courant (null = carte non démarrée / expirée)
 *  - bons_achat                      : bons d'achat fidélité émis par la caisse web
 *  - sms_envois                      : journal des SMS envoyés via Bip SMS
 *  - retailconfigs                   : paramètres fidélité et SMS dans la page « Général » du bureau
 *
 * Le n° de carte fidélité utilise la colonne existante clients.barcode (déjà remplie par le bureau).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sousfamilles', 'is_loyalty_enabled')) {
            Schema::table('sousfamilles', function (Blueprint $table) {
                $table->boolean('is_loyalty_enabled')->default(false);
            });
            // Valeur de départ : la famille « chaussures » (le bon d'achat se compte « par paire »).
            // Modifiable ensuite dans Paramètres > Configuration > Général.
            $familleChaussures = DB::table('sousfamilles')
                ->whereIn('sousfamillelibelle', ['MOCASSIN', 'DERBIES', 'SNEAKERS ET BASKET', 'SANDALE', 'BOOTS'])
                ->value('familleid');
            if ($familleChaussures) {
                DB::table('sousfamilles')->where('familleid', $familleChaussures)->update(['is_loyalty_enabled' => true]);
            }
        }

        if (!Schema::hasColumn('clients', 'prenom')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->text('prenom')->nullable();
            });
        }
        if (!Schema::hasColumn('clients', 'fidelite_debut')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->timestamp('fidelite_debut')->nullable();
            });
        }

        if (!Schema::hasTable('bons_achat')) {
            Schema::create('bons_achat', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('clientid');
                $table->decimal('montant', 10, 3);
                $table->date('date_emission');
                $table->date('date_expiration');
                $table->boolean('utilise')->default(false);
                $table->unsignedBigInteger('ticketid_source')->nullable();
                $table->timestamps();
            });
        }
        Schema::table('bons_achat', function (Blueprint $table) {
            if (!Schema::hasColumn('bons_achat', 'annule')) {
                $table->boolean('annule')->default(false);
            }
            if (!Schema::hasColumn('bons_achat', 'motif')) {
                $table->text('motif')->nullable();
            }
            if (!Schema::hasColumn('bons_achat', 'cticketid_utilisation')) {
                $table->unsignedBigInteger('cticketid_utilisation')->nullable();
            }
            if (!Schema::hasColumn('bons_achat', 'date_utilisation')) {
                $table->timestamp('date_utilisation')->nullable();
            }
        });
        DB::statement('CREATE INDEX IF NOT EXISTS bons_achat_clientid_idx ON bons_achat (clientid)');
        DB::statement('CREATE INDEX IF NOT EXISTS bons_achat_ticketid_source_idx ON bons_achat (ticketid_source)');

        if (!Schema::hasTable('sms_envois')) {
            Schema::create('sms_envois', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('clientid')->nullable();
                $table->unsignedBigInteger('cticketid')->nullable();
                $table->integer('userid')->nullable();
                $table->integer('siteid')->nullable();
                $table->string('telephone', 20);
                $table->text('message');
                $table->string('statut', 20); // envoye | echec | erreur | non_configure
                $table->text('reponse')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        $this->ajouterParametres();
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_envois');
        Schema::dropIfExists('bons_achat');

        if (Schema::hasColumn('clients', 'fidelite_debut')) {
            Schema::table('clients', fn (Blueprint $t) => $t->dropColumn('fidelite_debut'));
        }
        if (Schema::hasColumn('clients', 'prenom')) {
            Schema::table('clients', fn (Blueprint $t) => $t->dropColumn('prenom'));
        }
        if (Schema::hasColumn('sousfamilles', 'is_loyalty_enabled')) {
            Schema::table('sousfamilles', fn (Blueprint $t) => $t->dropColumn('is_loyalty_enabled'));
        }

        DB::table('retailconfigs')->whereIn('libelle', array_keys($this->parametres()))->delete();
    }

    /**
     * Paramètres Golden Pos affichés dans Paramètres > Configuration > Général.
     * typevalueid : 0 texte, 1 entier, 2 booléen, 3 décimal (convention du bureau).
     */
    private function parametres(): array
    {
        return [
            'fidelite_palier1' => ['Fidélité : remise 1ère vente (%)', 1, '10', 60],
            'fidelite_palier2' => ['Fidélité : remise 2ème vente (%)', 1, '20', 61],
            'fidelite_palier3' => ['Fidélité : remise à partir de la 3ème vente (%)', 1, '30', 62],
            'fidelite_seuil_bon' => ['Fidélité : bon d\'achat à partir de la Nième vente', 1, '4', 63],
            'fidelite_montant_bon' => ['Fidélité : montant du bon d\'achat par paire (DT)', 3, '10.000', 64],
            'fidelite_validite_bon_mois' => ['Fidélité : validité du bon d\'achat (mois)', 1, '3', 65],
            'fidelite_ventes_par_an' => ['Fidélité : ventes minimales par an pour garder la carte', 1, '2', 66],
            'bipsms_url' => ['SMS : URL de l\'API Bip SMS', 0, 'http://bulksms.bipsms.tn/Api/Api.aspx', 70],
            'bipsms_sender' => ['SMS : nom de l\'expéditeur', 0, (string) env('BIPSMS_SENDER', 'GOLDENPOS'), 71],
            'bipsms_key' => ['SMS : clé API Bip SMS (vide = utiliser le fichier .env)', 0, '', 72],
        ];
    }

    private function ajouterParametres(): void
    {
        $existants = DB::table('retailconfigs')->pluck('libelle')->flip();

        foreach ($this->parametres() as $libelle => [$caption, $type, $valeur, $ordre]) {
            if ($existants->has($libelle)) {
                continue;
            }
            DB::table('retailconfigs')->insert([
                'retailconfigid' => \App\Support\Identifiants::prochain('retailconfigs', 'retailconfigid'),
                'libelle' => $libelle,
                'caption' => $caption,
                'value' => $valeur,
                'typevalueid' => $type,
                'pageid' => 1,
                'pagelibelle' => 'Général',
                'typeaffichage' => $type,
                'tablename' => null,
                'tableid' => null,
                'tablelibelle' => null,
                'ordreaffichage' => $ordre,
                'libelletooltip' => $caption,
                'description' => 'Paramètre Golden Pos',
            ]);
        }
    }
};
