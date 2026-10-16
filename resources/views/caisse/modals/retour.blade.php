<!-- RETOUR TICKET MODAL -->
<div id="retourModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 1000px; max-height: 90%; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333; flex: 1; text-align: center;">Mouvement Ticket</h2>
            <button type="button" onclick="closeRetourModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666; font-weight: bold;">&times;</button>
        </div>
        <!-- Search Section -->
        <div style="padding: 15px; display: flex; justify-content: center; align-items: center; gap: 10px; border-bottom: 1px solid #ddd;">
            <input type="text" id="retourTicketNumero" style="width: 300px; padding: 6px; border: 1px solid #ccc; border-radius: 2px;">
            <button type="button" onclick="browseTicketsRetour()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 2px;">...</button>
            <button type="button" onclick="openMouvementsModal()" style="padding: 6px 15px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 2px;">Mvts</button>
        </div>
        <!-- Table -->
        <div style="flex: 1; overflow-y: auto; padding: 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center;">
                <thead style="background: #fff; position: sticky; top: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold; text-align: left;">Référence</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold; text-align: left;">Désignation</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte Vendu</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte Récupérée</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Qte</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Prix</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; font-weight: bold;">Rem%</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Total</th>
                    </tr>
                </thead>
                <tbody id="retourTbody">
                    <!-- rows dynamically populated -->
                </tbody>
            </table>
        </div>
        <!-- Footer Buttons -->
        <div style="padding: 15px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px; background: #f8f9fa;">
            <button type="button" onclick="validerRetour()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeRetourModal()" style="background: white; border: 1px solid #333; border-radius: 4px; width: 80px; height: 34px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>
