<!-- TICKET PREVIEW MODAL -->
<div id="ticketPreviewModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1030; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: transparent; border: none; box-shadow: none; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 324px; display: flex; justify-content: flex-end; margin-bottom: 5px;">
            <button type="button" onclick="closeTicketPreviewModal()" style="background: white; border: 1px solid #ccc; border-radius: 50%; width: 30px; height: 30px; font-size: 16px; cursor: pointer; color: #333; font-weight: bold;">&times;</button>
        </div>
        <div id="ticketPreviewContent" style="background: transparent;">
            <!-- Receipt loaded here -->
        </div>
    </div>
</div>
