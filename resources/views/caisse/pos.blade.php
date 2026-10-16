{{--
    Écran de caisse (POS). Fichier d'assemblage : le contenu est réparti dans
      caisse/partials/  en-tête (styles) et écran principal ;
      caisse/modals/    une modale par fichier ;
      caisse/scripts/   le JavaScript, dans l'ordre d'exécution d'origine.
    L'ordre des @include est significatif (les scripts utilisent les modales déjà déclarées).
--}}
<!DOCTYPE html>
<html lang="fr">
@include('caisse.partials.head')
<body>
@include('partials.ui-dialogs')
@include('caisse.partials.ecran')

@include('caisse.modals.creation-client')

@include('caisse.modals.fiche-client')

@include('caisse.modals.historique-client')

@include('caisse.modals.consultation-articles')

@include('caisse.modals.sms')

@include('caisse.modals.apercu-ticket')

@include('caisse.modals.consultation-stock')

@include('caisse.modals.modification-ligne')

@include('transfert.demande_envoye.partials.product_modal')

@include('caisse.modals.variantes')

@include('caisse.scripts.caisse')

@include('caisse.modals.recherche-client')

@include('caisse.modals.vendeur')

@include('caisse.modals.cheque-cadeau')

@include('caisse.modals.retour')

@include('caisse.modals.mouvements')

@include('caisse.modals.consultation-tickets')

@include('caisse.scripts.tickets-paiements')

@include('caisse.modals.cheque')

@include('caisse.scripts.cheque')

@include('caisse.modals.remise-globale')

@include('caisse.modals.paiement-rapide')

@include('caisse.scripts.remise-paiement-rapide')

@include('caisse.modals.paiement-multiple')

@include('caisse.modals.bon-achat')

@include('caisse.modals.avoir')

@include('caisse.modals.cheques-cadeaux')

@include('caisse.modals.coupon')

@include('caisse.scripts.cheques-cadeaux-coupon')

@include('caisse.modals.reprise')

@include('caisse.modals.complement-acompte')

@include('caisse.scripts.complement-acompte')

</body>
</html>
