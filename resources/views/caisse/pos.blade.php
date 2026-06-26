<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caisse {{ $siteName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --primary-light: #e0f2fe;
            --border: #e2e8f0;
            --text-secondary: #64748b;
        }
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #f8f9fa; /* slightly grey bg */
            overflow: hidden; /* no scrolling on main POS */
        }

        .pos-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 5px;
        }

        /* HEADER */
        .pos-header {
            display: flex;
            align-items: flex-start;
            padding: 5px;
            background: white;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .header-title {
            font-family: 'Press Start 2P', monospace;
            font-size: 16px;
            margin: 0;
            padding: 10px;
            width: 350px;
        }

        .header-fields {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            flex: 1;
        }

        .field-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-group label {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .field-group input {
            border: 1px solid #d1d5db;
            padding: 4px;
            border-radius: 3px;
            outline: none;
        }

        .btn-lookup {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 2px 8px;
            cursor: pointer;
            border-radius: 3px;
        }

        .header-stats {
            display: flex;
            gap: 15px;
            font-size: 11px;
            font-weight: 600;
            margin-left: auto;
        }

        /* MAIN AREA */
        .pos-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* TICKET TABLE AREA */
        .ticket-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
            margin: 5px;
            border: 1px solid var(--border);
            border-radius: 4px;
        }

        .ticket-table-container {
            flex: 1;
            overflow: auto;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .ticket-table th {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--border);
            font-weight: 600;
            background: #f9fafb;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .ticket-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .ref-input {
            width: 100%;
            border: 1px solid #d1d5db;
            padding: 4px;
            outline: none;
        }
        
        .ticket-footer {
            padding: 10px;
            background: #f9fafb;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
        }

        /* RIGHT PANEL */
        .pos-right {
            width: 150px;
            background: white;
            border-left: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .grand-total {
            background: white;
            border: 1px solid black;
            text-align: right;
            padding: 15px 10px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .btn-validate {
            background: white;
            border: 1px solid black;
            border-radius: 4px;
            cursor: pointer;
            height: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn-validate:hover {
            background: #f3f4f6;
        }

        .btn-validate svg {
            width: 48px;
            height: 48px;
        }

        .right-small-btns {
            display: flex;
            gap: 5px;
        }

        .btn-square {
            flex: 1;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .btn-square:hover {
            background: #f3f4f6;
        }

        /* BOTTOM ACTIONS GRID */
        .pos-footer-grid {
            margin-top: 5px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2px;
            background: white;
        }

        .grid-header {
            background: black;
            color: white;
            text-align: center;
            padding: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .grid-col {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .grid-btn {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: center;
            font-size: 11px;
            cursor: pointer;
            flex: 1;
        }

        .grid-btn:hover {
            background: #f3f4f6;
        }

        .split-col {
            display: flex;
            gap: 2px;
            flex: 1;
        }

        .split-col .grid-btn {
            flex: 1;
        }
        
        .display-value {
            font-family: 'Share Tech Mono', monospace;
            font-size: 32px;
            color: black;
            text-transform: uppercase;
        }
        
        .align-right { text-align: right !important; }

        /* Editable cells */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
        .editable-cell {
            border: 1px solid transparent;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            color: inherit;
            text-align: right;
            width: 60px;
            outline: none;
            cursor: text;
        }
        .editable-cell:focus {
            background: #fff;
            border: 1px solid #3b82f6;
            border-radius: 3px;
        }

        /* MODAL STYLES */
        .form-group {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .form-group label {
            white-space: nowrap;
            font-size: 11px;
            font-weight: 600;
        }
        .form-control {
            border: 1px solid #d1d5db;
            padding: 4px;
            border-radius: 4px;
            font-size: 11px;
            outline: none;
        }
        .btn-save {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-save:hover { background: #f3f4f6; }
        
        .cart-row:hover { background-color: #f1f5f9; }
        
        /* Pagination Styles for Modal */
        .pagination-wrapper {
            padding: 10px;
            background: #fff;
            border-top: 1px solid #e5e7eb;
        }
        .pagination-wrapper svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .pagination-wrapper nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .pagination-wrapper nav > div:first-child {
            display: none;
        }
        .pagination-wrapper nav > div:last-child {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }
        .pagination-wrapper p {
            margin: 0;
            color: #64748b;
        }
        .pagination-wrapper span.relative,
        .pagination-wrapper a.relative {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            margin-left: -1px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-decoration: none;
            transition: background 0.2s;
        }
        .pagination-wrapper a.relative:hover {
            background: #f1f5f9;
            color: #0ea5e9;
        }
        .pagination-wrapper span[aria-current="page"] > span {
            background: #e0f2fe;
            color: #0ea5e9;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            margin-left: -1px;
        }
        
        .modal-content .table-container table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .modal-content .table-container th {
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #4b5563;
            background: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .modal-content .table-container td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }
        .modal-content .table-container tr:hover td {
            background: #f8fafc;
        }
    </style>
</head>
<body>

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
                <span style="margin-right: 15px;">Demande Transf: 0</span>
                <span>Bon Transf: 0</span>
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
            <div class="grand-total" id="grandTotal">0.000</div>
            
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
                <button class="grid-btn" onclick="openQuickPaymentModal(3, 'Réglement Carte Bancaire')">C.B</button>
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

<!-- CREATE CLIENT MODAL -->
<div id="createClientModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 750px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Nouveau Client</h2>
            <button type="button" onclick="closeCreateClientModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px;">
            <form id="createClientForm">
                @csrf
                <table style="width: 100%; border-spacing: 10px; border-collapse: separate;">
                    <tr>
                        <td style="width: 120px;"><label>Nom / Raison</label></td>
                        <td><input type="text" name="raison" id="cc_raison" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>Prénom</label></td>
                        <td><input type="text" name="prenom" id="cc_prenom" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="bloque_credit" style="vertical-align: middle;"> Bloque Crédit</label></td>
                    </tr>
                    <tr>
                        <td><label>Matricule Fiscal</label></td>
                        <td><input type="text" name="matricule_fiscal" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="g_fidelite" checked style="vertical-align: middle;"> G.Fidèlite</label></td>
                        <td style="text-align: right;"><label>N° Carte Fid</label></td>
                        <td><input type="text" name="num_fidelite" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Telephone</label></td>
                        <td colspan="2"><input type="text" name="telephone" id="cc_telephone" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>E-mail</label></td>
                        <td><input type="email" name="email" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Date de Naissance</label></td>
                        <td colspan="2"><input type="date" name="date_naissance" value="{{ date('Y-m-d') }}" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td style="text-align: right;"><label>Ville</label></td>
                        <td><input type="text" name="ville" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td colspan="5"><label>Adresse</label></td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <textarea name="adresse" rows="2" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px; resize: vertical;"></textarea>
                        </td>
                    </tr>
                </table>
                <div id="cc_error" style="color: #dc2626; font-size: 11px; margin-top: 5px; display: none;">Raison & & Téléphone Obligatoire</div>
            </form>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="submitCreateClient()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeCreateClientModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

<!-- EDIT CLIENT MODAL -->
<div id="editClientModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 750px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 id="editClientModalTitle" style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Modification Client</h2>
            <button type="button" onclick="closeEditClientModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px;">
            <form id="editClientForm">
                @csrf
                <table style="width: 100%; border-spacing: 10px; border-collapse: separate;">
                    <tr>
                        <td style="width: 120px;"><label>Nom / Raison</label></td>
                        <td><input type="text" name="raison" id="ec_raison" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>Prénom</label></td>
                        <td><input type="text" name="prenom" id="ec_prenom" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="bloque_credit" id="ec_bloque_credit" style="vertical-align: middle;"> Bloque Crédit</label></td>
                    </tr>
                    <tr>
                        <td><label>Matricule Fiscal</label></td>
                        <td><input type="text" name="matricule_fiscal" id="ec_matricule_fiscal" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="g_fidelite" id="ec_g_fidelite" style="vertical-align: middle;"> G.Fidèlite</label></td>
                        <td style="text-align: right;"><label>N° Carte Fid</label></td>
                        <td><input type="text" name="num_fidelite" id="ec_num_fidelite" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Telephone</label></td>
                        <td colspan="2"><input type="text" name="telephone" id="ec_telephone" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>E-mail</label></td>
                        <td><input type="email" name="email" id="ec_email" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Date de Naissance</label></td>
                        <td colspan="2"><input type="date" name="date_naissance" id="ec_date_naissance" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td style="text-align: right;"><label>Ville</label></td>
                        <td><input type="text" name="ville" id="ec_ville" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td colspan="5"><label>Adresse</label></td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <textarea name="adresse" id="ec_adresse" rows="2" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px; resize: vertical;"></textarea>
                        </td>
                    </tr>
                </table>
                <div id="ec_error" style="color: #dc2626; font-size: 11px; margin-top: 5px; display: none;">Raison & & Téléphone Obligatoire</div>
            </form>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="submitEditClient()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeEditClientModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

<!-- CLIENT HISTORY MODAL -->
<div id="clientHistoryModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 800px; max-height: 80%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 id="clientHistoryModalTitle" style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Historique d'Achat</h2>
            <button type="button" onclick="closeClientHistoryModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #ddd;">
                        <th style="padding: 8px; font-weight: 600;">Date</th>
                        <th style="padding: 8px; font-weight: 600;">N° Ticket</th>
                        <th style="padding: 8px; font-weight: 600; text-align: center;">Qté Total</th>
                        <th style="padding: 8px; font-weight: 600; text-align: right;">Montant TTC</th>
                    </tr>
                </thead>
                <tbody id="clientHistoryTableBody">
                    <tr><td colspan="4" style="text-align: center; padding: 20px;">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeClientHistoryModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">Fermer</button>
        </div>
    </div>
</div>

<!-- ARTICLE HISTORY MODAL -->
<div id="articleHistoryModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1025; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 900px; max-height: 85%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Consultation des Articles (Historique)</h2>
            <button type="button" onclick="closeArticleHistoryModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Search Bar -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; gap: 10px;">
            <input type="text" id="articleHistorySearch" placeholder="Client, N° Tél, N° Carte Fidélité..." style="flex: 1; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" onkeypress="if(event.key==='Enter') searchArticleHistory()">
            <button onclick="searchArticleHistory()" style="padding: 6px 15px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Rechercher</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #ddd;">
                        <th style="padding: 8px; font-weight: 600;">Date</th>
                        <th style="padding: 8px; font-weight: 600;">Ticket</th>
                        <th style="padding: 8px; font-weight: 600;">Client</th>
                        <th style="padding: 8px; font-weight: 600;">Ref/Désignation</th>
                        <th style="padding: 8px; font-weight: 600;">Couleur/Taille</th>
                        <th style="padding: 8px; font-weight: 600; text-align: center;">Qté</th>
                        <th style="padding: 8px; font-weight: 600; text-align: right;">Total TTC</th>
                    </tr>
                </thead>
                <tbody id="articleHistoryTableBody">
                    <tr><td colspan="7" style="text-align: center; padding: 20px;">Veuillez effectuer une recherche...</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeArticleHistoryModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">Fermer</button>
        </div>
    </div>
</div>

<!-- SMS MODAL -->
<div id="smsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1025; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 500px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Envoyer SMS (Bip SMS)</h2>
            <button type="button" onclick="closeSmsModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px;">
            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Téléphone Destinataire</label>
                <input type="text" id="smsTelephone" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Ex: 216XXXXXXXX">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Message</label>
                <textarea id="smsMessage" rows="4" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="Tapez votre message ici..."></textarea>
            </div>
            <div id="smsFeedback" style="display: none; padding: 10px; margin-top: 10px; border-radius: 4px; text-align: center;"></div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="sendSmsAction()" style="padding: 6px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Envoyer</button>
            <button type="button" onclick="closeSmsModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
        </div>
    </div>
</div>

<!-- TICKET PREVIEW MODAL -->
<div id="ticketPreviewModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: transparent; border: none; box-shadow: none; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 324px; display: flex; justify-content: flex-end; margin-bottom: 5px;">
            <button type="button" onclick="closeTicketPreviewModal()" style="background: white; border: 1px solid #ccc; border-radius: 50%; width: 30px; height: 30px; font-size: 16px; cursor: pointer; color: #333; font-weight: bold;">&times;</button>
        </div>
        <div id="ticketPreviewContent" style="background: transparent;">
            <!-- Receipt loaded here -->
        </div>
    </div>
</div>

<!-- CHECK STOCK MODAL (ADVANCED) -->
<div id="checkStockModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #f8f9fa; z-index: 1025; justify-content: flex-start; align-items: flex-start; flex-direction: column;">
    
    <!-- Top Bar -->
    <div style="width: 100%; display: flex; align-items: flex-end; padding: 10px; background: white; border-bottom: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1); gap: 10px;">
        
        <div style="font-weight: bold; font-size: 14px; margin-right: 20px; padding-bottom: 5px;">Consultation Stock</div>
        
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Référence</label>
            <div style="display: flex;">
                <input type="text" id="advStockRef" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockRef').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Rayon</label>
            <div style="display: flex;">
                <select id="advStockRayon" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px 0 0 2px;">
                    <option value="">Select Rayon...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->categoryid }}">{{ $cat->categorylibelle }}</option>
                    @endforeach
                </select>
                <button onclick="document.getElementById('advStockRayon').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Couleur</label>
            <div style="display: flex;">
                <input type="text" id="advStockCouleur" style="border: 1px solid #ccc; padding: 4px; width: 100px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockCouleur').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Taille</label>
            <div style="display: flex;">
                <input type="text" id="advStockTaille" style="border: 1px solid #ccc; padding: 4px; width: 80px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockTaille').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px; margin-left: 10px;">
            <label style="font-size: 10px; color: #666;">Type Stock</label>
            <div style="display: flex;">
                <select id="advStockType" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px;">
                    <option value="reel">Réel</option>
                    <option value="virtuel">Virtuel</option>
                    <option value="reserve">Réservé</option>
                </select>
            </div>
        </div>

        <button onclick="searchAdvancedStock()" style="border: 1px solid #ccc; background: white; padding: 4px 15px; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 26px; margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </button>

        <div style="flex: 1; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeCheckStockModal()" style="border: 1px solid #ccc; background: white; width: 40px; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #333;">
                &times;
            </button>
        </div>
    </div>

    <!-- Table content -->
    <div style="flex: 1; width: 100%; overflow-y: auto; padding: 10px; background: white;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="border-bottom: 2px solid #ccc; background: #f1f5f9;">
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Référence</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Désignation</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Rayon</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Couleur</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Taille</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Dépôt / Magasin</th>
                    <th style="padding: 8px; text-align: right; background: #e2e8f0;" id="advStockQtyHeader">Quantité (Réel)</th>
                </tr>
            </thead>
            <tbody id="checkStockTableBody">
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">Veuillez saisir des critères et cliquer sur la coche pour chercher.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- EDIT LINE MODAL -->
<div id="editLineModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1035; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 350px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 13px;">
        <div style="padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Modifier la ligne</h2>
            <button type="button" onclick="closeEditLineModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <div style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Désignation</label>
                <div id="editLineDesignation" style="padding: 8px; background: #f1f5f9; border: 1px solid #ddd; border-radius: 4px; color: #333;">-</div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Prix Unitaire (TTC)</label>
                <input type="number" id="editLinePrix" step="0.001" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Quantité</label>
                    <input type="number" id="editLineQte" step="0.01" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Remise (%)</label>
                    <input type="number" id="editLineRemise" min="0" max="100" step="0.01" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeEditLineModal()" style="padding: 8px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="button" onclick="saveEditLine()" style="padding: 8px 15px; border: none; background: #2563eb; color: white; border-radius: 4px; cursor: pointer; font-weight: bold;">Valider</button>
            </div>
        </div>
    </div>
</div>

@include('transfert.demande_envoye.partials.product_modal')

<!-- SECOND MODAL FOR VARIANTS -->
<div id="variantsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1010; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 800px; max-height: 80%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <!-- Modal Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="flex: 1; text-align: center;">
                <h2 id="variantsModalTitle" style="margin: 0; font-size: 14px; color: #111827; font-weight: 700;">Titre Produit</h2>
            </div>
            <button type="button" onclick="document.getElementById('variantsModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                 <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="filter-variants" class="form-control" style="width: 100%; padding-left: 25px;" placeholder="Enter text to search..." onkeyup="filterVariantsList()">
                </div>
            </div>
            
            <div class="table-container" style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr>
                            <th style="width:15%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Code</th>
                            <th style="width:20%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Couleur</th>
                            <th style="width:10%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Taille</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Prix</th>
                            <th style="width:10%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Stock</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Stk.En Cours</th>
                            <th style="width:10%; text-align: center; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">
                                <a href="#" style="color: #9ca3af; text-decoration: none;">Clear</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody">
                        <!-- Variants here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Bottom controls inside variants modal -->
            <div style="display: flex; justify-content: flex-end; padding-top: 10px; border-top: 1px solid #e5e7eb; margin-top: 10px;">
                <button class="btn-save" style="padding: 6px 15px; margin-right: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                </button>
                <button class="btn-save" style="padding: 6px 15px;" onclick="document.getElementById('variantsModal').style.display='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT FOR LOGIC -->
<script>
    console.log("=== GOLDEN POS POS SCRIPT START ===");
    
    // Global functions definition at the very top
    function sendSmsDirectly(tel, msg) {
        let formData = new FormData();
        formData.append('telephone', tel);
        formData.append('message', msg);
        formData.append('_token', '{{ csrf_token() }}');

        fetch("{{ route('vente.caisse.pos.send_sms') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('SMS envoyé avec succès !');
            } else {
                alert('Erreur lors de l\'envoi du SMS: ' + (data.message || ''));
            }
        })
        .catch(error => {
            console.error(error);
            alert('Erreur réseau lors de l\'envoi du SMS.');
        });
    }

    window.submitTicketToBackend = function(data) {
        fetch(`{{ route('vente.caisse.store') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (typeof closePaymentModal === 'function') closePaymentModal();
                if (typeof closeMultiPaymentModal === 'function') closeMultiPaymentModal();
                alert(res.message);
                
                // Prompt to send SMS if client phone is available
                if (res.client_tel) {
                    let message = "";
                    if (res.is_first_sale) {
                        message = `Bienvenue chez Golden Pos, ${res.client_nom}! Nous vous remercions pour votre premier achat. Votre ticket N° ${res.cticketnumero} d'un montant de ${parseFloat(res.totalttc).toFixed(3)} DT a été validé.`;
                    } else {
                        message = `Merci pour votre fidélité chez Golden Pos, ${res.client_nom}! Votre ticket N° ${res.cticketnumero} d'un montant de ${parseFloat(res.totalttc).toFixed(3)} DT a été validé.`;
                    }
                    
                    if (confirm(`Voulez-vous envoyer un SMS de confirmation au client (${res.client_tel}) ?`)) {
                        sendSmsDirectly(res.client_tel, message);
                    }
                }

                if (res.print_url) {
                    window.open(res.print_url, '_blank').focus();
                }
                ticketLines = [];
                currentClientId = {{ $client ? $client->clientid : 1 }};
                document.getElementById('clientName').innerText = 'PASSAGER';
                document.getElementById('clientSoldeInfo').style.display = 'none';
                document.getElementById('clientCode').value = '4110001';
                
                // Refresh and increment the ticket number automatically
                if (res.cticketnumero) {
                    document.getElementById('ticketNumber').value = parseInt(res.cticketnumero) + 1;
                }
                
                // Keep the same currentVendeurId (vendor persists across tickets)
                
                renderTable();
                
                fetch(`{{ route('vente.caisse.en_attente') }}`)
                    .then(r => r.json())
                    .then(r => { console.log('En attente count:', r.count); })
                    .catch(e => console.error(e));
            } else {
                alert("Erreur : " + res.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert("Une erreur est survenue lors de l'enregistrement.");
        });
    };

    window.executeMiseEnAttente = function() {
        let data = {
            clientid: currentClientId,
            vendeurid: currentVendeurId,
            en_attente: true,
            lignes: ticketLines
        };
        window.submitTicketToBackend(data);
    };

    window.miseEnAttente = function() {
        try {
            if (ticketLines.length === 0) {
                alert("Le ticket est vide.");
                return;
            }
            pendingAction = 'miseEnAttente';
            openVendeurModal();
        } catch (e) {
            console.error("Error in miseEnAttente:", e);
            alert("Erreur lors de l'ouverture du popup: " + e.message);
        }
    };

    let ticketLines = [];
    let currentVariants = []; // Store variants to allow filtering
    let currentClientLoyaltyTier = 0;

    // Simulate adding an item when pressing enter on the ref input
    document.getElementById('scanInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            let ref = this.value.trim();
            
            fetch(`{{ route('vente.caisse.pos.scan_product') }}?code=${encodeURIComponent(ref)}`)
                .then(response => response.json())
                .then(product => {
                    if(product) {
                        addProductToTicket(product);
                    } else {
                        alert('Produit non trouvé');
                    }
                    this.value = '';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la recherche du produit');
                });
        }
    });

    let isRetourMode = false;

    function addProductToTicket(productData) {
        let prix = parseFloat(productData.ttc_vente) || 0;
        let remise = 0;
        if (productData.is_loyalty_enabled == 1) {
            remise = currentClientLoyaltyTier || 0;
        }
        let prixNet = prix - (prix * (remise / 100));
        let qteVal = isRetourMode ? -1 : 1;
        let product = {
            produitid: productData.produitid,
            produit2id: productData.produit2id,
            ref: productData.reference || productData.produitcode,
            code: productData.produitcode || productData.reference,
            reference: productData.reference || productData.produitcode,
            designation: productData.produitlibelle,
            taille: productData.taillelibelle,
            couleur: productData.couleurlibelle,
            qte: qteVal,
            prix: prix,
            remise: remise,
            prixNet: prixNet,
            total: qteVal * prixNet,
            stock: parseFloat(productData.total_stock) || 0,
            is_loyalty_enabled: productData.is_loyalty_enabled || 0
        };
        
        // Check if product already exists to increment Qty
        let existingLine = ticketLines.find(l => l.produitid === product.produitid && l.produit2id === product.produit2id);
        if (existingLine) {
            existingLine.qte += qteVal;
            existingLine.total = existingLine.qte * existingLine.prixNet;
        } else {
            ticketLines.push(product);
        }
        
        isRetourMode = false; // Reset
        renderTable();
    }

    let selectedLineIndex = -1;

    function selectLine(index) {
        selectedLineIndex = index;
        updateDisplaysForSelectedLine();
        
        let tbody = document.getElementById('ticketBody');
        for (let i = 0; i < tbody.children.length; i++) {
            tbody.children[i].style.backgroundColor = (i === index) ? '#e0f2fe' : ''; // Highlight selected row
        }
    }

    function updateDisplaysForSelectedLine() {
        if (selectedLineIndex >= 0 && selectedLineIndex < ticketLines.length) {
            let line = ticketLines[selectedLineIndex];
            let qte = parseFloat(line.qte) || 0;
            let prix = parseFloat(line.prix) || 0;
            let remise = parseFloat(line.remise) || 0;
            let total = parseFloat(line.total) || 0;
            
            document.getElementById('display-qte-prix').innerText = `${qte} X ${prix.toFixed(3)}`;
            document.getElementById('display-remise').innerText = `REMISE ${remise}`;
            document.getElementById('display-total').innerText = `TOTAL ${total.toFixed(3)}`;
            
            document.getElementById('product-info-name').innerText = `${line.designation || ''}`;
            document.getElementById('product-info-stock').innerText = `Stock ${line.stock || 0}`;
        } else {
            document.getElementById('display-qte-prix').innerText = '***BIENVENUE CHEZ {{ strtoupper($siteName) }}***';
            document.getElementById('display-remise').innerText = '';
            document.getElementById('display-total').innerText = '';
            
            document.getElementById('product-info-name').innerText = '';
            document.getElementById('product-info-stock').innerText = '';
        }
    }

    function renderTable() {
        // Remove existing rows
        let tbody = document.getElementById('ticketBody');
        tbody.innerHTML = '';
        
        let totalQte = 0;
        let grandTotal = 0;

        ticketLines.forEach((line, index) => {
            let qte = parseFloat(line.qte) || 0;
            let total = parseFloat(line.total) || 0;
            let prix = parseFloat(line.prix) || 0;
            let remise = parseFloat(line.remise) || 0;
            let prixNet = parseFloat(line.prixNet) || 0;
            
            totalQte += qte;
            grandTotal += total;

            let tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            tr.onclick = function() {
                selectLine(index);
            };
            if (index === selectedLineIndex) {
                tr.style.backgroundColor = '#e0f2fe';
            }

            tr.innerHTML = `
                <td>${line.reference || line.ref || ''}</td>
                <td>${line.designation || ''} ${line.taille ? ' - ' + line.taille : ''} ${line.couleur ? ' - ' + line.couleur : ''}</td>
                <td class="align-right">
                    <input type="number" value="${qte}" class="editable-cell" onchange="updateQty(${index}, this.value)" onfocus="this.select(); selectLine(${index});">
                </td>
                <td class="align-right">${prix.toFixed(3)}</td>
                <td class="align-right">
                    <input type="number" value="${remise}" class="editable-cell" onchange="updateRemise(${index}, this.value)" min="0" max="100" onfocus="this.select(); selectLine(${index});">
                </td>
                <td class="align-right">${prixNet.toFixed(3)}</td>
                <td class="align-right">${total.toFixed(3)}</td>
                <td style="text-align: center;">
                    <button onclick="removeLine(${index}); event.stopPropagation();" style="background: none; border: none; cursor: pointer; color: red;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Update stats
        document.getElementById('lblNbLignes').innerText = ticketLines.length;
        document.getElementById('lblTotalQte').innerText = totalQte;
        
        let formattedTotal = grandTotal.toFixed(3);
        document.getElementById('grandTotal').innerText = formattedTotal;
        document.getElementById('lblRestePayer').innerText = formattedTotal;
        
        if (ticketLines.length > 0) {
            // If no line selected, select the last one added
            if (selectedLineIndex < 0 || selectedLineIndex >= ticketLines.length) {
                selectedLineIndex = ticketLines.length - 1;
            }
            updateDisplaysForSelectedLine();
        } else {
            selectedLineIndex = -1;
            updateDisplaysForSelectedLine();
        }
        
        // Re-focus input
        document.getElementById('scanInput').focus();

        // Save to localStorage
        localStorage.setItem('pos_ticketLines', JSON.stringify(ticketLines));
        if (currentClientId) {
            localStorage.setItem('pos_currentClientId', currentClientId);
            localStorage.setItem('pos_clientName', document.getElementById('clientName').innerText);
            localStorage.setItem('pos_clientCode', document.getElementById('clientCode').value);
        } else {
            localStorage.removeItem('pos_currentClientId');
            localStorage.removeItem('pos_clientName');
            localStorage.removeItem('pos_clientCode');
        }
    }

    function updateQty(index, newQty) {
        let qte = parseFloat(newQty);
        if (qte > 0) {
            ticketLines[index].qte = qte;
            ticketLines[index].total = ticketLines[index].qte * ticketLines[index].prixNet;
        }
        renderTable();
    }

    function updateRemise(index, newRemise) {
        let remise = parseFloat(newRemise) || 0;
        if (remise >= 0 && remise <= 100) {
            ticketLines[index].remise = remise;
            let prix = ticketLines[index].prix;
            let prixNet = prix - (prix * (remise / 100));
            ticketLines[index].prixNet = prixNet;
            ticketLines[index].total = ticketLines[index].qte * prixNet;
        }
        renderTable();
    }

    function removeLine(index) {
        ticketLines.splice(index, 1);
        renderTable();
    }

    // validerTicket moved to bottom script

    function searchTicket(ticketId) {
        if (!ticketId) return;
        console.log('Recherche du ticket N°:', ticketId);
        // TODO: Ajouter l'appel AJAX pour récupérer le ticket
        alert('Recherche du ticket N° ' + ticketId + ' (fonctionnalité à implémenter)');
    }

    function annulerTicket() {
        if (complementMode) {
            exitComplementMode();
            return;
        }
        ticketLines = [];
        currentClientId = null;
        document.getElementById('clientCode').value = '';
        document.getElementById('clientName').innerText = 'PASSAGER';
        document.getElementById('clientSoldeInfo').style.display = 'none';
        renderTable();
        localStorage.removeItem('pos_ticketLines');
        localStorage.removeItem('pos_currentClientId');
        localStorage.removeItem('pos_clientName');
        localStorage.removeItem('pos_clientCode');
    }

    // ========== LOGIQUE MODAL PRODUIT ==========
    function handleRetour2Click() {
        isRetourMode = true;
        openProductModal();
    }

    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
        searchProducts(); // Load initially
    }

    function searchProducts() {
        const tbody = document.getElementById('modal-tbody');
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">Chargement...</td></tr>';
        
        const params = new URLSearchParams({
            sousfamilleid: document.getElementById('filter-sf').value,
            familleid: document.getElementById('filter-f').value,
            saisonid: document.getElementById('filter-s').value,
            categoryid: document.getElementById('filter-c').value,
            marqueid: document.getElementById('filter-m').value,
            search: document.getElementById('filter-search').value
        });

        fetch(`{{ route('vente.caisse.pos.search_products') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if(data.length === 0) {
                    tbody.innerHTML = '<tr id="no-data-row"><td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">No data to display</td></tr>';
                    return;
                }

                data.forEach(p => {
                    let price = parseFloat(p.ttc_vente) || 0;
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onmouseover = () => tr.style.background = '#f8fafc';
                    tr.onmouseout = () => tr.style.background = 'transparent';
                    
                    // Quand on clique sur une ligne du modal, on ouvre les variantes
                    tr.onclick = () => {
                        openVariantsModal(p);
                    };

                    let stk = parseFloat(p.total_stock) || 0;
                    tr.innerHTML = `
                        <td>${p.produitcode || ''}</td>
                        <td>${p.reference || ''}</td>
                        <td>${p.barcode2 || ''}</td>
                        <td>${p.produitlibelle || ''} ${p.taillelibelle ? ' - '+p.taillelibelle : ''} ${p.couleurlibelle ? ' - '+p.couleurlibelle : ''}</td>
                        <td>${p.famillelibelle || ''}</td>
                        <td>${p.sousfamillelibelle || ''}</td>
                        <td style="font-weight: bold; color: #059669;">${price.toFixed(3)}</td>
                        <td>${Number.isInteger(stk) ? stk : stk.toFixed(2)}</td>
                        <td>${p.fournisseur || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; color: red; padding: 20px;">Erreur de chargement</td></tr>';
            });
    }

    // ========== LOGIQUE MODAL VARIANTES ==========
    function openVariantsModal(parentProduct) {
        document.getElementById('variantsModal').style.display = 'flex';
        
        let titlePrice = parseFloat(parentProduct.ttc_vente) || 0;
        document.getElementById('variantsModalTitle').innerText = `${parentProduct.produitlibelle || 'Produit'} Prix:${titlePrice.toFixed(3)}`;
        
        const tbody = document.getElementById('variants-tbody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Chargement des variantes...</td></tr>';
        
        fetch(`{{ route('vente.caisse.pos.variants') }}?produitid=${parentProduct.produitid}`)
            .then(res => res.json())
            .then(data => {
                currentVariants = data;
                renderVariantsList(currentVariants);
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: red; padding: 20px;">Erreur de chargement des variantes</td></tr>';
            });
    }

    function renderVariantsList(variants) {
        const tbody = document.getElementById('variants-tbody');
        tbody.innerHTML = '';
        
        if(variants.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Aucune variante disponible</td></tr>';
            return;
        }

        variants.forEach(v => {
            let price = parseFloat(v.ttc_vente) || 0;
            let stk = parseFloat(v.total_stock) || 0;
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #e5e7eb';
            
            tr.innerHTML = `
                <td style="padding: 6px; color: #111827;">${v.produitcode || ''}</td>
                <td style="padding: 6px; color: #111827;">${v.couleurlibelle || ''}</td>
                <td style="padding: 6px; color: #111827;">${v.taillelibelle || ''}</td>
                <td style="padding: 6px; text-align: right; color: #111827;">${price.toFixed(3)}</td>
                <td style="padding: 6px; text-align: right; color: #111827;">${Number.isInteger(stk) ? stk : stk.toFixed(2)}</td>
                <td style="padding: 6px; text-align: right; color: #111827;">0</td>
                <td style="padding: 6px; text-align: center;">
                    <button type="button" style="background: #7e22ce; color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer;" onclick='selectVariant(${JSON.stringify(v).replace(/'/g, "&#39;")})'>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterVariantsList() {
        const search = document.getElementById('filter-variants').value.toLowerCase();
        if(!search) {
            renderVariantsList(currentVariants);
            return;
        }
        
        const filtered = currentVariants.filter(v => {
            return (v.produitcode && v.produitcode.toLowerCase().includes(search)) ||
                   (v.couleurlibelle && v.couleurlibelle.toLowerCase().includes(search)) ||
                   (v.taillelibelle && v.taillelibelle.toLowerCase().includes(search));
        });
        
        renderVariantsList(filtered);
    }

    function selectVariant(variantData) {
        addProductToTicket(variantData);
        // Optional: Hide modals if you want single add, or keep open to add multiple
        document.getElementById('variantsModal').style.display = 'none';
        document.getElementById('productModal').style.display = 'none';
        document.getElementById('scanInput').focus();
    }

    // === CLIENT SEARCH ===
    let currentClientId = {{ $client ? $client->clientid : 1 }};
    let selectedClientTemp = null;
    let currentClientPage = 1;

    function openClientModal() {
        document.getElementById('clientModal').style.display = 'flex';
        // Clear creation form
        document.getElementById('newClientRaison').value = '';
        document.getElementById('newClientTel').value = '';
        // Clear filters
        document.getElementById('clientSearchInput').value = '';
        document.querySelectorAll('.client-filter').forEach(i => i.value = '');
        selectedClientTemp = null;
        searchClients();
    }

    function closeClientModal() {
        document.getElementById('clientModal').style.display = 'none';
        document.getElementById('scanInput').focus();
        if (typeof pendingPaymentMode !== 'undefined') {
            pendingPaymentMode = null;
        }
    }

    function confirmClientSelection() {
        if (selectedClientTemp) {
            selectClient(selectedClientTemp);
        } else {
            closeClientModal();
        }
    }

    function searchClients(page = 1) {
        currentClientPage = page;
        let q = document.getElementById('clientSearchInput').value;
        let filters = {
            f_code: document.getElementById('f_code').value,
            f_nom: document.getElementById('f_nom').value,
            f_tel: document.getElementById('f_tel').value,
            f_adresse: document.getElementById('f_adresse').value,
            f_mf: document.getElementById('f_mf').value,
            f_remise: document.getElementById('f_remise').value,
            f_plafonremise: document.getElementById('f_plafonremise').value,
            f_solde: document.getElementById('f_solde').value,
        };

        let params = new URLSearchParams({ q: q, page: page });
        for (let key in filters) {
            if (filters[key]) params.append(key, filters[key]);
        }

        fetch(`{{ route('vente.caisse.pos.search_clients') }}?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                let tbody = document.getElementById('clientsTbody');
                tbody.innerHTML = '';
                
                let clients = data.data || data; // handle pagination object
                clients.forEach(c => {
                    let tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onmouseover = function() { if (!this.classList.contains('selected-row')) this.style.backgroundColor = '#f0f9ff'; };
                    tr.onmouseout = function() { if (!this.classList.contains('selected-row')) this.style.backgroundColor = ''; };
                    tr.onclick = function() {
                        let prev = tbody.querySelector('.selected-row');
                        if (prev) { prev.classList.remove('selected-row'); prev.style.backgroundColor = ''; }
                        this.classList.add('selected-row');
                        this.style.backgroundColor = '#dbeafe';
                        selectedClientTemp = c;
                    };
                    tr.ondblclick = function() {
                        selectedClientTemp = c;
                        confirmClientSelection();
                    };
                    tr.innerHTML = `
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${c.clientcode || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">${c.nom || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${c.tel || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${c.adressefacturation || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${c.mf || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${Number(c.remise || 0)}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${Number(c.plafonremise || 0)}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: right;">${Number(c.solde || 0)}</td>
                    `;
                    tbody.appendChild(tr);
                });

                // Render pagination
                let pagDiv = document.getElementById('clientsPagination');
                pagDiv.innerHTML = '';
                if (data.last_page > 1) {
                    for (let i = 1; i <= data.last_page; i++) {
                        // Keep simple pagination: max 5 pages around current
                        if (i == 1 || i == data.last_page || (i >= currentClientPage - 2 && i <= currentClientPage + 2)) {
                            let btn = document.createElement('button');
                            btn.innerText = i;
                            btn.style.padding = '4px 8px';
                            btn.style.border = '1px solid #d1d5db';
                            btn.style.background = i === currentClientPage ? '#6366f1' : 'white';
                            btn.style.color = i === currentClientPage ? 'white' : 'black';
                            btn.style.cursor = 'pointer';
                            btn.style.borderRadius = '4px';
                            btn.onclick = () => searchClients(i);
                            pagDiv.appendChild(btn);
                        } else if (i === currentClientPage - 3 || i === currentClientPage + 3) {
                            let span = document.createElement('span');
                            span.innerText = '...';
                            pagDiv.appendChild(span);
                        }
                    }
                }
            });
    }

    function createNewClient() {
        let raison = document.getElementById('newClientRaison').value.trim();
        let tel = document.getElementById('newClientTel').value.trim();
        if (!raison || !tel) {
            alert("Raison et Téléphone sont obligatoires !");
            return;
        }

        fetch(`{{ route('vente.caisse.pos.store_client') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nom: raison, tel: tel })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                selectClient(res.client);
            } else {
                alert("Erreur: " + res.message);
            }
        });
    }

    function selectClient(client) {
        currentClientId = client.clientid;
        document.getElementById('clientCode').value = client.clientcode || client.clientid;
        document.getElementById('clientName').innerText = client.nom || 'PASSAGER';
        
        let soldeInfoDiv = document.getElementById('clientSoldeInfo');
        if (!client.nom || client.nom.toUpperCase() === 'PASSAGER') {
            soldeInfoDiv.style.display = 'none';
        } else {
            soldeInfoDiv.style.display = 'block';
            let solde = parseFloat(client.solde || 0).toFixed(3);
            let soldeFid = parseFloat(client.soldefidelite || 0).toFixed(3);
            let pFid = parseFloat(client.pointfidelite || 0).toFixed(1);
            soldeInfoDiv.innerText = `Solde : ${solde} DT | Solde.Fid : ${soldeFid} DT | P.Fid: ${pFid}`;
        }
        
        // Loyalty update for ALL clients (even PASSAGER to reset to 0)
        currentClientLoyaltyTier = client.loyalty_tier || 0;
        ticketLines.forEach(line => {
            if (line.is_loyalty_enabled == 1) {
                line.remise = currentClientLoyaltyTier;
                line.prixNet = line.prix - (line.prix * (line.remise / 100));
                line.total = line.qte * line.prixNet;
            }
        });
        renderTable();
        
        if (currentClientLoyaltyTier > 0) {
            // Show notification to user
            let msg = document.createElement('div');
            msg.style.position = 'fixed';
                msg.style.bottom = '20px';
                msg.style.right = '20px';
                msg.style.backgroundColor = '#10b981';
                msg.style.color = 'white';
                msg.style.padding = '10px 20px';
                msg.style.borderRadius = '5px';
                msg.style.zIndex = '9999';
                msg.innerText = `Fidélité : Remise de ${currentClientLoyaltyTier}% appliquée !`;
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 4000);
            }
        
        if (typeof pendingPaymentMode !== 'undefined' && pendingPaymentMode && currentClientId && currentClientId != 1) {
            let mode = pendingPaymentMode;
            pendingPaymentMode = null;
            closeClientModal();
            setTimeout(() => {
                openPaymentModal(mode.id, mode.name);
            }, 150);
            return;
        }

        if (typeof pendingComplementAcompte !== 'undefined' && pendingComplementAcompte && currentClientId && currentClientId != 1) {
            pendingComplementAcompte = false;
            closeClientModal();
            setTimeout(() => {
                openComplementAcompteModal();
            }, 150);
            return;
        }
        
        closeClientModal();
    }

    function openCreateClientModal() {
        document.getElementById('createClientForm').reset();
        document.getElementById('cc_error').style.display = 'none';
        document.getElementById('createClientModal').style.display = 'flex';
    }

    function closeCreateClientModal() {
        document.getElementById('createClientModal').style.display = 'none';
    }

    function submitCreateClient() {
        let raison = document.getElementById('cc_raison').value.trim();
        let tel = document.getElementById('cc_telephone').value.trim();
        
        if (!raison || !tel) {
            document.getElementById('cc_error').style.display = 'block';
            return;
        }
        
        let formData = new FormData(document.getElementById('createClientForm'));
        
        fetch("{{ route('vente.caisse.pos.store_client') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Auto select the new client
                selectClient(data.client);
                closeCreateClientModal();
            } else {
                alert(data.message || 'Erreur lors de la création');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur serveur');
        });
    }

    function openEditClientModal() {
        if (!currentClientId) {
            alert('Veuillez d\'abord sélectionner un client.');
            return;
        }

        document.getElementById('ec_error').style.display = 'none';

        // Fetch current client data
        fetch(`{{ url('vente/caisse/pos/client') }}/${currentClientId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let c = data.client;
                    document.getElementById('editClientModalTitle').textContent = 'Modification Client ' + (c.nom || '');
                    document.getElementById('ec_raison').value = c.nom || '';
                    document.getElementById('ec_prenom').value = c.prenom || '';
                    document.getElementById('ec_bloque_credit').checked = c.bloquercredit == 1;
                    document.getElementById('ec_matricule_fiscal').value = c.mf || '';
                    document.getElementById('ec_g_fidelite').checked = c.fidelite == 1;
                    document.getElementById('ec_num_fidelite').value = c.num_fidelite || c.barcode || '';
                    document.getElementById('ec_telephone').value = c.tel || '';
                    document.getElementById('ec_email').value = c.email || '';
                    if(c.datenaissance && c.datenaissance.length > 10) {
                        document.getElementById('ec_date_naissance').value = c.datenaissance.substring(0, 10);
                    } else {
                        document.getElementById('ec_date_naissance').value = c.datenaissance || '';
                    }
                    document.getElementById('ec_ville').value = c.ville || '';
                    document.getElementById('ec_adresse').value = c.adressefacturation || '';
                    
                    document.getElementById('editClientModal').style.display = 'flex';
                } else {
                    alert('Client introuvable.');
                }
            })
            .catch(err => console.error(err));
    }

    function closeEditClientModal() {
        document.getElementById('editClientModal').style.display = 'none';
    }

    function submitEditClient() {
        if (!currentClientId) return;

        let raison = document.getElementById('ec_raison').value.trim();
        let tel = document.getElementById('ec_telephone').value.trim();
        
        if (!raison || !tel) {
            document.getElementById('ec_error').style.display = 'block';
            return;
        }
        
        let formData = new FormData(document.getElementById('editClientForm'));
        
        fetch(`{{ url('vente/caisse/pos/client') }}/${currentClientId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update interface if name changed
                selectClient(data.client);
                closeEditClientModal();
            } else {
                alert(data.message || 'Erreur lors de la modification');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur serveur');
        });
    }

    function openClientHistoryModal() {
        if (!currentClientId) {
            alert('Veuillez d\'abord sélectionner un client.');
            return;
        }

        let displayName = document.getElementById('displayClientName') ? document.getElementById('displayClientName').textContent : '';
        document.getElementById('clientHistoryModalTitle').textContent = 'Historique d\'Achat - ' + displayName;
        document.getElementById('clientHistoryTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">Chargement...</td></tr>';
        document.getElementById('clientHistoryModal').style.display = 'flex';

        fetch(`{{ url('vente/caisse/pos/client-history') }}/${currentClientId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    if (data.history.length === 0) {
                        html = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #666;">Aucun achat trouvé pour ce client.</td></tr>';
                    } else {
                        data.history.forEach(t => {
                            let dateObj = new Date(t.cticketdate);
                            let dateStr = dateObj.toLocaleDateString('fr-FR') + ' ' + dateObj.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                            html += `
                                <tr style="border-bottom: 1px solid #eee; cursor: pointer;" onclick="showTicketPreview(${t.cticketid})" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 8px;">${dateStr}</td>
                                    <td style="padding: 8px; font-weight: bold;">${t.cticketnumero || ''}</td>
                                    <td style="padding: 8px; text-align: center;">${Number(t.totalqte).toFixed(2)}</td>
                                    <td style="padding: 8px; text-align: right; color: #059669; font-weight: bold;">${Number(t.totalttc).toFixed(3)}</td>
                                </tr>
                            `;
                        });
                    }
                    document.getElementById('clientHistoryTableBody').innerHTML = html;
                } else {
                    document.getElementById('clientHistoryTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Erreur de chargement.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('clientHistoryTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: red;">Erreur serveur.</td></tr>';
            });
    }

    function closeClientHistoryModal() {
        document.getElementById('clientHistoryModal').style.display = 'none';
    }

    // --- ARTICLE HISTORY MODAL ---
    function openArticleHistoryModal() {
        document.getElementById('articleHistoryModal').style.display = 'flex';
        document.getElementById('articleHistorySearch').value = '';
        document.getElementById('articleHistoryTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Veuillez effectuer une recherche...</td></tr>';
    }

    function closeArticleHistoryModal() {
        document.getElementById('articleHistoryModal').style.display = 'none';
    }

    function searchArticleHistory() {
        let q = document.getElementById('articleHistorySearch').value.trim();
        let tbody = document.getElementById('articleHistoryTableBody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Recherche en cours...</td></tr>';

        fetch(`{{ route('vente.caisse.pos.article_history') }}?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    if (data.history.length === 0) {
                        html = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Aucun article trouvé pour cette recherche.</td></tr>';
                    } else {
                        data.history.forEach(h => {
                            let d = h.date ? h.date.substring(0, 16) : '';
                            let clientInfo = (h.client || '') + (h.telephone ? ' - ' + h.telephone : '') + (h.carte ? ' - ' + h.carte : '');
                            let prodInfo = (h.reference || '') + ' ' + (h.designation || '');
                            let varInfo = (h.couleur || '') + ' / ' + (h.taille || '');
                            html += `<tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px;">${d}</td>
                                <td style="padding: 8px;">${h.ticket || ''}</td>
                                <td style="padding: 8px;">${clientInfo}</td>
                                <td style="padding: 8px;">${prodInfo}</td>
                                <td style="padding: 8px;">${varInfo}</td>
                                <td style="padding: 8px; text-align: center;">${parseFloat(h.qte).toFixed(0)}</td>
                                <td style="padding: 8px; text-align: right; font-weight: bold;">${parseFloat(h.totalttc).toFixed(3)}</td>
                            </tr>`;
                        });
                    }
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Erreur serveur</td></tr>`;
                }
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Erreur de connexion</td></tr>`;
            });
    }

    // --- SMS MODAL ---
    function openSmsModal() {
        document.getElementById('smsModal').style.display = 'flex';
        document.getElementById('smsFeedback').style.display = 'none';
        document.getElementById('smsMessage').value = '';
        
        // Auto fill if client is selected
        if (currentClientId) {
            fetch(`{{ url('vente/caisse/pos/client') }}/${currentClientId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.client && data.client.tel) {
                        document.getElementById('smsTelephone').value = data.client.tel;
                    }
                });
        } else {
            document.getElementById('smsTelephone').value = '';
        }
    }

    function closeSmsModal() {
        document.getElementById('smsModal').style.display = 'none';
    }

    function sendSmsAction() {
        let tel = document.getElementById('smsTelephone').value.trim();
        let msg = document.getElementById('smsMessage').value.trim();
        let feedback = document.getElementById('smsFeedback');

        if (!tel || !msg) {
            feedback.style.display = 'block';
            feedback.style.backgroundColor = '#fca5a5';
            feedback.style.color = '#7f1d1d';
            feedback.innerHTML = 'Veuillez remplir le numéro de téléphone et le message.';
            return;
        }

        feedback.style.display = 'block';
        feedback.style.backgroundColor = '#fef08a';
        feedback.style.color = '#854d0e';
        feedback.innerHTML = 'Envoi en cours...';

        let formData = new FormData();
        formData.append('telephone', tel);
        formData.append('message', msg);
        formData.append('_token', '{{ csrf_token() }}');

        fetch("{{ route('vente.caisse.pos.send_sms') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                feedback.style.backgroundColor = '#a7f3d0';
                feedback.style.color = '#065f46';
                feedback.innerHTML = 'SMS Envoyé avec succès !';
                setTimeout(closeSmsModal, 2000);
            } else {
                feedback.style.backgroundColor = '#fca5a5';
                feedback.style.color = '#7f1d1d';
                feedback.innerHTML = data.message || 'Erreur lors de l\'envoi.';
            }
        })
        .catch(error => {
            feedback.style.backgroundColor = '#fca5a5';
            feedback.style.color = '#7f1d1d';
            feedback.innerHTML = 'Erreur réseau.';
        });
    }

    function showTicketPreview(ticketId) {
        document.getElementById('ticketPreviewContent').innerHTML = '<div style="background: white; padding: 20px; border-radius: 4px;">Chargement du ticket...</div>';
        document.getElementById('ticketPreviewModal').style.display = 'flex';

        fetch(`{{ url('vente/tickets') }}/${ticketId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            document.getElementById('ticketPreviewContent').innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            document.getElementById('ticketPreviewContent').innerHTML = '<div style="background: white; padding: 20px; border-radius: 4px; color: red;">Erreur lors du chargement.</div>';
        });
    }

    function closeTicketPreviewModal() {
        document.getElementById('ticketPreviewModal').style.display = 'none';
    }

    function openCheckStockModal() {
        document.getElementById('checkStockModal').style.display = 'flex';
        
        // If a line is selected, pre-fill reference
        if (selectedLineIndex >= 0 && selectedLineIndex < ticketLines.length) {
            let line = ticketLines[selectedLineIndex];
            document.getElementById('advStockRef').value = line.ref || '';
            document.getElementById('advStockCouleur').value = line.couleur || '';
            document.getElementById('advStockTaille').value = line.taille || '';
            document.getElementById('advStockType').value = 'reel';
            searchAdvancedStock();
        } else {
            // Clear fields if nothing selected
            document.getElementById('advStockRef').value = '';
            document.getElementById('advStockRayon').value = '';
            document.getElementById('advStockCouleur').value = '';
            document.getElementById('advStockTaille').value = '';
            document.getElementById('advStockType').value = 'reel';
            document.getElementById('checkStockTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">Veuillez saisir des critères et cliquer sur la coche pour chercher.</td></tr>';
        }
    }

    function searchAdvancedStock() {
        let ref = document.getElementById('advStockRef').value;
        let rayon = document.getElementById('advStockRayon').value;
        let couleur = document.getElementById('advStockCouleur').value;
        let taille = document.getElementById('advStockTaille').value;
        let type = document.getElementById('advStockType').value;
        
        let typeLabels = {'reel': 'Réel', 'virtuel': 'Virtuel', 'reserve': 'Réservé'};
        document.getElementById('advStockQtyHeader').innerText = 'Quantité (' + typeLabels[type] + ')';

        document.getElementById('checkStockTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Chargement...</td></tr>';

        let qs = new URLSearchParams();
        if(ref) qs.append('reference', ref);
        if(rayon) qs.append('rayonid', rayon);
        if(couleur) qs.append('couleur', couleur);
        if(taille) qs.append('taille', taille);
        
        fetch(`{{ route('vente.caisse.pos.advanced_check_stock') }}?` + qs.toString())
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    if (data.stock.length === 0) {
                        html = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #666;">Aucun stock trouvé pour ces critères.</td></tr>';
                    } else {
                        data.stock.forEach(s => {
                            let qty = 0;
                            if(type === 'reel') qty = s.reel;
                            else if(type === 'virtuel') qty = s.virtuel;
                            else if(type === 'reserve') qty = s.reserve;
                            
                            html += `
                                <tr style="border-bottom: 1px solid #eee;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${s.reference || ''}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${s.designation || ''}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${s.rayon || ''}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${s.couleur || ''}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${s.taille || ''}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee; font-weight: bold;">${s.site_nom || ''}</td>
                                    <td style="padding: 8px; text-align: right; font-weight: bold; color: ${qty < 0 ? '#dc2626' : (qty > 0 ? '#059669' : '#333')}">${Number(qty).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                    }
                    document.getElementById('checkStockTableBody').innerHTML = html;
                } else {
                    document.getElementById('checkStockTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Erreur de chargement.</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('checkStockTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: red;">Erreur serveur.</td></tr>';
            });
    }

    function closeCheckStockModal() {
        document.getElementById('checkStockModal').style.display = 'none';
    }

    // --- MODIFICATION LIGNE ---
    function openEditLineModal() {
        if (selectedLineIndex < 0 || selectedLineIndex >= ticketLines.length) {
            alert('Veuillez d\'abord sélectionner un article dans le ticket.');
            return;
        }

        let line = ticketLines[selectedLineIndex];
        document.getElementById('editLineDesignation').textContent = line.designation + (line.taille ? ' - ' + line.taille : '') + (line.couleur ? ' - ' + line.couleur : '');
        document.getElementById('editLinePrix').value = line.prix;
        document.getElementById('editLineQte').value = line.qte;
        document.getElementById('editLineRemise').value = line.remise;
        
        document.getElementById('editLineModal').style.display = 'flex';
        document.getElementById('editLinePrix').focus();
    }

    function closeEditLineModal() {
        document.getElementById('editLineModal').style.display = 'none';
    }

    function saveEditLine() {
        if (selectedLineIndex < 0 || selectedLineIndex >= ticketLines.length) return;
        
        let newPrix = parseFloat(document.getElementById('editLinePrix').value) || 0;
        let newQte = parseFloat(document.getElementById('editLineQte').value) || 0;
        let newRemise = parseFloat(document.getElementById('editLineRemise').value) || 0;
        
        if (newQte <= 0) {
            alert("La quantité doit être supérieure à 0.");
            return;
        }
        
        let line = ticketLines[selectedLineIndex];
        line.prix = newPrix;
        line.qte = newQte;
        line.remise = newRemise;
        
        let prixNet = line.prix - (line.prix * (line.remise / 100));
        line.prixNet = prixNet;
        line.total = line.qte * prixNet;
        
        renderTable();
        closeEditLineModal();
    }

    // --- IMPRIMER TICKET (PROVISOIRE) ---
    function printDraftTicket() {
        if (ticketLines.length === 0) {
            alert('Le ticket est vide !');
            return;
        }
        
        let clientName = document.getElementById('displayClientName') ? document.getElementById('displayClientName').textContent : 'PASSAGER';
        let dateStr = new Date().toLocaleString('fr-FR');
        let vendeur = '{{ $defaultVendeur ? $defaultVendeur->nom . ' ' . $defaultVendeur->prenom : 'Non spécifié' }}';
        
        let ticketHTML = `
            <html>
            <head>
                <style>
                    body { font-family: monospace; width: 300px; margin: 0 auto; padding: 10px; font-size: 12px; }
                    .center { text-align: center; }
                    .bold { font-weight: bold; }
                    .line { border-bottom: 1px dashed #000; margin: 5px 0; }
                    table { width: 100%; border-collapse: collapse; }
                    td { padding: 2px 0; }
                    .right { text-align: right; }
                </style>
            </head>
            <body>
                <div class="center bold" style="font-size: 16px;">{{ strtoupper($siteName) }}</div>
                <div class="center">Ticket Provisoire</div>
                <div class="line"></div>
                <div>Date: ${dateStr}</div>
                <div>Client: ${clientName}</div>
                <div>Vendeur: ${vendeur}</div>
                <div class="line"></div>
                <table>
                    <thead>
                        <tr class="bold" style="border-bottom: 1px dashed #000;">
                            <td>Art.</td>
                            <td class="right">Qte</td>
                            <td class="right">Total</td>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        let total = 0;
        let totalQte = 0;
        ticketLines.forEach(line => {
            let label = line.designation.substring(0, 15);
            ticketHTML += `
                <tr>
                    <td>${label}</td>
                    <td class="right">${line.qte}</td>
                    <td class="right">${line.total.toFixed(3)}</td>
                </tr>
            `;
            total += line.total;
            totalQte += line.qte;
        });
        
        ticketHTML += `
                    </tbody>
                </table>
                <div class="line"></div>
                <table>
                    <tr class="bold">
                        <td>TOTAL QTE</td>
                        <td class="right">${totalQte}</td>
                    </tr>
                    <tr class="bold" style="font-size: 14px;">
                        <td>TOTAL TTC</td>
                        <td class="right">${total.toFixed(3)}</td>
                    </tr>
                </table>
                <div class="line"></div>
                <div class="center">Merci de votre visite !</div>
            </body>
            </html>
        `;
        
        let printWindow = window.open('', '', 'width=400,height=600');
        printWindow.document.write(ticketHTML);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function searchClientById(id) {
        fetch(`{{ route('vente.caisse.pos.search_clients') }}?q=${encodeURIComponent(id)}`)
            .then(r => r.json())
            .then(clients => {
                if (clients.length > 0) {
                    let match = clients.find(c => c.clientid == id);
                    if (match) {
                        selectClient(match);
                    } else {
                        selectClient(clients[0]);
                    }
                }
            });
    }

    // === VENDEUR SEARCH ===
    let currentVendeurId = {{ $defaultVendeur ? $defaultVendeur->employeeid : 'null' }};
    let selectedVendeurTemp = null;

    function openVendeurModal() {
        document.getElementById('vendeurModal').style.display = 'flex';
        document.getElementById('vendeurSearchInput').value = '';
        document.getElementById('vendeurSearchInput').focus();
        selectedVendeurTemp = null;
        searchVendeurs('');
    }

    function closeVendeurModal() {
        document.getElementById('vendeurModal').style.display = 'none';
        document.getElementById('scanInput').focus();
    }

    let pendingAction = null;

    function confirmVendeurSelection() {
        if (selectedVendeurTemp) {
            currentVendeurId = selectedVendeurTemp.employeeid;
            document.getElementById('vendeurName').value = selectedVendeurTemp.nom || '';
        }
        closeVendeurModal();

        if (pendingAction === 'miseEnAttente') {
            pendingAction = null;
            window.executeMiseEnAttente();
        }
    }

    function searchVendeurs(q) {
        fetch(`{{ route('vente.caisse.pos.search_vendeurs') }}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(vendeurs => {
                let tbody = document.getElementById('vendeursTbody');
                tbody.innerHTML = '';
                vendeurs.forEach(v => {
                    let tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onmouseover = function() { if (!this.classList.contains('selected-row')) this.style.backgroundColor = '#f0f9ff'; };
                    tr.onmouseout = function() { if (!this.classList.contains('selected-row')) this.style.backgroundColor = ''; };
                    tr.onclick = function() {
                        // Deselect previous
                        let prev = tbody.querySelector('.selected-row');
                        if (prev) { prev.classList.remove('selected-row'); prev.style.backgroundColor = ''; }
                        // Select this row
                        this.classList.add('selected-row');
                        this.style.backgroundColor = '#dbeafe';
                        selectedVendeurTemp = v;
                    };
                    tr.ondblclick = function() {
                        selectedVendeurTemp = v;
                        confirmVendeurSelection();
                    };
                    tr.innerHTML = `
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${v.code || ''}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">${v.nom || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function selectVendeur(vendeur) {
        currentVendeurId = vendeur.employeeid;
        document.getElementById('vendeurName').value = vendeur.nom || '';
        closeVendeurModal();
    }

</script>

<!-- CLIENT SEARCH MODAL -->
<div id="clientModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1200px; max-height: 90%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700;">Sélection des clients</h2>
            <div style="display: flex; gap: 5px;">
                <button type="button" onclick="confirmClientSelection()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeClientModal()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Create Form -->
        <div style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; flex-direction: column;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <label style="font-weight: bold; font-size: 12px; color: #6b7280;">Raison</label>
                <input type="text" id="newClientRaison" style="flex: 1; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
                
                <label style="font-weight: bold; font-size: 12px; color: #6b7280;">Tél</label>
                <input type="text" id="newClientTel" style="width: 250px; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
                
                <button type="button" onclick="createNewClient()" style="padding: 6px 15px; border: 1px solid #111827; background: white; color: #111827; font-weight: bold; font-size: 12px; border-radius: 4px; cursor: pointer;">Créer Client</button>
            </div>
            <div style="color: #ef4444; font-size: 11px; margin-top: 5px; font-weight: bold;">Raison && Téléphone Obligatoire</div>
        </div>

        <!-- Search & Table -->
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="clientSearchInput" placeholder="Enter text to search..." style="width: 100%; padding: 6px 8px 6px 25px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;" oninput="searchClients(1)">
                </div>
            </div>
            
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; white-space: nowrap;">
                    <thead style="position: sticky; top: 0; background: white; z-index: 2;">
                        <tr style="background: #f8fafc; text-align: left;">
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Code</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Raison Social</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Tél</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Adresse</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Code.TVA</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">%Remise</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">% Plafond Rem</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Solde</th>
                        </tr>
                        <tr>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_code" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_nom" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_tel" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_adresse" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_mf" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_remise" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_plafonremise" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_solde" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="searchClients(1)"></th>
                        </tr>
                    </thead>
                    <tbody id="clientsTbody"></tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="clientsPagination" style="margin-top: 10px; display: flex; gap: 5px;"></div>
        </div>
    </div>
</div>

<!-- VENDEUR SEARCH MODAL -->
<div id="vendeurModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; max-height: 70%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <!-- Header with title + ✓ and ✕ buttons -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 14px; font-weight: 700; flex: 1; text-align: center;">Liste des Vendeurs</h2>
            <div style="display: flex; gap: 5px;">
                <button type="button" onclick="confirmVendeurSelection()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeVendeurModal()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="vendeurSearchInput" placeholder="Enter text to search..." style="width: 100%; padding: 6px 8px 6px 25px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;" oninput="searchVendeurs(this.value)">
                </div>
            </div>
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Code</th>
                            <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Nom</th>
                        </tr>
                    </thead>
                    <tbody id="vendeursTbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- CHEQUE CADEAU MODAL -->
<div id="chequeCadeauModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 500px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-family: Arial, sans-serif;">
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Type Cheque Cadeau</h2>
            <button type="button" onclick="closeChequeCadeauModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666; font-weight: bold;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <div style="border: 1px solid #ddd; padding: 15px; display: flex; align-items: center; justify-content: center; gap: 20px; background: #f9f9f9; border-radius: 4px; margin-bottom: 15px;">
                <label style="font-weight: normal; font-size: 13px;">Montant</label>
                <input type="number" step="0.001" id="chequeCadeauMontant" style="width: 150px; padding: 8px; border: 1px solid #ccc; font-weight: bold; font-size: 14px; text-align: center;">
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                @foreach($typeChequeCadeaus as $type)
                    <button type="button" onclick="validerChequeCadeau({{ $type->typechequecadeauid }})" style="background: white; border: 1px solid #2c3e50; padding: 10px 20px; min-width: 100px; cursor: pointer; border-radius: 2px; font-size: 12px;">
                        {{ $type->libelle }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- RETOUR TICKET MODAL -->
<div id="retourModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1000px; max-height: 90%; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333; flex: 1; text-align: center;">Mouvement Ticket</h2>
            <button type="button" onclick="closeRetourModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666; font-weight: bold;">&times;</button>
        </div>
        <!-- Search Section -->
        <div style="padding: 15px; display: flex; justify-content: center; align-items: center; gap: 10px; border-bottom: 1px solid #ddd;">
            <input type="text" id="retourTicketNumero" style="width: 300px; padding: 6px; border: 1px solid #ccc; border-radius: 2px;">
            <button type="button" onclick="browseTicketsRetour()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 2px;">...</button>
            <button type="button" onclick="openMouvementsModal()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 2px;">Mvts</button>
        </div>
        <!-- Table -->
        <div style="flex: 1; overflow-y: auto; padding: 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center;">
                <thead style="background: #fff; position: sticky; top: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold; text-align: left;">Référence</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold; text-align: left;">Désignation</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte Vendu</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte Récupérée</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Prix</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Rem%</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Total</th>
                    </tr>
                </thead>
                <tbody id="retourTbody">
                    <!-- rows dynamically populated -->
                </tbody>
            </table>
        </div>
        <!-- Footer Buttons -->
        <div style="padding: 15px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px; background: #f8f9fa;">
            <button type="button" onclick="validerRetour()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeRetourModal()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

<!-- MOUVEMENTS MODAL -->
<div id="mouvementsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1100px; height: 700px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); display: flex; flex-direction: column; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; text-align: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Mouvements</h2>
        </div>
        <!-- Filters Section -->
        <div style="padding: 15px; display: flex; align-items: center; justify-content: center; gap: 15px; border-bottom: 1px solid #ddd;">
            <label style="font-weight: bold; font-size: 12px;">DU</label>
            <input type="date" id="mvtFilterDu" value="{{ date('Y-m-d') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">
            
            <label style="font-weight: bold; font-size: 12px;">AU</label>
            <input type="date" id="mvtFilterAu" value="{{ date('Y-m-d') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">

            <label style="font-weight: bold; font-size: 12px;">Client</label>
            <select id="mvtFilterClient" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 200px; font-size: 12px;">
                <option value="">PASSAGER</option>
            </select>

            <button type="button" onclick="loadMouvementsData()" style="padding: 6px 20px; border: 1px solid #333; background: white; cursor: pointer; border-radius: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            </button>
        </div>
        <div style="padding: 10px 15px; display: flex; justify-content: flex-end; border-bottom: 1px solid #ddd;">
            <div style="position: relative;">
                <input type="text" id="mvtSearchText" placeholder="Enter text to search..." onkeyup="filterMouvementsTable()" style="padding: 6px 6px 6px 25px; border: 1px solid #ccc; width: 250px; font-size: 12px;">
                <svg style="position: absolute; left: 6px; top: 8px;" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
        </div>
        <!-- Table -->
        <div style="flex: 1; overflow: auto; padding: 0 15px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; text-align: left;" id="mvtTable">
                <thead style="background: #f8f9fa; position: sticky; top: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <tr>
                        <th style="padding: 8px; border: 1px solid #ddd; width: 30px; text-align: center;"><input type="checkbox" onclick="toggleAllMouvements(this)"></th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Numéro</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Reference</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Produit</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Taille</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Couleur</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Qte</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">PU.TTC</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Remise</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Total</th>
                    </tr>
                </thead>
                <tbody id="mvtTbody">
                    <tr><td colspan="11" style="text-align: center; padding: 40px; font-weight: bold; color: #555;">No data to display</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Footer Buttons -->
        <div style="padding: 15px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px; background: #f8f9fa;">
            <button type="button" onclick="validerMouvementsSelection()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeMouvementsModal()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>

<!-- CONSULTATION TICKETS MODAL -->
<div id="consultationModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1300px; max-height: 95%; border-radius: 4px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #fff; position: relative;">
            <div style="flex: 1;"></div>
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #333; text-align: center; flex: 1;">Journal Vente</h2>
            <div style="flex: 1; display: flex; justify-content: flex-end; gap: 5px;">
                <button type="button" style="background: white; border: 1px solid #ccc; width: 40px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 2px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeConsultationModal()" style="background: white; border: 1px solid #ccc; width: 40px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 2px;" title="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Top Filters -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 15px; background: #fff; font-size: 12px;">
            <div style="display: flex; align-items: center; gap: 5px; flex: 1;">
                <label style="color: #555;">Indicateur</label>
                <select id="consultationIndicateur" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous">Tous</option>
                    <option value="Payé">Payé</option>
                    <option value="Non Payé">Non Payé</option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 5px; flex: 1.5;">
                <label style="color: #555;">Client</label>
                <select id="consultationClientSelect" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous"></option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 5px; flex: 1.5;">
                <label style="color: #555;">Vendeur</label>
                <select id="consultationVendeurSelect" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous">Tous</option>
                </select>
            </div>
            <div>
                <button type="button" onclick="fetchConsultationData()" style="background: white; border: 1px solid #555; padding: 4px 15px; cursor: pointer; border-radius: 2px; display: flex; align-items: center; justify-content: center; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </button>
            </div>
        </div>

        <div style="padding: 10px 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column; background: #fff;">
            <!-- Global Search -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 6px; top: 7px;">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="consultationGlobalSearch" placeholder="Enter text to search..." style="width: 100%; padding: 4px 6px 4px 22px; border: 1px solid #ccc; border-radius: 2px; font-size: 12px; outline: none;" oninput="renderConsultationTickets()">
                </div>
            </div>

            <!-- Grid -->
            <div style="flex: 1; overflow-y: auto; border: 1px solid #ccc;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; white-space: nowrap;">
                    <thead style="position: sticky; top: 0; background: #f9f9f9; z-index: 2;">
                        <tr style="text-align: left; color: #333;">
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Statut</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Date</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Numéro</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Client</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Tel. client</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Total TTC</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Montant reçu</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Reste</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Caissier</th>
                            <th style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: 600;">Vendeur</th>
                        </tr>
                        <tr style="background: #fff;">
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="statut" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="date" class="consult-filter" data-col="date" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" class="consult-filter" data-col="numero" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="client" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="tel" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="ttc" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="recu" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="reste" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="caissier" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="vendeur" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                        </tr>
                    </thead>
                    <tbody id="consultationTbody">
                        <tr><td colspan="10" style="text-align: center; padding: 30px; font-weight: bold;">No data to display</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let allConsultationTickets = [];
    
    function openConsultationModal() {
        document.getElementById('consultationModal').style.display = 'flex';
        fetchConsultationData();
    }
    
    function closeConsultationModal() {
        document.getElementById('consultationModal').style.display = 'none';
    }
    
    function fetchConsultationData() {
        fetch(`{{ route('vente.caisse.journal_data') }}`)
            .then(res => res.json())
            .then(data => {
                if(data.tickets) {
                    allConsultationTickets = data.tickets;
                    
                    // Populate Client Dropdown
                    let clientOpts = '<option value="Tous"></option>';
                    if(data.clients) {
                        data.clients.forEach(c => { clientOpts += `<option value="${c.clientid}">${c.nom}</option>`; });
                    }
                    document.getElementById('consultationClientSelect').innerHTML = clientOpts;
                    
                    // Populate Vendeur Dropdown
                    let vendeurOpts = '<option value="Tous">Tous</option>';
                    if(data.vendeurs) {
                        data.vendeurs.forEach(v => { vendeurOpts += `<option value="${v.employeeid}">${v.nom}</option>`; });
                    }
                    document.getElementById('consultationVendeurSelect').innerHTML = vendeurOpts;

                    renderConsultationTickets();
                }
            })
            .catch(err => console.error(err));
    }

    function renderConsultationTickets() {
        const globalSearch = document.getElementById('consultationGlobalSearch').value.toLowerCase();
        const indicateur = document.getElementById('consultationIndicateur').value;
        const clientFilter = document.getElementById('consultationClientSelect').options[document.getElementById('consultationClientSelect').selectedIndex].text;
        const vendeurFilter = document.getElementById('consultationVendeurSelect').options[document.getElementById('consultationVendeurSelect').selectedIndex].text;

        const filters = {
            statut: document.querySelector('.consult-filter[data-col="statut"]').value.toLowerCase(),
            date: document.querySelector('.consult-filter[data-col="date"]').value,
            numero: document.querySelector('.consult-filter[data-col="numero"]').value.toLowerCase(),
            client: document.querySelector('.consult-filter[data-col="client"]').value.toLowerCase(),
            tel: document.querySelector('.consult-filter[data-col="tel"]').value.toLowerCase(),
            ttc: document.querySelector('.consult-filter[data-col="ttc"]').value,
            recu: document.querySelector('.consult-filter[data-col="recu"]').value,
            reste: document.querySelector('.consult-filter[data-col="reste"]').value,
            caissier: document.querySelector('.consult-filter[data-col="caissier"]').value.toLowerCase(),
            vendeur: document.querySelector('.consult-filter[data-col="vendeur"]').value.toLowerCase()
        };

        const tbody = document.getElementById('consultationTbody');
        tbody.innerHTML = '';
        
        let count = 0;

        for(let i = 0; i < allConsultationTickets.length; i++) {
            let t = allConsultationTickets[i];
            
            // Format dates
            let d = new Date(t.datecreation);
            let dateStr = d.toLocaleDateString('fr-FR');
            let isoDate = d.toISOString().split('T')[0];
            
            let statut = t.netapayer > 0 ? "Non Payé" : "Payé";
            let cnom = t.client_nom || '';
            let vnom = t.vendeur_nom || '';
            let ctel = t.client_tel || '';
            let cainom = t.caissier_nom || '';
            let num = t.cticketnumero || '';
            
            // Indicateur Filter
            if(indicateur !== "Tous" && statut !== indicateur) continue;
            
            // Top Dropdowns
            if(document.getElementById('consultationClientSelect').value !== "Tous" && clientFilter && cnom !== clientFilter) continue;
            if(document.getElementById('consultationVendeurSelect').value !== "Tous" && vendeurFilter && vnom !== vendeurFilter) continue;
            
            // Global Search
            if(globalSearch) {
                let text = `${statut} ${dateStr} ${num} ${cnom} ${ctel} ${t.totalttc} ${t.acompte} ${t.netapayer} ${cainom} ${vnom}`.toLowerCase();
                if(!text.includes(globalSearch)) continue;
            }

            // Column Filters
            if(filters.statut && !statut.toLowerCase().includes(filters.statut)) continue;
            if(filters.date && isoDate !== filters.date) continue;
            if(filters.numero && !String(num).includes(filters.numero)) continue;
            if(filters.client && !cnom.toLowerCase().includes(filters.client)) continue;
            if(filters.tel && !ctel.toLowerCase().includes(filters.tel)) continue;
            if(filters.ttc && parseFloat(t.totalttc) !== parseFloat(filters.ttc)) continue;
            if(filters.recu && parseFloat(t.acompte) !== parseFloat(filters.recu)) continue;
            if(filters.reste && parseFloat(t.netapayer) !== parseFloat(filters.reste)) continue;
            if(filters.caissier && !cainom.toLowerCase().includes(filters.caissier)) continue;
            if(filters.vendeur && !vnom.toLowerCase().includes(filters.vendeur)) continue;

            count++;
            
            let bgStatut = statut === "Payé" ? "#22c55e" : "#ef4444";
            
            let tr = document.createElement('tr');
            tr.style.borderBottom = "1px solid #eee";
            tr.className = "ticket-row";
            tr.style.cursor = "pointer";
            tr.innerHTML = `
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">
                    <span style="background: ${bgStatut}; color: white; padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 10px;">${statut}</span>
                </td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${dateStr}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${num}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${cnom}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${ctel}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.totalttc).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.acompte).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.netapayer).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${cainom}</td>
                <td style="padding: 6px 8px;">${vnom}</td>
            `;
            
            tr.ondblclick = () => { window.open(`/vente/tickets/${t.cticketid}`, '_blank', 'width=800,height=600'); };
            tbody.appendChild(tr);
        }

        if(count === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 30px; font-weight: bold;">No data to display</td></tr>';
        }
    }

    // Attach listener to all column filters
    document.querySelectorAll('.consult-filter').forEach(inp => {
        inp.addEventListener('input', () => { renderConsultationTickets(); });
    });

    function validerTicket() {
        openMultiPaymentModal(1); // Default pre-fill Espèce
    }

    let mpTotalDu = 0;

    function openCreditFlow() {
        if (ticketLines.length === 0 && !complementMode) {
            alert("Le ticket est vide.");
            return;
        }
        let clientName = document.getElementById('clientName').innerText;
        if (clientName === 'PASSAGER' || !currentClientId || currentClientId == 1) {
            alert("Veuillez d'abord sélectionner un client pour effectuer une vente à crédit.");
            openClientModal();
        } else {
            let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
            let payload = {
                vendeurid: document.getElementById('vendeurName').dataset.id || null,
                clientid: currentClientId,
                lignes: ticketLines,
                totalttc: total,
                acompte: 0,
                netapayer: total,
                reglements: []
            };

            if (complementMode) {
                alert("Impossible de passer un crédit total sur un complément d'acompte.");
                return;
            } else {
                window.submitTicketToBackend(payload, false);
            }
        }
    }

    function openMultiPaymentModal(defaultModeId = null) {
        if (ticketLines.length === 0 && !complementMode) {
            alert("Le ticket est vide.");
            return;
        }

        document.getElementById('multiPaymentModal').style.display = 'flex';
        
        let vendeur = document.getElementById('vendeurName').value || document.getElementById('vendeurName').dataset.name || 'Vendeur';
        document.getElementById('mpVendeurName').innerText = vendeur;
        document.getElementById('mpClientName').innerText = document.getElementById('clientName').innerText;

        mpTotalDu = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
        document.getElementById('mpNetAPayer').value = mpTotalDu.toFixed(3);
        
        mpReglementsArray = [];
        document.getElementById('mpEspece').value = '';
        document.getElementById('mpCheque').value = '';
        document.getElementById('mpCB').value = '';
        
        if (defaultModeId === 1) document.getElementById('mpEspece').value = mpTotalDu.toFixed(3);
        else if (defaultModeId === 2) document.getElementById('mpCheque').value = mpTotalDu.toFixed(3);
        else if (defaultModeId === 3) document.getElementById('mpCB').value = mpTotalDu.toFixed(3);

        toggleMpAddBtn('Espece');
        toggleMpAddBtn('Cheque');
        toggleMpAddBtn('CB');

        renderMpTable();
    }

    function closeMultiPaymentModal() {
        document.getElementById('multiPaymentModal').style.display = 'none';
    }

    function toggleMpAddBtn(type) {
        let val = parseFloat(document.getElementById('mp' + type).value);
        let btn = document.getElementById('mpBtn' + type);
        if (val > 0) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    }

    function mpAddLine(type, modeId) {
        let input = document.getElementById('mp' + type);
        let val = parseFloat(input.value) || 0;
        if (val <= 0) return;
        
        let modeName = '';
        if (type === 'Espece') modeName = 'Espèce';
        else if (type === 'Cheque') modeName = 'Cheque';
        else if (type === 'CB') modeName = 'C.B';

        // Add to array
        let today = new Date().toISOString().split('T')[0];
        mpReglementsArray.push({
            id: Date.now(),
            modeId: modeId,
            mode: modeName,
            montant: val,
            numero: '',
            date: today,
            banque: ''
        });

        // Clear input
        input.value = '';
        toggleMpAddBtn(type);
        renderMpTable();
    }

    function renderMpTable() {
        let tbody = document.getElementById('mpReglementsTbody');
        tbody.innerHTML = '';
        
        let paye = 0;
        if (mpReglementsArray.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 30px; font-weight: bold;">No data to display</td></tr>';
        } else {
            mpReglementsArray.forEach((row, index) => {
                paye += row.montant;
                let tr = document.createElement('tr');
                
                let detailsHTML = `
                    <td style="padding: 2px; border-right: 1px solid #ddd;"></td>
                    <td style="padding: 2px; border-right: 1px solid #ddd;"></td>
                    <td style="padding: 2px; border-right: 1px solid #ddd;"></td>
                `;

                if (row.modeId === 2 || row.modeId === 3 || row.modeId === 4 || row.modeId === 9) {
                    detailsHTML = `
                        <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${row.numero}" onchange="updateMpRow(${index}, 'numero', this.value)" style="width: 100%; border: 1px solid #eee; padding: 4px; box-sizing: border-box; outline: none;" placeholder="Numéro"></td>
                        <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="date" value="${row.date}" onchange="updateMpRow(${index}, 'date', this.value)" style="width: 100%; border: 1px solid #eee; padding: 4px; box-sizing: border-box; outline: none;"></td>
                        <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${row.banque}" onchange="updateMpRow(${index}, 'banque', this.value)" style="width: 100%; border: 1px solid #eee; padding: 4px; box-sizing: border-box; outline: none;" placeholder="Banque"></td>
                    `;
                }

                tr.innerHTML = `
                    <td style="padding: 5px; border-right: 1px solid #ddd;">${row.mode}</td>
                    <td style="padding: 5px; border-right: 1px solid #ddd; text-align: right; font-weight: bold;">${row.montant.toFixed(3)}</td>
                    ${detailsHTML}
                    <td style="padding: 2px; text-align: center;"><button onclick="removeMpRow(${index})" style="background:none; border:none; color:red; cursor:pointer; font-weight:bold; padding: 0 5px;">X</button></td>
                `;
                tbody.appendChild(tr);
            });
        }
        
        let reste = mpTotalDu - paye;
        document.getElementById('mpResteAPayer').value = reste.toFixed(3);
        let sumBonAchat = mpReglementsArray.filter(r => r.modeId === 4).reduce((sum, r) => sum + r.montant, 0);
        let elBonAchat = document.getElementById('mpBonAchatVal');
        if (elBonAchat) elBonAchat.value = sumBonAchat.toFixed(3);
    }

    function updateMpRow(index, field, val) {
        mpReglementsArray[index][field] = val;
    }

    function removeMpRow(index) {
        mpReglementsArray.splice(index, 1);
        renderMpTable();
    }


    // CHEQUE CADEAU LOGIC
    function openChequeCadeauModal() {
        let total = parseFloat(document.getElementById('grandTotal').innerText);
        document.getElementById('chequeCadeauMontant').value = total.toFixed(3);
        document.getElementById('chequeCadeauModal').style.display = 'flex';
    }

    function closeChequeCadeauModal() {
        document.getElementById('chequeCadeauModal').style.display = 'none';
    }

    function validerChequeCadeau(typeId) {
        let mnt = parseFloat(document.getElementById('chequeCadeauMontant').value) || 0;
        let grandTotal = parseFloat(document.getElementById('grandTotal').innerText);
        
        if (mnt <= 0) {
            alert("Le montant doit être supérieur à 0");
            return;
        }

        let data = {
            clientid: currentClientId,
            vendeurid: currentVendeurId,
            lignes: ticketLines,
            totalttc: grandTotal,
            reglements: [{ modereglementid: 5, montant: mnt, typechequecadeauid: typeId }]
        };

        closeChequeCadeauModal();
        submitTicketToBackend(data);
    }

    function closePaymentModal() {
        let m = document.getElementById('paymentModal');
        if (m) m.style.display = 'none';
        let s = document.getElementById('scanInput');
        if (s) s.focus();
    }

    function calcRendu() {
        let total = parseFloat(document.getElementById('paymentTotal').innerText);
        let recu = parseFloat(document.getElementById('paymentMontant').value || 0);
        let rendu = recu - total;
        document.getElementById('paymentRendu').innerText = rendu >= 0 ? rendu.toFixed(3) : '0.000';
    }

    function mpGetReglements() {
        return mpReglementsArray.map(r => ({
            modereglementid: r.modeId,
            montant: r.montant,
            numero: r.numero || '',
            banque: r.banque || '',
            date: r.date || ''
        }));
    }

    // BON D'ACHAT LOGIC
    let baLignes = [];
    let baAdding = false;
    let baDraft = { numero: '', montant: '', remise: '' };

    function openBonAchatModal() {
        document.getElementById('bonAchatModal').style.display = 'flex';
        document.getElementById('baNetAPayer').innerText = mpTotalDu.toFixed(3);
        document.getElementById('baCodeInput').value = '';
        document.getElementById('baNom').value = document.getElementById('clientName').innerText === 'PASSAGER' ? '' : document.getElementById('clientName').innerText;
        document.getElementById('baTelephone').value = '';
        baLignes = [];
        baAdding = false;
        renderBaTable();
    }

    function closeBonAchatModal() {
        document.getElementById('bonAchatModal').style.display = 'none';
    }

    function baAddLine() {
        baAdding = true;
        let code = document.getElementById('baCodeInput').value.trim();
        baDraft = { numero: code, montant: '', remise: '' };
        document.getElementById('baCodeInput').value = '';
        renderBaTable();
    }

    function baSaveDraft() {
        let num = document.getElementById('baDraftNum').value.trim();
        let mnt = parseFloat(document.getElementById('baDraftMnt').value);
        let rem = parseFloat(document.getElementById('baDraftRem').value) || 0;
        if (!num || isNaN(mnt) || mnt <= 0) {
            alert("Veuillez saisir un numéro et un montant valide.");
            return;
        }
        baLignes.push({ numero: num, montant: mnt, remise: rem });
        baAdding = false;
        renderBaTable();
    }

    function baCancelDraft() {
        baAdding = false;
        renderBaTable();
    }

    function renderBaTable() {
        let tbody = document.getElementById('baTbody');
        tbody.innerHTML = '';
        if (baLignes.length === 0 && !baAdding) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; font-weight: bold;">No data to display</td></tr>';
            return;
        }

        baLignes.forEach((l, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${l.numero}" onchange="baUpdate(${index}, 'numero', this.value)" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="number" step="0.001" value="${l.montant}" onchange="baUpdate(${index}, 'montant', this.value)" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="number" step="0.01" value="${l.remise}" onchange="baUpdate(${index}, 'remise', this.value)" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; text-align: center;"><button onclick="baRemove(${index})" style="background: none; border: none; color: red; cursor: pointer; font-weight: bold;">X</button></td>
            `;
            tbody.appendChild(tr);
        });

        if (baAdding) {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="text" id="baDraftNum" value="${baDraft.numero}" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;">
                </td>
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="number" step="0.001" id="baDraftMnt" value="${baDraft.montant}" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;">
                </td>
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="number" step="0.01" id="baDraftRem" value="${baDraft.remise}" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;">
                </td>
                <td style="padding: 4px; text-align: center; white-space: nowrap;">
                    <button onclick="baSaveDraft()" style="background: #673ab7; color: white; border: none; border-radius: 3px; padding: 5px 12px; cursor: pointer; font-size: 11px; font-weight: bold; margin-right: 4px;">Save</button>
                    <button onclick="baCancelDraft()" style="background: white; color: #333; border: 1px solid #ccc; border-radius: 3px; padding: 4px 12px; cursor: pointer; font-size: 11px;">Cancel</button>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    function baUpdate(index, field, val) {
        if (field === 'numero') baLignes[index].numero = val;
        else baLignes[index][field] = parseFloat(val) || 0;
    }

    function baRemove(index) {
        baLignes.splice(index, 1);
        renderBaTable();
    }

    function validerBonAchat() {
        let nom = document.getElementById('baNom').value.trim();
        if (nom === '') {
            alert("Nom et Prénom sont obligatoires.");
            return;
        }

        let totalBA = baLignes.reduce((sum, l) => sum + l.montant, 0);
        if (totalBA > 0) {
            baLignes.forEach(l => {
                if(l.montant > 0) {
                    let today = new Date().toISOString().split('T')[0];
                    mpReglementsArray.push({
                        id: Date.now() + Math.random(),
                        modeId: 4,
                        mode: "Bon d'achat",
                        montant: l.montant,
                        numero: l.numero,
                        date: today,
                        banque: ''
                    });
                }
            });
            renderMpTable();
        }
        
        closeBonAchatModal();
    }

    // AVOIR LOGIC
    function openAvoirModal() {
        document.getElementById('avoirModal').style.display = 'flex';
        let input = document.getElementById('avoirBarcodeInput');
        input.value = '';
        setTimeout(() => input.focus(), 100);
    }

    function closeAvoirModal() {
        document.getElementById('avoirModal').style.display = 'none';
    }

    function validerAvoir() {
        let barcode = document.getElementById('avoirBarcodeInput').value.trim();
        if (barcode === '') {
            alert("Veuillez saisir ou scanner un code à barre.");
            return;
        }

        fetch(`/vente/caisse/pos/check-avoir?code=${encodeURIComponent(barcode)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let av = data.avoir;

                    // Warn if avoir client doesn't match current client (except if current is PASSAGER)
                    if (av.clientid && av.clientid != currentClientId && currentClientId != 1) {
                        if (!confirm(`Cet avoir appartient à ${av.client_name}. Voulez-vous quand même l'utiliser ?`)) {
                            return;
                        }
                    }

                    let paye = mpReglementsArray.reduce((sum, r) => sum + r.montant, 0);
                    let reste = mpTotalDu - paye;

                    if (reste <= 0) {
                        alert("Le ticket est déjà entièrement réglé.");
                        return;
                    }

                    let montantAUtiliser = Math.min(av.montant, reste);
                    let today = new Date().toISOString().split('T')[0];

                    mpReglementsArray.push({
                        id: Date.now() + Math.random(),
                        modeId: 9,
                        mode: "Avoir",
                        montant: montantAUtiliser,
                        numero: av.numerointerne || av.cavoirnumero || barcode,
                        date: today,
                        banque: ''
                    });

                    renderMpTable();
                    closeAvoirModal();
                } else {
                    alert(data.message || "Avoir invalide ou introuvable.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Erreur lors de la vérification de l'avoir.");
            });
    }

    function mpSubmitValider(isAcompte) {
        let reglements = mpGetReglements();
        let paye = reglements.reduce((sum, r) => sum + r.montant, 0);
        let reste = mpTotalDu - paye;

        if (paye <= 0) {
            alert("Veuillez saisir au moins un montant.");
            return;
        }

        if (!isAcompte && reste > 0.005) {
            alert("Le total n'est pas réglé. Cliquez sur 'Valider Acompte' si vous souhaitez valider un paiement partiel.");
            return;
        }

        if (paye > mpTotalDu + 0.005) {
            alert("Le montant reçu ne peut pas dépasser le total (pas de rendu automatique pour les paiements multiples).");
            return;
        }

        if (complementMode) {
            // Send array of reglements
            fetch('/vente/caisse/store-complement-acompte', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    cticketid: complementTicketId, 
                    reglements: reglements 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Complément enregistré avec succès!');
                    closeMultiPaymentModal();
                    exitComplementMode();
                    document.getElementById('complementAcompteModal').style.display = 'none';
                } else {
                    alert('Erreur: ' + (data.message || ''));
                }
            })
            .catch(err => {
                console.error(err);
                alert("Erreur de communication.");
            });
            return;
        }

        // Standard Ticket Mode
        let payload = {
            vendeurid: document.getElementById('vendeurName').dataset.id || null,
            clientid: currentClientId,
            lignes: ticketLines,
            reglements: reglements,
            totalttc: parseFloat(document.getElementById('grandTotal').innerText),
            acompte: paye,
            netapayer: reste < 0 ? 0 : reste
        };

        window.submitTicketToBackend(payload, false);
    }

    function openRepriseModal() {
        fetch(`{{ route('vente.caisse.en_attente') }}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    let tbody = document.getElementById('repriseTbody');
                    tbody.innerHTML = '';
                    if (res.tickets.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">Aucun ticket en attente.</td></tr>';
                    } else {
                        res.tickets.forEach(t => {
                            let tr = document.createElement('tr');
                            tr.style.borderBottom = '1px solid #ddd';
                            tr.innerHTML = `
                                <td style="padding: 8px;">${t.cticketnumero || t.cticketid}</td>
                                <td style="padding: 8px;">${t.client_nom || 'PASSAGER'}</td>
                                <td style="padding: 8px;">${t.datecreation}</td>
                                <td style="padding: 8px; text-align: right; font-weight: bold;">${parseFloat(t.totalttc).toFixed(3)}</td>
                                <td style="padding: 8px; text-align: center;">
                                    <button onclick="loadReprise(${t.cticketid})" style="padding: 4px 10px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Reprendre</button>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                    document.getElementById('repriseModal').style.display = 'flex';
                }
            });
    }

    function closeRepriseModal() {
        document.getElementById('repriseModal').style.display = 'none';
    }

    function loadReprise(id) {
        if (ticketLines.length > 0) {
            if (!confirm("Attention, votre ticket en cours n'est pas vide. Voulez-vous vraiment le remplacer par le ticket en attente ?")) {
                return;
            }
        }

        fetch(`{{ url('vente/caisse/reprise') }}/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    currentClientId = res.clientid;
                    ticketLines = res.lignes;
                    
                    // Récupérer les infos du client si ce n'est pas le passager
                    if (currentClientId && currentClientId != 1) {
                        fetch(`{{ url('vente/caisse/pos/client') }}/${currentClientId}`)
                            .then(cRes => cRes.json())
                            .then(cData => {
                                if (cData.success) {
                                    document.getElementById('clientName').innerText = cData.client.nom;
                                    document.getElementById('clientCode').value = cData.client.clientcode || cData.client.clientid;
                                    let soldeInfoDiv = document.getElementById('clientSoldeInfo');
                                    soldeInfoDiv.style.display = 'block';
                                    let solde = parseFloat(cData.client.solde || 0).toFixed(3);
                                    let soldeFid = parseFloat(cData.client.soldefidelite || 0).toFixed(3);
                                    let pFid = parseFloat(cData.client.pointfidelite || 0).toFixed(1);
                                    soldeInfoDiv.innerText = `Solde : ${solde} DT | Solde.Fid : ${soldeFid} DT | P.Fid: ${pFid}`;
                                    
                                    // Save to localStorage now that it's fetched
                                    localStorage.setItem('pos_currentClientId', currentClientId);
                                    localStorage.setItem('pos_clientName', cData.client.nom);
                                    localStorage.setItem('pos_clientCode', cData.client.clientcode || cData.client.clientid);
                                }
                            });
                    } else {
                        document.getElementById('clientName').innerText = 'PASSAGER';
                        document.getElementById('clientCode').value = '';
                        document.getElementById('clientSoldeInfo').style.display = 'none';
                        localStorage.removeItem('pos_currentClientId');
                    }

                    renderTable();
                    closeRepriseModal();
                } else {
                    alert("Erreur: " + res.message);
                }
            });
    }

    // Restore from localStorage immediately (script is at the bottom)
    (function() {
        console.log("=== DEBUT RESTAURATION LOCALSTORAGE ===");
        let savedClient = localStorage.getItem('pos_currentClientId');
        console.log("savedClient:", savedClient);
        if (savedClient) {
            currentClientId = savedClient;
            let cName = localStorage.getItem('pos_clientName');
            let cCode = localStorage.getItem('pos_clientCode');
            console.log("cName:", cName, "cCode:", cCode);
            if (cName) document.getElementById('clientName').innerText = cName;
            if (cCode) document.getElementById('clientCode').value = cCode;
            
            let soldeInfoDiv = document.getElementById('clientSoldeInfo');
            if (!cName || cName.toUpperCase() === 'PASSAGER') {
                soldeInfoDiv.style.display = 'none';
            } else {
                soldeInfoDiv.style.display = 'block';
            }
        }

        let savedLines = localStorage.getItem('pos_ticketLines');
        console.log("savedLines length:", savedLines ? savedLines.length : 0);
        if (savedLines) {
            try {
                let parsed = JSON.parse(savedLines);
                console.log("parsed lines:", parsed);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    ticketLines = parsed;
                    renderTable();
                    console.log("Table rendered via localStorage");
                } else {
                    console.log("Parsed lines array is empty.");
                }
            } catch (e) {
                console.error("Erreur de parsing ticketLines", e);
            }
        }
        console.log("=== FIN RESTAURATION LOCALSTORAGE ===");
    })();

    // --- RETOUR TICKET LOGIC ---
    let ticketRetourLines = []; 

    function openRetourModal() {
        document.getElementById('retourTicketNumero').value = '';
        document.getElementById('retourTbody').innerHTML = '';
        ticketRetourLines = [];
        document.getElementById('retourModal').style.display = 'flex';
        document.getElementById('retourTicketNumero').focus();
    }

    function closeRetourModal() {
        document.getElementById('retourModal').style.display = 'none';
        document.getElementById('scanInput').focus();
    }

    function loadTicketMvts() {
        let num = document.getElementById('retourTicketNumero').value.trim();
        if (!num) return;
        
        // Fetch ticket details from backend
        fetch(`/vente/caisse/ticket-details/${num}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.ticket) {
                    ticketRetourLines = res.lines;
                    renderRetourTbody();
                } else {
                    alert("Ticket introuvable.");
                }
            })
            .catch(err => alert("Erreur de récupération du ticket."));
    }

    function renderRetourTbody() {
        let tbody = document.getElementById('retourTbody');
        tbody.innerHTML = '';
        ticketRetourLines.forEach((l, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left;">${l.article_ref}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left;">${l.article_designation}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">${parseFloat(l.qte)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">0</td> <!-- Qte Récupérée -->
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">
                    <input type="number" step="1" max="${parseFloat(l.qte)}" min="0" value="0" style="width: 60px; text-align: center; border: 1px solid #ccc; font-weight: bold;" onchange="updateRetourLine(${index}, this.value)" onkeyup="updateRetourLine(${index}, this.value)">
                </td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">${parseFloat(l.prix).toFixed(3)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">${parseFloat(l.remise).toFixed(2)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;" id="retourTotal_${index}">0.000</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateRetourLine(index, retQte) {
        let qte = parseFloat(retQte) || 0;
        let line = ticketRetourLines[index];
        if (qte > parseFloat(line.qte)) {
            alert("La quantité à retourner ne peut pas dépasser la quantité vendue.");
            qte = parseFloat(line.qte);
            document.querySelectorAll('#retourTbody tr')[index].querySelector('input[type=number]').value = qte;
        }
        line.qte_retour = qte;
        let total = qte * parseFloat(line.prix) * (1 - (parseFloat(line.remise)/100));
        document.getElementById(`retourTotal_${index}`).innerText = total.toFixed(3);
    }

    function validerRetour() {
        let hasRetour = false;
        ticketRetourLines.forEach(l => {
            if (l.qte_retour > 0) {
                // Add to current ticket lines as negative quantities
                let retourLine = {
                    produitid: l.articleid,
                    produit2id: l.produit2id,
                    code: l.article_ref,
                    reference: l.article_ref,
                    designation: l.article_designation,
                    qte: -Math.abs(l.qte_retour),
                    prix: l.prix,
                    remise: l.remise,
                    prixNet: parseFloat(l.prix) * (1 - (parseFloat(l.remise)/100)),
                    total: -(l.qte_retour * parseFloat(l.prix) * (1 - (parseFloat(l.remise)/100))),
                    stock: 0
                };
                ticketLines.push(retourLine);
                hasRetour = true;
            }
        });
        if (hasRetour) {
            renderTable();
            closeRetourModal();
        } else {
            alert("Veuillez saisir au moins une quantité à retourner pour valider.");
        }
    }
    
    function browseTicketsRetour() {
        closeRetourModal();
        openConsultationModal();
    }

    // --- MOUVEMENTS MODAL LOGIC ---
    let mouvementsData = [];

    function openMouvementsModal() {
        document.getElementById('mouvementsModal').style.display = 'flex';
        // optionally load clients into the dropdown here or just use the current PASSAGER
        // Let's copy clients from consultation modal or global clients list if we had one
        let clientSelect = document.getElementById('mvtFilterClient');
        if (clientSelect.options.length <= 1) {
            let options = '<option value="">PASSAGER</option>';
            document.querySelectorAll('#consultationClientFilter option').forEach(opt => {
                if (opt.value) {
                    options += `<option value="${opt.value}">${opt.innerText}</option>`;
                }
            });
            clientSelect.innerHTML = options;
        }
        
        loadMouvementsData();
    }

    function closeMouvementsModal() {
        document.getElementById('mouvementsModal').style.display = 'none';
        document.getElementById('retourTicketNumero').focus();
    }

    function loadMouvementsData() {
        let du = document.getElementById('mvtFilterDu').value;
        let au = document.getElementById('mvtFilterAu').value;
        let clientid = document.getElementById('mvtFilterClient').value;
        
        fetch(`{{ route('vente.caisse.mouvements') }}?du=${du}&au=${au}&clientid=${clientid}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    mouvementsData = res.mouvements;
                    renderMouvementsTable(mouvementsData);
                } else {
                    alert("Erreur de chargement des mouvements.");
                }
            })
            .catch(err => alert("Erreur serveur."));
    }

    function renderMouvementsTable(data) {
        let tbody = document.getElementById('mvtTbody');
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" style="text-align: center; padding: 40px; font-weight: bold; color: #555;">No data to display</td></tr>`;
            return;
        }
        
        data.forEach((m, index) => {
            let total = parseFloat(m.qte) * parseFloat(m.prix) * (1 - (parseFloat(m.remise)/100));
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">
                    <input type="checkbox" class="mvt-checkbox" data-index="${index}">
                </td>
                <td style="padding: 8px; border: 1px solid #ddd;">${m.cticketnumero || ''}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${(m.date || '').substring(0,10)}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${m.reference || ''}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${m.designation || ''}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${m.taille || ''}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${m.couleur || ''}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${parseFloat(m.qte)}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${parseFloat(m.prix).toFixed(3)}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${parseFloat(m.remise).toFixed(2)}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${total.toFixed(3)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filterMouvementsTable() {
        let text = document.getElementById('mvtSearchText').value.toLowerCase();
        let filtered = mouvementsData.filter(m => 
            (m.cticketnumero || '').toLowerCase().includes(text) ||
            (m.reference || '').toLowerCase().includes(text) ||
            (m.designation || '').toLowerCase().includes(text)
        );
        renderMouvementsTable(filtered);
    }

    function toggleAllMouvements(source) {
        document.querySelectorAll('.mvt-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    }

    function validerMouvementsSelection() {
        let selectedIndexes = [];
        document.querySelectorAll('.mvt-checkbox:checked').forEach(cb => {
            selectedIndexes.push(parseInt(cb.getAttribute('data-index')));
        });
        
        if (selectedIndexes.length === 0) {
            alert("Veuillez sélectionner au moins un mouvement.");
            return;
        }
        
        // Push selected to ticketRetourLines so they show in Retour modal
        selectedIndexes.forEach(idx => {
            let m = mouvementsData[idx];
            // Format for Retour Modal
            ticketRetourLines.push({
                articleid: m.articleid,
                produit2id: m.produit2id,
                article_ref: m.reference,
                article_designation: m.designation,
                qte: m.qte,
                prix: m.prix,
                remise: m.remise,
                qte_retour: 0 // Default to 0, user will change it in Retour Modal
            });
        });
        
        renderRetourTbody();
        closeMouvementsModal();
    }

</script>

<!-- CHEQUE MODAL -->
<div id="chequeModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 650px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Informations Cheque</h2>
            <button type="button" onclick="closeChequeModal()" style="background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: #666;">&times;</button>
        </div>

        <div style="padding: 15px;">
            <!-- Top section -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-weight: bold; color: #333;">Montant</label>
                    <input type="number" step="0.001" id="chqGlobalMontant" style="width: 120px; padding: 6px; border: 1px solid #ccc; border-radius: 2px; outline: none; font-size: 14px;">
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-weight: bold; color: #333;">Nombre de cheque</label>
                    <input type="number" id="chqNombre" value="1" min="1" style="width: 60px; padding: 6px; border: 1px solid #ccc; border-radius: 2px; outline: none; font-size: 14px; text-align: center;">
                    <button onclick="generateChequeLines()" style="padding: 4px 15px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; height: 32px; display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </div>
            </div>

            <!-- Table section -->
            <div style="border: 1px solid #ddd;">
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <thead style="background: white; border-bottom: 1px solid #ddd;">
                        <tr>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Montant</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Propriétaire</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Numéro</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Banque</th>
                            <th style="padding: 8px; font-weight: normal; color: #333;">Échéance</th>
                        </tr>
                    </thead>
                    <tbody id="chqTbody">
                        <!-- Lines generated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; flex-direction: column; background: #f8f9fa;">
            <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 5px;">
                <button onclick="validerChequeModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeChequeModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; color: #333;">
                    &times;
                </button>
            </div>
            <div style="color: #dc2626; font-size: 13px;">numéro chèque && Montant Obligatoire</div>
        </div>
    </div>
</div>

<script>
    let chqLines = [];

    function openChequeModal() {
        if (ticketLines.length === 0 && !complementMode) {
            alert("Le ticket est vide.");
            return;
        }

        let isMultiOpen = document.getElementById('multiPaymentModal').style.display === 'flex';
        let total = 0;
        
        if (isMultiOpen) {
            total = parseFloat(document.getElementById('mpResteAPayer').value) || 0;
        } else {
            total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
        }

        document.getElementById('chqGlobalMontant').value = total.toFixed(3);
        document.getElementById('chqNombre').value = 1;
        document.getElementById('chequeModal').style.display = 'flex';
        generateChequeLines(); // Auto-generate 1 line
    }

    function closeChequeModal() {
        document.getElementById('chequeModal').style.display = 'none';
    }

    function generateChequeLines() {
        let globalMontant = parseFloat(document.getElementById('chqGlobalMontant').value) || 0;
        let nbr = parseInt(document.getElementById('chqNombre').value) || 1;
        
        if (nbr < 1) nbr = 1;

        let amountPerCheque = (globalMontant / nbr).toFixed(3);
        let propName = document.getElementById('clientName').innerText === 'PASSAGER' ? 'PASSAGER' : document.getElementById('clientName').innerText;

        chqLines = [];
        let today = new Date().toISOString().split('T')[0];

        for (let i = 0; i < nbr; i++) {
            chqLines.push({
                montant: (i === nbr - 1) ? (globalMontant - (amountPerCheque * (nbr - 1))).toFixed(3) : amountPerCheque,
                proprietaire: propName,
                numero: '',
                banque: '',
                echeance: today
            });
        }
        renderChequeLines();
    }

    function renderChequeLines() {
        let tbody = document.getElementById('chqTbody');
        tbody.innerHTML = '';
        
        chqLines.forEach((l, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                    <input type="number" step="0.001" value="${l.montant}" onchange="updateChqLine(${index}, 'montant', this.value)" style="width: 80px; padding: 4px; border: 1px solid #ccc; outline: none; text-align: center; font-size: 12px;">
                </td>
                <td style="padding: 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                    <input type="text" value="${l.proprietaire}" onchange="updateChqLine(${index}, 'proprietaire', this.value)" style="width: 100px; padding: 4px; border: 1px solid #ccc; outline: none; font-size: 12px;">
                </td>
                <td style="padding: 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                    <input type="text" placeholder="Numéro" value="${l.numero}" onchange="updateChqLine(${index}, 'numero', this.value)" style="width: 90px; padding: 4px; border: 1px solid #ccc; outline: none; font-size: 12px;">
                </td>
                <td style="padding: 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                    <input type="text" placeholder="Banque" value="${l.banque}" onchange="updateChqLine(${index}, 'banque', this.value)" style="width: 90px; padding: 4px; border: 1px solid #ccc; outline: none; font-size: 12px;">
                </td>
                <td style="padding: 4px; border-bottom: 1px solid #ddd;">
                    <input type="date" value="${l.echeance}" onchange="updateChqLine(${index}, 'echeance', this.value)" style="width: 110px; padding: 4px; border: 1px solid #ccc; outline: none; font-size: 12px;">
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function updateChqLine(index, field, value) {
        chqLines[index][field] = value;
    }

    function validerChequeModal() {
        for(let l of chqLines) {
            let m = parseFloat(l.montant) || 0;
            if (m <= 0 || !l.numero.trim()) {
                alert("numéro chèque && Montant Obligatoire pour toutes les lignes.");
                return;
            }
        }

        let totalChq = chqLines.reduce((sum, l) => sum + (parseFloat(l.montant) || 0), 0);
        let isMultiOpen = document.getElementById('multiPaymentModal').style.display === 'flex';

        if (isMultiOpen) {
            chqLines.forEach(l => {
                mpReglementsArray.push({
                    id: Date.now() + Math.random(),
                    modeId: 2, // 2 for Cheque
                    mode: 'Cheque',
                    montant: parseFloat(l.montant),
                    numero: l.numero,
                    date: l.echeance,
                    banque: l.banque,
                    proprietaire: l.proprietaire
                });
            });
            renderMpTable();
            closeChequeModal();
        } else {
            let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
            
            let regs = chqLines.map(l => ({
                modereglementid: 2,
                montant: parseFloat(l.montant),
                numero: l.numero,
                banque: l.banque,
                date: l.echeance,
                proprietaire: l.proprietaire
            }));

            let payload = {
                vendeurid: document.getElementById('vendeurName').dataset.id || null,
                clientid: currentClientId,
                lignes: ticketLines,
                totalttc: total,
                acompte: totalChq,
                netapayer: Math.max(0, total - totalChq),
                reglements: regs
            };

            if (complementMode) {
                fetch('/vente/caisse/store-complement-acompte', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        cticketid: complementTicketId, 
                        reglements: payload.reglements 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Complément enregistré avec succès!');
                        closeChequeModal();
                        exitComplementMode();
                        document.getElementById('complementAcompteModal').style.display = 'none';
                    } else {
                        alert('Erreur: ' + (data.message || ''));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Erreur réseau.");
                });
            } else {
                closeChequeModal();
                window.submitTicketToBackend(payload, false);
            }
        }
    }
</script>

<!-- QUICK PAYMENT MODAL -->
<div id="quickPaymentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 350px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <div style="width: 100%; text-align: center;">
                <h2 id="quickPaymentTitle" style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Réglement Espèce</h2>
            </div>
            <button type="button" onclick="closeQuickPaymentModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666; position: absolute; right: 15px;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 30px 20px; display: flex; align-items: center; justify-content: center; gap: 15px;">
            <label style="font-size: 16px; color: #555;">Montant</label>
            <input type="number" id="quickPaymentMontant" step="0.001" style="width: 180px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none; font-size: 14px;" onkeydown="if(event.key === 'Enter') validerQuickPayment()">
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; justify-content: center; align-items: center; gap: 10px; background: #f8f9fa;">
            <button onclick="validerQuickPayment()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button onclick="closeQuickPaymentModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; color: #333;">
                &times;
            </button>
        </div>
    </div>
</div>

<script>
    let quickPaymentModeId = 1;

    function openQuickPaymentModal(modeId, title) {
        if (ticketLines.length === 0 && !complementMode) {
            alert("Le ticket est vide.");
            return;
        }
        quickPaymentModeId = modeId;
        document.getElementById('quickPaymentTitle').innerText = title;

        let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
        document.getElementById('quickPaymentMontant').value = total.toFixed(3);
        document.getElementById('quickPaymentModal').style.display = 'flex';
        setTimeout(() => document.getElementById('quickPaymentMontant').focus(), 100);
    }

    function closeQuickPaymentModal() {
        document.getElementById('quickPaymentModal').style.display = 'none';
    }

    function validerQuickPayment() {
        let mnt = parseFloat(document.getElementById('quickPaymentMontant').value);
        if (isNaN(mnt) || mnt <= 0) {
            alert("Montant invalide.");
            return;
        }

        let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);

        let payload = {
            vendeurid: document.getElementById('vendeurName').dataset.id || null,
            clientid: currentClientId,
            lignes: ticketLines,
            totalttc: total,
            acompte: mnt,
            netapayer: Math.max(0, total - mnt),
            reglements: [{
                modereglementid: quickPaymentModeId,
                montant: mnt,
                numero: '',
                banque: '',
                date: new Date().toISOString().split('T')[0]
            }]
        };

        if (complementMode) {
            fetch('/vente/caisse/store-complement-acompte', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    cticketid: complementTicketId, 
                    reglements: payload.reglements 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Complément enregistré avec succès!');
                    closeQuickPaymentModal();
                    exitComplementMode();
                    document.getElementById('complementAcompteModal').style.display = 'none';
                } else {
                    alert('Erreur: ' + (data.message || ''));
                }
            })
            .catch(err => {
                console.error(err);
                alert("Erreur réseau.");
            });
        } else {
            closeQuickPaymentModal();
            window.submitTicketToBackend(payload, false);
        }
    }
</script>

<!-- MULTI PAYMENT MODAL -->
<div id="multiPaymentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 800px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: #333;">Réglement Client</h2>
            <div>Vendeur <span id="mpVendeurName" style="font-weight: bold; margin-right: 20px;"></span></div>
            <button type="button" onclick="closeMultiPaymentModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Client Name -->
        <div style="text-align: center; padding: 10px; font-weight: bold; color: #0284c7; border-bottom: 1px solid #eee;">
            <a href="#" id="mpClientName" style="text-decoration: none; color: inherit;">PASSAGER</a>
        </div>

        <!-- Body split -->
        <div style="display: flex; padding: 15px; gap: 20px;">
            <!-- Left Panel: Détails Règlements -->
            <div style="flex: 3; border: 1px solid #ddd; min-height: 200px; display: flex; flex-direction: column;">
                <div style="padding: 5px; background: #f8f9fa; border-bottom: 1px solid #ddd; color: #0284c7;">Détails Règlements</div>
                <div style="flex: 1; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                        <thead>
                            <tr style="background: #f1f5f9; border-bottom: 1px solid #ddd;">
                                <th style="padding: 5px; border-right: 1px solid #ddd;">Mode</th>
                                <th style="padding: 5px; border-right: 1px solid #ddd; text-align: right;">Montant</th>
                                <th style="padding: 5px; border-right: 1px solid #ddd;">Numéro</th>
                                <th style="padding: 5px; border-right: 1px solid #ddd;">Echéance</th>
                                <th style="padding: 5px;">Banque</th>
                            </tr>
                        </thead>
                        <tbody id="mpReglementsTbody">
                            <tr><td colspan="5" style="text-align: center; padding: 30px; font-weight: bold;">No data to display</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel: Inputs -->
            <div style="flex: 2; display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; color: #16a34a; font-size: 16px; font-weight: bold;">
                    <span>Net À PAYER</span>
                    <input type="text" id="mpNetAPayer" readonly style="width: 120px; padding: 4px; border: 1px solid #ddd; text-align: right; background: #f8f9fa; font-weight: bold; color: #16a34a;" value="0.000">
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; color: #dc2626; font-size: 16px; font-weight: bold;">
                    <span>RESTE À PAYER</span>
                    <input type="text" id="mpResteAPayer" readonly style="width: 120px; padding: 4px; border: 1px solid #ddd; text-align: right; background: #f8f9fa; font-weight: bold; color: #dc2626;" value="0.000">
                </div>
                <hr style="border: none; border-top: 1px solid #eee; margin: 5px 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Espèce</span>
                    <div style="display: flex; gap: 5px;">
                        <input type="number" id="mpEspece" step="0.001" style="width: 120px; padding: 4px; border: 1px solid #ccc; text-align: right;" oninput="toggleMpAddBtn('Espece')">
                        <button id="mpBtnEspece" onclick="mpAddLine('Espece', 1)" style="padding: 0 5px; border: 1px solid #ccc; background: white; cursor: pointer; display: none; color: green; font-weight: bold;">✓</button>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Cheque</span>
                    <div style="display: flex; gap: 5px;">
                        <input type="number" id="mpCheque" step="0.001" style="width: 120px; padding: 4px; border: 1px solid #ccc; text-align: right;" oninput="toggleMpAddBtn('Cheque')">
                        <button id="mpBtnCheque" onclick="mpAddLine('Cheque', 2)" style="padding: 0 5px; border: 1px solid #ccc; background: white; cursor: pointer; display: none; color: green; font-weight: bold;">✓</button>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Carte Crédit</span>
                    <div style="display: flex; gap: 5px;">
                        <input type="number" id="mpCB" step="0.001" style="width: 120px; padding: 4px; border: 1px solid #ccc; text-align: right;" oninput="toggleMpAddBtn('CB')">
                        <button id="mpBtnCB" onclick="mpAddLine('CB', 3)" style="padding: 0 5px; border: 1px solid #ccc; background: white; cursor: pointer; display: none; color: green; font-weight: bold;">✓</button>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Bon d'achat</span>
                    <div style="display: flex; gap: 5px;">
                        <input type="text" id="mpBonAchatVal" value="0.000" readonly style="width: 120px; padding: 4px; border: 1px solid #ccc; text-align: right; outline: none;">
                        <button onclick="openBonAchatModal()" style="padding: 2px 10px; border: 1px solid #333; border-radius: 4px; background: white; cursor: pointer; font-weight: bold;">...</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <div style="display: flex; gap: 10px;">
                <button onclick="openAvoirModal()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; font-weight: bold; cursor: pointer;">Avoir</button>
                <button onclick="openChequeCadeauxModal()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; font-weight: bold; cursor: pointer;">Chèque Cadeaux</button>
                <button onclick="openCouponModal()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; font-weight: bold; cursor: pointer;">Coupon</button>
            </div>
        </div>
        <div style="padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; background: white;">
            <button id="btnMpValiderAcompte" onclick="mpSubmitValider(true)" style="padding: 8px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; font-weight: bold; cursor: pointer;">Valider Acompte</button>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="mpSubmitValider(false)" style="width: 80px; height: 35px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeMultiPaymentModal()" style="width: 80px; height: 35px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold;">
                    &times;
                </button>
            </div>
        </div>
    </div>
</div>

<!-- BON D'ACHAT MODAL -->
<div id="bonAchatModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Bon d'achat</h2>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: bold;">Net à Payer <span id="baNetAPayer">0.000</span></span>
                <button type="button" onclick="closeBonAchatModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
            </div>
        </div>

        <div style="padding: 15px; display: flex; flex-direction: column; gap: 15px;">
            <!-- Code Input -->
            <input type="text" id="baCodeInput" style="width: 50%; padding: 8px; border: 1px solid #ccc;" onkeydown="if(event.key==='Enter') baAddLine()">

            <!-- Table -->
            <div style="border: 1px solid #ddd; min-height: 100px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
                    <thead style="background: #f8f9fa; border-bottom: 1px solid #ddd;">
                        <tr>
                            <th style="padding: 5px; border-right: 1px solid #ddd;">Numéro</th>
                            <th style="padding: 5px; border-right: 1px solid #ddd;">MONTANT</th>
                            <th style="padding: 5px; border-right: 1px solid #ddd;">REMISE %</th>
                            <th style="padding: 5px; text-align: center;"><a href="#" onclick="baAddLine()" style="color: #0284c7; text-decoration: underline;">New</a></th>
                        </tr>
                    </thead>
                    <tbody id="baTbody">
                        <tr><td colspan="4" style="text-align: center; padding: 20px; font-weight: bold;">No data to display</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Client Info -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <label style="font-weight: bold; white-space: nowrap;">Nom Prenom</label>
                <input type="text" id="baNom" style="flex: 1; padding: 6px; border: 1px solid #ccc;">
                <label style="font-weight: bold; white-space: nowrap;">Telephone</label>
                <input type="text" id="baTelephone" style="flex: 1; padding: 6px; border: 1px solid #ccc;">
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: #dc2626; font-weight: bold; font-size: 16px;">Nom && Prenom Obligatoire</div>
            <div style="display: flex; gap: 10px;">
                <button onclick="validerBonAchat()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeBonAchatModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold;">
                    &times;
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AVOIR MODAL -->
<div id="avoirModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 480px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); font-family: Arial, sans-serif; overflow: hidden; border: 1px solid #ccc;">
        <!-- Header -->
        <div style="padding: 10px 15px; background: #f3f4f6; border-bottom: 1px solid #e5e7eb; font-size: 14px; font-weight: normal; color: #333;">
            Saisie Avoir Client
        </div>

        <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px; align-items: center;">
            <!-- Code Input -->
            <div style="display: flex; align-items: center; width: 100%; gap: 10px;">
                <label style="font-size: 13px; color: #475569; font-weight: normal; white-space: nowrap; width: 100px;">Code à barre</label>
                <input type="text" id="avoirBarcodeInput" style="flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; outline: none;" onkeydown="if(event.key==='Enter') validerAvoir()">
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px; width: 100%;">
                <button onclick="validerAvoir()" style="width: 80px; height: 38px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeAvoirModal()" style="width: 80px; height: 38px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CHEQUE CADEAUX MODAL -->
<div id="chequeCadeauxModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 550px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Type Cheque Cadeau</h2>
            <button type="button" onclick="closeChequeCadeauxModal()" style="background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: #666;">&times;</button>
        </div>

        <div style="padding: 20px;">
            <div style="border: 1px solid #ddd; border-radius: 4px;">
                <div style="padding: 20px; display: flex; justify-content: center; border-bottom: 1px solid #ddd;">
                    <div style="display: flex; align-items: center; justify-content: center; border: 1px solid #1e293b; border-radius: 4px; padding: 15px; width: 90%; gap: 30px;">
                        <span style="font-size: 14px; color: #333;">Montant</span>
                        <input type="number" step="0.001" id="ccMontant" style="width: 150px; padding: 8px; border: 1px solid #ccc; border-radius: 2px; text-align: center; outline: none; font-weight: bold; font-size: 14px;">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; padding: 20px; gap: 10px;">
                    <button onclick="addChequeCadeaux('PLUXEE')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">PLUXEE</button>
                    <button onclick="addChequeCadeaux('JOKER')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">JOKER</button>
                    <button onclick="addChequeCadeaux('SERVIMAX')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">SERVIMAX</button>
                    <button onclick="addChequeCadeaux('TOPCHEQUE')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">TOPCHEQUE</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- COUPON MODAL -->
<div id="couponModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Coupon</h2>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: bold;">Net à Payer <span id="cpNetAPayer">0.000</span></span>
                <button type="button" onclick="closeCouponModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
            </div>
        </div>

        <div style="padding: 15px; display: flex; flex-direction: column; gap: 15px;">
            <!-- Code Input -->
            <input type="text" id="cpCodeInput" style="width: 50%; padding: 8px; border: 1px solid #ccc;" onkeydown="if(event.key==='Enter') cpAddLine()">

            <!-- Table -->
            <div style="border: 1px solid #ddd; min-height: 100px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
                    <thead style="background: #f8f9fa; border-bottom: 1px solid #ddd;">
                        <tr>
                            <th style="padding: 5px; border-right: 1px solid #ddd;">Numéro</th>
                            <th style="padding: 5px; border-right: 1px solid #ddd;">MONTANT</th>
                            <th style="padding: 5px; text-align: center;"><a href="#" onclick="cpAddLine()" style="color: #0284c7; text-decoration: underline;">New</a></th>
                        </tr>
                    </thead>
                    <tbody id="cpTbody">
                        <tr><td colspan="3" style="text-align: center; padding: 20px; font-weight: bold;">No data to display</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Client Info -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <label style="font-weight: bold; white-space: nowrap;">Nom Prenom</label>
                <input type="text" id="cpNom" style="flex: 1; padding: 6px; border: 1px solid #ccc;">
                <label style="font-weight: bold; white-space: nowrap;">Telephone</label>
                <input type="text" id="cpTelephone" style="flex: 1; padding: 6px; border: 1px solid #ccc;">
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; justify-content: center; align-items: center; gap: 10px;">
            <button onclick="validerCoupon()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button onclick="closeCouponModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold;">
                &times;
            </button>
        </div>
    </div>
</div>

<script>
    function openChequeCadeauxModal() {
        if (ticketLines.length === 0 && !complementMode) {
            alert("Le ticket est vide.");
            return;
        }

        let isMultiOpen = document.getElementById('multiPaymentModal').style.display === 'flex';
        let total = 0;
        
        if (isMultiOpen) {
            total = parseFloat(document.getElementById('mpResteAPayer').value) || 0;
        } else {
            total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
        }

        document.getElementById('ccMontant').value = total.toFixed(3);
        document.getElementById('chequeCadeauxModal').style.display = 'flex';
    }

    function closeChequeCadeauxModal() {
        document.getElementById('chequeCadeauxModal').style.display = 'none';
    }

    function addChequeCadeaux(type) {
        let val = parseFloat(document.getElementById('ccMontant').value) || 0;
        if (val <= 0) {
            alert("Montant invalide");
            return;
        }
        
        let today = new Date().toISOString().split('T')[0];
        let isMultiOpen = document.getElementById('multiPaymentModal').style.display === 'flex';

        if (isMultiOpen) {
            mpReglementsArray.push({
                id: Date.now(),
                modeId: 7, // Assuming 7 for cheque cadeau
                mode: 'Chèque Cadeau',
                montant: val,
                numero: '',
                date: today,
                banque: type
            });

            renderMpTable();
            closeChequeCadeauxModal();
        } else {
            // Direct submission
            let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
            let payload = {
                vendeurid: document.getElementById('vendeurName').dataset.id || null,
                clientid: currentClientId,
                lignes: ticketLines,
                totalttc: total,
                acompte: val,
                netapayer: Math.max(0, total - val),
                reglements: [{
                    modereglementid: 7,
                    montant: val,
                    numero: '',
                    banque: type,
                    date: today
                }]
            };

            if (complementMode) {
                fetch('/vente/caisse/store-complement-acompte', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        cticketid: complementTicketId, 
                        reglements: payload.reglements 
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Complément enregistré avec succès!');
                        closeChequeCadeauxModal();
                        exitComplementMode();
                        document.getElementById('complementAcompteModal').style.display = 'none';
                    } else {
                        alert('Erreur: ' + (data.message || ''));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Erreur réseau.");
                });
            } else {
                closeChequeCadeauxModal();
                window.submitTicketToBackend(payload, false);
            }
        }
    }

    // COUPON LOGIC
    let cpLignes = [];
    let cpAdding = false;
    let cpDraft = { numero: '', montant: '' };

    function openCouponModal() {
        document.getElementById('couponModal').style.display = 'flex';
        let reste = document.getElementById('mpResteAPayer').value;
        document.getElementById('cpNetAPayer').innerText = parseFloat(reste).toFixed(3);
        document.getElementById('cpCodeInput').value = '';
        document.getElementById('cpNom').value = document.getElementById('clientName').innerText === 'PASSAGER' ? '' : document.getElementById('clientName').innerText;
        document.getElementById('cpTelephone').value = '';
        cpLignes = [];
        cpAdding = false;
        renderCpTable();
    }

    function closeCouponModal() {
        document.getElementById('couponModal').style.display = 'none';
    }

    function cpAddLine() {
        cpAdding = true;
        let code = document.getElementById('cpCodeInput').value.trim();
        cpDraft = { numero: code, montant: '' };
        document.getElementById('cpCodeInput').value = '';
        renderCpTable();
    }

    function cpSaveDraft() {
        let num = document.getElementById('cpDraftNum').value.trim();
        let mnt = parseFloat(document.getElementById('cpDraftMnt').value);
        if (!num || isNaN(mnt) || mnt <= 0) {
            alert("Veuillez saisir un numéro et un montant valide.");
            return;
        }
        cpLignes.push({ numero: num, montant: mnt });
        cpAdding = false;
        renderCpTable();
    }

    function cpCancelDraft() {
        cpAdding = false;
        renderCpTable();
    }

    function renderCpTable() {
        let tbody = document.getElementById('cpTbody');
        tbody.innerHTML = '';
        if (cpLignes.length === 0 && !cpAdding) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 20px; font-weight: bold;">No data to display</td></tr>';
            return;
        }

        cpLignes.forEach((l, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${l.numero}" onchange="cpUpdate(${index}, 'numero', this.value)" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="number" step="0.001" value="${l.montant}" onchange="cpUpdate(${index}, 'montant', this.value)" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; text-align: center;"><button onclick="cpRemove(${index})" style="background: none; border: none; color: red; cursor: pointer; font-weight: bold;">X</button></td>
            `;
            tbody.appendChild(tr);
        });

        if (cpAdding) {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="text" id="cpDraftNum" value="${cpDraft.numero}" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;">
                </td>
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="number" step="0.001" id="cpDraftMnt" value="${cpDraft.montant}" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;">
                </td>
                <td style="padding: 4px; text-align: center; white-space: nowrap;">
                    <button onclick="cpSaveDraft()" style="background: #673ab7; color: white; border: none; border-radius: 3px; padding: 5px 12px; cursor: pointer; font-size: 11px; font-weight: bold; margin-right: 4px;">Save</button>
                    <button onclick="cpCancelDraft()" style="background: white; color: #333; border: 1px solid #ccc; border-radius: 3px; padding: 4px 12px; cursor: pointer; font-size: 11px;">Cancel</button>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    function cpUpdate(index, field, val) {
        if (field === 'numero') cpLignes[index].numero = val;
        else cpLignes[index][field] = parseFloat(val) || 0;
    }

    function cpRemove(index) {
        cpLignes.splice(index, 1);
        renderCpTable();
    }

    function validerCoupon() {
        let totalCP = cpLignes.reduce((sum, l) => sum + l.montant, 0);
        if (totalCP > 0) {
            cpLignes.forEach(l => {
                if(l.montant > 0) {
                    let today = new Date().toISOString().split('T')[0];
                    mpReglementsArray.push({
                        id: Date.now() + Math.random(),
                        modeId: 6, // Assuming 6 for coupon
                        mode: "Coupon",
                        montant: l.montant,
                        numero: l.numero,
                        date: today,
                        banque: ''
                    });
                }
            });
            renderMpTable();
        }
        closeCouponModal();
    }
</script>

<!-- REPRISE MODAL -->
<div id="repriseModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; max-height: 80%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Tickets en Attente</h2>
            <button type="button" onclick="closeRepriseModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <div style="padding: 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">N° Ticket</th>
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Client</th>
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Date</th>
                        <th style="padding: 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Total TTC</th>
                        <th style="padding: 8px; text-align: center; border-bottom: 2px solid #e5e7eb;">Action</th>
                    </tr>
                </thead>
                <tbody id="repriseTbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- COMPLEMENT ACOMPTE MODAL -->
<div id="complementAcompteModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 950px; max-height: 85%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Complement acomptes</h2>
            <button type="button" onclick="closeComplementAcompteModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <!-- Filters -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">DU :</label>
                <input type="date" id="compAcompte_du" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px;">
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">AU :</label>
                <input type="date" id="compAcompte_au" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px;">
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">Client :</label>
                <select id="compAcompte_client" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px; min-width: 150px;">
                    <option value="">-- Tous --</option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">N° :</label>
                <input type="text" id="compAcompte_numero" placeholder="Numéro ticket" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px; width: 100px;">
            </div>
            <button onclick="fetchComplementTickets()" style="padding: 5px 15px; border: none; background: #2563eb; color: white; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold;">Filtrer</button>
        </div>
        <!-- Table -->
        <div style="padding: 10px 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">N° Ticket</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Date</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Client</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Caissier</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Vendeur</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Total Qte</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Total TTC</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Acompte</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Reste</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Montant reçu</th>
                        <th style="padding: 6px 8px; text-align: center; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody id="complementAcompteTbody">
                    <tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Cliquez sur Filtrer pour charger les tickets</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Footer totals -->
        <div style="padding: 8px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 20px; font-weight: bold; font-size: 11px;">
            <span>Total Qte: <span id="compAcompte_totalQte">0</span></span>
            <span>Total TTC: <span id="compAcompte_totalTTC">0.000</span></span>
            <span>Total Reste: <span id="compAcompte_totalReste">0.000</span></span>
        </div>
    </div>
</div>


<script>
    // ===== COMPLEMENT ACOMPTE LOGIC =====
    let pendingComplementAcompte = false;
    let compAcompteTicketsData = [];
    let complementMode = false;
    let complementTicketId = null;
    let complementReste = 0;
    let complementNumero = '';

    function openComplementAcompteFlow() {
        // Si pas de client sélectionné (PASSAGER), demander de choisir un client d'abord
        if (!currentClientId || currentClientId == 1) {
            pendingComplementAcompte = true;
            openClientModal();
            return;
        }
        openComplementAcompteModal();
    }

    function openComplementAcompteModal() {
        let today = new Date().toISOString().split('T')[0];
        let oneYearAgo = new Date();
        oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1);
        document.getElementById('compAcompte_du').value = oneYearAgo.toISOString().split('T')[0];
        document.getElementById('compAcompte_au').value = today;
        document.getElementById('compAcompte_numero').value = '';
        
        document.getElementById('complementAcompteModal').style.display = 'flex';
        fetchComplementTickets();
    }

    function closeComplementAcompteModal() {
        document.getElementById('complementAcompteModal').style.display = 'none';
    }

    function fetchComplementTickets() {
        let du = document.getElementById('compAcompte_du').value;
        let au = document.getElementById('compAcompte_au').value;
        let clientid = document.getElementById('compAcompte_client').value;
        let numero = document.getElementById('compAcompte_numero').value;

        let params = new URLSearchParams({ du, au });
        if (clientid) params.append('clientid', clientid);
        if (numero) params.append('numero', numero);

        document.getElementById('complementAcompteTbody').innerHTML = '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Chargement...</td></tr>';

        fetch(`/vente/caisse/tickets-reste?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    compAcompteTicketsData = data.tickets;
                    renderComplementTickets(data.tickets);
                    let sel = document.getElementById('compAcompte_client');
                    let currentVal = sel.value;
                    sel.innerHTML = '<option value="">-- Tous --</option>';
                    if (data.clients) {
                        data.clients.forEach(c => {
                            let opt = document.createElement('option');
                            opt.value = c.clientid;
                            opt.textContent = c.nom;
                            sel.appendChild(opt);
                        });
                    }
                    sel.value = currentVal;
                } else {
                    document.getElementById('complementAcompteTbody').innerHTML = '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #dc2626;">Erreur de chargement</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('complementAcompteTbody').innerHTML = '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #dc2626;">Erreur réseau</td></tr>';
            });
    }

    function renderComplementTickets(tickets) {
        let tbody = document.getElementById('complementAcompteTbody');
        tbody.innerHTML = '';

        if (!tickets || tickets.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Aucun ticket avec reste à payer</td></tr>';
            document.getElementById('compAcompte_totalQte').textContent = '0';
            document.getElementById('compAcompte_totalTTC').textContent = '0.000';
            document.getElementById('compAcompte_totalReste').textContent = '0.000';
            return;
        }

        let totalQte = 0, totalTTC = 0, totalReste = 0;

        tickets.forEach(t => {
            let date = t.datecreation ? new Date(t.datecreation).toLocaleDateString('fr-FR') : '';
            let reste = parseFloat(t.netapayer) || 0;
            let acompte = parseFloat(t.acompte) || 0;
            let ttc = parseFloat(t.totalttc) || 0;
            let qte = parseFloat(t.totalqte) || 0;

            totalQte += qte;
            totalTTC += ttc;
            totalReste += reste;

            let tr = document.createElement('tr');
            tr.style.cursor = 'pointer';
            tr.onmouseover = function() { this.style.background = '#f0f7ff'; };
            tr.onmouseout = function() { this.style.background = ''; };
            tr.innerHTML = `
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${t.cticketnumero || ''}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${date}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${t.client_nom || ''}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${t.caissier_nom || ''}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${t.vendeur_nom || ''}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right;">${qte}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right;">${ttc.toFixed(3)}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right;">${acompte.toFixed(3)}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right; color: #dc2626; font-weight: bold;">${reste.toFixed(3)}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; text-align: right;">
                    <input type="number" step="0.001" value="${reste.toFixed(3)}" style="width: 80px; padding: 2px 4px; border: 1px solid #ccc; border-radius: 2px; text-align: right; font-size: 11px;" id="compMontant_${t.cticketid}">
                </td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; text-align: center;">
                    <button onclick="selectComplementTicket(${t.cticketid}, '${t.cticketnumero}', ${reste}, ${t.clientid || 0})" style="padding: 3px 10px; border: none; background: #16a34a; color: white; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold;" title="Charger dans le panier">✓</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('compAcompte_totalQte').textContent = totalQte;
        document.getElementById('compAcompte_totalTTC').textContent = totalTTC.toFixed(3);
        document.getElementById('compAcompte_totalReste').textContent = totalReste.toFixed(3);
    }

    function selectComplementTicket(cticketid, numero, reste, clientid) {
        if (ticketLines.length > 0) {
            if (!confirm("Le panier n'est pas vide. Voulez-vous le remplacer par ce ticket ?")) {
                return;
            }
        }

        // Récupérer le montant personnalisé si modifié
        let montantInput = document.getElementById('compMontant_' + cticketid);
        let montantCustom = montantInput ? parseFloat(montantInput.value) : reste;
        if (isNaN(montantCustom) || montantCustom <= 0) montantCustom = reste;
        if (montantCustom > reste) montantCustom = reste;

        // Charger les détails du ticket dans le panier
        fetch(`/vente/caisse/ticket-details/${numero}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    // Activer le mode complément
                    complementMode = true;
                    complementTicketId = cticketid;
                    complementReste = montantCustom;
                    complementNumero = numero;

                    // Charger les lignes du ticket dans le panier
                    ticketLines = [];
                    res.lines.forEach(l => {
                        ticketLines.push({
                            produitid: l.produitid || 0,
                            produit2id: l.produit2id || 0,
                            ref: l.article_ref || '',
                            code: l.article_ref || '',
                            reference: l.article_ref || '',
                            designation: l.article_designation || '',
                            taille: l.taille || '',
                            couleur: l.couleur || '',
                            qte: parseFloat(l.qte) || 0,
                            prix: parseFloat(l.prix) || 0,
                            remise: parseFloat(l.remise) || 0,
                            prixNet: parseFloat(l.prix) * (1 - (parseFloat(l.remise) || 0) / 100),
                            total: parseFloat(l.totalttc) || 0,
                            stock: 0
                        });
                    });

                    // Mettre à jour le client
                    if (clientid && clientid != 1) {
                        currentClientId = clientid;
                        fetch(`/vente/caisse/pos/client/${clientid}`)
                            .then(cRes => cRes.json())
                            .then(cData => {
                                if (cData.success) {
                                    document.getElementById('clientName').innerText = cData.client.nom;
                                    document.getElementById('clientCode').value = cData.client.clientcode || cData.client.clientid;
                                    let soldeInfoDiv = document.getElementById('clientSoldeInfo');
                                    soldeInfoDiv.style.display = 'block';
                                    let solde = parseFloat(cData.client.solde || 0).toFixed(3);
                                    let soldeFid = parseFloat(cData.client.soldefidelite || 0).toFixed(3);
                                    let pFid = parseFloat(cData.client.pointfidelite || 0).toFixed(1);
                                    soldeInfoDiv.innerText = `Solde : ${solde} DT | Solde.Fid : ${soldeFid} DT | P.Fid: ${pFid}`;
                                }
                            });
                    }

                    // Render le panier
                    renderTable();

                    // Afficher le reste dans le header
                    updateComplementDisplay();

                    closeComplementAcompteModal();
                } else {
                    alert("Erreur: " + (res.message || 'Ticket introuvable'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur réseau lors du chargement du ticket.');
            });
    }

    function updateComplementDisplay() {
        if (!complementMode) return;
        let reste = complementReste;
        // Afficher le reste dans le grandTotal
        document.getElementById('grandTotal').innerText = reste.toFixed(3);
        document.getElementById('grandTotal').style.color = '#dc2626';

        // Mettre à jour le footer
        let totalTTC = 0;
        ticketLines.forEach(l => totalTTC += parseFloat(l.total) || 0);
        let acompte = totalTTC - reste;
        document.getElementById('lblAcompte').innerText = acompte.toFixed(3);
        document.getElementById('lblRestePayer').innerText = reste.toFixed(3);
        document.getElementById('lblRestePayer').style.color = '#dc2626';

        // Afficher un bandeau "Mode Complément" dans le display
        document.getElementById('display-qte-prix').innerText = `COMPLEMENT TICKET N° ${complementNumero}`;
        document.getElementById('display-qte-prix').style.color = '#dc2626';
        document.getElementById('display-remise').innerText = '';
        document.getElementById('display-total').innerText = `RESTE: ${reste.toFixed(3)}`;
        document.getElementById('display-total').style.color = '#dc2626';

        // Mettre le N° du ticket dans le champ
        document.getElementById('ticketNumber').value = complementNumero;
    }

    function exitComplementMode() {
        complementMode = false;
        complementTicketId = null;
        complementReste = 0;
        complementNumero = '';
        ticketLines = [];

        // Reset les styles
        document.getElementById('grandTotal').style.color = '';
        document.getElementById('lblRestePayer').style.color = '';
        document.getElementById('display-qte-prix').style.color = '';
        document.getElementById('display-total').style.color = '';
        document.getElementById('lblAcompte').innerText = '0.000';
        document.getElementById('ticketNumber').value = '';

        // Reset le client
        currentClientId = {{ $client ? $client->clientid : 1 }};
        document.getElementById('clientName').innerText = 'PASSAGER';
        document.getElementById('clientSoldeInfo').style.display = 'none';
        document.getElementById('clientCode').value = '4110001';

        renderTable();
    }

    function validerComplementAcompte() {
        // Cette fonction est appelée depuis validerPaiement quand complementMode est actif
        let modeId = document.getElementById('paymentModeId').value;
        let montant = complementReste;

        fetch('/vente/caisse/store-complement-acompte', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                cticketid: complementTicketId,
                montant: montant,
                modereglementid: modeId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closePaymentModal();
                let msg = document.createElement('div');
                msg.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#10b981;color:white;padding:10px 20px;border-radius:5px;z-index:9999;font-family:Arial;font-size:13px;';
                msg.textContent = data.message || 'Complément enregistré !';
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 3000);
                exitComplementMode();
            } else {
                alert(data.message || "Erreur lors de l'enregistrement.");
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erreur réseau.');
        });
    }
</script>

</body>
</html>
