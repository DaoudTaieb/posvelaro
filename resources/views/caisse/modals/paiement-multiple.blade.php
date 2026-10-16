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
                        <button id="mpBtnCB" onclick="mpAddLine('CB', 4)" style="padding: 0 5px; border: 1px solid #ccc; background: white; cursor: pointer; display: none; color: green; font-weight: bold;">✓</button>
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
