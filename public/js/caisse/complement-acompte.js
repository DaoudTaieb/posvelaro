/*
 * Golden Pos - caisse : complement-acompte
 * Extrait de resources/views/caisse/scripts/complement-acompte.blade.php (script classique, non module :
 * fonctions et variables globales partagées avec les autres fichiers et les attributs onclick).
 * Valeurs serveur (routes, jeton CSRF, client et vendeur par défaut) : objet GP défini par
 * resources/views/caisse/partials/config-js.blade.php.
 */
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
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${escHtml(t.cticketnumero || '')}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${escHtml(date)}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${escHtml(t.client_nom || '')}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${escHtml(t.caissier_nom || '')}</td>
                <td style="padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px;">${escHtml(t.vendeur_nom || '')}</td>
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

    async function selectComplementTicket(cticketid, numero, reste, clientid) {
        if (ticketLines.length > 0) {
            if (!(await confirmer("Le panier n'est pas vide. Voulez-vous le remplacer par ce ticket ?", { okLabel: 'Remplacer' }))) {
                return;
            }
        }

        // Récupérer le montant personnalisé si modifié
        let montantInput = document.getElementById('compMontant_' + cticketid);
        let montantCustom = montantInput ? parseFloat(montantInput.value) : reste;
        if (isNaN(montantCustom) || montantCustom <= 0) montantCustom = reste;
        if (montantCustom > reste) montantCustom = reste;

        // Charger les détails du ticket dans le panier
        fetch(`${GP.urls['/vente/caisse/ticket-details']}/${cticketid}`)
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
                            prixNet: l.puttcnet != null ? parseFloat(l.puttcnet) : parseFloat(l.prix) * (1 - (parseFloat(l.remise) || 0) / 100),
                            total: parseFloat(l.totalttcnet) || 0,
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
                    notifier("Erreur: " + (res.message || 'Ticket introuvable'));
                }
            })
            .catch(err => {
                console.error(err);
                notifier('Erreur réseau lors du chargement du ticket.');
            });
    }

    function updateComplementDisplay() {
        if (!complementMode) return;
        let reste = complementReste;
        const regle = reste <= 0.0005;
        const couleur = regle ? '#16a34a' : '#dc2626';

        // Afficher le reste dans le grandTotal
        document.getElementById('grandTotal').innerText = reste.toFixed(3);
        document.getElementById('grandTotal').style.color = couleur;

        // Mettre à jour le footer
        let totalTTC = 0;
        ticketLines.forEach(l => totalTTC += parseFloat(l.total) || 0);
        let acompte = totalTTC - reste;
        document.getElementById('lblAcompte').innerText = acompte.toFixed(3);
        document.getElementById('lblRestePayer').innerText = reste.toFixed(3);
        document.getElementById('lblRestePayer').style.color = couleur;

        // Bandeau : ticket réglé (consultation) ou complément à encaisser
        document.getElementById('display-qte-prix').innerText = regle
            ? `TICKET N° ${complementNumero} - RÉGLÉ`
            : `COMPLEMENT TICKET N° ${complementNumero}`;
        document.getElementById('display-qte-prix').style.color = couleur;
        document.getElementById('display-remise').innerText = '';
        document.getElementById('display-total').innerText = regle
            ? `TOTAL: ${totalTTC.toFixed(3)}`
            : `RESTE: ${reste.toFixed(3)}`;
        document.getElementById('display-total').style.color = couleur;

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
        currentClientId = GP.clientParDefautId;
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
                notifier(data.message || "Erreur lors de l'enregistrement.");
            }
        })
        .catch(err => {
            console.error(err);
            notifier('Erreur réseau.');
        });
    }
