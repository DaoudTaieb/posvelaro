<!-- ARTICLE HISTORY MODAL -->
<div id="articleHistoryModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1025; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 900px; max-height: 85%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Consultation des Articles (Historique)</h2>
            <button type="button" onclick="closeArticleHistoryModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Search Bar -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; gap: 10px;">
            <input type="text" id="articleHistorySearch" placeholder="Client, N° Tél, N° Carte Fidélité..." style="flex: 1; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" onkeypress="if(event.key==='Enter') searchArticleHistory()">
            <button onclick="searchArticleHistory()" style="padding: 6px 15px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Rechercher</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid #ddd;">
                        <th style="padding: 8px; font-weight: 600;">Date</th>
                        <th style="padding: 8px; font-weight: 600;">Ticket</th>
                        <th style="padding: 8px; font-weight: 600;">Client</th>
                        <th style="padding: 8px; font-weight: 600;">Ref/Désignation</th>
                        <th style="padding: 8px; font-weight: 600;">Couleur/Taille</th>
                        <th style="padding: 8px; font-weight: 600; text-align: center;">Qté</th>
                        <th style="padding: 8px; font-weight: 600; text-align: right;">Total TTC</th>
                    </tr>
                </thead>
                <tbody id="articleHistoryTableBody">
                    <tr><td colspan="7" style="text-align: center; padding: 20px;">Veuillez effectuer une recherche...</td></tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeArticleHistoryModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">Fermer</button>
        </div>
    </div>
</div>
