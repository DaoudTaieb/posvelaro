<!-- REPRISE MODAL -->
<div id="repriseModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 600px; max-height: 80%; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Tickets en Attente</h2>
            <button type="button" onclick="closeRepriseModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <div style="padding: 15px; overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">N° Ticket</th>
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Client</th>
                        <th style="padding: 8px; text-align: left; border-bottom: 2px solid #e5e7eb;">Date</th>
                        <th style="padding: 8px; text-align: right; border-bottom: 2px solid #e5e7eb;">Total TTC</th>
                        <th style="padding: 8px; text-align: center; border-bottom: 2px solid #e5e7eb;">Action</th>
                    </tr>
                </thead>
                <tbody id="repriseTbody">
                </tbody>
            </table>
        </div>
    </div>
</div>
