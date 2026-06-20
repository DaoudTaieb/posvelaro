<?php
echo "produits barcode2:\n";
echo json_encode(DB::table('produits')->whereNotNull('barcode2')->where('barcode2', '!=', '')->limit(2)->get(['produitid', 'produitcode', 'barcode2']));
echo "\n\nproduit2s barcode2:\n";
echo json_encode(DB::table('produit2s')->whereNotNull('barcode2')->where('barcode2', '!=', '')->limit(2)->get(['produit2id', 'produitid', 'barcode2']));
