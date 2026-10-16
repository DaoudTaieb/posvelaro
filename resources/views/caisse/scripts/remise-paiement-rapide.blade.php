<script>
    // GLOBAL DISCOUNT LOGIC
    function openGlobalDiscountModal() {
        if (ticketExistantVerrouille()) return;
        if (ticketLines.length === 0) return;
        let currentTotal = parseFloat(document.getElementById('grandTotal').innerText);
        document.getElementById('globalDiscountInput').value = currentTotal.toFixed(3);
        document.getElementById('globalDiscountModal').style.display = 'flex';
        setTimeout(() => {
            let input = document.getElementById('globalDiscountInput');
            input.focus();
            input.select();
        }, 100);
    }

    function closeGlobalDiscountModal() {
        document.getElementById('globalDiscountModal').style.display = 'none';
    }

    function validerGlobalDiscount() {
        let targetTotal = parseFloat(document.getElementById('globalDiscountInput').value);
        if (isNaN(targetTotal) || targetTotal < 0) {
            notifier("Montant invalide");
            return;
        }

        let grossTotal = 0;
        ticketLines.forEach(l => {
            let qte = parseFloat(l.qte) || 0;
            let prix = parseFloat(l.prix) || 0;
            grossTotal += (qte * prix);
        });

        if (grossTotal === 0) {
            notifier("Total brut du ticket est 0");
            return;
        }

        let discountRatio = (grossTotal - targetTotal) / grossTotal;
        let remiseGlobale = discountRatio * 100;

        if (remiseGlobale < 0) remiseGlobale = 0;
        if (remiseGlobale > 100) remiseGlobale = 100;

        ticketLines.forEach(l => {
            l.remise = remiseGlobale;
            let prix = parseFloat(l.prix) || 0;
            l.prixNet = prix * (1 - (remiseGlobale / 100));
            l.total = (parseFloat(l.qte) || 0) * l.prixNet;
        });

        closeGlobalDiscountModal();
        renderTable();
    }

    let quickPaymentModeId = 1;

    function openQuickPaymentModal(modeId, title) {
        if (ticketDejaRegle()) return;
        if (ticketLines.length === 0 && !complementMode) {
            notifier("Le ticket est vide.");
            return;
        }
        quickPaymentModeId = modeId;
        document.getElementById('quickPaymentTitle').innerText = title;

        let total = complementMode ? complementReste : parseFloat(document.getElementById('grandTotal').innerText);
        document.getElementById('quickPaymentMontant').value = total.toFixed(3);
        
        // Reset recu and rendu
        document.getElementById('quickPaymentRecu').value = '';
        document.getElementById('quickPaymentRendu').value = '0.000';
        document.getElementById('quickPaymentRendu').style.color = '#047857';

        document.getElementById('quickPaymentModal').style.display = 'flex';
        setTimeout(() => document.getElementById('quickPaymentRecu').focus(), 100);
    }

    function setQuickPaymentRecu(amount) {
        let total = parseFloat(document.getElementById('quickPaymentMontant').value) || 0;
        let recuInput = document.getElementById('quickPaymentRecu');
        
        if (amount === 'exact') {
            recuInput.value = total.toFixed(3);
        } else {
            recuInput.value = parseFloat(amount).toFixed(3);
        }
        calculerRendu();
        recuInput.focus();
    }

    function calculerRendu() {
        let total = parseFloat(document.getElementById('quickPaymentMontant').value) || 0;
        let recu = parseFloat(document.getElementById('quickPaymentRecu').value) || 0;
        let rendu = recu - total;
        
        let renduInput = document.getElementById('quickPaymentRendu');
        renduInput.value = rendu.toFixed(3);
        
        if (rendu < 0) {
            renduInput.style.color = '#dc2626'; // Red
        } else {
            renduInput.style.color = '#047857'; // Green
        }
    }

    function closeQuickPaymentModal() {
        document.getElementById('quickPaymentModal').style.display = 'none';
    }

    function validerQuickPayment() {
        let total = parseFloat(document.getElementById('quickPaymentMontant').value);
        let recu = parseFloat(document.getElementById('quickPaymentRecu').value);
        
        if (isNaN(recu) || recu <= 0) {
            // S'il n'a rien saisi et qu'il tape Entrée, on suppose que le compte est bon.
            recu = total;
        }

        if (recu < total) {
            notifier("Le montant reçu est inférieur au total à payer.");
            return;
        }

        let mnt = total; // On valide toujours le montant exact à payer pour la caisse

        let payload = {
            vendeurid: currentVendeurId,
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
                    notifier('Complément enregistré avec succès!');
                    closeQuickPaymentModal();
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
            closeQuickPaymentModal();
            window.submitTicketToBackend(payload, false);
        }
    }
</script>
