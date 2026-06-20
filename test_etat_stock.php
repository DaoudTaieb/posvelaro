<?php
$sub = DB::table('vdetmvtstcs')->where('siteid', 102)
    ->select('produit2id', 
        DB::raw('SUM(CASE WHEN qteachat > 0 THEN qteachat ELSE 0 END) as total_achat'), 
        DB::raw('SUM(CASE WHEN qteachat < 0 THEN ABS(qteachat) ELSE 0 END) as total_ret_achat'), 
        DB::raw('SUM(CASE WHEN qtevente > 0 THEN qtevente ELSE 0 END) as total_vente'), 
        DB::raw('SUM(CASE WHEN qtevente < 0 THEN ABS(qtevente) ELSE 0 END) as total_ret_vente'), 
        DB::raw('SUM(CASE WHEN (qtetransfert + qteinout + qteecart) > 0 THEN (qtetransfert + qteinout + qteecart) ELSE 0 END) as total_entrer'), 
        DB::raw('SUM(CASE WHEN (qtetransfert + qteinout + qteecart) < 0 THEN ABS(qtetransfert + qteinout + qteecart) ELSE 0 END) as total_sortie')
    )->groupBy('produit2id');

echo json_encode(DB::table('vproduit2stocks')
    ->leftJoinSub($sub, 'm', 'vproduit2stocks.produit2id', '=', 'm.produit2id')
    ->where('siteid', 102)
    ->limit(1)
    ->get()
);
