<div id="productModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: white; width: 80%; height: 80%; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <!-- Modal Header -->
        <div style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h2 style="margin: 0; font-size: 16px; color: var(--text);">Sélection Produit</h2>
            <button type="button" onclick="document.getElementById('productModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-secondary);">&times;</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 20px; flex: 1; overflow: hidden; display: flex; flex-direction: column; gap: 15px;">
            <!-- Filters -->
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex-direction: row; align-items: center;">
                    <label style="width: 80px;">Sous Famille</label>
                    <select id="filter-sf" class="form-control" style="width: 150px;">
                        <option value=""></option>
                        @foreach($sousFamilles as $sf) <option value="{{$sf->sousfamilleid}}">{{$sf->sousfamillelibelle}}</option> @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex-direction: row; align-items: center;">
                    <label style="width: 45px;">Famille</label>
                    <select id="filter-f" class="form-control" style="width: 150px;">
                        <option value=""></option>
                        @foreach($familles as $f) <option value="{{$f->familleid}}">{{$f->famillelibelle}}</option> @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex-direction: row; align-items: center;">
                    <label style="width: 40px;">Saison</label>
                    <select id="filter-s" class="form-control" style="width: 150px;">
                        <option value=""></option>
                        @foreach($saisons as $s) <option value="{{$s->category4id}}">{{$s->category4libelle}}</option> @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex-direction: row; align-items: center;">
                    <label style="width: 55px;">Catégorie</label>
                    <select id="filter-c" class="form-control" style="width: 150px;">
                        <option value=""></option>
                        @foreach($categories as $c) <option value="{{$c->categoryid}}">{{$c->categorylibelle}}</option> @endforeach
                    </select>
                </div>
                
                <div style="flex-basis: 100%; height: 0;"></div> <!-- Force new line -->
                
                <div class="form-group" style="flex-direction: row; align-items: center;">
                    <label style="width: 70px;">Marque</label>
                    <select id="filter-m" class="form-control" style="width: 150px;">
                        <option value=""></option>
                        @foreach($marques as $m) <option value="{{$m->category2id}}">{{$m->category2libelle}}</option> @endforeach
                    </select>
                </div>
                
                <button class="btn-save" style="padding: 4px 15px; border-color: var(--border);" onclick="searchProducts()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                </button>

                <div style="margin-left: auto; position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 8px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="filter-search" class="form-control" style="width: 100%; padding-left: 25px;" placeholder="Enter text to search..." onkeyup="if(event.key === 'Enter') searchProducts()">
                </div>
            </div>

            <div class="table-container" style="flex: 1; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width:10%">Code</th>
                            <th style="width:12%">Référence</th>
                            <th style="width:12%">Code à barre</th>
                            <th style="width:20%">Désignation</th>
                            <th style="width:10%">Famille</th>
                            <th style="width:10%">Sous famille</th>
                            <th style="width:8%">Prix TTC</th>
                            <th style="width:8%">STK</th>
                            <th style="width:10%">Fournisseur</th>
                        </tr>
                    </thead>
                    <tbody id="modal-tbody">
                        <tr id="no-data-row">
                            <td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">No data to display</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


