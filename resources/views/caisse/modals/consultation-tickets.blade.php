<!-- CONSULTATION TICKETS MODAL -->
<div id="consultationModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1300px; max-height: 95%; border-radius: 4px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #fff; position: relative;">
            <div style="flex: 1;"></div>
            <h2 style="margin: 0; font-size: 16px; font-weight: 700; color: #333; text-align: center; flex: 1;">Journal Vente</h2>
            <div style="flex: 1; display: flex; justify-content: flex-end; gap: 5px;">
                <button type="button" style="background: white; border: 1px solid #ccc; width: 40px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 2px;" title="Valider">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" onclick="closeConsultationModal()" style="background: white; border: 1px solid #ccc; width: 40px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center; border-radius: 2px;" title="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Top Filters -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 15px; background: #fff; font-size: 12px;">
            <div style="display: flex; align-items: center; gap: 5px; flex: 1;">
                <label style="color: #555;">Indicateur</label>
                <select id="consultationIndicateur" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous">Tous</option>
                    <option value="Payé">Payé</option>
                    <option value="Non Payé">Non Payé</option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 5px; flex: 1.5;">
                <label style="color: #555;">Client</label>
                <select id="consultationClientSelect" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous"></option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 5px; flex: 1.5;">
                <label style="color: #555;">Vendeur</label>
                <select id="consultationVendeurSelect" style="flex: 1; padding: 4px; border: 1px solid #ccc; border-radius: 2px; outline: none;" onchange="renderConsultationTickets()">
                    <option value="Tous">Tous</option>
                </select>
            </div>
            <div>
                <button type="button" onclick="fetchConsultationData()" style="background: white; border: 1px solid #555; padding: 4px 15px; cursor: pointer; border-radius: 2px; display: flex; align-items: center; justify-content: center; height: 28px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </button>
            </div>
        </div>

        <div style="padding: 10px 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column; background: #fff;">
            <!-- Global Search -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 5px;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 6px; top: 7px;">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="consultationGlobalSearch" placeholder="Enter text to search..." style="width: 100%; padding: 4px 6px 4px 22px; border: 1px solid #ccc; border-radius: 2px; font-size: 12px; outline: none;" oninput="renderConsultationTickets()">
                </div>
            </div>

            <!-- Grid -->
            <div style="flex: 1; overflow-y: auto; border: 1px solid #ccc;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; white-space: nowrap;">
                    <thead style="position: sticky; top: 0; background: #f9f9f9; z-index: 2;">
                        <tr style="text-align: left; color: #333;">
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Statut</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Date</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Numéro</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Client</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Tel. client</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Total TTC</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Montant reçu</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Reste</th>
                            <th style="padding: 6px 8px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd; font-weight: 600;">Caissier</th>
                            <th style="padding: 6px 8px; border-bottom: 1px solid #ddd; font-weight: 600;">Vendeur</th>
                        </tr>
                        <tr style="background: #fff;">
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="statut" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="date" class="consult-filter" data-col="date" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" class="consult-filter" data-col="numero" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="client" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="tel" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="ttc" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="recu" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="number" step="0.001" class="consult-filter" data-col="reste" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-right: 1px solid #ddd; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="caissier" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                            <td style="padding: 2px 4px; border-bottom: 1px solid #ddd;"><input type="text" class="consult-filter" data-col="vendeur" style="width: 100%; border: 1px solid #ccc; padding: 2px 4px; font-size: 11px;"></td>
                        </tr>
                    </thead>
                    <tbody id="consultationTbody">
                        <tr><td colspan="10" style="text-align: center; padding: 30px; font-weight: bold;">No data to display</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
