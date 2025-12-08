(function(){
    'use strict';

    // Show a subtle popup when the user actively tries to close the page.
    // Use beforeunload as primary trigger and also intercept common keyboard shortcuts
    const STORAGE_KEY = 'exitReviewShown_v1';
    let shown = false;
    let formSubmitting = false;

    function createSubtleModal() {
        if (document.getElementById('exitReviewModal')) return;

        const style = document.createElement('style');
        style.textContent = `
        /* Subtle bottom-right modal */
        #exitReviewModal { position: fixed; right: 16px; bottom: 16px; z-index: 9999; display: flex; }
        #exitReviewModal .modal-box { background: white; border-radius: 10px; padding: 0.9rem; width: 320px; box-shadow: 0 8px 24px rgba(0,0,0,0.2); font-size: 14px; }
        #exitReviewModal h3 { margin: 0 0 0.4rem 0; font-size: 15px; }
        #exitReviewModal p { margin: 0 0 0.6rem 0; color:#444 }
        #exitReviewModal label { display:block; margin: 0.35rem 0; }
        #exitReviewModal input, #exitReviewModal textarea, #exitReviewModal select { width: 100%; padding: 0.45rem; border: 1px solid #eee; border-radius: 6px; font-size: 13px }
        #exitReviewModal .modal-actions { display:flex; gap:0.4rem; justify-content:flex-end; margin-top:0.6rem }
        #exitReviewModal .btn { padding: 0.45rem 0.7rem; border-radius: 6px; border: none; cursor: pointer; font-size: 13px }
        #exitReviewModal .btn-primary { background: var(--accent-green, #28a745); color: #fff }
        #exitReviewModal .btn-secondary { background: #6c757d; color: #fff }
        `;
        document.head.appendChild(style);

        const modal = document.createElement('div');
        modal.id = 'exitReviewModal';
        modal.style.display = 'none';

        modal.innerHTML = `
            <div class="modal-box">
                <h3>¿Nos dejas una valoración?</h3>
                <p>Tu feedback nos ayuda. Solo toma 30 segundos.</p>
                <form id="exitReviewForm">
                    <label><input name="name" placeholder="Tu nombre" required></label>
                    <label>
                        <select name="rating">
                            <option value="5">5 — Excelente</option>
                            <option value="4">4 — Muy bien</option>
                            <option value="3">3 — Bien</option>
                            <option value="2">2 — Regular</option>
                            <option value="1">1 — Malo</option>
                        </select>
                    </label>
                    <label><textarea name="comment" rows="2" placeholder="Comentario breve" required></textarea></label>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" id="exitReviewSkip">No, gracias</button>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        const form = document.getElementById('exitReviewForm');
        const skip = document.getElementById('exitReviewSkip');

        skip.addEventListener('click', () => {
            hideModal();
            markShown();
            detachBeforeUnload();
        });

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            if (formSubmitting) return;
            formSubmitting = true;
            const btn = form.querySelector('button[type="submit"]');
            const fd = new FormData(form);
            const payload = {
                name: fd.get('name'),
                rating: fd.get('rating'),
                comment: fd.get('comment'),
                page: window.location.pathname.split('/').pop() || 'unknown'
            };
            btn.disabled = true; btn.textContent = 'Enviando...';
            try {
                const res = await fetch('api/save_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json && json.success) {
                    btn.textContent = 'Gracias';
                    setTimeout(()=>{ hideModal(); markShown(); detachBeforeUnload(); }, 800);
                } else {
                    alert('No se pudo enviar la valoración.');
                    btn.disabled = false; btn.textContent = 'Enviar';
                }
            } catch(err) {
                alert('Error enviando valoración.');
                btn.disabled = false; btn.textContent = 'Enviar';
            } finally {
                formSubmitting = false;
            }
        });

        // clicking outside the modal hides it
        modal.addEventListener('click', (e)=>{
            if (e.target === modal) {
                hideModal();
                markShown();
                detachBeforeUnload();
            }
        });
    }

    function showModal() {
        if (shown) return;
        createSubtleModal();
        const modal = document.getElementById('exitReviewModal');
        if (!modal) return;
        modal.style.display = 'block';
        shown = true;
    }

    function hideModal() {
        const modal = document.getElementById('exitReviewModal');
        if (!modal) return;
        modal.style.display = 'none';
    }

    function markShown(){
        try{ localStorage.setItem(STORAGE_KEY, '1'); }catch(e){}
        shown = true;
    }

    function alreadyShown(){
        try{ return localStorage.getItem(STORAGE_KEY) === '1'; }catch(e){ return false; }
    }

    // beforeunload handler — try to show modal and block unload once
    function onBeforeUnload(e){
        if (alreadyShown() || shown) return;
        showModal();
        // Ask browser to show native confirmation dialog (most browsers ignore custom text)
        e.preventDefault();
        e.returnValue = '';
        return '';
    }

    function detachBeforeUnload(){
        try{ window.removeEventListener('beforeunload', onBeforeUnload); }catch(e){}
    }

    // Intercept common keyboard shortcuts for closing tab/window
    function onKeyDown(e){
        if (alreadyShown() || shown) return;
        const key = (e.key || '').toLowerCase();
        // Ctrl/Cmd+W, Ctrl+F4, Alt+F4
        if ((key === 'w' && (e.ctrlKey || e.metaKey)) || (key === 'f4' && (e.ctrlKey || e.altKey))) {
            e.preventDefault();
            e.stopPropagation();
            showModal();
            return false;
        }
    }

    // Attach listeners
    setTimeout(()=>{
        if (alreadyShown()) return;
        window.addEventListener('beforeunload', onBeforeUnload);
        window.addEventListener('keydown', onKeyDown, true);
    }, 300);

})();
