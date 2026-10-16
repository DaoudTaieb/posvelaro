<!-- SECOND MODAL FOR VARIANTS -->
<div id="variantsModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1010; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 800px; max-height: 80%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <!-- Modal Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="flex: 1; text-align: center;">
                <h2 id="variantsModalTitle" style="margin: 0; font-size: 14px; color: #111827; font-weight: 700;">Titre Produit</h2>
            </div>
            <button type="button" onclick="document.getElementById('variantsModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; padding: 0;">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 15px; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
                 <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="filter-variants" class="form-control" style="width: 100%; padding-left: 25px;" placeholder="Enter text to search..." onkeyup="filterVariantsList()">
                </div>
            </div>
            
            <div class="table-container" style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr>
                            <th style="width:15%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Code</th>
                            <th style="width:20%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Couleur</th>
                            <th style="width:10%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Taille</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Prix</th>
                            <th style="width:10%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Stock</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Stk.En Cours</th>
                            <th style="width:10%; text-align: center; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">
                                <a href="#" style="color: #9ca3af; text-decoration: none;">Clear</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody">
                        <!-- Variants here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Bottom controls inside variants modal -->
            <div style="display: flex; justify-content: flex-end; padding-top: 10px; border-top: 1px solid #e5e7eb; margin-top: 10px;">
                <button class="btn-save" style="padding: 6px 15px; margin-right: 5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                </button>
                <button class="btn-save" style="padding: 6px 15px;" onclick="document.getElementById('variantsModal').style.display='none'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
    </div>
</div>
