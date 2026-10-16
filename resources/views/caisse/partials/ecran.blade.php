
<div class="pos-container">

    <!-- HEADER -->
    <div class="pos-header" style="flex-direction: column; padding: 10px; border-bottom: 1px solid var(--border);">
        <!-- TOP LCD DISPLAY -->
        <div class="top-display" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 15px;">
            <div class="display-value" id="display-qte-prix" style="flex: 1; text-align: left;">***BIENVENUE CHEZ {{ strtoupper($siteName) }}***</div>
            <div class="display-value" id="display-remise" style="flex: 1; text-align: center;"></div>
            <div class="display-value" id="display-total" style="flex: 1; text-align: right;"></div>
        </div>

        <!-- HEADER FIELDS -->
        <div class="header-fields" style="width: 100%; display: flex; align-items: center; justify-content: space-between; font-size: 11px; padding-bottom: 25px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="field-group">
                    <label>Ticket N°</label>
                    <input type="text" id="ticketNumber" value="{{ $draftId ?? '' }}" style="width: 110px; padding: 4px; border: 1px solid #9ca3af; border-radius: 4px; text-align: center; font-weight: bold;" onkeypress="if(event.key==='Enter'){ searchTicket(this.value); }" title="Tapez un numéro pour chercher ou laissez vide pour un nouveau ticket">
                </div>
                <div class="field-group">
                    <label>Date</label>
                    <span style="padding-left: 5px; font-weight: bold;">{{ date('d/m/Y') }}</span>
                </div>
                
                <div class="field-group">
                    <label>Client</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="text" id="clientCode" value="{{ $client ? ($client->clientcode ?: $client->clientid) : '1' }}" style="width: 150px; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px 0 0 4px; border-right: none;" onkeypress="if(event.key==='Enter'){searchClientById(this.value)}">
                        <button class="btn-lookup" onclick="openClientModal()" style="padding: 4px 10px; border: 1px solid #d1d5db; background: white; border-radius: 0 4px 4px 0; cursor: pointer; height: 25px;">...</button>
                        
                        <div style="position: absolute; top: 100%; left: 0; padding-top: 4px; font-size: 11px; white-space: nowrap;">
                            <div id="clientName" style="text-transform: uppercase;">{{ $client ? $client->nom : 'PASSAGER' }}</div>
                            <div id="clientSoldeInfo" style="margin-top: 2px; color: #0369a1; {{ (!$client || $client->nom === 'PASSAGER') ? 'display: none;' : '' }}">
                                Solde : {{ number_format((float)($client->solde ?? 0), 3, '.', '') }} DT | Solde.Fid : {{ number_format((float)($client->soldefidelite ?? 0), 3, '.', '') }} DT | P.Fid: {{ number_format((float)($client->pointfidelite ?? 0), 1, '.', '') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label>Vendeur</label>
                    <input type="text" id="vendeurName" value="{{ $defaultVendeur ? $defaultVendeur->nom : '' }}" style="width: 150px;" readonly>
                    <button class="btn-lookup" onclick="openVendeurModal()">...</button>
                </div>
            </div>

            <div class="header-stats" style="font-weight: bold;">
                <span style="margin-right: 15px; color: {{ ($demandeTransfCount ?? 0) > 0 ? '#dc3545' : 'inherit' }};">Demande Transf: {{ $demandeTransfCount ?? 0 }}</span>
                <span style="color: {{ ($bonTransfCount ?? 0) > 0 ? '#dc3545' : 'inherit' }};">Bon Transf: {{ $bonTransfCount ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="pos-main">
        <!-- TICKET TABLE -->
        <div class="ticket-area">
            <div class="ticket-table-container">
                <table class="ticket-table" id="ticketTable">
                    <thead>
                        <tr>
                            <th style="width: 250px;">
                                <div style="display: flex; align-items: center; gap: 5px;">
                                    <span style="width: 50px; display: inline-block;">Réf</span>
                                    <input type="text" id="scanInput" class="ref-input" autofocus style="width: 100%; border: 1px solid #d1d5db; outline: none; padding: 2px 5px; font-weight: normal;">
                                    <button class="btn-lookup" onclick="openProductModal()">...</button>
                                </div>
                            </th>
                            <th>Désignation</th>
                            <th class="align-right" style="width: 60px;">Qte</th>
                            <th class="align-right" style="width: 80px;">Prix</th>
                            <th class="align-right" style="width: 60px;">Rem%</th>
                            <th class="align-right" style="width: 80px;">Prix Net</th>
                            <th class="align-right" style="width: 80px;">Total</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ticketBody">
                        <!-- Dynamically added rows -->
                    </tbody>
                </table>
            </div>

            <!-- TICKET FOOTER -->
            <div class="ticket-footer">
                <div>Nbre Ligne <span id="lblNbLignes" style="margin-left: 10px; font-weight: normal;">0</span></div>
                <div>Total Quantité <span id="lblTotalQte" style="margin-left: 10px; font-weight: normal;">0</span></div>
                <div>Acompte <span id="lblAcompte" style="margin-left: 10px; font-weight: normal;">0.000</span></div>
                <div style="color: #6b7280;">Reste à payer <span id="lblRestePayer" style="margin-left: 10px; font-weight: normal; color: #111827;">0.000</span></div>
            </div>

            <!-- PRODUCT INFO BLOCK -->
            <div style="padding: 10px 15px; background: white; border-top: 1px solid var(--border);">
                <div id="product-info-name" style="text-transform: uppercase; font-size: 14px; font-weight: bold; margin-bottom: 3px; min-height: 16px;"></div>
                <div id="product-info-stock" style="font-size: 11px; font-weight: bold; min-height: 12px;"></div>
            </div>
        </div>

        <!-- RIGHT AREA -->
        <div class="pos-right">
            <div class="grand-total" id="grandTotal" onclick="openGlobalDiscountModal()" style="cursor: pointer;" title="Cliquez pour appliquer une remise globale sur le ticket">0.000</div>
            
            <button class="btn-validate" onclick="validerTicket()" title="Valider Ticket">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>

            <div class="right-small-btns">
                <button class="btn-square" onclick="annulerTicket()" title="Annuler Ticket">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
                <a href="{{ url('/') }}" class="btn-square" title="Quitter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- BOTTOM ACTIONS GRID -->
    <div class="pos-footer-grid">
        <!-- COL 1: Clients -->
        <div class="grid-col">
            <div class="grid-header">Clients</div>
            <button class="grid-btn" onclick="openClientModal()">Recherche</button>
            <button class="grid-btn" onclick="openCreateClientModal()">Création</button>
            <button class="grid-btn" onclick="openEditClientModal()">Fiche</button>
            <button class="grid-btn" onclick="openClientHistoryModal()">Historique d'Achat</button>
        </div>

        <!-- COL 2: Articles -->
        <div class="grid-col">
            <div class="grid-header">Articles</div>
            <button class="grid-btn" onclick="openProductModal()">Recherche</button>
            <button class="grid-btn" onclick="openCheckStockModal()">Stock</button>
            <button class="grid-btn" onclick="openEditLineModal()">Modification</button>
            <button class="grid-btn" onclick="printDraftTicket()">Imprimer Ticket</button>
        </div>

        <!-- COL 3: Services -->
        <div class="grid-col">
            <div class="grid-header">Services</div>
            <button class="grid-btn" onclick="window.miseEnAttente()">Mise en attente du ticket</button>
            <button class="grid-btn" onclick="openRepriseModal()">Reprise d'un ticket en attente</button>
            <button class="grid-btn" onclick="openConsultationModal()">Consultation Tickets</button>
            <button class="grid-btn" onclick="openArticleHistoryModal()">Consultation Articles</button>
            <button class="grid-btn" onclick="openSmsModal()">Envoyer SMS</button>
            <div class="split-col">
                <button class="grid-btn" onclick="window.location.href='{{ route('vente.journee.etat') }}'" style="background: #e0f2fe; border-color: #0ea5e9; font-weight: bold; color: #0369a1;">Etat Caisse</button>
                <button class="grid-btn" onclick="window.location.href='{{ route('vente.journee.cloture') }}'" style="background: #fee2e2; border-color: #ef4444; font-weight: bold; color: #991b1b;">Clôture</button>
            </div>
        </div>

        <!-- COL 4: Règlements -->
        <div class="grid-col">
            <div class="grid-header">Règlements</div>
            <div class="split-col">
                <button class="grid-btn" style="font-weight: bold;" onclick="openQuickPaymentModal(1, 'Réglement Espèce')">Espèce</button>
                <button class="grid-btn" onclick="openChequeModal()">Cheque</button>
            </div>
            <div class="split-col">
                <button class="grid-btn" onclick="openQuickPaymentModal(4, 'Réglement Carte Bancaire')">C.B</button>
                <button class="grid-btn" onclick="openChequeCadeauxModal()">Chq.Cad</button>
            </div>
            <div class="split-col">
                <button class="grid-btn" onclick="openRetourModal()">Retour</button>
                <button class="grid-btn" onclick="handleRetour2Click()">Retour2</button>
            </div>
            <div class="split-col">
                <button class="grid-btn" onclick="openCreditFlow()">Crédit</button>
                <button class="grid-btn" onclick="openComplementAcompteFlow()">C.Acompte</button>
            </div>
        </div>
    </div>

</div>
