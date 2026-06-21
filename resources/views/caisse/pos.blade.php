<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caisse VELARO</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
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
            align-items: center;
        }

        .field-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-group input {
            border: 1px solid #d1d5db;
            padding: 4px;
            font-size: 11px;
        }

        .btn-lookup {
            border: 1px solid #d1d5db;
            background: white;
            cursor: pointer;
            padding: 2px 8px;
            color: #9ca3af;
        }

        .header-stats {
            display: flex;
            gap: 20px;
            font-weight: bold;
            padding-right: 20px;
        }

        /* MAIN AREA */
        .pos-main {
            display: flex;
            flex: 1;
            gap: 5px;
            min-height: 0; /* needed for flex child overflow */
        }

        /* TICKET AREA */
        .ticket-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
            border: 1px solid #e5e7eb;
        }

        .ticket-table-container {
            flex: 1;
            overflow-y: auto;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .ticket-table th {
            text-align: left;
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: bold;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .ticket-table td {
            padding: 6px;
            border-bottom: 1px solid #f3f4f6;
        }

        .ref-input {
            width: 120px;
            padding: 4px;
            border: 1px solid #d1d5db;
            outline: none;
            font-size: 11px;
        }

        /* TICKET FOOTER */
        .ticket-footer {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background: #f8f9fa;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            font-weight: bold;
            color: #4b5563;
        }

        /* RIGHT ACTIONS */
        .pos-right {
            width: 120px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .grand-total {
            background: white;
            border: 1px solid black;
            text-align: right;
            padding: 15px 10px;
            font-family: 'Courier New', Courier, monospace;
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
        
        .align-right { text-align: right; }
    </style>
</head>
<body>

<div class="pos-container">

    <!-- HEADER -->
    <div class="pos-header">
        <h1 class="header-title">***BIENVENUE CHEZ VELARO***</h1>
        
        <div class="header-fields">
            <div class="field-group">
                <label>Ticket N°</label>
                <input type="text" style="width: 80px;" disabled>
            </div>
            <div class="field-group">
                <label>Date</label>
                <span style="padding-left: 5px;">{{ date('d/m/Y') }}</span>
            </div>
            
            <div style="width: 20px;"></div> <!-- Spacer -->

            <div class="field-group">
                <label>Client</label>
                <input type="text" id="clientCode" value="{{ $client ? $client->clientid : '4110001' }}" style="width: 80px;" readonly>
                <button class="btn-lookup">...</button>
                <div style="font-weight: bold; margin-left: 5px; width: 100px; text-align: center;">{{ $client ? $client->nom : 'PASSAGER' }}</div>
            </div>

            <div style="width: 20px;"></div> <!-- Spacer -->

            <div class="field-group">
                <label>Vendeur</label>
                <input type="text" style="width: 100px;" readonly>
                <button class="btn-lookup">...</button>
            </div>
        </div>

        <div class="header-stats">
            <div>Demande Transf: 0</div>
            <div>Bon Transf: 0</div>
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
                            <th style="width: 160px;">Réf</th>
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
                        <!-- Input Row -->
                        <tr id="inputRow">
                            <td>
                                <div style="display: flex;">
                                    <input type="text" id="scanInput" class="ref-input" autofocus>
                                    <button class="btn-lookup">...</button>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
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
            <button class="grid-btn">Recherche</button>
            <button class="grid-btn">Création</button>
            <button class="grid-btn">Fiche</button>
            <button class="grid-btn">Historique d'Achat</button>
        </div>

        <!-- COL 2: Articles -->
        <div class="grid-col">
            <div class="grid-header">Articles</div>
            <button class="grid-btn">Recherche</button>
            <button class="grid-btn">Stock</button>
            <button class="grid-btn">Modification</button>
            <button class="grid-btn">Imprimer Ticket</button>
        </div>

        <!-- COL 3: Services -->
        <div class="grid-col">
            <div class="grid-header">Services</div>
            <button class="grid-btn">Mise en attente du ticket</button>
            <button class="grid-btn">Reprise d'un ticket en attente</button>
            <button class="grid-btn">Consultation Tickets</button>
            <button class="grid-btn">Réimprimer Ticket</button>
        </div>

        <!-- COL 4: Règlements -->
        <div class="grid-col">
            <div class="grid-header">Règlements</div>
            <div class="split-col">
                <button class="grid-btn" style="font-weight: bold;">Espèce</button>
                <button class="grid-btn">Cheque</button>
            </div>
            <div class="split-col">
                <button class="grid-btn">C.B</button>
                <button class="grid-btn">Chq.Cad</button>
            </div>
            <div class="split-col">
                <button class="grid-btn">Retour</button>
                <button class="grid-btn">Retour2</button>
            </div>
            <div class="split-col">
                <button class="grid-btn">Crédit</button>
                <button class="grid-btn">C.Acompte</button>
            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT FOR LOGIC -->
<script>
    let ticketLines = [];

    // Simulate adding an item when pressing enter on the ref input
    document.getElementById('scanInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            let ref = this.value.trim();
            // In a real app, this would be an AJAX call to get product info.
            // We simulate a product fetch here:
            let mockProduct = {
                ref: ref,
                designation: 'PRODUIT ' + ref,
                qte: 1,
                prix: 15.500,
                remise: 0,
                prixNet: 15.500,
                total: 15.500
            };
            
            // Check if product already exists to increment Qty
            let existingLine = ticketLines.find(l => l.ref === ref);
            if (existingLine) {
                existingLine.qte += 1;
                existingLine.total = existingLine.qte * existingLine.prixNet;
            } else {
                ticketLines.push(mockProduct);
            }
            
            this.value = '';
            renderTable();
        }
    });

    function renderTable() {
        // Remove existing rows except the input row
        let tbody = document.getElementById('ticketBody');
        let inputRow = document.getElementById('inputRow');
        tbody.innerHTML = '';
        
        let totalQte = 0;
        let grandTotal = 0;

        ticketLines.forEach((line, index) => {
            totalQte += line.qte;
            grandTotal += line.total;

            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${line.ref}</td>
                <td>${line.designation}</td>
                <td class="align-right">
                    <input type="number" value="${line.qte}" style="width: 50px; text-align: right;" onchange="updateQty(${index}, this.value)">
                </td>
                <td class="align-right">${line.prix.toFixed(3)}</td>
                <td class="align-right">${line.remise}</td>
                <td class="align-right">${line.prixNet.toFixed(3)}</td>
                <td class="align-right">${line.total.toFixed(3)}</td>
                <td style="text-align: center;">
                    <button onclick="removeLine(${index})" style="background: none; border: none; cursor: pointer; color: red;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Always append the input row at the bottom
        tbody.appendChild(inputRow);
        
        // Update stats
        document.getElementById('lblNbLignes').innerText = ticketLines.length;
        document.getElementById('lblTotalQte').innerText = totalQte;
        
        let formattedTotal = grandTotal.toFixed(3);
        document.getElementById('grandTotal').innerText = formattedTotal;
        document.getElementById('lblRestePayer').innerText = formattedTotal;
        
        // Re-focus input
        document.getElementById('scanInput').focus();
    }

    function updateQty(index, newQty) {
        let qte = parseFloat(newQty);
        if (qte > 0) {
            ticketLines[index].qte = qte;
            ticketLines[index].total = ticketLines[index].qte * ticketLines[index].prixNet;
        }
        renderTable();
    }

    function removeLine(index) {
        ticketLines.splice(index, 1);
        renderTable();
    }

    function validerTicket() {
        if (ticketLines.length === 0) {
            alert('Le ticket est vide !');
            return;
        }
        alert('Ticket validé avec succès ! (Simulation)');
        annulerTicket(); // Reset for next customer
    }

    function annulerTicket() {
        ticketLines = [];
        renderTable();
    }
</script>

</body>
</html>
