@extends('layouts.app')
@section('title', 'État de la Journée')

@section('content')
<div class="main-content-inner" style="padding: 20px;">
    
    {{-- Contrôles d'impression (cachés lors de l'impression) --}}
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
        <button onclick="window.print()" style="background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; font-weight: 500;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Imprimer l'état
        </button>
    </div>

    {{-- Conteneur principal (A4 / Paysage approx) --}}
    <div class="etat-container" style="background: white; border: 1px solid #ccc; padding: 20px; font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #333;">
        
        {{-- En-tête du rapport --}}
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
            <div style="font-weight: 700; text-transform: uppercase;">
                CLÔTURE DE LA CAISSE Velaro || {{ $caisse->libelle ?? 'CAISSE' }} 
                {{ \Carbon\Carbon::parse($journalCaisse->dateouverture)->format('d-m-Y H:i') }} - 
                @if($journalCaisse->isclosed)
                    CAISSE CLÔTURÉE
                @else
                    CAISSE NON CLÔTURÉE
                @endif
                || OUVRIR PAR {{ $adminName }}
            </div>
            <div style="display: flex; gap: 4px;">
                <button onclick="document.getElementById('filterModal').style.display='flex'" style="background: none; border: 1px solid #ccc; padding: 4px 8px; cursor: pointer;" title="Filtrer les journées">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </button>
                <button onclick="window.location.href='{{ route('vente.journee.etat') }}'" style="background: none; border: 1px solid #ccc; padding: 4px 8px; cursor: pointer;" title="Réinitialiser">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        {{-- Ligne 1 : Ventes & CA Sous Familles --}}
        <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px; margin-bottom: 20px;">
            
            {{-- VENTES & RETOURS EFFECTUEES --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Ventes & Retours effectuées
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">CODE</th>
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">RÉFÉRENCE</th>
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">DÉSIGNATION</th>
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">COULEUR</th>
                            <th style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">TAILLE</th>
                            <th style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">PV.U.TTC</th>
                            <th style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">QTÉS</th>
                            <th style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">REMISE $</th>
                            <th style="text-align: right; padding: 6px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventesRaw as $v)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">{{ $v->code }}</td>
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">{{ $v->reference }}</td>
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0; max-width: 120px; word-wrap: break-word;">{{ $v->designation }}</td>
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">{{ $v->couleur }}</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ $v->taille }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">{{ number_format($v->pv_u_ttc, 3, '.', '') }}</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ round($v->qtes) }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">{{ number_format($v->remise, 3, '.', '') }}</td>
                            <td style="text-align: right; padding: 6px;">{{ number_format($v->montant, 3, '.', '') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="padding: 10px; text-align: center; color: #94a3b8;">Aucune vente enregistrée.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align: right; padding: 4px 6px; font-weight: 600; border-right: 1px solid #e2e8f0; border-left: none; border-bottom: none;">Ventes</td>
                            <td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ $totalVentesQte }}</td>
                            <td style="text-align: right; padding: 4px 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 4px 6px;">{{ number_format($totalVentesMontant, 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" style="text-align: right; padding: 4px 6px; font-weight: 600; color: #dc2626; border-right: 1px solid #e2e8f0;">Retour</td>
                            <td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ $totalRetourQte }}</td>
                            <td style="text-align: right; padding: 4px 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 4px 6px;">{{ number_format($totalRetourMontant, 3, '.', '') }}</td>
                        </tr>
                        <tr style="font-weight: 700; border-top: 1px solid #e2e8f0;">
                            <td colspan="6" style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">Total</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ $totalVentesQte + $totalRetourQte }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 6px;">{{ number_format($totalVentesMontant + $totalRetourMontant, 3, '.', '') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- CHIFFRE D'AFFAIRE / Sous FAMILLES --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; display: flex; flex-direction: column;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Chiffre d'affaire / Sous Familles
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">CODE</th>
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">Sous FAMILLE</th>
                            <th style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">PV.U.TTC</th>
                            <th style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">QTÉS</th>
                            <th style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">REMISE $</th>
                            <th style="text-align: right; padding: 6px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caSousFamilles as $sf)
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">{{ $sf->code }}</td>
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">{{ $sf->libelle }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">{{ number_format($sf->pv_u_ttc, 3, '.', '') }}</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ round($sf->qtes) }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">{{ number_format($sf->remise, 3, '.', '') }}</td>
                            <td style="text-align: right; padding: 6px;">{{ number_format($sf->montant, 3, '.', '') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding: 10px; text-align: center; color: #94a3b8;">Aucune donnée</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right; padding: 4px 6px; font-weight: 600; border-right: 1px solid #e2e8f0;">Ventes</td>
                            <td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ $caSousFamilles->sum('qtes') }}</td>
                            <td style="text-align: right; padding: 4px 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 4px 6px;">{{ number_format($caSousFamilles->sum('montant'), 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align: right; padding: 4px 6px; font-weight: 600; color: #dc2626; border-right: 1px solid #e2e8f0;">Retour</td>
                            <td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">0</td>
                            <td style="text-align: right; padding: 4px 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 4px 6px;">0.000</td>
                        </tr>
                        <tr style="font-weight: 700; border-top: 1px solid #e2e8f0;">
                            <td colspan="3" style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">Total</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ $caSousFamilles->sum('qtes') }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">0.000</td>
                            <td style="text-align: right; padding: 6px;">{{ number_format($caSousFamilles->sum('montant'), 3, '.', '') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Ligne 2 : Blocs de pied de page --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            
            {{-- CAISSE --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Caisse
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">INTITULÉ</th>
                            <th style="text-align: right; padding: 6px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">VENTES REGLEES</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['ventes_reglees'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">+ACOMPTES ( N.V )</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['acomptes_nv'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">+ACOMPTES ( A.V )</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['acomptes_av'], 3, '.', '') }}</td></tr>
                        <tr style="color: #2563eb; font-weight: 600;"><td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">RECETTE BRUTE</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['recette_brute'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">- DEPENSES DIVERS</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['depenses_divers'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">- ACOMPTES PERSONNELS</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['acomptes_personnels'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">- COMMISSIONS</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['commissions'], 3, '.', '') }}</td></tr>
                        <tr style="color: #16a34a; font-weight: 600;"><td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">RECETTE NETTE</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($caisseTotaux['recette_nette'], 3, '.', '') }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- DÉTAILS RECETTES --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Détails Recettes
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">INTITULÉ</th>
                            <th style="text-align: right; padding: 6px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Espèce</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['espece'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Dépense</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['depense'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Espèce Net</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['espece_net'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Chèque</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['cheque'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Carte Crédit</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['carte_credit'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Bon D'Achats</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['bon_achats'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Chèque Cadeau</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['cheque_cadeau'], 3, '.', '') }}</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Autres</td><td style="text-align: right; padding: 4px 6px;">{{ number_format($recettes['autres'], 3, '.', '') }}</td></tr>
                        <tr style="font-weight: 700; border-top: 1px solid #e2e8f0;"><td style="padding: 6px; border-right: 1px solid #e2e8f0;">Total</td><td style="text-align: right; padding: 6px;">{{ number_format($totalRecettesDetails, 3, '.', '') }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- AUTRES INFORMATIONS --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Autres Informations
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">INTITULÉ</th>
                            <th style="text-align: right; padding: 6px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Crédit</td><td style="text-align: right; padding: 4px 6px;">0.000</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Crédit Acompte</td><td style="text-align: right; padding: 4px 6px;">0.000</td></tr>
                        <tr><td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">Coupon Manuella</td><td style="text-align: right; padding: 4px 6px;">0.000</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- CHIFFRE D'AFFAIRE / Vendeur --}}
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div style="background: #f8fafc; padding: 8px 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; font-size: 11px;">
                    Chiffre d'affaire / Vendeur
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <th style="text-align: left; padding: 6px; border-right: 1px solid #e2e8f0;">Nom</th>
                            <th style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">Qté</th>
                            <th style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">MONTANT</th>
                            <th style="text-align: right; padding: 6px;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caVendeur as $vendeur)
                        @php
                            $percentage = $totalVendeurMontant > 0 ? ($vendeur->montant / $totalVendeurMontant) * 100 : 0;
                        @endphp
                        <tr>
                            <td style="padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ $vendeur->nom }} {{ $vendeur->prenom }}</td>
                            <td style="text-align: center; padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ round($vendeur->qte) }}</td>
                            <td style="text-align: right; padding: 4px 6px; border-right: 1px solid #e2e8f0;">{{ number_format($vendeur->montant, 3, '.', '') }}</td>
                            <td style="text-align: right; padding: 4px 6px;">{{ number_format($percentage, 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 10px; text-align: center; color: #94a3b8;">Aucune donnée</td>
                        </tr>
                        @endforelse
                        <tr style="font-weight: 700; border-top: 1px solid #e2e8f0;">
                            <td style="padding: 6px; border-right: 1px solid #e2e8f0;">Total</td>
                            <td style="text-align: center; padding: 6px; border-right: 1px solid #e2e8f0;">{{ $caVendeur->sum('qte') }}</td>
                            <td style="text-align: right; padding: 6px; border-right: 1px solid #e2e8f0;">{{ number_format($totalVendeurMontant, 3, '.', '') }}</td>
                            <td style="text-align: right; padding: 6px;">100</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>

    {{-- MODAL FILTRE JOURNÉES CLÔTURES --}}
    <div id="filterModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: flex-start; padding-top: 40px;">
        <div style="background: white; width: 820px; max-height: 85vh; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden;">
            
            {{-- Header modal --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333;">Filtre journées Clôtures</h3>
                <button onclick="document.getElementById('filterModal').style.display='none'" style="background: none; border: none; cursor: pointer; font-size: 24px; color: #666; line-height: 1;">&times;</button>
            </div>
            
            {{-- Filtre section --}}
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f9fafb;">
                <div style="font-weight: 600; font-size: 13px; margin-bottom: 12px; color: #555;">Filtre</div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="font-size: 13px; font-weight: 500; color: #666;">DU</label>
                    <input type="date" id="filterDu" style="border: 1px solid #ccc; padding: 6px 10px; border-radius: 4px; font-size: 13px;">
                    <label style="font-size: 13px; font-weight: 500; color: #666;">AU</label>
                    <input type="date" id="filterAu" style="border: 1px solid #ccc; padding: 6px 10px; border-radius: 4px; font-size: 13px;">
                    <button onclick="filterJournees()" style="background: white; border: 1px solid #ccc; padding: 6px 16px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                    </button>
                </div>
            </div>

            {{-- Barre de recherche texte --}}
            <div style="padding: 10px 20px; display: flex; justify-content: flex-end;">
                <div style="position: relative; width: 240px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="filterSearch" placeholder="Enter text to search..." oninput="filterTableSearch()" style="width: 100%; border: 1px solid #ccc; padding: 6px 10px 6px 28px; border-radius: 4px; font-size: 12px;">
                </div>
            </div>

            {{-- Table des résultats --}}
            <div style="flex: 1; overflow-y: auto; padding: 0 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;" id="filterResultsTable">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0; position: sticky; top: 0; background: white;">
                            <th style="text-align: left; padding: 8px 6px; font-weight: 600; color: #555;">N°</th>
                            <th style="text-align: left; padding: 8px 6px; font-weight: 600; color: #555;">DATE OUVERTURE</th>
                            <th style="text-align: left; padding: 8px 6px; font-weight: 600; color: #555;">DATE CLÔTURE</th>
                            <th style="text-align: right; padding: 8px 6px; font-weight: 600; color: #555;">MONTANT THÉORIQUE</th>
                            <th style="text-align: right; padding: 8px 6px; font-weight: 600; color: #555;">MONTANT CIÔTURE</th>
                            <th style="text-align: center; padding: 8px 6px; width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="filterResultsBody">
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: #999; font-style: italic;">
                                Cliquez sur le bouton filtre pour rechercher des sessions.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div style="padding: 10px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f9fafb;">
                <div id="filterPagination" style="display: flex; gap: 4px;"></div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #666;">
                    Page Size: 
                    <select id="filterPageSize" style="border: 1px solid #ccc; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                        <option value="60" selected>60</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .sidebar {
                display: none !important;
            }
            .top-navbar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .etat-container {
                border: none !important;
                padding: 0 !important;
            }
            #filterModal {
                display: none !important;
            }
        }
    </style>
</div>
@endsection

@section('scripts')
<script>
    // Initialiser les dates par défaut (10 jours avant à aujourd'hui)
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const tenDaysAgo = new Date(today);
        tenDaysAgo.setDate(today.getDate() - 10);
        
        document.getElementById('filterDu').value = tenDaysAgo.toISOString().split('T')[0];
        document.getElementById('filterAu').value = today.toISOString().split('T')[0];
    });

    function filterJournees() {
        const du = document.getElementById('filterDu').value;
        const au = document.getElementById('filterAu').value;
        const tbody = document.getElementById('filterResultsBody');
        
        tbody.innerHTML = '<tr><td colspan="6" style="padding: 30px; text-align: center; color: #999;"><div style="display: inline-block; width: 20px; height: 20px; border: 2px solid #ccc; border-top-color: #666; border-radius: 50%; animation: spin 0.6s linear infinite;"></div> Chargement...</td></tr>';

        fetch(`{{ route('vente.journee.etat.filter') }}?du=${du}&au=${au}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="padding: 30px; text-align: center; color: #999; font-style: italic;">Aucune session trouvée pour cette période.</td></tr>';
                return;
            }

            let html = '';
            data.forEach(s => {
                const dateOuv = s.dateouverture ? new Date(s.dateouverture).toLocaleString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
                const dateClot = s.datecloture ? new Date(s.datecloture).toLocaleString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
                const montantTheo = parseFloat(s.montanttheorique || 0).toFixed(3);
                const montantClot = parseFloat(s.montantcloture || 0).toFixed(3);
                
                html += `<tr class="filter-row" style="border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.15s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background=''" data-numero="${s.journalcaissenumero || ''}" data-date="${dateOuv}">
                    <td style="padding: 8px 6px;">${s.journalcaissenumero || s.journalcaisseid}</td>
                    <td style="padding: 8px 6px; color: #2563eb;">${dateOuv}</td>
                    <td style="padding: 8px 6px;">${dateClot}</td>
                    <td style="text-align: right; padding: 8px 6px;">${montantTheo}</td>
                    <td style="text-align: right; padding: 8px 6px;">${montantClot}</td>
                    <td style="text-align: center; padding: 8px 6px;">
                        <button onclick="selectSession(${s.journalcaisseid})" style="background: none; border: none; cursor: pointer; color: #2563eb; font-size: 16px;" title="Sélectionner cette session">
                            ✔
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;

            // Mise à jour pagination
            const pag = document.getElementById('filterPagination');
            pag.innerHTML = `<span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #6b21a8; color: white; border-radius: 4px; font-size: 12px; font-weight: 600;">1</span>`;
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" style="padding: 30px; text-align: center; color: #dc2626;">Erreur: ${err.message}</td></tr>`;
        });
    }

    function selectSession(journalcaisseid) {
        window.location.href = `{{ route('vente.journee.etat') }}?journalcaisseid=${journalcaisseid}`;
    }

    function filterTableSearch() {
        const search = document.getElementById('filterSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#filterResultsBody .filter-row');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    }

    // Fermer modal en cliquant sur le fond
    document.getElementById('filterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection
