<!-- CLIENT SEARCH MODAL -->
<div id="clientModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1200px; max-height: 90%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 16px; font-weight: 700;">Sélection des clients</h2>
            <div style="display: flex; gap: 5px;">
                <button type="button" onclick="confirmClientSelection()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeClientModal()" style="background: white; border: 1px solid #d1d5db; width: 36px; height: 36px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 4px;" title="Annuler">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Create Form -->
        <div style="padding: 15px; border-bottom: 1px solid #e5e7eb; display: flex; flex-direction: column;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <label style="font-weight: bold; font-size: 12px; color: #6b7280;">Raison</label>
                <input type="text" id="newClientRaison" style="flex: 1; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
                
                <label style="font-weight: bold; font-size: 12px; color: #6b7280;">Tél</label>
                <input type="text" id="newClientTel" style="width: 250px; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;">
                
                <button type="button" onclick="createNewClient()" style="padding: 6px 15px; border: 1px solid #111827; background: white; color: #111827; font-weight: bold; font-size: 12px; border-radius: 4px; cursor: pointer;">Créer Client</button>
            </div>
            <div style="color: #ef4444; font-size: 11px; margin-top: 5px; font-weight: bold;">Raison && Téléphone Obligatoire</div>
        </div>

        <!-- Search & Table -->
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="clientSearchInput" placeholder="Enter text to search..." style="width: 100%; padding: 6px 8px 6px 25px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px;" oninput="rechercheClientsDifferee()">
                </div>
            </div>
            
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; white-space: nowrap;">
                    <thead style="position: sticky; top: 0; background: white; z-index: 2;">
                        <tr style="background: #f8fafc; text-align: left;">
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Code</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Raison Social</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Tél</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Adresse</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Code.TVA</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">%Remise</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">% Plafond Rem</th>
                            <th style="padding: 8px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;">Solde</th>
                        </tr>
                        <tr>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_code" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_nom" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_tel" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_adresse" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_mf" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_remise" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_plafonremise" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                            <th style="padding: 4px; border-bottom: 2px solid #e5e7eb;"><input type="text" id="f_solde" class="client-filter" style="width: 100%; padding: 4px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 11px;" oninput="rechercheClientsDifferee()"></th>
                        </tr>
                    </thead>
                    <tbody id="clientsTbody"></tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="clientsPagination" style="margin-top: 10px; display: flex; gap: 5px;"></div>
        </div>
    </div>
</div>
