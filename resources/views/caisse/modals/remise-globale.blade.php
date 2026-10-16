<!-- GLOBAL DISCOUNT MODAL -->
<div id="globalDiscountModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 350px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); font-family: Arial, sans-serif; overflow: hidden;">
        <!-- Header -->
        <div style="padding: 15px 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Remise sur Ticket</h2>
            <button type="button" onclick="closeGlobalDiscountModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 20px; display: flex; flex-direction: column; gap: 15px; align-items: center;">
            <label style="font-size: 14px; color: #475569; font-weight: 600;">Nouveau Total TTC</label>
            <input type="number" id="globalDiscountInput" step="0.001" style="width: 200px; padding: 10px; border: 2px solid #3b82f6; border-radius: 6px; outline: none; font-size: 20px; font-weight: bold; text-align: center; color: #1d4ed8;" onkeydown="if(event.key === 'Enter') validerGlobalDiscount()">
        </div>
        
        <!-- Footer -->
        <div style="padding: 15px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: center; gap: 10px; background: #f8fafc;">
            <button onclick="validerGlobalDiscount()" style="padding: 10px 30px; border: none; background: #10b981; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px;">
                Valider
            </button>
            <button onclick="closeGlobalDiscountModal()" style="padding: 10px 30px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; color: #475569;">
                Annuler
            </button>
        </div>
    </div>
</div>
