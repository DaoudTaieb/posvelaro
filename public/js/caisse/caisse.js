/*
 * Golden Pos - caisse : caisse
 * Extrait de resources/views/caisse/scripts/caisse.blade.php (script classique, non module :
 * fonctions et variables globales partagées avec les autres fichiers et les attributs onclick).
 * Valeurs serveur (routes, jeton CSRF, client et vendeur par défaut) : objet GP défini par
 * resources/views/caisse/partials/config-js.blade.php.
 */
    console.log("=== GOLDEN POS POS SCRIPT START ===");

    // Échappe les données (noms clients, articles...) avant insertion via innerHTML
    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    }

    // Global functions definition at the very top
    function sendSmsDirectly(tel, msg, ctx) {
        let formData = new FormData();
        formData.append('telephone', tel);
        formData.append('message', msg);
        if (ctx && ctx.clientid) formData.append('clientid', ctx.clientid);
        if (ctx && ctx.cticketid) formData.append('cticketid', ctx.cticketid);
        formData.append('_token', GP.csrf);

        fetch(GP.routes['vente.caisse.pos.send_sms'], {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notifier('SMS envoyé avec succès !');
            } else {
                notifier('Erreur lors de l\'envoi du SMS: ' + (data.message || ''));
            }
        })
        .catch(error => {
            console.error(error);
            notifier('Erreur réseau lors de l\'envoi du SMS.');
        });
    }

    // Ticket en attente repris dans la caisse : supprimé côté serveur seulement à la validation
    let currentRepriseId = null;
    try { currentRepriseId = localStorage.getItem('pos_repriseId') || null; } catch (e) {}
    function setRepriseId(id) {
        currentRepriseId = id ? String(id) : null;
        try {
            if (currentRepriseId) localStorage.setItem('pos_repriseId', currentRepriseId);
            else localStorage.removeItem('pos_repriseId');
        } catch (e) {}
    }

    window.submitTicketToBackend = function(data) {
        if (currentRepriseId && !data.reprise_cticketid) {
            data.reprise_cticketid = currentRepriseId;
        }
        fetch(`${GP.routes['vente.caisse.store']}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': GP.csrf
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                setRepriseId(null); // le ticket en attente repris a été remplacé par ce ticket
                if (typeof closePaymentModal === 'function') closePaymentModal();
                if (typeof closeMultiPaymentModal === 'function') closeMultiPaymentModal();
                notifier(res.message);
                
                // Informations fidélité (bon d'achat gagné, carte expirée, bons annulés sur retour)
                (res.fidelite_messages || []).forEach(m => notifier(m, 'info', 9000));

                // Proposer l'envoi du SMS (texte préparé par le serveur : bienvenue à la 1ère vente, puis fidélité)
                if (res.client_tel && res.sms_message) {
                    if (res.sms_disponible === false) {
                        notifier("SMS non envoyé : le service Bip SMS n'est pas configuré (Paramètres > Général).", 'warning', 8000);
                    } else {
                        confirmer(`Voulez-vous envoyer un SMS de confirmation au client (${res.client_tel}) ?`, { titre: 'SMS client', okLabel: 'Envoyer' })
                            .then(ok => { if (ok) sendSmsDirectly(res.client_tel, res.sms_message, { clientid: res.clientid, cticketid: res.cticketid }); });
                    }
                }

                // Vider le panier AVANT d'ouvrir le reçu : le ticket est enregistré,
                // il ne doit plus pouvoir être re-validé même si l'ouverture du reçu échoue.
                ticketLines = [];
                currentClientId = GP.clientParDefautId;
                document.getElementById('clientName').innerText = 'PASSAGER';
                document.getElementById('clientSoldeInfo').style.display = 'none';
                document.getElementById('clientCode').value = '4110001';
                
                // Refresh and increment the ticket number automatically
                if (res.cticketnumero) {
                    document.getElementById('ticketNumber').value = parseInt(res.cticketnumero) + 1;
                }
                
                // Keep the same currentVendeurId (vendor persists across tickets)
                
                renderTable();

                // Impression thermique directe faite par le serveur : pas de fenêtre d'impression.
                // Mode navigateur, ou échec de l'imprimante : on ouvre le reçu comme avant.
                const imprimeDirect = res.impression && res.impression.ok;
                if (res.impression && !res.impression.ok && res.impression.mode !== 'navigateur') {
                    notifier(res.impression.message + ' Le reçu s\'ouvre dans le navigateur.', 'warning', 8000);
                }
                if (res.print_url && !imprimeDirect) {
                    const recu = window.open(res.print_url, '_blank');
                    if (recu) {
                        recu.focus();
                    } else {
                        notifier("Le reçu n'a pas pu s'ouvrir : autorisez les fenêtres pop-up pour ce site afin d'imprimer les tickets.", 'warning', 8000);
                    }
                }
                
                fetch(`${GP.routes['vente.caisse.en_attente']}`)
                    .then(r => r.json())
                    .then(r => { console.log('En attente count:', r.count); })
                    .catch(e => console.error(e));
            } else {
                notifier("Erreur : " + res.message);
            }
        })
        .catch(err => {
            console.error(err);
            notifier("Une erreur est survenue lors de l'enregistrement.");
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
                notifier("Le ticket est vide.");
                return;
            }
            if (typeof complementMode !== 'undefined' && complementMode) {
                notifier("Impossible de mettre en attente un complément d'acompte.");
                return;
            }
            pendingAction = 'miseEnAttente';
            openVendeurModal();
        } catch (e) {
            console.error("Error in miseEnAttente:", e);
            notifier("Erreur lors de l'ouverture du popup: " + e.message);
        }
    };

    let ticketLines = [];
    let currentVariants = []; // Store variants to allow filtering
    let currentClientLoyaltyTier = 0;

    // Simulate adding an item when pressing enter on the ref input
    document.getElementById('scanInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim() !== '') {
            let ref = this.value.trim();
            
            fetch(`${GP.routes['vente.caisse.pos.scan_product']}?code=${encodeURIComponent(ref)}`)
                .then(response => response.json())
                .then(product => {
                    if (product && product.choisir_variante) {
                        // Code commun à plusieurs tailles/couleurs : choisir la variante
                        openVariantsModal(product);
                    } else if(product) {
                        addProductToTicket(product);
                    } else {
                        notifier('Produit non trouvé');
                    }
                    this.value = '';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    notifier('Erreur lors de la recherche du produit');
                });
        }
    });

    let isRetourMode = false;

    // Un ticket existant chargé depuis le journal (réglé ou en complément) ne se modifie pas :
    // seules ses lignes enregistrées font foi. Pour vendre, il faut d'abord l'annuler (poubelle).
    function ticketExistantVerrouille() {
        if (typeof complementMode !== 'undefined' && complementMode) {
            notifier(`Le ticket N° ${complementNumero} est un ticket existant : il ne peut pas être modifié. Annulez-le (poubelle) pour commencer une nouvelle vente.`, 'warning');
            return true;
        }
        return false;
    }

    function addProductToTicket(productData) {
        if (ticketExistantVerrouille()) return;
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
        
        // Même article déjà dans le panier : on cumule seulement une ligne de même sens (vente avec vente,
        // retour avec retour), non liée à un ticket d'origine et au même prix net.
        // Sinon une vente scannée annulerait une ligne de retour (quantité 0 = article gratuit).
        let existingLine = ticketLines.find(l =>
            String(l.produit2id) === String(product.produit2id)
            && !l.detventeid
            && Math.sign(parseFloat(l.qte)) === Math.sign(qteVal)
            && Math.abs(parseFloat(l.prixNet) - prixNet) < 0.0005);
        if (existingLine) {
            existingLine.qte += qteVal;
            existingLine.total = existingLine.qte * existingLine.prixNet;
        } else {
            ticketLines.push(product);
        }

        // Alerte stock : quantité vendue dans le ticket supérieure au stock du site
        if (qteVal > 0 && productData.total_stock !== undefined && productData.total_stock !== null) {
            const dejaVendu = ticketLines
                .filter(l => String(l.produit2id) === String(product.produit2id) && parseFloat(l.qte) > 0)
                .reduce((s, l) => s + parseFloat(l.qte), 0);
            const stockSite = parseFloat(productData.total_stock) || 0;
            if (dejaVendu > stockSite) {
                notifier(`Stock insuffisant pour ${product.ref || ''} ${product.taille || ''} ${product.couleur || ''} : ${stockSite} en stock, ${dejaVendu} dans le ticket. Le stock deviendra négatif.`, 'warning', 7000);
            }
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
            document.getElementById('display-qte-prix').innerText = '***BIENVENUE CHEZ ' + GP.siteNom + '***';
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
                <td>${escHtml(line.reference || line.ref || '')}</td>
                <td>${escHtml(line.designation || '')} ${escHtml(line.taille ? ' - ' + line.taille : '')} ${escHtml(line.couleur ? ' - ' + line.couleur : '')}</td>
                <td class="align-right">
                    <input type="number" value="${qte}" class="editable-cell" onchange="updateQty(${index}, this.value)" onfocus="this.select(); selectLine(${index});">
                </td>
                <td class="align-right">${prix.toFixed(3)}</td>
                <td class="align-right">
                    <input type="number" value="${remise}" class="editable-cell" onchange="updateRemise(${index}, this.value)" min="0" max="100" onfocus="this.select(); selectLine(${index});">
                </td>
                <td class="align-right">${prixNet.toFixed(3)}</td>
                <td class="align-right">
                    <input type="number" value="${total.toFixed(3)}" class="editable-cell" onchange="updateLineTotal(${index}, this.value)" min="0" onfocus="this.select(); selectLine(${index});">
                </td>
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

        // Save to localStorage (panier en cours uniquement).
        // Un ticket existant chargé depuis le journal (mode complément) n'est jamais sauvegardé :
        // restauré après un rechargement, il deviendrait une nouvelle vente des mêmes articles.
        if (typeof complementMode !== 'undefined' && complementMode) {
            localStorage.removeItem('pos_ticketLines');
            localStorage.removeItem('pos_currentClientId');
            localStorage.removeItem('pos_clientName');
            localStorage.removeItem('pos_clientCode');
            return;
        }
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
        if (ticketExistantVerrouille()) { renderTable(); return; }
        let qte = parseFloat(newQty);
        if (qte > 0) {
            ticketLines[index].qte = qte;
            ticketLines[index].total = ticketLines[index].qte * ticketLines[index].prixNet;
        }
        renderTable();
    }

    function updateRemise(index, newRemise) {
        if (ticketExistantVerrouille()) { renderTable(); return; }
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

    function updateLineTotal(index, newTotal) {
        if (ticketExistantVerrouille()) { renderTable(); return; }
        let targetTotal = parseFloat(newTotal);
        if (isNaN(targetTotal) || targetTotal < 0) return;
        
        let line = ticketLines[index];
        let grossTotal = (parseFloat(line.qte) || 0) * (parseFloat(line.prix) || 0);
        
        if (grossTotal > 0) {
            let discountRatio = (grossTotal - targetTotal) / grossTotal;
            let remise = discountRatio * 100;
            
            if (remise < 0) remise = 0;
            if (remise > 100) remise = 100;
            
            line.remise = remise;
            line.prixNet = line.prix * (1 - (remise / 100));
            line.total = line.qte * line.prixNet;
        }
        renderTable();
    }

    function removeLine(index) {
        if (ticketExistantVerrouille()) { renderTable(); return; }
        ticketLines.splice(index, 1);
        renderTable();
    }

    // validerTicket moved to bottom script

    function searchTicket(ticketId) {
        if (!ticketId) return;
        console.log('Recherche du ticket N°:', ticketId);
        // TODO: Ajouter l'appel AJAX pour récupérer le ticket
        notifier('Recherche du ticket N° ' + ticketId + ' (fonctionnalité à implémenter)');
    }

    function annulerTicket() {
        if (complementMode) {
            exitComplementMode();
            return;
        }
        ticketLines = [];
        currentClientId = null;
        // Annuler une reprise : le ticket reste dans la liste « en attente » (rien n'est perdu)
        if (currentRepriseId) {
            notifier('Reprise annulée : le ticket reste dans la liste des tickets en attente.', 'info');
            setRepriseId(null);
        }
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
        if (ticketExistantVerrouille()) return;
        document.getElementById('productModal').style.display = 'flex';
        searchProducts(); // Load initially
    }

    function searchProducts(page = 1) {
        const tbody = document.getElementById('modal-tbody');
        tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 20px;">Chargement...</td></tr>';
        
        const params = new URLSearchParams({
            sousfamilleid: document.getElementById('filter-sf').value,
            familleid: document.getElementById('filter-f').value,
            saisonid: document.getElementById('filter-s').value,
            categoryid: document.getElementById('filter-c').value,
            marqueid: document.getElementById('filter-m').value,
            search: document.getElementById('filter-search').value,
            page: page
        });

        fetch(`${GP.routes['vente.caisse.pos.search_products']}?${params.toString()}`)
            .then(res => res.json())
            .then(response => {
                const data = response.data || [];
                tbody.innerHTML = '';
                if(data.length === 0) {
                    tbody.innerHTML = '<tr id="no-data-row"><td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">No data to display</td></tr>';
                    renderPagination(null);
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
                    const cellStyle = 'padding: 10px 12px; font-size: 12px; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-bottom: 1px solid #e2e8f0;';
                    tr.innerHTML = `
                        <td style="${cellStyle}">${escHtml(p.produitcode || '')}</td>
                        <td style="${cellStyle}">${escHtml(p.reference || '')}</td>
                        <td style="${cellStyle}">${escHtml(p.barcode2 || '')}</td>
                        <td style="${cellStyle} white-space: normal;">${escHtml(p.produitlibelle || '')}</td>
                        <td style="${cellStyle}">${escHtml(p.famillelibelle || '')}</td>
                        <td style="${cellStyle}">${escHtml(p.sousfamillelibelle || '')}</td>
                        <td style="${cellStyle} font-weight: bold; color: #059669; text-align: right;">${price.toFixed(3)}</td>
                        <td style="${cellStyle} text-align: center;">${Number.isInteger(stk) ? stk : stk.toFixed(2)}</td>
                        <td style="${cellStyle}">${p.fournisseur || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
                renderPagination(response);
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="11" style="text-align: center; color: red; padding: 20px;">Erreur de chargement</td></tr>';
            });
    }

    function renderPagination(response) {
        const container = document.getElementById('pagination-container');
        if (!container) return;
        container.innerHTML = '';
        if (!response || response.last_page <= 1) return;

        for (let i = 1; i <= response.last_page; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.style.padding = '5px 10px';
            btn.style.border = '1px solid #cbd5e1';
            btn.style.background = (i === response.current_page) ? '#0f172a' : '#fff';
            btn.style.color = (i === response.current_page) ? '#fff' : '#334155';
            btn.style.borderRadius = '4px';
            btn.style.cursor = 'pointer';
            if (i !== response.current_page) {
                btn.onmouseover = () => btn.style.background = '#f1f5f9';
                btn.onmouseout = () => btn.style.background = '#fff';
            }
            btn.onclick = () => searchProducts(i);
            container.appendChild(btn);
        }
    }

    // ========== LOGIQUE MODAL VARIANTES ==========
    function openVariantsModal(parentProduct) {
        document.getElementById('variantsModal').style.display = 'flex';
        
        let titlePrice = parseFloat(parentProduct.ttc_vente) || 0;
        document.getElementById('variantsModalTitle').innerText = `${parentProduct.produitlibelle || 'Produit'} Prix:${titlePrice.toFixed(3)}`;
        
        const tbody = document.getElementById('variants-tbody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Chargement des variantes...</td></tr>';
        
        fetch(`${GP.routes['vente.caisse.pos.variants']}?produitid=${parentProduct.produitid}`)
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
                <td style="padding: 6px; color: #111827;">${escHtml(v.produitcode || '')}</td>
                <td style="padding: 6px; color: #111827;">${escHtml(v.couleurlibelle || '')}</td>
                <td style="padding: 6px; color: #111827;">${escHtml(v.taillelibelle || '')}</td>
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
    let currentClientId = GP.clientParDefautId;
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

        fetch(`${GP.routes['vente.caisse.pos.search_clients']}?${params.toString()}`)
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
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${escHtml(c.clientcode || '')}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">${escHtml(c.nom || '')}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${escHtml(c.tel || '')}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${escHtml(c.adressefacturation || '')}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${escHtml(c.mf || '')}</td>
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
            notifier("Raison et Téléphone sont obligatoires !");
            return;
        }

        fetch(`${GP.routes['vente.caisse.pos.store_client']}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': GP.csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ raison: raison, telephone: tel })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                selectClient(res.client);
            } else {
                notifier("Erreur: " + res.message);
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
        
        fetch(GP.routes['vente.caisse.pos.store_client'], {
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
                notifier(data.message || 'Erreur lors de la création');
            }
        })
        .catch(err => {
            console.error(err);
            notifier('Erreur serveur');
        });
    }

    function openEditClientModal() {
        if (!currentClientId) {
            notifier('Veuillez d\'abord sélectionner un client.');
            return;
        }

        document.getElementById('ec_error').style.display = 'none';

        // Fetch current client data
        fetch(`${GP.urls['vente/caisse/pos/client']}/${currentClientId}`)
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
                    notifier('Client introuvable.');
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
        
        fetch(`${GP.urls['vente/caisse/pos/client']}/${currentClientId}`, {
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
                notifier(data.message || 'Erreur lors de la modification');
            }
        })
        .catch(err => {
            console.error(err);
            notifier('Erreur serveur');
        });
    }

    function openClientHistoryModal() {
        if (!currentClientId) {
            notifier('Veuillez d\'abord sélectionner un client.');
            return;
        }

        let displayName = document.getElementById('displayClientName') ? document.getElementById('displayClientName').textContent : '';
        document.getElementById('clientHistoryModalTitle').textContent = 'Historique d\'Achat - ' + displayName;
        document.getElementById('clientHistoryTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">Chargement...</td></tr>';
        document.getElementById('clientHistoryModal').style.display = 'flex';

        fetch(`${GP.urls['vente/caisse/pos/client-history']}/${currentClientId}`)
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
                                    <td style="padding: 8px; font-weight: bold;">${escHtml(t.cticketnumero || '')}</td>
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

        fetch(`${GP.routes['vente.caisse.pos.article_history']}?q=${encodeURIComponent(q)}`)
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
                                <td style="padding: 8px;">${escHtml(h.ticket || '')}</td>
                                <td style="padding: 8px;">${escHtml(clientInfo)}</td>
                                <td style="padding: 8px;">${escHtml(prodInfo)}</td>
                                <td style="padding: 8px;">${escHtml(varInfo)}</td>
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
            fetch(`${GP.urls['vente/caisse/pos/client']}/${currentClientId}`)
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
        formData.append('_token', GP.csrf);

        fetch(GP.routes['vente.caisse.pos.send_sms'], {
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

    let ticketPreviewId = null;

    // Réimpression du ticket affiché : imprimante thermique de la caisse, ou reçu navigateur selon le réglage
    function reimprimerTicketPreview() {
        if (!ticketPreviewId) return;
        fetch(`${GP.routes['vente.caisse.imprimer'].replace(/\/0$/, '')}/${ticketPreviewId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': GP.csrf, 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    notifier(res.message, 'success');
                    return;
                }
                if (res.mode && res.mode !== 'navigateur') {
                    notifier(res.message + ' Le reçu s\'ouvre dans le navigateur.', 'warning', 8000);
                }
                if (res.print_url) window.open(res.print_url, '_blank');
            })
            .catch(() => notifier('Erreur réseau lors de l\'impression.', 'error'));
    }

    function showTicketPreview(ticketId) {
        ticketPreviewId = ticketId;
        document.getElementById('ticketPreviewContent').innerHTML = '<div style="background: white; padding: 20px; border-radius: 4px;">Chargement du ticket...</div>';
        document.getElementById('ticketPreviewModal').style.display = 'flex';

        fetch(`${GP.urls['vente/tickets']}/${ticketId}`, {
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
        
        fetch(`${GP.routes['vente.caisse.pos.advanced_check_stock']}?` + qs.toString())
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
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${escHtml(s.reference || '')}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${escHtml(s.designation || '')}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${escHtml(s.rayon || '')}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${escHtml(s.couleur || '')}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee;">${escHtml(s.taille || '')}</td>
                                    <td style="padding: 8px; border-right: 1px solid #eee; font-weight: bold;">${escHtml(s.site_nom || '')}</td>
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
        if (ticketExistantVerrouille()) return;
        if (selectedLineIndex < 0 || selectedLineIndex >= ticketLines.length) {
            notifier('Veuillez d\'abord sélectionner un article dans le ticket.');
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
            notifier("La quantité doit être supérieure à 0.");
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
            notifier('Le ticket est vide !');
            return;
        }
        
        let clientName = document.getElementById('displayClientName') ? document.getElementById('displayClientName').textContent : 'PASSAGER';
        let dateStr = new Date().toLocaleString('fr-FR');
        let vendeur = GP.vendeurParDefaut.nom;
        
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
                <div class="center bold" style="font-size: 16px;">${escHtml(GP.siteNom)}</div>
                <div class="center">Ticket Provisoire</div>
                <div class="line"></div>
                <div>Date: ${dateStr}</div>
                <div>Client: ${escHtml(clientName)}</div>
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
        if (!printWindow) {
            notifier("Impression impossible : autorisez les fenêtres pop-up pour ce site.", 'warning', 8000);
            return;
        }
        printWindow.document.write(ticketHTML);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function searchClientById(id) {
        fetch(`${GP.routes['vente.caisse.pos.search_clients']}?q=${encodeURIComponent(id)}`)
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
    let currentVendeurId = GP.vendeurParDefaut.id;
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
        fetch(`${GP.routes['vente.caisse.pos.search_vendeurs']}?q=${encodeURIComponent(q)}`)
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
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb;">${escHtml(v.code || '')}</td>
                        <td style="padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">${escHtml(v.nom || '')}</td>
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

