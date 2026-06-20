<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Ventes Journée</title>
    <style>
        body {
            background-color: #a3a3a3;
            margin: 0;
            padding: 10px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            color: #000;
        }
        .ticket {
            background-color: #fff;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            margin: 0 auto;
            padding: 15px;
            border: 2px solid #fbbf24;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .flex { display: flex; justify-content: space-between; }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin-bottom: 20px;
        }

        .header-info {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .line-solid {
            border-bottom: 2px solid #000;
            margin: 5px 0;
        }
        .line-dashed {
            border-bottom: 1px dashed #000;
            margin: 3px 0;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 13px;
        }

        .table-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-top: 2px;
        }

        .table-row .name { width: 75%; }
        .table-row .qte { width: 25%; text-align: right; }
        
        .recap-row .col1 { width: 50%; }
        .recap-row .col2 { width: 25%; text-align: center; }
        .recap-row .col3 { width: 25%; text-align: center; }
    </style>
</head>
<body>

<div class="ticket">
    <div class="title">Détails Ventes Journée</div>
    
    <div class="header-info">
        <div>Période:</div>
        <div>Du {{ $journee->dateouverture ? \Carbon\Carbon::parse($journee->dateouverture)->format('d/m/Y H:i') : '' }} Au {{ $journee->datecloture ? \Carbon\Carbon::parse($journee->datecloture)->format('d/m/Y H:i') : 'En cours' }}</div>
        
        <div>Site:</div>
        <div>{{ $journee->agence_nom ?? 'Velaro' }}</div>
        
        <div>Caisse:</div>
        <div>{{ $journee->siteid }} - {{ $journee->caisse_nom ?? 'CaisseM1' }}</div>
        
        <div>Chiffre:</div>
        <div>{{ number_format((float)$chiffre, 3, '.', '') }}</div>
        
        <div>Qté:</div>
        <div>{{ (int)$totalQte }}</div>
    </div>

    <div class="line-solid"></div>

    {{-- Boucle Familles --}}
    @foreach($groupedByFamille as $famName => $famData)
        <div class="text-center font-bold" style="margin-top: 10px; font-size: 15px;">{{ mb_strtoupper($famName) }}</div>
        
        @foreach($famData['sous_familles'] as $sfName => $sfData)
            <div class="font-bold" style="margin-top: 5px; font-size: 14px;">{{ mb_strtoupper($sfName) }}</div>
            <div class="line-dashed"></div>
            <div class="table-header">
                <div>Produit</div>
                <div>Qté</div>
            </div>
            <div class="line-dashed"></div>
            
            @foreach($sfData['produits'] as $prodName => $prodQte)
                <div class="table-row">
                    <div class="name">{{ mb_strtoupper($prodName) }}</div>
                    <div class="qte">{{ (int)$prodQte }}</div>
                </div>
            @endforeach
            
            <div class="line-solid"></div>
            <div class="table-row font-bold">
                <div class="name">Total {{ mb_strtoupper($sfName) }}</div>
                <div class="qte">{{ (int)$sfData['total_qte'] }}</div>
            </div>
            <div class="line-solid"></div>
        @endforeach
        
        <div class="table-row font-bold">
            <div class="name" style="text-align: center; width: 100%;">Total {{ mb_strtoupper($famName) }} <span style="float: right;">{{ number_format($famData['total_qte'], 1, '.', '') }}</span></div>
        </div>
        <div class="line-solid"></div>
    @endforeach

    {{-- RECAP COULEUR --}}
    <div class="title" style="margin-top: 25px;">Récap Ventes Par Couleur</div>
    <div class="line-dashed"></div>
    <div class="table-header recap-row">
        <div class="col1">Couleur</div>
        <div class="col2">Qté</div>
        <div class="col3">%</div>
    </div>
    <div class="line-dashed"></div>
    @foreach($groupedByCouleur as $coulName => $coulQte)
        <div class="table-row recap-row">
            <div class="col1">{{ mb_strtoupper($coulName) }}</div>
            <div class="col2">{{ (int)$coulQte }}</div>
            <div class="col3">{{ $totalQte > 0 ? round(($coulQte / $totalQte) * 100) : 0 }} %</div>
        </div>
    @endforeach
    <div class="line-solid"></div>
    <div class="table-row recap-row font-bold">
        <div class="col1">Total</div>
        <div class="col2">{{ (int)array_sum($groupedByCouleur) }}</div>
        <div class="col3"></div>
    </div>
    <div class="line-solid"></div>

    {{-- RECAP VENDEUR --}}
    <div class="title" style="margin-top: 25px;">Récap Ventes Par Vendeur</div>
    <div class="line-dashed"></div>
    <div class="table-header recap-row">
        <div class="col1">Vendeur</div>
        <div class="col2">Qté</div>
        <div class="col3">%</div>
    </div>
    <div class="line-dashed"></div>
    @foreach($groupedByVendeur as $vendName => $vendQte)
        <div class="table-row recap-row">
            <div class="col1">{{ mb_strtoupper($vendName) }}</div>
            <div class="col2">{{ (int)$vendQte }}</div>
            <div class="col3">{{ $totalQte > 0 ? round(($vendQte / $totalQte) * 100) : 0 }} %</div>
        </div>
    @endforeach
    <div class="line-solid"></div>
    <div class="table-row recap-row font-bold">
        <div class="col1">Total</div>
        <div class="col2">{{ (int)array_sum($groupedByVendeur) }}</div>
        <div class="col3"></div>
    </div>
    <div class="line-solid"></div>

</div>

</body>
</html>
