<!-- EDIT CLIENT MODAL -->
<div id="editClientModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 750px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 12px; font-family: Arial, sans-serif;">
        <!-- Header -->
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa;">
            <h2 id="editClientModalTitle" style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Modification Client</h2>
            <button type="button" onclick="closeEditClientModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <!-- Body -->
        <div style="padding: 15px;">
            <form id="editClientForm">
                @csrf
                <table style="width: 100%; border-spacing: 10px; border-collapse: separate;">
                    <tr>
                        <td style="width: 120px;"><label>Nom / Raison</label></td>
                        <td><input type="text" name="raison" id="ec_raison" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>Prénom</label></td>
                        <td><input type="text" name="prenom" id="ec_prenom" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="bloque_credit" id="ec_bloque_credit" style="vertical-align: middle;"> Bloque Crédit</label></td>
                    </tr>
                    <tr>
                        <td><label>Matricule Fiscal</label></td>
                        <td><input type="text" name="matricule_fiscal" id="ec_matricule_fiscal" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td><label><input type="checkbox" name="g_fidelite" id="ec_g_fidelite" style="vertical-align: middle;"> G.Fidèlite</label></td>
                        <td style="text-align: right;"><label>N° Carte Fid</label></td>
                        <td><input type="text" name="num_fidelite" id="ec_num_fidelite" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Telephone</label></td>
                        <td colspan="2"><input type="text" name="telephone" id="ec_telephone" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;" required></td>
                        <td style="text-align: right;"><label>E-mail</label></td>
                        <td><input type="email" name="email" id="ec_email" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td><label>Date de Naissance</label></td>
                        <td colspan="2"><input type="date" name="date_naissance" id="ec_date_naissance" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                        <td style="text-align: right;"><label>Ville</label></td>
                        <td><input type="text" name="ville" id="ec_ville" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"></td>
                    </tr>
                    <tr>
                        <td colspan="5"><label>Adresse</label></td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <textarea name="adresse" id="ec_adresse" rows="2" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 3px; resize: vertical;"></textarea>
                        </td>
                    </tr>
                </table>
                <div id="ec_error" style="color: #dc2626; font-size: 11px; margin-top: 5px; display: none;">Raison & & Téléphone Obligatoire</div>
            </form>
        </div>
        
        <!-- Footer -->
        <div style="padding: 10px 15px; border-top: 1px solid #ddd; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" onclick="submitEditClient()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </button>
            <button type="button" onclick="closeEditClientModal()" style="padding: 6px 20px; border: 1px solid #333; background: white; border-radius: 4px; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1f2937" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</div>
