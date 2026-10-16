<!-- VENDEUR SEARCH MODAL -->
<div id="vendeurModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; max-height: 70%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <!-- Header with title + ✓ and ✕ buttons -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 14px; font-weight: 700; flex: 1; text-align: center;">Liste des Vendeurs</h2>
            <div style="display: flex; gap: 5px;">
                <button type="button" onclick="confirmVendeurSelection()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeVendeurModal()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="vendeurSearchInput" placeholder="Enter text to search..." style="width: 100%; padding: 6px 8px 6px 25px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;" oninput="rechercheVendeursDifferee(this.value)">
                </div>
            </div>
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead>
                        <tr style="background: #f1f5f9;">
                            <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Code</th>
                            <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Nom</th>
                        </tr>
                    </thead>
                    <tbody id="vendeursTbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
