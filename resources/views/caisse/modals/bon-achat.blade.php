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
            <!-- Code Input (scan du code-barres imprimé sur le ticket, ex. BA000123) -->
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="baCodeInput" placeholder="Scanner ou saisir le N° du bon (BA000123)" style="width: 60%; padding: 8px; border: 1px solid #ccc;" onkeydown="if(event.key==='Enter') { event.preventDefault(); baAddLine(); }">
                <button type="button" onclick="baAddLine()" style="padding: 8px 14px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; font-weight: bold;">Ajouter</button>
            </div>
            <!-- Bons disponibles du client sélectionné -->
            <div id="baBonsClient" style="font-size: 12px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center;"></div>

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
