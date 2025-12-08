// Minimal reviews frontend: fetch and submit reviews
(function(){
    'use strict';

    async function fetchReviews(page){
        try{
            const res = await fetch('api/get_reviews.php?page='+encodeURIComponent(page));
            const json = await res.json();
            return json.reviews || [];
        }catch(e){
            return [];
        }
    }

    async function submitReview(payload){
        const res = await fetch('api/save_review.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        return res.json();
    }

    function renderStars(rating){
        let s = '';
        for(let i=1;i<=5;i++){
            s += (i<=rating)? '★' : '☆';
        }
        return s;
    }

    // Public API: attach to a container
    window.ReviewsWidget = {
        async init(containerSelector, page, options){
            const container = document.querySelector(containerSelector);
            if(!container) return;

            const list = document.createElement('div');
            list.className = 'reviews-list';

            // Append review list
            container.appendChild(list);

            // Optionally append the form. Pass { showForm: false } to hide it.
            let form = null;
            const showForm = !(options && options.showForm === false);
            if (showForm) {
                form = document.createElement('form');
                form.className = 'reviews-form';
                form.innerHTML = `
                    <h3>Leave a review</h3>
                    <label>Name <input name="name" required></label>
                    <label>Rating
                        <select name="rating">
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </label>
                    <label>Comment <textarea name="comment" required></textarea></label>
                    <div style="margin-top:.5rem"><button type="submit" class="cta-button">Submit Review</button></div>
                `;
                container.appendChild(form);
            }

            async function load(){
                list.innerHTML = '<p class="muted">Loading reviews…</p>';
                const reviews = await fetchReviews(page);
                if(!reviews || reviews.length===0){
                    list.innerHTML = '<p class="muted">No reviews yet. Be the first to leave feedback!</p>';
                    return;
                }
                list.innerHTML = '';
                const avg = (reviews.reduce((s,r)=>s+r.rating,0)/reviews.length).toFixed(1);
                const header = document.createElement('div');
                header.className = 'reviews-header';
                header.innerHTML = `<strong>Average rating:</strong> ${avg} / 5 (${reviews.length})`;
                list.appendChild(header);

                reviews.slice(0,10).forEach(r=>{
                    const el = document.createElement('div');
                    el.className = 'review-item';
                    const nameEl = document.createElement('strong');
                    nameEl.textContent = r.name;
                    const ratingEl = document.createElement('span');
                    ratingEl.className = 'review-rating';
                    ratingEl.textContent = renderStars(r.rating);
                    const dateEl = document.createElement('span');
                    dateEl.className = 'review-date';
                    dateEl.textContent = new Date(r.created_at).toLocaleDateString();
                    const metaDiv = document.createElement('div');
                    metaDiv.className = 'review-meta';
                    metaDiv.appendChild(nameEl);
                    metaDiv.appendChild(document.createTextNode(' '));
                    metaDiv.appendChild(ratingEl);
                    metaDiv.appendChild(document.createTextNode(' '));
                    metaDiv.appendChild(dateEl);
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'review-comment';
                    commentDiv.textContent = r.comment;
                    el.appendChild(metaDiv);
                    el.appendChild(commentDiv);
                    list.appendChild(el);
                });
            }

            if (form) {
                form.addEventListener('submit', async function(e){
                    e.preventDefault();
                    const fd = new FormData(form);
                    const payload = {
                        name: fd.get('name'),
                        rating: fd.get('rating'),
                        comment: fd.get('comment'),
                        page: page
                    };
                    const btn = form.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                    const res = await submitReview(payload);
                    btn.disabled = false;
                    btn.textContent = 'Submit Review';
                    if(res && res.success){
                        form.reset();
                        await load();
                    } else {
                        alert('Could not save review: ' + (res.error || 'unknown'));
                    }
                });
            }

            load();
        }
    };
})();
