@extends('layouts.app')
@section('title', 'Imprimantes et douchettes')

@section('content')
<style>
    .peri-grille { padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; align-items: end; }
    .peri-entete { padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .peri-entete h2 { margin: 0; font-size: 15px; font-weight: 600; }
    .peri-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .peri-aide { font-size: 11px; color: var(--text-muted); }
    .peri-alerte { font-size: 12px; color: #b45309; display: none; }
    .peri-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .peri-table th, .peri-table td { padding: 8px 12px; border-bottom: 1px solid var(--border); text-align: left; }
    .peri-table th { font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; }
    .peri-etat { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
    .peri-etat.pret { background: #dcfce7; color: #166534; }
    .peri-etat.attention { background: #fef3c7; color: #92400e; }
    .peri-etat.erreur { background: #fee2e2; color: #991b1b; }
    .peri-resultat { margin-top: 12px; padding: 12px 14px; border-radius: 8px; font-size: 13px; display: none; line-height: 1.6; }
    .peri-resultat.ok { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .peri-resultat.ko { background: #fef2f2; border: 1px solid #fecaca; }
    .peri-liste { margin: 0; padding-left: 18px; font-size: 13px; line-height: 1.7; }
</style>

<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Imprimantes et douchettes</h1>
            <p class="page-subtitle">Imprimante thermique et tiroir-caisse de chaque caisse, et test du lecteur de codes-barres. En mode Windows ou réseau, le ticket part directement à l'imprimante à la validation, sans fenêtre d'impression.</p>
        </div>
    </div>

    {{-- Noms exacts des imprimantes Windows du poste, proposés dans les champs « Imprimante Windows » --}}
    <datalist id="imprimantesWindows"></datalist>

    @forelse($caisses as $caisse)
        @php $c = $configs[$caisse->caisseid]; @endphp
        <div class="content-card" style="margin-bottom: 20px;">
            <div class="peri-entete">
                <h2>{{ $caisse->libelle }} <span style="color: var(--text-muted); font-weight: 400;">(caisse n° {{ $caisse->numero }})</span></h2>
                <div class="peri-actions">
                    <form method="POST" action="{{ route('parametre.impression.test', $caisse->caisseid) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-outline" {{ $c['mode'] === 'navigateur' ? 'disabled' : '' }}>Imprimer un ticket de test</button>
                    </form>
                    <form method="POST" action="{{ route('parametre.impression.tiroir', $caisse->caisseid) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-outline" {{ $c['mode'] === 'navigateur' ? 'disabled' : '' }}>Ouvrir le tiroir-caisse</button>
                    </form>
                </div>
            </div>
            <form method="POST" action="{{ route('parametre.impression.update', $caisse->caisseid) }}" class="impression-form peri-grille">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Mode d'impression</label>
                    <select name="mode" class="form-control">
                        @foreach($modes as $valeur => $libelle)
                            <option value="{{ $valeur }}" {{ $c['mode'] === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Imprimante Windows (nom exact)</label>
                    <input type="text" name="imprimante" value="{{ $c['imprimante'] }}" class="form-control champ-imprimante" list="imprimantesWindows" placeholder="EPSON TM-T20II Receipt" autocomplete="off">
                    <span class="peri-aide">Choisissez dans la liste. Réglage partagé avec le logiciel de bureau.</span>
                    <span class="peri-alerte">Cette imprimante n'est pas installée sur ce poste.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Imprimante réseau (IP:port)</label>
                    <input type="text" name="reseau" value="{{ $c['reseau'] }}" class="form-control" placeholder="192.168.1.50:9100">
                </div>
                <div class="form-group">
                    <label class="form-label">Caractères par ligne</label>
                    <select name="colonnes" class="form-control">
                        @foreach([48 => '48 (papier 80 mm)', 42 => '42 (80 mm, police large)', 32 => '32 (papier 58 mm)'] as $n => $libelle)
                            <option value="{{ $n }}" {{ (int) $c['colonnes'] === $n ? 'selected' : '' }}>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <label style="display: flex; gap: 8px; align-items: center; font-size: 13px;">
                    <input type="checkbox" name="accents" value="1" {{ $c['accents'] ? 'checked' : '' }}> Imprimer les accents (page de code PC858)
                </label>
                <label style="display: flex; gap: 8px; align-items: center; font-size: 13px;">
                    <input type="checkbox" name="tiroir" value="1" {{ $c['tiroir'] ? 'checked' : '' }}> Ouvrir le tiroir-caisse à l'encaissement en espèces
                </label>
                <div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    @empty
        <div class="content-card"><p style="padding: 20px;">Aucune caisse sur ce site.</p></div>
    @endforelse

    {{-- Imprimantes installées sur le poste qui fait tourner Golden Pos --}}
    <div class="content-card" style="margin-bottom: 20px;">
        <div class="peri-entete">
            <h2>Imprimantes installées sur ce poste</h2>
            <button type="button" class="btn btn-outline" id="btnActualiserImprimantes">Actualiser</button>
        </div>
        <div style="padding: 0 0 8px;">
            <table class="peri-table">
                <thead><tr><th>Nom (à recopier dans le réglage)</th><th>Pilote</th><th>Port</th><th>État</th></tr></thead>
                <tbody id="listeImprimantes"><tr><td colspan="4" class="peri-aide">Recherche des imprimantes…</td></tr></tbody>
            </table>
        </div>
    </div>

    {{-- Test de la douchette --}}
    <div class="content-card" style="margin-bottom: 20px;">
        <div class="peri-entete">
            <h2>Test de la douchette (lecteur de codes-barres)</h2>
        </div>
        <div style="padding: 20px;">
            <label class="form-label" for="testDouchette">Cliquez dans le champ puis scannez un article, une carte fidélité ou un bon d'achat</label>
            <input type="text" id="testDouchette" class="form-control" autocomplete="off" style="max-width: 420px; font-family: monospace; font-size: 16px;" placeholder="Scannez ici…">
            <div id="resultatDouchette" class="peri-resultat"></div>
        </div>
    </div>

    {{-- Aide d'installation --}}
    <div class="content-card" style="margin-bottom: 20px;">
        <div class="peri-entete"><h2>Installation</h2></div>
        <div style="padding: 16px 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div>
                <strong style="font-size: 13px;">Imprimante thermique</strong>
                <ul class="peri-liste">
                    <li><b>USB</b> : installer le pilote du fabricant sur le poste Golden Pos, puis choisir le mode « Imprimante Windows » et le nom dans la liste.</li>
                    <li><b>Réseau (Ethernet)</b> : donner une adresse IP fixe à l'imprimante, puis choisir le mode « Imprimante réseau » (port 9100).</li>
                    <li>Papier 80 mm : 48 caractères ; papier 58 mm : 32 caractères.</li>
                    <li>Accents illisibles sur le ticket de test : décocher « Imprimer les accents ».</li>
                </ul>
            </div>
            <div>
                <strong style="font-size: 13px;">Tiroir-caisse</strong>
                <ul class="peri-liste">
                    <li>Le tiroir se branche sur la prise RJ11 (« DK » ou « Cash drawer ») de l'imprimante, pas sur le PC.</li>
                    <li>Cocher « Ouvrir le tiroir-caisse à l'encaissement » ; le bouton « Ouvrir le tiroir-caisse » vérifie le branchement.</li>
                </ul>
            </div>
            <div>
                <strong style="font-size: 13px;">Douchette</strong>
                <ul class="peri-liste">
                    <li>Brancher en USB : elle fonctionne comme un clavier, sans pilote.</li>
                    <li>Elle doit envoyer « Entrée » (ou Tab) après le code : c'est le réglage d'usine de presque tous les modèles.</li>
                    <li>Si le test affiche « &amp;é"'( » au lieu de « 12345 », la douchette est en clavier QWERTY : Golden Pos corrige automatiquement, mais il vaut mieux scanner le code « Clavier français / AZERTY » du manuel de la douchette.</li>
                    <li>En caisse, le code est lu même si le curseur n'est plus dans le champ « Réf ».</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const urlImprimantes = @json(route('parametre.impression.imprimantes'));
    const urlDouchette = @json(route('parametre.impression.douchette'));
    const etats = {
        pret: ['Prête', 'pret'], impression: ['Impression en cours', 'pret'], prechauffage: ['Préchauffage', 'attention'],
        arrete: ['Arrêtée', 'erreur'], hors_ligne: ['Hors ligne', 'erreur'], inconnu: ['Inconnu', 'attention'], autre: ['Autre', 'attention'],
    };
    const echapper = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    let nomsInstalles = null;

    function verifierChamps() {
        document.querySelectorAll('.champ-imprimante').forEach(champ => {
            const alerte = champ.parentElement.querySelector('.peri-alerte');
            const mode = champ.form.querySelector('[name="mode"]').value;
            const absent = nomsInstalles !== null && mode === 'windows' && champ.value.trim() !== '' && !nomsInstalles.includes(champ.value.trim());
            alerte.style.display = absent ? 'block' : 'none';
        });
    }

    function chargerImprimantes(actualiser) {
        const corps = document.getElementById('listeImprimantes');
        corps.innerHTML = '<tr><td colspan="4" class="peri-aide">Recherche des imprimantes…</td></tr>';
        fetch(urlImprimantes + (actualiser ? '?actualiser=1' : ''), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (!d.disponible) {
                    corps.innerHTML = `<tr><td colspan="4" class="peri-aide">${echapper(d.message)}</td></tr>`;
                    return;
                }
                nomsInstalles = d.imprimantes.map(i => i.nom);
                document.getElementById('imprimantesWindows').innerHTML = d.imprimantes.map(i => `<option value="${echapper(i.nom)}"></option>`).join('');
                corps.innerHTML = d.imprimantes.length === 0
                    ? '<tr><td colspan="4" class="peri-aide">Aucune imprimante installée sur ce poste.</td></tr>'
                    : d.imprimantes.map(i => {
                        const [libelle, classe] = etats[i.etat] || etats.inconnu;
                        return `<tr><td><b>${echapper(i.nom)}</b>${i.defaut ? ' <span class="peri-aide">(par défaut)</span>' : ''}</td>`
                            + `<td>${echapper(i.pilote)}</td><td>${echapper(i.port)}</td>`
                            + `<td><span class="peri-etat ${classe}">${libelle}</span></td></tr>`;
                    }).join('');
                verifierChamps();
            })
            .catch(() => { corps.innerHTML = '<tr><td colspan="4" class="peri-aide">Liste des imprimantes indisponible.</td></tr>'; });
    }

    document.getElementById('btnActualiserImprimantes').addEventListener('click', () => chargerImprimantes(true));
    document.querySelectorAll('.champ-imprimante, .impression-form [name="mode"]').forEach(el => el.addEventListener('input', verifierChamps));
    document.querySelectorAll('.impression-form [name="mode"]').forEach(el => el.addEventListener('change', verifierChamps));
    chargerImprimantes(false);

    // Test de la douchette : vitesse de frappe (douchette ou clavier), suffixe et résultat en caisse
    const champ = document.getElementById('testDouchette');
    const zone = document.getElementById('resultatDouchette');
    let frappes = [];

    champ.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== 'Tab') {
            if (e.key.length === 1) {
                if (this.value === '') frappes = [];
                frappes.push(performance.now());
            }
            return;
        }
        e.preventDefault();
        const code = this.value;
        if (code.trim() === '') return;
        const ecarts = frappes.slice(1).map((t, i) => t - frappes[i]);
        const moyenne = ecarts.length ? ecarts.reduce((a, b) => a + b, 0) / ecarts.length : 0;
        const suffixe = e.key === 'Enter' ? 'Entrée' : 'Tab';
        const parDouchette = ecarts.length >= 2 && moyenne < 50;

        fetch(urlDouchette + '?code=' + encodeURIComponent(code), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                const types = { produit: 'Article', client: 'Carte fidélité', bon: "Bon d'achat" };
                const lignes = [
                    `<b>Code reçu :</b> <code>${echapper(code)}</code> (${code.length} caractères, suivi de « ${suffixe} »)`,
                    `<b>Saisie :</b> ${parDouchette ? `douchette (${moyenne.toFixed(0)} ms entre deux caractères)` : (ecarts.length >= 2 ? `clavier ou douchette lente (${moyenne.toFixed(0)} ms entre deux caractères)` : 'collage ou saisie trop courte pour mesurer')}`,
                ];
                if (d.qwerty) {
                    lignes.push(`<b>Clavier :</b> la douchette est réglée en QWERTY. ${d.corrige ? `Golden Pos a corrigé le code en <code>${echapper(d.code)}</code>.` : ''} Pour un réglage propre, scannez le code « Clavier français / AZERTY » du manuel de la douchette.`);
                }
                lignes.push(d.type
                    ? `<b>En caisse :</b> ${types[d.type]} reconnu : ${echapper(d.libelle)}`
                    : `<b>En caisse :</b> ${echapper(d.libelle || 'code inconnu')}`);
                zone.className = 'peri-resultat ' + (d.type ? 'ok' : 'ko');
                zone.innerHTML = lignes.join('<br>');
                zone.style.display = 'block';
            })
            .catch(() => {
                zone.className = 'peri-resultat ko';
                zone.textContent = 'Erreur lors de la vérification du code.';
                zone.style.display = 'block';
            })
            .finally(() => { champ.value = ''; frappes = []; champ.focus(); });
    });
})();
</script>
@endsection
