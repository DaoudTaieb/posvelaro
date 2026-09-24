{{--
    Notifications (toasts) et boîte de confirmation intégrées à l'application.
    Remplacent les boîtes de dialogue natives du navigateur (alerte / confirmation), à ne plus utiliser.

    notifier(message, type = auto, dureeMs)      type : success | error | warning | info
    await confirmer(message, { titre, okLabel, cancelLabel, danger })   -> true / false
    confirmerFormulaire(event, form, message, options)                  -> pour onsubmit / onclick d'un bouton submit
--}}
<style>
    #ui-toasts { position: fixed; top: 16px; right: 16px; z-index: 9000; display: flex; flex-direction: column; gap: 10px; max-width: 420px; pointer-events: none; }
    .ui-toast { pointer-events: auto; display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 10px; background: #ffffff; color: #0f172a; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18); border-left: 5px solid #64748b; font: 500 14px/1.4 'Inter', 'Segoe UI', Arial, sans-serif; animation: ui-toast-in .22s ease-out; }
    .ui-toast.leaving { animation: ui-toast-out .2s ease-in forwards; }
    .ui-toast-success { border-left-color: #10b981; } .ui-toast-success .ui-toast-icon { color: #10b981; }
    .ui-toast-error   { border-left-color: #ef4444; } .ui-toast-error .ui-toast-icon   { color: #ef4444; }
    .ui-toast-warning { border-left-color: #f59e0b; } .ui-toast-warning .ui-toast-icon { color: #f59e0b; }
    .ui-toast-info    { border-left-color: #0ea5e9; } .ui-toast-info .ui-toast-icon    { color: #0ea5e9; }
    .ui-toast-icon { flex: 0 0 auto; width: 20px; height: 20px; margin-top: 1px; }
    .ui-toast-text { flex: 1 1 auto; white-space: pre-line; word-break: break-word; }
    .ui-toast-close { flex: 0 0 auto; border: none; background: none; color: #94a3b8; font-size: 18px; line-height: 1; cursor: pointer; padding: 0 2px; }
    .ui-toast-close:hover { color: #0f172a; }
    @keyframes ui-toast-in  { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: none; } }
    @keyframes ui-toast-out { to { opacity: 0; transform: translateX(24px); } }

    #ui-confirm { position: fixed; inset: 0; z-index: 9100; display: none; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
    #ui-confirm.open { display: flex; }
    .ui-confirm-card { width: min(440px, calc(100vw - 32px)); background: #fff; border-radius: 14px; box-shadow: 0 25px 60px rgba(15, 23, 42, 0.3); overflow: hidden; animation: ui-confirm-in .18s ease-out; }
    .ui-confirm-head { display: flex; align-items: center; gap: 12px; padding: 18px 22px 0; }
    .ui-confirm-badge { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #e0f2fe; color: #0284c7; flex: 0 0 auto; }
    .ui-confirm-card.danger .ui-confirm-badge { background: #fee2e2; color: #dc2626; }
    .ui-confirm-title { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
    .ui-confirm-message { padding: 12px 22px 22px; color: #334155; font-size: 14px; line-height: 1.55; white-space: pre-line; }
    .ui-confirm-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
    .ui-confirm-btn { min-width: 96px; height: 38px; padding: 0 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: 1px solid #cbd5e1; background: #fff; color: #334155; }
    .ui-confirm-btn:hover { background: #f1f5f9; }
    .ui-confirm-btn.ok { border-color: transparent; background: #0ea5e9; color: #fff; }
    .ui-confirm-btn.ok:hover { background: #0284c7; }
    .ui-confirm-card.danger .ui-confirm-btn.ok { background: #dc2626; }
    .ui-confirm-card.danger .ui-confirm-btn.ok:hover { background: #b91c1c; }
    .ui-confirm-btn:focus-visible { outline: 3px solid #bae6fd; outline-offset: 1px; }
    @keyframes ui-confirm-in { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
</style>

<div id="ui-toasts" aria-live="polite"></div>

<div id="ui-confirm" role="dialog" aria-modal="true" aria-labelledby="ui-confirm-title">
    <div class="ui-confirm-card">
        <div class="ui-confirm-head">
            <div class="ui-confirm-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
            <h3 class="ui-confirm-title" id="ui-confirm-title">Confirmation</h3>
        </div>
        <div class="ui-confirm-message" id="ui-confirm-message"></div>
        <div class="ui-confirm-actions">
            <button type="button" class="ui-confirm-btn" id="ui-confirm-cancel">Annuler</button>
            <button type="button" class="ui-confirm-btn ok" id="ui-confirm-ok">OK</button>
        </div>
    </div>
</div>

<script>
(function () {
    const ICONS = {
        success: '<svg class="ui-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        error:   '<svg class="ui-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        warning: '<svg class="ui-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        info:    '<svg class="ui-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };

    // Devine le type d'après le texte quand l'appelant ne le précise pas
    function devinerType(message) {
        const m = String(message).toLowerCase();
        if (/succès|succes|enregistr|validé|envoyé avec|effectué|ajouté|modifié|supprimé/.test(m)) return 'success';
        if (/erreur|impossible|introuvable|invalide|refus|échec|echec|incorrect|dépasse|depasse|déjà|deja|non autoris/.test(m)) return 'error';
        return 'warning';
    }

    window.notifier = function (message, type, dureeMs) {
        const conteneur = document.getElementById('ui-toasts');
        if (!conteneur) { console.warn('notifier():', message); return; }
        type = type || devinerType(message);
        if (!ICONS[type]) type = 'info';

        const toast = document.createElement('div');
        toast.className = 'ui-toast ui-toast-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.innerHTML = ICONS[type] + '<div class="ui-toast-text"></div><button type="button" class="ui-toast-close" aria-label="Fermer">&times;</button>';
        toast.querySelector('.ui-toast-text').textContent = String(message ?? '');

        const fermer = () => {
            if (toast.classList.contains('leaving')) return;
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 200);
        };
        toast.querySelector('.ui-toast-close').onclick = fermer;
        conteneur.appendChild(toast);

        // Les toasts les plus anciens disparaissent au-delà de 5
        while (conteneur.children.length > 5) conteneur.firstElementChild.remove();

        const duree = dureeMs ?? (type === 'error' ? 6500 : 4000);
        if (duree > 0) setTimeout(fermer, duree);
        return toast;
    };

    let confirmationEnCours = null;

    window.confirmer = function (message, options) {
        options = options || {};
        const overlay = document.getElementById('ui-confirm');
        if (!overlay) return Promise.resolve(false);

        // Une seule confirmation à la fois : la précédente est annulée
        if (confirmationEnCours) confirmationEnCours(false);

        const card = overlay.querySelector('.ui-confirm-card');
        const btnOk = document.getElementById('ui-confirm-ok');
        const btnCancel = document.getElementById('ui-confirm-cancel');
        document.getElementById('ui-confirm-title').textContent = options.titre || 'Confirmation';
        document.getElementById('ui-confirm-message').textContent = String(message ?? '');
        btnOk.textContent = options.okLabel || 'Confirmer';
        btnCancel.textContent = options.cancelLabel || 'Annuler';
        card.classList.toggle('danger', !!options.danger);

        const precedent = document.activeElement;
        overlay.classList.add('open');
        setTimeout(() => btnOk.focus(), 0);

        return new Promise(resolve => {
            const terminer = (valeur) => {
                overlay.classList.remove('open');
                btnOk.onclick = btnCancel.onclick = null;
                document.removeEventListener('keydown', surTouche, true);
                confirmationEnCours = null;
                if (precedent && typeof precedent.focus === 'function') precedent.focus();
                resolve(valeur);
            };
            const surTouche = (e) => {
                if (e.key === 'Escape') { e.preventDefault(); e.stopPropagation(); terminer(false); }
                else if (e.key === 'Enter') { e.preventDefault(); e.stopPropagation(); terminer(true); }
            };
            confirmationEnCours = terminer;
            btnOk.onclick = () => terminer(true);
            btnCancel.onclick = () => terminer(false);
            overlay.onclick = (e) => { if (e.target === overlay) terminer(false); };
            document.addEventListener('keydown', surTouche, true);
        });
    };

    // Pour les formulaires HTML : onsubmit="return confirmerFormulaire(event, this, 'Message ?')"
    // ou sur un bouton submit : onclick="return confirmerFormulaire(event, this.form, 'Message ?')"
    window.confirmerFormulaire = function (event, form, message, options) {
        // Seconde soumission, déjà confirmée : on la laisse passer (surtout ne pas appeler preventDefault)
        if (form && form.dataset.uiConfirme === '1') { delete form.dataset.uiConfirme; return true; }
        if (event) event.preventDefault();
        window.confirmer(message, Object.assign({ danger: /supprim|clôtur|clotur|définitiv|definitiv/i.test(message) }, options || {}))
            .then(ok => {
                if (!ok || !form) return;
                form.dataset.uiConfirme = '1';
                if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
            });
        return false;
    };
})();
</script>
