<!-- SMS MODAL -->
<div id="smsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1025; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 500px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif; display: flex; flex-direction: column;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Envoyer SMS (Bip SMS)</h2>
            <button type="button" onclick="closeSmsModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px;">
            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Téléphone Destinataire</label>
                <input type="text" id="smsTelephone" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Ex: 216XXXXXXXX">
            </div>
            <div style="margin-bottom: 10px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Message</label>
                <textarea id="smsMessage" rows="4" style="width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="Tapez votre message ici..."></textarea>
            </div>
            <div id="smsFeedback" style="display: none; padding: 10px; margin-top: 10px; border-radius: 4px; text-align: center;"></div>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="sendSmsAction()" style="padding: 6px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Envoyer</button>
            <button type="button" onclick="closeSmsModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">Annuler</button>
        </div>
    </div>
</div>
