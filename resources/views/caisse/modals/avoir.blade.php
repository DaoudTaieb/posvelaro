<!-- AVOIR MODAL -->
<div id="avoirModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 480px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); font-family: Arial, sans-serif; overflow: hidden; border: 1px solid #ccc;">
        <!-- Header -->
        <div style="padding: 10px 15px; background: #f3f4f6; border-bottom: 1px solid #e5e7eb; font-size: 14px; font-weight: normal; color: #333;">
            Saisie Avoir Client
        </div>

        <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px; align-items: center;">
            <!-- Code Input -->
            <div style="display: flex; align-items: center; width: 100%; gap: 10px;">
                <label style="font-size: 13px; color: #475569; font-weight: normal; white-space: nowrap; width: 100px;">Code à barre</label>
                <input type="text" id="avoirBarcodeInput" style="flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; outline: none;" onkeydown="if(event.key==='Enter') validerAvoir()">
            </div>

            <!-- Buttons -->
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px; width: 100%;">
                <button onclick="validerAvoir()" style="width: 80px; height: 38px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button onclick="closeAvoirModal()" style="width: 80px; height: 38px; border: 1px solid #1e293b; background: white; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </div>
</div>
