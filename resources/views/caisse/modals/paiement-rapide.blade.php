<!-- QUICK PAYMENT MODAL -->
<div id="quickPaymentModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 450px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); font-family: Arial, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div style="padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 id="quickPaymentTitle" style="margin: 0; font-size: 18px; font-weight: 700; color: #1e293b;">Réglement Espèce</h2>
            <button type="button" onclick="closeQuickPaymentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                
                <!-- Total à payer -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: #f1f5f9; border-radius: 6px;">
                    <span style="font-size: 16px; font-weight: 600; color: #475569;">Total à payer</span>
                    <input type="text" id="quickPaymentMontant" readonly style="width: 150px; padding: 8px; border: none; background: transparent; text-align: right; font-size: 20px; font-weight: bold; color: #0f172a; outline: none;">
                </div>

                <!-- Montant Reçu -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 16px; font-weight: 600; color: #334155;">Montant reçu</span>
                    <div style="position: relative;">
                        <input type="number" id="quickPaymentRecu" step="0.001" style="width: 150px; padding: 10px; border: 2px solid #3b82f6; border-radius: 6px; outline: none; font-size: 20px; font-weight: bold; text-align: right; color: #1d4ed8;" oninput="calculerRendu()" onkeydown="if(event.key === 'Enter') validerQuickPayment()">
                    </div>
                </div>

                <!-- Monnaie à rendre -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: #ecfdf5; border-radius: 6px; border: 1px solid #d1fae5;">
                    <span style="font-size: 16px; font-weight: 600; color: #065f46;">À rendre</span>
                    <input type="text" id="quickPaymentRendu" readonly style="width: 150px; padding: 8px; border: none; background: transparent; text-align: right; font-size: 24px; font-weight: bold; color: #047857; outline: none;" value="0.000">
                </div>

                <!-- Raccourcis -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
                    <button type="button" onclick="setQuickPaymentRecu('exact')" style="grid-column: span 3; padding: 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 14px; color: #334155;">Le compte est bon</button>
                    <button type="button" onclick="setQuickPaymentRecu(10)" style="padding: 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px; color: #1e293b;">10 DT</button>
                    <button type="button" onclick="setQuickPaymentRecu(20)" style="padding: 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px; color: #1e293b;">20 DT</button>
                    <button type="button" onclick="setQuickPaymentRecu(50)" style="padding: 10px; border: 1px solid #cbd5e1; background: white; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px; color: #1e293b;">50 DT</button>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 15px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 10px; background: #f8fafc;">
            <button onclick="closeQuickPaymentModal()" style="padding: 10px 20px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; cursor: pointer; font-weight: bold; color: #475569;">Annuler</button>
            <button onclick="validerQuickPayment()" style="padding: 10px 20px; border: none; background: #10b981; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Valider
            </button>
        </div>
    </div>
</div>
