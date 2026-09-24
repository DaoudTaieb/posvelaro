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
                    <input type="text" id="filter-variants" class="form-control" style="width: 100%; padding-left: 25px;" placeholder="Rechercher..." onkeyup="filterVariantsList()">
                </div>
            </div>
            
            <div class="table-container" style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr>
                            <th style="width:20%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Code</th>
                            <th style="width:25%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Couleur</th>
                            <th style="width:15%; text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Taille</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Prix</th>
                            <th style="width:15%; text-align: right; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Stock</th>
                            <th style="width:10%; text-align: center; padding: 8px; border-bottom: 2px solid #e5e7eb; background: white; position: sticky; top: 0; z-index: 10;">Act.</th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody">
                        <!-- Variants here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Bottom controls inside variants modal -->
            <div style="display: flex; justify-content: flex-end; padding-top: 10px; border-top: 1px solid #e5e7eb; margin-top: 10px;">
                <button type="button" class="btn btn-outline" style="padding: 6px 15px;" onclick="document.getElementById('variantsModal').style.display='none'">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentVariants = [];
    
    function openVariantsModal(parentProduct) {
        document.getElementById('variantsModal').style.display = 'flex';
        
        let titlePrice = parseFloat(parentProduct.ttc_vente || parentProduct.ttc) || 0;
        document.getElementById('variantsModalTitle').innerText = `${parentProduct.produitlibelle || parentProduct.designation || 'Produit'} - Prix: ${titlePrice.toFixed(3)}`;
        
        const tbody = document.getElementById('variants-tbody');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">Chargement des variantes...</td></tr>';
        
        // Fetch from the POS controller's variants endpoint which takes produitid
        fetch(`{{ route('vente.caisse.pos.variants') }}?produitid=${parentProduct.produitid}`)
            .then(res => res.json())
            .then(data => {
                currentVariants = data;
                renderVariantsList(currentVariants);
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red; padding: 20px;">Erreur de chargement des variantes</td></tr>';
            });
    }

    // Échappe les valeurs insérées dans innerHTML (texte et attributs)
    function escHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderVariantsList(variants) {
        const tbody = document.getElementById('variants-tbody');
        tbody.innerHTML = '';
        
        if(variants.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">Aucune variante disponible</td></tr>';
            return;
        }

        variants.forEach(v => {
            let price = parseFloat(v.ttc_vente || v.ttc) || 0;
            let stk = parseFloat(v.total_stock) || 0;
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #e5e7eb';
            
            tr.innerHTML = `
                <td style="padding: 6px; color: #111827;">${escHtml(v.produitcode)}</td>
                <td style="padding: 6px; color: #111827;">${escHtml(v.couleurlibelle)}</td>
                <td style="padding: 6px; color: #111827;">${escHtml(v.taillelibelle)}</td>
                <td style="padding: 6px; text-align: right; color: #111827;">${price.toFixed(3)}</td>
                <td style="padding: 6px; text-align: right; color: #111827;">${Number.isInteger(stk) ? stk : stk.toFixed(2)}</td>
                <td style="padding: 6px; text-align: center;">
                    <button type="button" class="select-variant-btn" style="background: var(--primary); color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </button>
                </td>
            `;
            tr.querySelector('.select-variant-btn').addEventListener('click', () => selectVariant(Object.assign({}, v)));
            tbody.appendChild(tr);
        });
    }

    function filterVariantsList() {
        const search = document.getElementById('filter-variants').value.toLowerCase();
        if(!search) {
            renderVariantsList(currentVariants);
            return;
        }
        
        const filtered = currentVariants.filter(v => {
            return (v.produitcode && v.produitcode.toLowerCase().includes(search)) ||
                   (v.couleurlibelle && v.couleurlibelle.toLowerCase().includes(search)) ||
                   (v.taillelibelle && v.taillelibelle.toLowerCase().includes(search));
        });
        
        renderVariantsList(filtered);
    }

    function selectVariant(variantData) {
        // Ensure properties match what selectProduct expects
        if (!variantData.designation && variantData.produitlibelle) {
            variantData.designation = variantData.produitlibelle;
        }
        if (!variantData.taille && variantData.taillelibelle) {
            variantData.taille = variantData.taillelibelle;
        }
        if (!variantData.couleur && variantData.couleurlibelle) {
            variantData.couleur = variantData.couleurlibelle;
        }
        
        // Hide modals
        document.getElementById('variantsModal').style.display = 'none';
        
        // Call the parent page's selectProduct function
        if (typeof selectProduct === 'function') {
            selectProduct(variantData);
        }
    }
</script>
