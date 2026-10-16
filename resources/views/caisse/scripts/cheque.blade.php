<script>
    let chqLines = [];

    function openChequeModal() {
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
                    <input type="text" placeholder="Numéro" value="${escHtml(l.numero)}" onchange="updateChqLine(${index}, 'numero', this.value)" style="width: 90px; padding: 4px; border: 1px solid #ccc; outline: none; font-size: 12px;">
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
                notifier("numéro chèque && Montant Obligatoire pour toutes les lignes.");
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
                vendeurid: currentVendeurId,
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
                        notifier('Complément enregistré avec succès!');
                        closeChequeModal();
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
                closeChequeModal();
                window.submitTicketToBackend(payload, false);
            }
        }
    }
</script>
