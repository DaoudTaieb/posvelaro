<!-- MOUVEMENTS MODAL -->
<div id="mouvementsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1100px; height: 700px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); display: flex; flex-direction: column; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; text-align: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Mouvements</h2>
        </div>
        <!-- Filters Section -->
        <div style="padding: 15px; display: flex; align-items: center; justify-content: center; gap: 15px; border-bottom: 1px solid #ddd;">
            <label style="font-weight: bold; font-size: 12px;">DU</label>
            <input type="date" id="mvtFilterDu" value="{{ date('Y-m-d') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">
            
            <label style="font-weight: bold; font-size: 12px;">AU</label>
            <input type="date" id="mvtFilterAu" value="{{ date('Y-m-d') }}" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">

            <label style="font-weight: bold; font-size: 12px;">Client</label>
            <select id="mvtFilterClient" style="padding: 6px; border: 1px solid #ccc; border-radius: 4px; width: 200px; font-size: 12px;">
                <option value="">PASSAGER</option>
            </select>

            <button type="button" onclick="loadMouvementsData()" style="padding: 6px 20px; border: 1px solid #333; background: white; cursor: pointer; border-radius: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            </button>
        </div>
        <div style="padding: 10px 15px; display: flex; justify-content: flex-end; border-bottom: 1px solid #ddd;">
            <div style="position: relative;">
                <input type="text" id="mvtSearchText" placeholder="Enter text to search..." onkeyup="filterMouvementsTable()" style="padding: 6px 6px 6px 25px; border: 1px solid #ccc; width: 250px; font-size: 12px;">
                <svg style="position: absolute; left: 6px; top: 8px;" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
        </div>
        <!-- Table -->
        <div style="flex: 1; overflow: auto; padding: 0 15px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; text-align: left;" id="mvtTable">
                <thead style="background: #f8f9fa; position: sticky; top: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <tr>
                        <th style="padding: 8px; border: 1px solid #ddd; width: 30px; text-align: center;"><input type="checkbox" onclick="toggleAllMouvements(this)"></th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Numéro</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Reference</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Produit</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Taille</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Couleur</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Qte</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">PU.TTC</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Remise</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Total</th>
                    </tr>
                </thead>
                <tbody id="mvtTbody">
                    <tr><td colspan="11" style="text-align: center; padding: 40px; font-weight: bold; color: #555;">No data to display</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Footer Buttons -->
        <div style="padding: 15px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px; background: #f8f9fa;">
            <button type="button" onclick="validerMouvementsSelection()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeMouvementsModal()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>
