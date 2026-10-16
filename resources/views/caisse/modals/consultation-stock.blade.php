<!-- CHECK STOCK MODAL (ADVANCED) -->
<div id="checkStockModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #f8f9fa; z-index: 1025; justify-content: flex-start; align-items: flex-start; flex-direction: column;">
    
    <!-- Top Bar -->
    <div style="width: 100%; display: flex; align-items: flex-end; padding: 10px; background: white; border-bottom: 1px solid #ddd; box-shadow: 0 1px 3px rgba(0,0,0,0.1); gap: 10px;">
        
        <div style="font-weight: bold; font-size: 14px; margin-right: 20px; padding-bottom: 5px;">Consultation Stock</div>
        
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Référence</label>
            <div style="display: flex;">
                <input type="text" id="advStockRef" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockRef').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Rayon</label>
            <div style="display: flex;">
                <select id="advStockRayon" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px 0 0 2px;">
                    <option value="">Select Rayon...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->categoryid }}">{{ $cat->categorylibelle }}</option>
                    @endforeach
                </select>
                <button onclick="document.getElementById('advStockRayon').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Couleur</label>
            <div style="display: flex;">
                <input type="text" id="advStockCouleur" style="border: 1px solid #ccc; padding: 4px; width: 100px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockCouleur').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px;">
            <label style="font-size: 10px; color: #666;">Taille</label>
            <div style="display: flex;">
                <input type="text" id="advStockTaille" style="border: 1px solid #ccc; padding: 4px; width: 80px; outline: none; border-radius: 2px 0 0 2px;" onkeypress="if(event.key==='Enter') searchAdvancedStock()">
                <button onclick="document.getElementById('advStockTaille').value=''" style="border: 1px solid #ccc; border-left: none; background: #fff; padding: 4px 8px; cursor: pointer; border-radius: 0 2px 2px 0; color: #666;">&times;</button>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2px; margin-left: 10px;">
            <label style="font-size: 10px; color: #666;">Type Stock</label>
            <div style="display: flex;">
                <select id="advStockType" style="border: 1px solid #ccc; padding: 4px; width: 120px; outline: none; border-radius: 2px;">
                    <option value="reel">Réel</option>
                    <option value="virtuel">Virtuel</option>
                    <option value="reserve">Réservé</option>
                </select>
            </div>
        </div>

        <button onclick="searchAdvancedStock()" style="border: 1px solid #ccc; background: white; padding: 4px 15px; border-radius: 2px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 26px; margin-bottom: 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </button>

        <div style="flex: 1; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeCheckStockModal()" style="border: 1px solid #ccc; background: white; width: 40px; height: 40px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #333;">
                &times;
            </button>
        </div>
    </div>

    <!-- Table content -->
    <div style="flex: 1; width: 100%; overflow-y: auto; padding: 10px; background: white;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="border-bottom: 2px solid #ccc; background: #f1f5f9;">
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Référence</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Désignation</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Rayon</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Couleur</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Taille</th>
                    <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Dépôt / Magasin</th>
                    <th style="padding: 8px; text-align: right; background: #e2e8f0;" id="advStockQtyHeader">Quantité (Réel)</th>
                </tr>
            </thead>
            <tbody id="checkStockTableBody">
                <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">Veuillez saisir des critères et cliquer sur la coche pour chercher.</td></tr>
            </tbody>
        </table>
    </div>
</div>
