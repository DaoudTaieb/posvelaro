<!-- CHEQUE CADEAU MODAL -->
<div id="chequeCadeauModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1020; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 500px; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); font-family: Arial, sans-serif;">
        <div style="padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 14px; font-weight: bold; color: #333;">Type Cheque Cadeau</h2>
            <button type="button" onclick="closeChequeCadeauModal()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #666; font-weight: bold;">&times;</button>
        </div>
        <div style="padding: 20px;">
            <div style="border: 1px solid #ddd; padding: 15px; display: flex; align-items: center; justify-content: center; gap: 20px; background: #f9f9f9; border-radius: 4px; margin-bottom: 15px;">
                <label style="font-weight: normal; font-size: 13px;">Montant</label>
                <input type="number" step="0.001" id="chequeCadeauMontant" style="width: 150px; padding: 8px; border: 1px solid #ccc; font-weight: bold; font-size: 14px; text-align: center;">
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                @foreach($typeChequeCadeaus as $type)
                    <button type="button" onclick="validerChequeCadeau({{ $type->typechequecadeauid }})" style="background: white; border: 1px solid #2c3e50; padding: 10px 20px; min-width: 100px; cursor: pointer; border-radius: 2px; font-size: 12px;">
                        {{ $type->libelle }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
