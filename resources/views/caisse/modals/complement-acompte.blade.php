<!-- COMPLEMENT ACOMPTE MODAL -->
<div id="complementAcompteModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 950px; max-height: 85%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Complement acomptes</h2>
            <button type="button" onclick="closeComplementAcompteModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <!-- Filters -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">DU :</label>
                <input type="date" id="compAcompte_du" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px;">
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">AU :</label>
                <input type="date" id="compAcompte_au" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px;">
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">Client :</label>
                <select id="compAcompte_client" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px; min-width: 150px;">
                    <option value="">-- Tous --</option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 4px;">
                <label style="font-weight: bold; white-space: nowrap;">N° :</label>
                <input type="text" id="compAcompte_numero" placeholder="Numéro ticket" style="padding: 4px 6px; border: 1px solid #ccc; border-radius: 3px; font-size: 11px; width: 100px;">
            </div>
            <button onclick="fetchComplementTickets()" style="padding: 5px 15px; border: none; background: #2563eb; color: white; border-radius: 3px; cursor: pointer; font-size: 11px; font-weight: bold;">Filtrer</button>
        </div>
        <!-- Table -->
        <div style="padding: 10px 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">N° Ticket</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Date</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Client</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Caissier</th>
                        <th style="padding: 6px 8px; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Vendeur</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Total Qte</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Total TTC</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Acompte</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Reste</th>
                        <th style="padding: 6px 8px; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Montant reçu</th>
                        <th style="padding: 6px 8px; text-align: center; border-bottom: 2px solid #e5e7eb; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody id="complementAcompteTbody">
                    <tr><td colspan="11" style="padding: 20px; text-align: center; color: #999;">Cliquez sur Filtrer pour charger les tickets</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Footer totals -->
        <div style="padding: 8px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 20px; font-weight: bold; font-size: 11px;">
            <span>Total Qte: <span id="compAcompte_totalQte">0</span></span>
            <span>Total TTC: <span id="compAcompte_totalTTC">0.000</span></span>
            <span>Total Reste: <span id="compAcompte_totalReste">0.000</span></span>
        </div>
    </div>
</div>
