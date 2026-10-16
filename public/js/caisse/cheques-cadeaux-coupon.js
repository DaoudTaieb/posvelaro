/*
 * Golden Pos - caisse : cheques-cadeaux-coupon
 * Extrait de resources/views/caisse/scripts/cheques-cadeaux-coupon.blade.php (script classique, non module :
 * fonctions et variables globales partagées avec les autres fichiers et les attributs onclick).
 * Valeurs serveur (routes, jeton CSRF, client et vendeur par défaut) : objet GP défini par
 * resources/views/caisse/partials/config-js.blade.php.
 */
    function openChequeCadeauxModal() {
        if (ticketDejaRegle()) return;
        if (ticketLines.length === 0 && !complementMode) {
            notifier("Le ticket est vide.");
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
            notifier("Montant invalide");
            return;
        }
        
        let today = new Date().toISOString().split('T')[0];
        let isMultiOpen = document.getElementById('multiPaymentModal').style.display === 'flex';

        if (isMultiOpen) {
            mpReglementsArray.push({
                id: Date.now(),
                modeId: 5, // 5 = Chèque Cadeaux
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
                vendeurid: currentVendeurId,
                clientid: currentClientId,
                lignes: ticketLines,
                totalttc: total,
                acompte: val,
                netapayer: Math.max(0, total - val),
                reglements: [{
                    modereglementid: 5,
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
                        notifier('Complément enregistré avec succès!');
                        closeChequeCadeauxModal();
                        exitComplementMode();
                        document.getElementById('complementAcompteModal').style.display = 'none';
                    } else {
                        notifier('Erreur: ' + (data.message || ''));
                    }
                })
                .catch(err => {
                    console.error(err);
                    notifier("Erreur réseau.");
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
            notifier("Veuillez saisir un numéro et un montant valide.");
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
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="text" value="${escHtml(l.numero)}" onchange="cpUpdate(${index}, 'numero', this.value)" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; border-right: 1px solid #ddd;"><input type="number" step="0.001" value="${l.montant}" onchange="cpUpdate(${index}, 'montant', this.value)" style="width: 100%; border: none; padding: 4px; text-align: right; outline: none; box-sizing: border-box;"></td>
                <td style="padding: 2px; text-align: center;"><button onclick="cpRemove(${index})" style="background: none; border: none; color: red; cursor: pointer; font-weight: bold;">X</button></td>
            `;
            tbody.appendChild(tr);
        });

        if (cpAdding) {
            let tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 2px; border-right: 1px solid #ddd;">
                    <input type="text" id="cpDraftNum" value="${escHtml(cpDraft.numero)}" style="width: 100%; border: none; padding: 4px; outline: none; box-sizing: border-box;">
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
                        modeId: 13, // 13 = Coupon Manuella
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
