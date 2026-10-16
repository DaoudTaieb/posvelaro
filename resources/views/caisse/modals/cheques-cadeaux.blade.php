<!-- CHEQUE CADEAUX MODAL -->
<div id="chequeCadeauxModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1040; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 550px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 14px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 15px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Type Cheque Cadeau</h2>
            <button type="button" onclick="closeChequeCadeauxModal()" style="background: none; border: none; font-size: 16px; font-weight: bold; cursor: pointer; color: #666;">&times;</button>
        </div>

        <div style="padding: 20px;">
            <div style="border: 1px solid #ddd; border-radius: 4px;">
                <div style="padding: 20px; display: flex; justify-content: center; border-bottom: 1px solid #ddd;">
                    <div style="display: flex; align-items: center; justify-content: center; border: 1px solid #1e293b; border-radius: 4px; padding: 15px; width: 90%; gap: 30px;">
                        <span style="font-size: 14px; color: #333;">Montant</span>
                        <input type="number" step="0.001" id="ccMontant" style="width: 150px; padding: 8px; border: 1px solid #ccc; border-radius: 2px; text-align: center; outline: none; font-weight: bold; font-size: 14px;">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; padding: 20px; gap: 10px;">
                    <button onclick="addChequeCadeaux('PLUXEE')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">PLUXEE</button>
                    <button onclick="addChequeCadeaux('JOKER')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">JOKER</button>
                    <button onclick="addChequeCadeaux('SERVIMAX')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">SERVIMAX</button>
                    <button onclick="addChequeCadeaux('TOPCHEQUE')" style="flex: 1; padding: 15px 0; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; text-align: center; font-size: 13px; color: #333;">TOPCHEQUE</button>
                </div>
            </div>
        </div>
    </div>
</div>
