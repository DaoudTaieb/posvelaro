# Golden Pos — notes techniques

Caisse web (Laravel 12, PHP 8.2) qui travaille **directement sur la base PostgreSQL `velaro`** du
logiciel de bureau *Lexpert Business*, toujours utilisé au quotidien par le magasin. Les deux
applications écrivent dans les mêmes tables : la base n'appartient pas à Laravel.

## Règles à respecter

- **Ne jamais modifier les triggers ni les fonctions PostgreSQL** du bureau (`cbl_stock`,
  `cticket_calc`, `creglement_calc`, `depense_calc`, `csolde_calc`…). Le code web s'adapte à eux.
  Le script `database/update_transfer_module.sql` ne doit **pas** être appliqué.
- **Une seule base : `velaro`.** Pas de base de test séparée. Les tests Feature tournent sur
  `velaro` en `DatabaseTransactions` (tout est annulé). Ne jamais lancer les tests Dusk
  (`tests/Browser`) : ils écrivent hors transaction.
- **Tout ajout de schéma passe par une migration idempotente** (`Schema::hasTable` /
  `hasColumn`), rejouable après une restauration du dump du bureau. Aucune table du bureau n'est
  modifiée en dehors des colonnes Golden Pos documentées ci-dessous.
- **Mots de passe en clair** dans `users.password` : le bureau les lit ainsi
  (`App\Auth\VelaroUserProvider`). Ne pas les hacher.
- **Site courant obligatoire** : `App\Support\SiteCourant::id()` ; un compte sans site est refusé.

## Conventions du logiciel de bureau (constatées en base)

| Sujet | Convention |
|---|---|
| Stock | Calculé par le trigger `cbl_stock` à partir des documents. Ne jamais écrire `stock2s` à la main. |
| Transferts | `detbontransferts.qte` = quantité **expédiée** (le trigger ignore `qteenvoi` / `qterecu`). Expéditeur débité à l'état > 1, récepteur crédité à l'état 3. |
| Règlements ticket | `creglements.documentid = 0`, `typeid = 1`, `statusreglementid = 2`, `montantav = -montant` + une ligne `creglementdets` (documentid = ticket). Voir `ReglementService`. |
| Totaux ticket | `cticket_calc` recalcule l'entête à partir des lignes ; `creglement_calc` recalcule acompte / reste. |
| Numéro de ticket | `max(cticketnumero) + 1`, sous `pg_advisory_xact_lock` (`NumerotationService`). |
| Identifiants | Plusieurs tables sans séquence utilisable : `max()+1` sous verrou consultatif (`ParametreHelpers`). |
| Retour d'argent | Dépense famille 10 « Retour Argent » liée au ticket (`depenses.cticketid`). |
| `retailconfigs` | Paramètres partagés ; booléens écrits « True » / « False » (casse conservée). |
| `prodid` | Colonne `integer` alors que `produitid` est un `bigint` à 13 chiffres : écrire 0. |
| Client passager | `clients.clientid = 1` (`config('pos.client_passager_id')`). |

## Ajouts Golden Pos dans la base

Migration `2026_10_16_100000_golden_pos_fidelite_sms` : tables `bons_achat`, `sms_envois` ;
colonnes `sousfamilles.is_loyalty_enabled`, `clients.prenom`, `clients.fidelite_debut` ; paramètres
`fidelite_*` et `bipsms_*` dans `retailconfigs` (page « Général »). Le n° de carte fidélité est la
colonne existante `clients.barcode`.

## Organisation du code

- `app/Services` : `TicketService`, `NumerotationService`, `ReglementService`, `JourneeCaisseService`,
  `FideliteService` (paliers 10/20/30 %, bon de 10 DT/paire dès la 4e vente, validité 3 mois,
  carte valable si 2 ventes par année anniversaire), `SmsService` (Bip SMS + journal `sms_envois`).
- `app/Support` : `Droits` (droits du rôle : `userdroits` / `userdroitdets` / `typedroits`),
  `SiteCourant`, `RetailConfig`, `TaxeProduit`, `Code128` (code-barres des bons d'achat).
- Droits sur les routes : middleware `droit:` (`routes/web.php`) et directive Blade `@peut`.
- Caisse : `resources/views/caisse/pos.blade.php` assemble `caisse/partials`, `caisse/modals`
  (une modale par fichier) et les scripts `public/js/caisse/*.js` (scripts classiques, ordre
  significatif : fonctions globales appelées par les `onclick`). Les valeurs serveur (routes,
  jeton CSRF, client et vendeur par défaut) sont dans l'objet `GP` (`caisse/partials/config-js`).
  Après modification d'un script : `node --check public/js/caisse/<fichier>.js`.
- Impression : `ImpressionTicketService` et `App\Support\EscPos` ; réglages par caisse dans
  `caisseconfigs` (`ticketprinter` du bureau, lignes `goldenpos_*`) ; envoi Windows par
  `resources/scripts/impression-brute.ps1` (spouleur, RAW) ou réseau (port 9100).

## Performance

- **Aucune ressource internet** : polices (Inter, Press Start 2P), Chart.js 4.4.9, jQuery 3.6.0 et
  Select2 4.1.0-rc.0 sont copiés dans `public/vendor` (versions figées dans `package.json`). Ne pas
  réintroduire de lien CDN : le magasin doit fonctionner même sans connexion internet.
- Chart.js n'est chargé que par le tableau de bord.
- Totaux des listes (tickets, commissions, état de stock, mouvements) : une seule requête
  d'agrégat ; recherche clients de la caisse : fidélité calculée en lot (`FideliteService::etatsEnLot`).
- Recherches à la frappe de la caisse : `differer()` (250 ms) et `fetchDernier()` (annule la requête
  précédente) dans `public/js/caisse/caisse.js`.
- Apache (`deploy/apache/golden-pos.conf`) : compression gzip et cache navigateur des fichiers statiques.

## Commandes

```bash
php artisan test --testsuite=Feature   # tests (transactions annulées sur velaro)
php artisan app:smoke-test             # ouvre toutes les pages GET, code retour 1 si une page plante
composer test                          # les deux
php artisan migrate                    # migrations Golden Pos (idempotentes)
```

## Exploitation (dossier `deploy/`)

- `sauvegarde_velaro.bat` : `pg_dump` compressé et vérifié, rotation 30 jours.
- `planifier_taches.bat` (administrateur) : `schedule:run` chaque minute (smoke test à 07:30,
  journal `storage/logs/smoke-test.log`) et sauvegarde quotidienne à 23:30.
- `apache/golden-pos.conf` : hôte virtuel XAMPP sur le port 8080, réservé au réseau local.
- `env.production.example` puis `mise_en_production.bat` : sauvegarde, dépendances, migrations,
  caches, smoke test.

Le plan d'amélioration et son état sont dans `PLAN_AMELIORATION.md`.
