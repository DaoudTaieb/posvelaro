<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$viewDef = "
 SELECT d.detdemandetransfertid,
    d.demandetransfertid,
    d.siteid,
    d.siterecepteurid,
    d.produitid,
    d.taxefamilleid,
    d.ht,
    d.ttc,
    d.qte,
    d.totalht,
    d.remise,
    d.remise2,
    d.totalhtnet,
    d.taxe1,
    d.vtaxe1,
    d.taxe2,
    d.vtaxe2,
    d.taxe3,
    d.vtaxe3,
    d.taxe4,
    d.vtaxe4,
    d.tva,
    d.vtva,
    d.totalttc,
    d.totalttcnet,
    d.date,
    d.description,
    d.largeur,
    d.longueur,
    d.surface,
    d.produit2id,
    d.pointer,
    d.ordre,
    d.prodid,
    d.dateproduction,
    d.dlc,
    d.lot,
    d.qterecu,
    d.qteecart,
    d.qteenvoi,
    b.demandetransfertnumero,
    p.ht_vente,
    p.ht_achat,
    p.produitlibelle,
    p.ttc_achat,
    p.ttc_vente,
    s.libelle AS site,
    s2.libelle AS siterecepteur,
    COALESCE(tt.tailleid, 0) AS tailleid,
    tt.taillelibelle,
    COALESCE(cc.couleurid, 0) AS couleurid,
    cc.couleurlibelle,
    cc.couleurcode,
    ps.barcode2,
    p.produitcode,
    p.reference,
    d.etatdemandetransfertid,
    COALESCE(v7.qtestock, 0::numeric) AS qtestock,
    ps.ordree AS produitordree,
    b.confirmer,
    s.ordre AS siteordree,
    d.journalcaisseid,
    j.journalcaissenumero,
    e2.libelle AS etatlibelle,
    COALESCE(v8.stockvirtuel, 0::numeric) AS qtestockrecep
   FROM detdemandetransferts d
     LEFT JOIN demandetransferts b ON d.demandetransfertid = b.demandetransfertid
     LEFT JOIN produits p ON d.produitid = p.produitid
     LEFT JOIN produit2s ps ON d.produit2id = ps.produit2id
     LEFT JOIN stock2s v7 ON d.produitid = v7.produitid AND d.produit2id = v7.produit2id AND d.siteid = v7.siteid
     LEFT JOIN sites s ON d.siteid = s.siteid
     LEFT JOIN sites s2 ON d.siterecepteurid = s2.siteid
     LEFT JOIN tailles tt ON ps.tailleid = tt.tailleid
     LEFT JOIN couleurs cc ON ps.couleurid = cc.couleurid
     LEFT JOIN etatdemandetransferts e2 ON d.etatdemandetransfertid = e2.etatdemandetransfertid
     LEFT JOIN journalcaisses j ON b.journalcaisseid = j.journalcaisseid
     LEFT JOIN stock2s v8 ON d.produitid = v8.produitid AND d.produit2id = v8.produit2id AND d.siterecepteurid = v8.siteid;
";

$viewDef2 = "
 SELECT d.detdemandetransfertid,
    d.demandetransfertid,
    d.siteid,
    d.siterecepteurid,
    d.produitid,
    d.taxefamilleid,
    d.ht,
    d.ttc,
    d.qte,
    d.totalht,
    d.remise,
    d.remise2,
    d.totalhtnet,
    d.taxe1,
    d.vtaxe1,
    d.taxe2,
    d.vtaxe2,
    d.taxe3,
    d.vtaxe3,
    d.taxe4,
    d.vtaxe4,
    d.tva,
    d.vtva,
    d.totalttc,
    d.totalttcnet,
    d.date,
    d.largeur,
    d.longueur,
    d.surface,
    d.produit2id,
    d.pointer,
    d.ordre,
    d.prodid,
    d.dateproduction,
    d.dlc,
    d.lot,
    d.qteenvoi,
    d.qteecart,
    b.demandetransfertnumero,
    p.ht_vente,
    p.ht_achat,
    p.produitlibelle,
    p.ttc_achat,
    p.ttc_vente,
    s.libelle AS site,
    s2.libelle AS siterecepteur,
    COALESCE(tt.tailleid, 0) AS tailleid,
    tt.taillelibelle,
    COALESCE(cc.couleurid, 0) AS couleurid,
    cc.couleurlibelle,
    cc.couleurcode,
    ps.barcode2,
    p.produitcode,
    p.reference,
    d.etatdemandetransfertid,
    b.demandetransfertdate,
    COALESCE(v7.qtestock, 0::numeric) AS qtestock,
    ps.ordree AS produitordree,
    b.confirmer,
    s.ordre AS siteordree,
        CASE
            WHEN COALESCE(v8.qtestock, 0::numeric) < d.qte THEN COALESCE(v8.qtestock, 0::numeric)
            ELSE d.qte
        END AS qterecu,
    COALESCE(v8.qtestock, 0::numeric) AS stockdisponible,
        CASE
            WHEN d.etatdemandetransfertid = 2 THEN 'En attente de validation'::text
            ELSE e2.libelle
        END AS etatlibelle,
        CASE
            WHEN d.etatdemandetransfertid = 2 AND COALESCE(v8.qtestock, 0::numeric) <= 0::numeric THEN 'Stock non disponible'::text
            ELSE d.description
        END AS description
   FROM detdemandetransferts d
     LEFT JOIN demandetransferts b ON d.demandetransfertid = b.demandetransfertid
     LEFT JOIN produits p ON d.produitid = p.produitid
     LEFT JOIN produit2s ps ON d.produit2id = ps.produit2id
     LEFT JOIN stock2s v7 ON d.produitid = v7.produitid AND d.produit2id = v7.produit2id AND d.siteid = v7.siteid
     LEFT JOIN stock2s v8 ON d.produitid = v8.produitid AND d.produit2id = v8.produit2id AND d.siterecepteurid = v8.siteid
     LEFT JOIN sites s ON d.siteid = s.siteid
     LEFT JOIN sites s2 ON d.siterecepteurid = s2.siteid
     LEFT JOIN tailles tt ON ps.tailleid = tt.tailleid
     LEFT JOIN couleurs cc ON ps.couleurid = cc.couleurid
     LEFT JOIN etatdemandetransferts e2 ON d.etatdemandetransfertid = e2.etatdemandetransfertid;
";


try {
    DB::statement('DROP VIEW IF EXISTS pointagedetdemandetransfertsviews CASCADE');
    echo "Dropped view pointagedetdemandetransfertsviews\n";

    DB::statement('DROP VIEW IF EXISTS detdemandetransfertsviews CASCADE');
    echo "Dropped view detdemandetransfertsviews\n";
    
    DB::statement('ALTER TABLE detdemandetransferts ALTER COLUMN prodid TYPE bigint');
    echo "Fixed detdemandetransferts prodid to BIGINT\n";
    
    DB::statement("CREATE OR REPLACE VIEW detdemandetransfertsviews AS " . $viewDef);
    echo "Recreated view detdemandetransfertsviews\n";

    DB::statement("CREATE OR REPLACE VIEW pointagedetdemandetransfertsviews AS " . $viewDef2);
    echo "Recreated view pointagedetdemandetransfertsviews\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "Done\n";
