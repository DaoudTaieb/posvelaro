<!-- EDIT LINE MODAL -->
<div id="editLineModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1035; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 350px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 13px;">
        <div style="padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Modifier la ligne</h2>
            <button type="button" onclick="closeEditLineModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <div style="padding: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Désignation</label>
                <div id="editLineDesignation" style="padding: 8px; background: #f1f5f9; border: 1px solid #ddd; border-radius: 4px; color: #333;">-</div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Prix Unitaire (TTC)</label>
                <input type="number" id="editLinePrix" step="0.001" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Quantité</label>
                    <input type="number" id="editLineQte" step="0.01" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555;">Remise (%)</label>
                    <input type="number" id="editLineRemise" min="0" max="100" step="0.01" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; outline: none;">
                </div>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeEditLineModal()" style="padding: 8px 15px; border: 1px solid #ccc; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
                <button type="button" onclick="saveEditLine()" style="padding: 8px 15px; border: none; background: #2563eb; color: white; border-radius: 4px; cursor: pointer; font-weight: bold;">Valider</button>
            </div>
        </div>
    </div>
</div>
