<div id="productModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; width: 92%; max-width: 1300px; height: 85%; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <!-- Modal Header -->
        <div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; flex-shrink: 0;">
            <h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #0f172a;">Sélection Produit</h2>
            <button type="button" onclick="document.getElementById('productModal').style.display='none'" style="background: none; border: 1px solid #e2e8f0; border-radius: 6px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.15s;">&times;</button>
        </div>

        <!-- Filters -->
        <div style="padding: 14px 24px; border-bottom: 1px solid #e2e8f0; background: #fafbfc; flex-shrink: 0;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Sous Famille</label>
                    <select id="filter-sf" class="form-control" style="width: 150px; height: 34px; font-size: 12px; border-radius: 6px;">
                        <option value="">Toutes</option>
                        @foreach($sousFamilles as $sf) <option value="{{$sf->sousfamilleid}}">{{$sf->sousfamillelibelle}}</option> @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Famille</label>
                    <select id="filter-f" class="form-control" style="width: 150px; height: 34px; font-size: 12px; border-radius: 6px;">
                        <option value="">Toutes</option>
                        @foreach($familles as $f) <option value="{{$f->familleid}}">{{$f->famillelibelle}}</option> @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Saison</label>
                    <select id="filter-s" class="form-control" style="width: 140px; height: 34px; font-size: 12px; border-radius: 6px;">
                        <option value="">Toutes</option>
                        @foreach($saisons as $s) <option value="{{$s->category4id}}">{{$s->category4libelle}}</option> @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Catégorie</label>
                    <select id="filter-c" class="form-control" style="width: 140px; height: 34px; font-size: 12px; border-radius: 6px;">
                        <option value="">Toutes</option>
                        @foreach($categories as $c) <option value="{{$c->categoryid}}">{{$c->categorylibelle}}</option> @endforeach
                    </select>
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Marque</label>
                    <select id="filter-m" class="form-control" style="width: 140px; height: 34px; font-size: 12px; border-radius: 6px;">
                        <option value="">Toutes</option>
                        @foreach($marques as $m) <option value="{{$m->category2id}}">{{$m->category2libelle}}</option> @endforeach
                    </select>
                </div>

                <button type="button" onclick="searchProducts()" style="height: 34px; padding: 0 14px; background: var(--primary, #4f46e5); color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filtrer
                </button>

                <div style="margin-left: auto; position: relative; width: 220px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 9px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="filter-search" class="form-control" style="width: 100%; height: 34px; padding-left: 32px; font-size: 12px; border-radius: 6px;" placeholder="Rechercher..." onkeyup="if(event.key === 'Enter') searchProducts()">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div style="flex: 1; overflow-y: auto; padding: 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; table-layout: fixed;">
                <thead>
                    <tr style="background: #f1f5f9; position: sticky; top: 0; z-index: 1;">
                        <th style="width: 6%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Code</th>
                        <th style="width: 8%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Référence</th>
                        <th style="width: 9%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Code à barre</th>
                        <th style="width: 25%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0;">Désignation</th>
                        <th style="width: 6%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Taille</th>
                        <th style="width: 6%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Couleur</th>
                        <th style="width: 8%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Famille</th>
                        <th style="width: 9%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Sous famille</th>
                        <th style="width: 8%; padding: 10px 12px; text-align: right; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Prix TTC</th>
                        <th style="width: 6%; padding: 10px 12px; text-align: center; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Stock</th>
                        <th style="width: 8%; padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Fournisseur</th>
                    </tr>
                </thead>
                <tbody id="modal-tbody">
                    <tr id="no-data-row">
                        <td colspan="11" style="padding: 60px 20px; text-align: center; color: #94a3b8; font-weight: 500; font-size: 13px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display: block; margin: 0 auto 10px;">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            Utilisez les filtres ou la recherche pour afficher des produits
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div id="pagination-container" style="display: flex; justify-content: flex-end; align-items: center; padding: 10px 20px; border-top: 1px solid #e2e8f0; background: #fff; gap: 5px;">
        </div>
    </div>
</div>
