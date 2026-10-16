# Golden Pos — Plan de correction et d'amélioration

Date : 16 octobre 2026. Base analysée : PostgreSQL 17, base `velaro` (127.0.0.1), 355 tables/vues.

## 1. Constat (ce que la base dit vraiment)

| Constat | Détail | Conséquence |
|---|---|---|
| Le web n'a jamais écrit en production | Les 795 tickets, 183 journées et 57 bons viennent du logiciel de bureau « Lexpert Business » (utilisateurs admin / neila, `caissierid = 0`, aucun `numerointerne` BTE-). Dernière vente bureau : aujourd'hui 16/10. | Golden Pos est en phase pré-production ; la base est une copie vivante du magasin Velaro (site 102). |
| Le schéma attendu par le code n'est pas en base | Absents : table `bons_achat`, table `migrations`, colonnes `sousfamilles.is_loyalty_enabled`, `clients.prenom`, `clients.num_fidelite`, `clients.date_premiere_vente`, `clients.total_ventes_fidelite`. Ces ajouts avaient été faits à la main (le log de juin/juillet les mentionne) puis perdus lors d'une restauration du dump du 18/06. | 4 écrans plantent en 500 aujourd'hui (smoke test) : dashboard, recherche produits POS, recherche clients POS, historique articles. La validation d'un ticket client fidélité et l'impression du ticket planteraient aussi. |
| Aucun script ne reproduit ces ajouts | Une seule migration (`bons_achat`) ; les colonnes n'ont jamais été scriptées. `db_schema_utf8.json` décrit une base qui n'existe plus. | Impossible de redéployer sur un autre poste ou chez un autre client sans refaire les ajouts à la main. |
| Trigger de stock `cbl_stock` | **Décision du 16/10/2026 : on garde le trigger tel qu'il est en base**, le script `update_transfer_module.sql` ne doit pas être appliqué. Le trigger débite l'expéditeur et crédite le récepteur de `detbontransferts.qte` (états > 1 puis = 3) et ignore `qteenvoi` / `qterecu`. Le code web écrit donc `qte` = quantité expédiée ; un écart de réception est enregistré mais ne modifie pas le stock (à régulariser par bon de sortie ou inventaire). | Module transfert aligné, 91 tests Feature au vert. |
| Règlements : le bureau écrit `creglements` ET `creglementdets` | 823 règlements, 824 détails, tous liés. Laravel n'écrit que `creglements` car le trigger `creglement_calc` plante sur `creglementdets` (colonne `bonconventionid` absente de cette table). | Les états du bureau qui lisent `creglementdets` ne verront pas les encaissements faits sur le web. |
| Numérotation des tickets | Le bureau numérote `2611010772` (préfixe 261101 + séquence). Laravel fait `max()+1`, ce qui suit la séquence, mais son repli `yy000001` est d'un autre format. | OK tant qu'il y a des tickets ; à verrouiller. |
| Référentiel | 3 sites (101 Central Achat sans vente, 102 Velaro, 103 Manar), 2 caisses, 3 utilisateurs (mots de passe de 4 caractères en clair), 10 rôles, 127 types de droits (toutes les abréviations citées dans le code existent). | Le système de droits est cohérent avec la base. |
| Qualité des données bureau | 77 lignes de stock négatives (65 sur le site 102), 19 tickets dont l'entête `totalttc` ne correspond pas aux lignes (tickets du bureau à 0), 2 tickets avec reste négatif, 6 tickets validés sans lignes, 100 % des tickets sans `statutdocumentid`. | Les filtres « statut » du web reposent sur une logique de repli ; le droit « stock négatif » sera sollicité. |
| Configuration bureau exploitable | `retailconfigs` contient déjà `moderetourargent`, `modereglementretourid`, `modereglementbonconventionid`, `tauxticketresto`, `perioderetour`. `stations.urlapilbtprint` existe pour l'impression. | Beaucoup de constantes codées en dur peuvent être lues depuis la base. |

## 2. Phase 0 — Remettre le projet d'aplomb (urgent, 2 à 3 jours)

1. ~~**Scripter le schéma additionnel**~~ **Fait le 16/10/2026** : migration idempotente `2026_10_16_100000_golden_pos_fidelite_sms` (table `bons_achat` complétée, `sms_envois`, `sousfamilles.is_loyalty_enabled`, `clients.prenom`, `clients.fidelite_debut`, paramètres fidélité/SMS dans `retailconfigs`). Le n° de carte fidélité utilise `clients.barcode` (colonne du bureau), le nombre de ventes est calculé depuis `ctickets`. Reste à faire : déplacer et documenter `update_transfer_module.sql`.
2. ~~**Vérifier les 4 routes 500**~~ **Fait** : smoke test à 0 erreur. Smoke test ajouté au `composer test` le 16/10/2026.
3. ~~**Environnement**~~ **Fait le 16/10/2026** (`LOG_CHANNEL=daily`, `LOG_DAILY_DAYS=14`, `Log::info` SQL supprimés ; reste `APP_ENV=production` et `APP_DEBUG=false` à poser dans le `.env` du poste magasin) : `APP_DEBUG=false` et `APP_ENV=production` sur le poste magasin, `LOG_CHANNEL=daily` (le log pèse 4,3 Mo), supprimer les 5 `Log::info` qui tracent le SQL dans tickets et commissions.
4. ~~**Nettoyage du dépôt**~~ **Fait le 16/10/2026** (scratch supprimés, `db_schema*.json` désuivis et ignorés, README réécrit) : supprimer les ~45 scripts jetables à la racine (`check_*.php`, `find_*.php`, `test*.php`, `get()`, `scratch_clients.txt`…), retirer `db_schema.json` et `db_schema_utf8.json` de git, remplacer le README Laravel par un README Golden Pos (installation, lancement, base).
5. ~~**Scripts de lancement**~~ **Fait le 16/10/2026** (PID enregistrés dans `.golden_pos.pid`, arrêt ciblé par `Fermer_Projet.bat` ; Apache non retenu) : `lancer_invisible.vbs` et `Fermer_Projet.bat` tuent tous les `php.exe` et `node.exe` de la machine. Les restreindre au PID du serveur (fichier `.pid`) ou passer à Apache (XAMPP est installé).

## 3. Phase 1 — Fiabilité métier de la caisse

État au 16/10/2026 : **fait**, sauf les deux derniers points. 99 tests Feature au vert, smoke test sans erreur.

- ✅ **Site courant** : `App\Support\SiteCourant` remplace les 41 replis `?? 102` ; un utilisateur sans site est refusé à la connexion et sur chaque écran (403).
- ✅ **Journée de caisse** : `JourneeCaisseService` donne une règle unique (journée de l'utilisateur, sinon journée ouverte la plus récente de son site) pour la caisse, la validation du ticket, le complément d'acompte, l'état et la clôture. Plusieurs caissiers peuvent travailler sur la même journée ; chaque ticket porte son `caissierid`. La liaison caisse ↔ poste (`stations`) reste à faire avec la Phase 3.
- ✅ **Ticket en attente** : la reprise ne supprime plus rien. Le ticket repris est supprimé seulement à la validation (ou à la remise en attente), dans la même transaction ; une double validation depuis deux postes est refusée.
- ✅ **Règlements** : sans toucher au trigger, la caisse écrit désormais au format du bureau (`creglements.documentid = 0`, statut Payé + `creglementdets` lié au ticket), vérifié sur les 823 règlements existants. Le bureau voit donc les encaissements du web.
- ✅ **Fidélité** : fait (voir commit fidélité/SMS).
- ✅ **TVA des transferts** : taux réel du produit (`taxefamilles.tvavente`) sur les demandes et bons, TVA totale en en-tête. `prodid` = 0 quand `produitid` dépasse un integer (convention déjà utilisée par les demandes).
- ✅ **Constantes** : `config/pos.php` + `retailconfigs`.
- ✅ **Validation** : client et vendeur du ticket doivent exister ; plus de vendeur par défaut « 1 ».
- ⏳ **Numérotation** : inchangée (`max()+1` sous verrou, compatible avec la séquence du bureau). Le format de repli `yy000001` ne sert que sur une base vide.
- ⏳ **FormRequest** : les validations sont encore dans les contrôleurs ; prévu avec le découpage de la Phase 2.

## 4. Phase 2 — Architecture et qualité

État au 16/10/2026 : **fait**. 99 tests Feature au vert, smoke test sans erreur.

- ✅ **Contrôleur caisse découpé** : `StoreTicketRequest` (validation), `TicketService` (lignes, TVA, totaux, variante), `NumerotationService` (numéro sous verrou), `ReglementService`, `FideliteService`, `JourneeCaisseService`. Refus métier via l'exception `VenteRefusee`. Ouverture et clôture de journée validées par `StoreOuvertureRequest` / `StoreClotureRequest`.
- ✅ **Vue caisse découpée** : `pos.blade.php` (5 270 lignes) devient un fichier d'assemblage de 80 lignes ; 25 modales dans `caisse/modals/`, l'écran et l'en-tête dans `caisse/partials/`, le JavaScript en 6 fichiers dans `caisse/scripts/`. HTML rendu vérifié identique avant/après (4 949 lignes). Le JavaScript reste en Blade (il utilise `route()` et `csrf_token()`) : le passage en modules Vite est reporté.
- ✅ **Modèle User** : relations vers des classes inexistantes remplacées par des accès lecture seule (`siteRecord`, `employeeRecord`, `roleRecord`).
- ✅ **Code mort** : `CaisseController::index`, `ClientController::show`, `CommissionController::calculate` et leurs routes supprimés ; double inclusion des boîtes de dialogue (identifiants HTML en double) corrigée dans la caisse et le layout.
- ✅ **Tests** : décision du 16/10/2026, tout reste sur la base `velaro` (pas de base de test séparée) ; les tests s'exécutent en transactions annulées. Pas de CI externe (la base n'est accessible que sur le poste).
- ✅ **Performance** : tableau de bord en 11 requêtes au lieu de 22, indicateurs vérifiés identiques. Pas d'index ajouté : volumes faibles (795 tickets) et base partagée avec le bureau.

## 5. Phase 3 — Fonctionnel et expérience utilisateur

État au 16/10/2026 : **fait**, sauf l'impression thermique. 105 tests Feature au vert, smoke test sans erreur.

- ✅ **Choix du site à la connexion** : proposé à tout rôle qui a le droit « Modifier Site doc vente » (`updatesitevente`, réglable dans Gestion des Droits), et plus seulement au login « admin ». Le contrôle est fait côté serveur : un rôle bloqué ne peut plus forcer un autre site en envoyant le formulaire à la main. Le site habituel est présélectionné.
- ✅ **Stocks négatifs** : écran Stock > Stocks négatifs (quantité, dernière vente de la variante, recherche) et alerte en caisse quand la quantité du ticket dépasse le stock du site.
- ✅ **Contrôle des données** : Paramètres > Contrôle des données (droit `config`), en lecture seule : tickets dont l'entête diffère des lignes, tickets sans ligne, tickets sur-payés, restes à payer, journées ouvertes depuis plus d'un jour, tickets en attente de plus de 7 jours, bons d'achat expirés, stocks négatifs.
- ✅ **Mots de passe** : page « Changer mon mot de passe » (clic sur le nom en haut à droite), contrôle de l'ancien mot de passe, 6 caractères minimum pour tout nouveau mot de passe (aussi en Gestion des utilisateurs). Stockage inchangé (le logiciel de bureau lit en clair).
- ✅ **Demandes de transfert** : conservées (fonctionnelles et testées), même si le magasin ne les utilise pas encore.
- ⏳ **Impression thermique** : `stations.urlapilbtprint` est vide sur les 3 postes ; à brancher quand l'API d'impression du bureau sera configurée. L'impression navigateur (`window.print()`) reste en place.

## 6. Phase 4 — Déploiement et exploitation

État au 16/10/2026 : **fait** (fichiers prêts et vérifiés ; l'installation sur le poste serveur reste à lancer par l'administrateur).

- ✅ **Serveur** : `deploy/apache/golden-pos.conf` (hôte virtuel XAMPP, port 8080, réseau local uniquement, HTTPS prêt à activer), syntaxe validée par `httpd -t`. `deploy/env.production.example` (debug désactivé, journaux 30 jours niveau warning) et `deploy/mise_en_production.bat` (sauvegarde, dépendances sans outils de dev, migrations, caches `optimize`, smoke test). Mise en cache des routes vérifiée. Vite n'est pas utilisé par les écrans : le serveur `npm run dev` n'est pas nécessaire en production.
- ✅ **Sauvegarde** : `deploy/sauvegarde_velaro.bat` (`pg_dump` compressé, archive vérifiée par `pg_restore --list`, rotation 30 jours). Testé : archive de 16 Mo en 3 s. Pas de test de restauration dans une autre base (décision : tout reste sur `velaro`).
- ✅ **Supervision** : smoke test planifié chaque jour à 07:30 (`routes/console.php`, journal `storage/logs/smoke-test.log`), code retour 1 et trace dans le journal applicatif si une page plante. `deploy/planifier_taches.bat` enregistre les tâches Windows (planificateur Laravel + sauvegarde 23:30).
- ✅ **Documentation** : `CLAUDE.md` (règles, conventions du bureau, ajouts Golden Pos, organisation du code, exploitation) et `README.md`.

**À faire sur le poste serveur (administrateur)** : copier `deploy/env.production.example` en `.env` et le compléter, inclure `golden-pos.conf` dans Apache, lancer `deploy/planifier_taches.bat` en administrateur, puis `deploy/mise_en_production.bat`.

## 7. Décisions à prendre avant de commencer

1. La fidélité (bons d'achat, paliers 10/20/30 %) est-elle voulue par le magasin ? Sinon retirer le code au lieu d'ajouter les colonnes.
2. Qui a le droit de modifier les triggers PostgreSQL du bureau (`creglement_calc`) ?
3. Un caissier peut-il vendre sur la journée d'un autre caissier du même site ?
4. Le web remplace-t-il le bureau pour la vente, ou les deux cohabitent-ils (dans ce cas la numérotation et `creglementdets` sont bloquants) ?
5. Base de test : peut-on restaurer le dump dans `velaro_test` sur ce poste ?

## 8. Ordre conseillé et effort

| Étape | Effort | Dépend de |
|---|---|---|
| Phase 0 (schéma, env, nettoyage) | 2 à 3 jours | Décision 1 |
| Phase 1 (fiabilité caisse) | 1 à 2 semaines | Décisions 2, 3, 4 |
| Phase 2 (architecture, tests, découpage POS) | 2 à 4 semaines | Décision 5 |
| Phase 3 (fonctionnel) | en continu | Phase 1 |
| Phase 4 (déploiement) | 2 à 3 jours | Phase 0 |
