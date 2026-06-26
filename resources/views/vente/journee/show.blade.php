<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Clôture N° {{ $journee->journalcaissenumero }}</title>
    <style>
        body {
            background-color: #888;
            margin: 0;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            color: #000;
        }
        .ticket {
            background-color: #fff;
            width: 350px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #fbbf24;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .flex { display: flex; justify-content: space-between; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .underline { text-decoration: underline; }
        
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .row .label { width: 60%; }
        .row .sign { width: 10%; text-align: center; }
        .row .value { width: 30%; text-align: right; }
    </style>
</head>
<body>

<div class="ticket">
    <div class="text-center">
        <div>_________________________________</div>
        <div style="margin-top: 20px;">_________________________________</div>
    </div>
    
    <div class="mt-2" style="margin-top: 30px;">
        <div class="font-bold">CLÔTURE N° : {{ $journee->journalcaissenumero }}</div>
        <div class="font-bold">Golden Pos</div>
        <div class="font-bold">CAISSE : {{ $journee->caisse_numero ?? $journee->caisseid }}</div>
        <div class="font-bold">OUVERTE LE : {{ $journee->dateouverture ? \Carbon\Carbon::parse($journee->dateouverture)->format('d/m/Y H:i') : '' }}</div>
        <div class="font-bold">CLOTURÉE LE : {{ $journee->datecloture ? \Carbon\Carbon::parse($journee->datecloture)->format('d/m/Y H:i') : 'Non clôturée' }}</div>
        <div class="font-bold">PAR : {{ $journee->caissier_nom }}</div>
    </div>

    <div class="font-bold underline mt-2 mb-2" style="font-size: 16px;">CAISSE</div>
    
    <div class="row">
        <div class="label">VENTES REGLEES</div>
        <div class="sign">+</div>
        <div class="value">{{ number_format((float)$journee->ventereglee, 3, '.', ' ') }}</div>
    </div>
    <div class="row">
        <div class="label">ACOMPTES</div>
        <div class="sign">+</div>
        <div class="value">{{ number_format((float)($journee->totalacompteticket ?? 0), 3, '.', ' ') }}</div>
    </div>
    <div class="row">
        <div class="label" style="text-align: right; width: 70%;">Recette Brute =</div>
        <div class="value font-bold">{{ number_format((float)$journee->recettebrut, 3, '.', ' ') }}</div>
    </div>
    
    <div class="row">
        <div class="label">DEPENSES DIV</div>
        <div class="sign">-</div>
        <div class="value">{{ number_format((float)$journee->montantdepense, 3, '.', ' ') }}</div>
    </div>
    <div class="row">
        <div class="label" style="text-align: right; width: 70%;">Recette Nette =</div>
        <div class="value font-bold">{{ number_format((float)$journee->recettenet, 3, '.', ' ') }}</div>
    </div>

    <div class="row font-bold mt-2">
        <div class="label" style="width: 70%;">Recette Physique</div>
        <div class="value">{{ number_format((float)$journee->recettephysique, 3, '.', ' ') }}</div>
    </div>
    <div class="row font-bold">
        <div class="label" style="width: 70%;">Ecart</div>
        <div class="value">{{ number_format((float)$journee->ecart, 3, '.', ' ') }}</div>
    </div>

    <div class="font-bold underline mt-2 mb-2" style="font-size: 16px;">DETAILS RECETTE</div>

    <div class="flex mb-2">
        <div>Espèces</div>
        <div>{{ number_format((float)$journee->totalespecephys, 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Chèques</div>
        <div>{{ number_format((float)$journee->totalchequephys, 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Carte de crédit</div>
        <div>{{ number_format((float)$journee->totaltpephys, 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Bon d'Achats</div>
        <div>{{ number_format((float)$journee->totalbonconventionphys, 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Chèque Cadeau</div>
        <div>{{ number_format((float)$journee->totalcontrebonphys, 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Autres</div>
        <div>{{ number_format((float)$journee->totalregautrephys, 3, '.', ' ') }}</div>
    </div>
    
    <br>
    
    <div class="flex mb-2">
        <div>Acomptes ( N.V )</div>
        <div>{{ number_format((float)($journee->acomptenewticket ?? 0), 3, '.', ' ') }}</div>
    </div>
    <div class="flex mb-2">
        <div>Acomptes ( A.V )</div>
        <div>{{ number_format((float)($journee->totalcreditacompte ?? 0), 3, '.', ' ') }}</div>
    </div>
    
    <br>

    <div class="flex mb-2">
        <div>Crédit</div>
        <div>{{ number_format((float)($journee->totalcredit ?? 0), 3, '.', ' ') }}</div>
    </div>

    <div class="flex font-bold" style="border-bottom: 1px dashed #000; padding-bottom: 4px; margin-top: 10px;">
        <div style="width: 50%;"></div>
        <div style="width: 25%; text-align: center;">NOMBRE</div>
        <div style="width: 25%; text-align: center;">QTE</div>
    </div>
    <div class="flex" style="margin-top: 4px;">
        <div style="width: 50%; text-align: right; padding-right: 10px;">VENTES</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->nbreticket ?? 0) }}</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->totalqtevente ?? 0) }}</div>
    </div>
    <div class="flex">
        <div style="width: 50%; text-align: right; padding-right: 10px;">RETOURS</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->nbreretour ?? 0) }}</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->totalqteretour ?? 0) }}</div>
    </div>
    <div class="flex">
        <div style="width: 50%; text-align: right; padding-right: 10px;">TRANSFERT REÇU</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->nbretransfertrecu ?? 0) }}</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->totalqtetransfertrecu ?? 0) }}</div>
    </div>
    <div class="flex">
        <div style="width: 50%; text-align: right; padding-right: 10px;">TRANSFERT ENVOYÉ</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->nbretransfertenv ?? 0) }}</div>
        <div style="width: 25%; text-align: center;">{{ (int)($journee->totalqtetransfertenv ?? 0) }}</div>
    </div>

    <div class="font-bold underline mt-2 mb-2" style="font-size: 16px;">DEPENSES DIV</div>
    <div class="flex font-bold" style="border-bottom: 1px dashed #000; padding-bottom: 4px;">
        <div>LIBELLE</div>
        <div>MONTANT</div>
    </div>
    <div class="flex mt-2">
        <div></div>
        <div class="font-bold">{{ number_format((float)$journee->montantdepense, 3, '.', ' ') }}</div>
    </div>

    <div class="text-center mt-2" style="margin-top: 20px;">
        (-----------FIN CLÔTURE-----------)
    </div>

</div>

<script>
    // Imprimer automatiquement au chargement si besoin (désactivé par défaut)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>
