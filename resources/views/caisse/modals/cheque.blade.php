<!-- CHEQUE MODAL -->
<div id="chequeModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 650px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Informations Cheque</h2>
            <button type="button" onclick="closeChequeModal()" style="background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: #666;">&times;</button>
        </div>

        <div style="padding: 15px;">
            <!-- Top section -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-weight: bold; color: #333;">Montant</label>
                    <input type="number" step="0.001" id="chqGlobalMontant" style="width: 120px; padding: 6px; border: 1px solid #ccc; border-radius: 2px; outline: none; font-size: 14px;">
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-weight: bold; color: #333;">Nombre de cheque</label>
                    <input type="number" id="chqNombre" value="1" min="1" style="width: 60px; padding: 6px; border: 1px solid #ccc; border-radius: 2px; outline: none; font-size: 14px; text-align: center;">
                    <button onclick="generateChequeLines()" style="padding: 4px 15px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; height: 32px; display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </div>
            </div>

            <!-- Table section -->
            <div style="border: 1px solid #ddd;">
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <thead style="background: white; border-bottom: 1px solid #ddd;">
                        <tr>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Montant</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Propriétaire</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Numéro</th>
                            <th style="padding: 8px; border-right: 1px solid #ddd; font-weight: normal; color: #333;">Banque</th>
                            <th style="padding: 8px; font-weight: normal; color: #333;">Échéance</th>
                        </tr>
                    </thead>
                    <tbody id="chqTbody">
                        <!-- Lines generated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; display: flex; flex-direction: column; background: #f8f9fa;">
            <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 5px;">
                <button onclick="validerChequeModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeChequeModal()" style="width: 80px; height: 35px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; color: #333;">
                    &times;
                </button>
            </div>
            <div style="color: #dc2626; font-size: 13px;">numéro chèque && Montant Obligatoire</div>
        </div>
    </div>
</div>
