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

## 3. Phase 1 — Fiabilité métier de la caisse (1 à 2 semaines)

- **Site courant** : remplacer les 41 `?? 102` par un service `SiteCourant` qui lève une erreur claire si l'utilisateur n'a pas de site ; refuser la connexion d'un utilisateur sans `siteid` ou sans `employeeid`.
- **Journée de caisse** : aujourd'hui, si le caissier n'a pas de journée ouverte, le ticket est rattaché à la journée ouverte d'un autre caissier du même site. Décider : soit interdire, soit tracer explicitement. Lier la caisse au poste (`caisses.machineid` / `stations`) comme le fait le bureau.
- **Ticket en attente** : ne plus supprimer le ticket au moment de la reprise ; le marquer « en reprise » et le supprimer seulement quand le nouveau ticket est validé (sinon perte si le navigateur se ferme).
- **Règlements** : corriger le trigger `creglement_calc` (tester `TG_TABLE_NAME` avant de lire `new.bonconventionid`) ou ajouter la colonne à `creglementdets`, puis réinsérer les `creglementdets` comme le bureau. À valider avec l'éditeur du logiciel de bureau.
- **Numérotation** : conserver `max()+1` sous verrou mais aligner le repli sur le format bureau (préfixe année + site + caisse). Idem pour les bons de transfert (`BTE-` n'existe pas côté bureau : vérifier qu'il n'est pas lu).
- **Fidélité** : sortir le recalcul du palier (`getLoyaltyTier`) d'un GET ; en faire une commande planifiée. Rendre le montant (10 DT/paire) et le seuil (4e vente) configurables dans `retailconfigs`.
- **TVA des transferts** : lire `taxefamilles.tvavente` du produit au lieu de `/ 1.19` codé en dur ; remplacer `prodid = 0` par la vraie valeur ou documenter la raison.
- **Constantes** : créer `config/pos.php` (client passager, modes de règlement, famille dépense « Retour argent », états transfert) alimenté par `retailconfigs` quand la clé existe.
- **Validation** : passer les gros `validate()` en FormRequest (ticket, clôture, bon de transfert), et valider `clientid`, `vendeurid`, `produit2id` comme existants et du bon site.

## 4. Phase 2 — Architecture et qualité (2 à 4 semaines)

- **Découper `CaisseController::store`** (390 lignes) en services : `TicketService`, `ReglementService`, `FideliteService`, `NumerotationService`, `TransfertService`. Les contrôleurs ne gardent que l'orchestration HTTP.
- **Découper `pos.blade.php`** (5 171 lignes, 150 fonctions JS, 28 modales) : une vue par modale dans `resources/views/caisse/modals/`, le JavaScript en modules Vite (`resources/js/pos/panier.js`, `paiement.js`, `clients.js`, `api.js`) avec un wrapper `fetch` qui gère CSRF, erreurs 403 et messages. Compiler avec `npm run build` et retirer `public/hot` en production.
- **Modèle User** : supprimer les relations vers Site, Employee et UserDroit qui n'existent pas, ou créer ces modèles en lecture seule.
- **Code mort** : supprimer `CaisseController::index`, `ClientController::show`, `CommissionController::calculate` et leurs routes ; retirer la double inclusion de `partials.ui-dialogs`.
- **Tests** : créer une base `velaro_test` restaurée depuis le dump, exécuter les migrations dessus, pointer `phpunit.xml` dessus. Ne plus jamais lancer les tests sur la base du magasin (le test Dusk modifie `stock2s`). Ajouter une CI (GitHub Actions ou Azure DevOps) qui lance `composer test` et le smoke test.
- **Performance** : index composite `ctickets (siteid, cticketdate)` et `detctickets (cticketid, qte)` ; mettre en cache 60 s les agrégats du dashboard (10 requêtes par affichage).

## 5. Phase 3 — Fonctionnel et expérience utilisateur (en continu)

- Sélecteur de site au login pour tout utilisateur multi-sites (aujourd'hui limité au login « admin » côté JavaScript).
- Impression thermique via l'API `stations.urlapilbtprint` déjà prévue par le bureau, au lieu de `window.print()`.
- Écran « Stock négatifs » et alerte à la vente, puisque 77 lignes sont déjà négatives.
- Écran de rapprochement bureau/web : tickets dont l'entête diffère des lignes (19 cas), journées non envoyées (`envoyee = false` sur les 183).
- Compléter la gestion des demandes de transfert (jamais utilisée : 0 lignes) ou la masquer tant que le site 101 ne l'utilise pas.
- Gestion des mots de passe : impossible de hacher tant que le bureau lit en clair ; prévoir au minimum une longueur minimale et une page « changer mon mot de passe ».

## 6. Phase 4 — Déploiement et exploitation

- Serveur : Apache (XAMPP) ou Nginx + PHP-FPM au lieu de `php artisan serve` ; `php artisan optimize` ; HTTPS sur le réseau local.
- Sauvegarde : `pg_dump` quotidien planifié + test de restauration ; conserver le script de schéma Golden Pos pour rejouer après restauration.
- Supervision : log quotidien, alerte sur erreurs 500, commande `app:smoke-test` planifiée chaque matin.
- Documentation : `CLAUDE.md` ou `docs/` décrivant la base, les triggers, les conventions du bureau (montantav négatif, caissierid 0) et la procédure d'installation.

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
