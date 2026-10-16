/*
 * Golden Pos - caisse : tickets-paiements
 * Extrait de resources/views/caisse/scripts/tickets-paiements.blade.php (script classique, non module :
 * fonctions et variables globales partagées avec les autres fichiers et les attributs onclick).
 * Valeurs serveur (routes, jeton CSRF, client et vendeur par défaut) : objet GP défini par
 * resources/views/caisse/partials/config-js.blade.php.
 */
    let allConsultationTickets = [];
    
    function openConsultationModal() {
        document.getElementById('consultationModal').style.display = 'flex';
        fetchConsultationData();
    }
    
    function closeConsultationModal() {
        document.getElementById('consultationModal').style.display = 'none';
    }
    
    function fetchConsultationData() {
        fetch(`${GP.routes['vente.caisse.journal_data']}`)
            .then(res => res.json())
            .then(data => {
                if(data.tickets) {
                    allConsultationTickets = data.tickets;
                    
                    // Populate Client Dropdown
                    let clientOpts = '<option value="Tous"></option>';
                    if(data.clients) {
                        data.clients.forEach(c => { clientOpts += `<option value="${escHtml(c.clientid)}">${escHtml(c.nom)}</option>`; });
                    }
                    document.getElementById('consultationClientSelect').innerHTML = clientOpts;
                    
                    // Populate Vendeur Dropdown
                    let vendeurOpts = '<option value="Tous">Tous</option>';
                    if(data.vendeurs) {
                        data.vendeurs.forEach(v => { vendeurOpts += `<option value="${escHtml(v.employeeid)}">${escHtml(v.nom)}</option>`; });
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
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${escHtml(cnom)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${escHtml(ctel)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.totalttc).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.acompte).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee; text-align: right;">${parseFloat(t.netapayer).toFixed(3)}</td>
                <td style="padding: 6px 8px; border-right: 1px solid #eee;">${escHtml(cainom)}</td>
                <td style="padding: 6px 8px;">${escHtml(vnom)}</td>
            `;
            tr.onclick = () => { loadTicketIntoPos(t); };
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

    // Ouverture d'un ticket depuis le journal des ventes : il se charge dans la caisse avec son état
    // réel (acompte déjà versé, reste à payer, numéro). Un ticket est un document déjà enregistré :
    // il n'est jamais re-validé comme une nouvelle vente. S'il reste un montant à payer, l'encaissement
    // se fait en complément d'acompte sur ce ticket.
    function loadTicketIntoPos(ticket) {
        const reste = Math.max(0, parseFloat(ticket.netapayer) || 0);
        closeConsultationModal();
        selectComplementTicket(ticket.cticketid, ticket.cticketnumero, reste, ticket.clientid);
    }

    // Ticket déjà réglé chargé dans la caisse : plus aucun encaissement possible
    function ticketDejaRegle() {
        if (complementMode && complementReste <= 0.0005) {
            notifier(`Le ticket N° ${complementNumero} est déjà réglé : aucun encaissement possible.`, 'info');
            return true;
        }
        return false;
    }

    function validerTicket() {
        openMultiPaymentModal(1); // Default pre-fill Espèce
    }

    let mpTotalDu = 0;

    function openCreditFlow() {
        if (ticketDejaRegle()) return;
        if (ticketLines.length === 0 && !complementMode) {
            notifier("Le ticket est vide.");
            return;
        }
        let clientName = document.getElementById('clientName').innerText;
        if (clientName === 'PASSAGER' || !currentClientId || currentClientId == 1) {
            notifier("Veuillez d'abord sélectionner un client pour effectuer une vente à crédit.");
            openClientModal();
        } else {
            let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
            let payload = {
                vendeurid: currentVendeurId,
                clientid: currentClientId,
                lignes: ticketLines,
                totalttc: total,
                acompte: 0,
                netapayer: total,
                reglements: []
            };

            if (complementMode) {
                notifier("Impossible de passer un crédit total sur un complément d'acompte.");
                return;
            } else {
                window.submitTicketToBackend(payload, false);
            }
        }
    }

    function openMultiPaymentModal(defaultModeId = null) {
        if (ticketDejaRegle()) return;
        if (ticketLines.length === 0 && !complementMode) {
            notifier("Le ticket est vide.");
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

                if ([2, 4, 9, 10].includes(row.modeId)) {
                    detailsHTML = `
                        <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${escHtml(row.numero)}" onchange="updateMpRow(${index}, 'numero', this.value)" style="width: 100%; border: 1px solid #eee; padding: 4px; box-sizing: border-box; outline: none;" placeholder="Numéro"></td>
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
        let sumBonAchat = mpReglementsArray.filter(r => r.modeId === 10).reduce((sum, r) => sum + r.montant, 0);
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
        if (ticketDejaRegle()) return;
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
            notifier("Le montant doit être supérieur à 0");
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
            date: r.date || '',
            proprietaire: r.proprietaire || '',
            cavoirid: r.cavoirid || null
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
        baChargerBonsClient();
        setTimeout(() => document.getElementById('baCodeInput').focus(), 50);
    }

    function closeBonAchatModal() {
        document.getElementById('bonAchatModal').style.display = 'none';
    }

    // Bons d'achat fidélité disponibles pour le client du ticket (cliquer pour l'ajouter)
    function baChargerBonsClient() {
        const cont = document.getElementById('baBonsClient');
        const passagerId = GP.clientPassagerId;
        if (!currentClientId || parseInt(currentClientId) === passagerId) {
            cont.innerHTML = '<span style="color:#666;">Sélectionnez un client fidélité pour voir ses bons disponibles, ou scannez le bon.</span>';
            return;
        }
        cont.innerHTML = '<span style="color:#666;">Chargement des bons du client...</span>';
        fetch(`${GP.urls['vente/caisse/pos/bons-client']}/${currentClientId}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success || !d.bons || d.bons.length === 0) {
                    cont.innerHTML = '<span style="color:#666;">Aucun bon d\'achat disponible pour ce client.</span>';
                    return;
                }
                let html = `<span style="font-weight:bold;">Bons disponibles (${parseFloat(d.solde).toFixed(3)} DT) :</span>`;
                d.bons.forEach(b => {
                    html += `<button type="button" onclick="baAjouterBon('${escHtml(b.numero)}')" title="Valable jusqu'au ${escHtml(b.date_expiration)}"
                        style="padding: 4px 10px; border: 1px solid #0284c7; color: #0369a1; background: #e0f2fe; border-radius: 12px; cursor: pointer; font-size: 12px;">
                        ${escHtml(b.numero)} · ${parseFloat(b.montant).toFixed(3)} DT</button>`;
                });
                cont.innerHTML = html;
            })
            .catch(() => { cont.innerHTML = '<span style="color:#b91c1c;">Impossible de charger les bons du client.</span>'; });
    }

    function baAddLine() {
        let code = document.getElementById('baCodeInput').value.trim();
        if (code === '') {
            // Saisie manuelle d'un bon externe (numéro + montant)
            baAdding = true;
            baDraft = { numero: '', montant: '', remise: '' };
            renderBaTable();
            return;
        }
        baAjouterBon(code);
    }

    // Vérifie le bon côté serveur (existence, validité, client) et l'ajoute avec son montant
    function baAjouterBon(code) {
        code = String(code || '').trim();
        if (baLignes.some(l => String(l.numero).toUpperCase() === code.toUpperCase())) {
            notifier('Ce bon d\'achat est déjà ajouté.', 'warning');
            return;
        }
        const passagerId = GP.clientPassagerId;
        const params = new URLSearchParams({ code: code });
        if (currentClientId && parseInt(currentClientId) !== passagerId) params.append('clientid', currentClientId);

        fetch(`${GP.routes['vente.caisse.pos.check_bon_achat']}?${params.toString()}`)
            .then(r => r.json())
            .then(d => {
                document.getElementById('baCodeInput').value = '';
                if (!d.success) {
                    notifier(d.message || 'Bon d\'achat invalide.', 'error');
                    return;
                }
                const dejaSaisi = baLignes.reduce((s, l) => s + (parseFloat(l.montant) || 0), 0);
                const restant = Math.max(0, mpTotalDu - dejaSaisi);
                if (restant <= 0.0005) {
                    notifier('Le net à payer est déjà couvert.', 'warning');
                    return;
                }
                const montant = Math.min(parseFloat(d.bon.montant), restant);
                baLignes.push({ numero: d.bon.numero, montant: parseFloat(montant.toFixed(3)), remise: 0 });
                if (!document.getElementById('baNom').value && d.bon.client_nom) document.getElementById('baNom').value = d.bon.client_nom;
                if (!document.getElementById('baTelephone').value && d.bon.client_tel) document.getElementById('baTelephone').value = d.bon.client_tel;
                if (montant + 0.0005 < parseFloat(d.bon.montant)) {
                    notifier(`Bon ${d.bon.numero} : ${parseFloat(d.bon.montant).toFixed(3)} DT disponibles, ${montant.toFixed(3)} DT utilisés (le bon sera soldé).`, 'info', 8000);
                }
                renderBaTable();
            })
            .catch(() => notifier('Erreur réseau lors de la vérification du bon.', 'error'));
    }

    function baSaveDraft() {
        let num = document.getElementById('baDraftNum').value.trim();
        let mnt = parseFloat(document.getElementById('baDraftMnt').value);
        let rem = parseFloat(document.getElementById('baDraftRem').value) || 0;
        if (!num || isNaN(mnt) || mnt <= 0) {
            notifier("Veuillez saisir un numéro et un montant valide.");
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
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${escHtml(l.numero)}" onchange="baUpdate(${index}, 'numero', this.value)" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;"></td>
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
                    <input type="text" id="baDraftNum" value="${escHtml(baDraft.numero)}" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;">
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
            notifier("Nom et Prénom sont obligatoires.");
            return;
        }

        let totalBA = baLignes.reduce((sum, l) => sum + l.montant, 0);
        if (totalBA > 0) {
            baLignes.forEach(l => {
                if(l.montant > 0) {
                    let today = new Date().toISOString().split('T')[0];
                    mpReglementsArray.push({
                        id: Date.now() + Math.random(),
                        modeId: 10, // 10 = Bon d'achat
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
            notifier("Veuillez saisir ou scanner un code à barre.");
            return;
        }

        fetch(`/vente/caisse/pos/check-avoir?code=${encodeURIComponent(barcode)}`)
            .then(res => res.json())
            .then(async data => {
                if (data.success) {
                    let av = data.avoir;

                    // Warn if avoir client doesn't match current client (except if current is PASSAGER)
                    if (av.clientid && av.clientid != currentClientId && currentClientId != 1) {
                        if (!(await confirmer(`Cet avoir appartient à ${av.client_name}. Voulez-vous quand même l'utiliser ?`, { titre: 'Avoir', okLabel: 'Utiliser' }))) {
                            return;
                        }
                    }

                    let paye = mpReglementsArray.reduce((sum, r) => sum + r.montant, 0);
                    let reste = mpTotalDu - paye;

                    if (reste <= 0) {
                        notifier("Le ticket est déjà entièrement réglé.");
                        return;
                    }

                    let montantAUtiliser = Math.min(av.montant, reste);
                    let today = new Date().toISOString().split('T')[0];

                    mpReglementsArray.push({
                        id: Date.now() + Math.random(),
                        modeId: 9,
                        mode: "Avoir",
                        cavoirid: av.cavoirid,
                        montant: montantAUtiliser,
                        numero: av.numerointerne || av.cavoirnumero || barcode,
                        date: today,
                        banque: ''
                    });

                    renderMpTable();
                    closeAvoirModal();
                } else {
                    notifier(data.message || "Avoir invalide ou introuvable.");
                }
            })
            .catch(err => {
                console.error(err);
                notifier("Erreur lors de la vérification de l'avoir.");
            });
    }

    function mpSubmitValider(isAcompte) {
        let reglements = mpGetReglements();
        let paye = reglements.reduce((sum, r) => sum + r.montant, 0);
        let reste = mpTotalDu - paye;

        if (paye <= 0) {
            notifier("Veuillez saisir au moins un montant.");
            return;
        }

        if (!isAcompte && reste > 0.005) {
            notifier("Le total n'est pas réglé. Cliquez sur 'Valider Acompte' si vous souhaitez valider un paiement partiel.");
            return;
        }

        if (paye > mpTotalDu + 0.005) {
            notifier("Le montant reçu ne peut pas dépasser le total (pas de rendu automatique pour les paiements multiples).");
            return;
        }

        if (complementMode) {
            // Send array of reglements
            fetch('/vente/caisse/store-complement-acompte', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': GP.csrf
                },
                body: JSON.stringify({ 
                    cticketid: complementTicketId, 
                    reglements: reglements 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    notifier('Complément enregistré avec succès!');
                    closeMultiPaymentModal();
                    exitComplementMode();
                    document.getElementById('complementAcompteModal').style.display = 'none';
                } else {
                    notifier('Erreur: ' + (data.message || ''));
                }
            })
            .catch(err => {
                console.error(err);
                notifier("Erreur de communication.");
            });
            return;
        }

        // Standard Ticket Mode
        let payload = {
            vendeurid: currentVendeurId,
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
        fetch(`${GP.routes['vente.caisse.en_attente']}`)
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
                                <td style="padding: 8px;">${escHtml(t.cticketnumero || t.cticketid)}</td>
                                <td style="padding: 8px;">${escHtml(t.client_nom || 'PASSAGER')}</td>
                                <td style="padding: 8px;">${t.datecreation}</td>
                                <td style="padding: 8px; text-align: right; font-weight: bold;">${parseFloat(t.totalttc).toFixed(3)}</td>
                                <td style="padding: 8px; text-align: center;">
                                    ${String(t.cticketid) === String(currentRepriseId)
                                        ? '<span style="color:#0369a1; font-weight:bold;">En cours dans la caisse</span>'
                                        : `<button onclick="loadReprise(${t.cticketid})" style="padding: 4px 10px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Reprendre</button>`}
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

    async function loadReprise(id) {
        if (ticketLines.length > 0) {
            if (!(await confirmer("Attention, votre ticket en cours n'est pas vide. Voulez-vous vraiment le remplacer par le ticket en attente ?", { okLabel: 'Remplacer' }))) {
                return;
            }
        }

        fetch(`${GP.urls['vente/caisse/reprise']}/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': GP.csrf, 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    setRepriseId(res.cticketid || id);
                    currentClientId = res.clientid;
                    ticketLines = res.lignes;
                    
                    // Récupérer les infos du client si ce n'est pas le passager
                    if (currentClientId && currentClientId != 1) {
                        fetch(`${GP.urls['vente/caisse/pos/client']}/${currentClientId}`)
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
                    notifier("Erreur: " + res.message);
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
        if (ticketExistantVerrouille()) return;
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
        fetch(`${GP.urls['/vente/caisse/ticket-details']}/${encodeURIComponent(num)}?par=numero`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.ticket) {
                    ticketRetourLines = res.lines;
                    renderRetourTbody();
                } else {
                    notifier("Ticket introuvable.");
                }
            })
            .catch(err => notifier("Erreur de récupération du ticket."));
    }

    function renderRetourTbody() {
        let tbody = document.getElementById('retourTbody');
        tbody.innerHTML = '';
        ticketRetourLines.forEach((l, index) => {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left;">${escHtml(l.article_ref)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left;">${escHtml(l.article_designation)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">${parseFloat(l.qte)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">${parseFloat(l.qte_retournee || 0)}</td> <!-- Qte Récupérée -->
                <td style="padding: 8px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd;">
                    <input type="number" step="1" max="${parseFloat(l.qte) - parseFloat(l.qte_retournee || 0)}" min="0" value="0" style="width: 60px; text-align: center; border: 1px solid #ccc; font-weight: bold;" onchange="updateRetourLine(${index}, this.value)" onkeyup="updateRetourLine(${index}, this.value)">
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
        const restant = parseFloat(line.qte) - parseFloat(line.qte_retournee || 0);
        if (qte > restant) {
            notifier(restant > 0
                ? `La quantité à retourner ne peut pas dépasser la quantité restante (${restant}).`
                : "Cet article a déjà été entièrement retourné.");
            qte = restant;
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
                    produitid: l.produitid,
                    produit2id: l.produit2id,
                    detventeid: l.detcticketid,
                    code: l.article_ref,
                    reference: l.article_ref,
                    designation: l.article_designation,
                    qte: -Math.abs(l.qte_retour),
                    prix: l.prix,
                    remise: l.remise,
                    prixNet: l.puttcnet != null ? parseFloat(l.puttcnet) : parseFloat(l.prix) * (1 - (parseFloat(l.remise)/100)),
                    total: -(l.qte_retour * (l.puttcnet != null ? parseFloat(l.puttcnet) : parseFloat(l.prix) * (1 - (parseFloat(l.remise)/100)))),
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
            notifier("Veuillez saisir au moins une quantité à retourner pour valider.");
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
        
        fetch(`${GP.routes['vente.caisse.mouvements']}?du=${du}&au=${au}&clientid=${clientid}`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    mouvementsData = res.mouvements;
                    renderMouvementsTable(mouvementsData);
                } else {
                    notifier("Erreur de chargement des mouvements.");
                }
            })
            .catch(err => notifier("Erreur serveur."));
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
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml(m.cticketnumero || '')}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml((m.date || '').substring(0,10))}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml(m.reference || '')}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml(m.designation || '')}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml(m.taille || '')}</td>
                <td style="padding: 8px; border: 1px solid #ddd;">${escHtml(m.couleur || '')}</td>
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
            notifier("Veuillez sélectionner au moins un mouvement.");
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

