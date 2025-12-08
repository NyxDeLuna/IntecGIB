(function(){
    'use strict';

    /**
     * Floating Review Button
     * Creates a floating action button (FAB) at the bottom-left of the page
     * Click to open a modal with the review form
     */

    function createFloatingButton() {
        // Create button styles
        const style = document.createElement('style');
        style.textContent = `
        /* Floating Action Button */
        #floatingReviewBtn {
            position: fixed;
            left: 20px;
            bottom: 20px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--accent-green, #6aaa64) 0%, #5a9b6a 100%);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(106, 170, 100, 0.4);
            z-index: 999;
            transition: all 0.3s ease;
            outline: none;
        }
        #floatingReviewBtn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(106, 170, 100, 0.6);
        }
        #floatingReviewBtn:active {
            transform: scale(0.95);
        }

        /* Review Modal Overlay */
        #reviewModalOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        #reviewModalOverlay.active {
            display: flex;
        }

        /* Review Modal */
        #reviewModal {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.4s ease;
            position: relative;
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Close button */
        #reviewModal .close-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }
        #reviewModal .close-btn:hover {
            color: #333;
        }

        /* Modal header */
        #reviewModal h2 {
            margin: 0 0 0.5rem 0;
            color: #1a1a1a;
            font-size: 1.5rem;
        }
        #reviewModal .modal-subtitle {
            color: #666;
            margin: 0 0 1.5rem 0;
            font-size: 0.95rem;
        }

        /* Form styles */
        #reviewForm {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        #reviewForm .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        #reviewForm label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        #reviewForm input,
        #reviewForm textarea,
        #reviewForm select {
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        #reviewForm input:focus,
        #reviewForm textarea:focus,
        #reviewForm select:focus {
            outline: none;
            border-color: var(--accent-green, #6aaa64);
            box-shadow: 0 0 8px rgba(106, 170, 100, 0.2);
        }
        #reviewForm textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Form actions */
        .form-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 0.5rem;
        }
        .form-actions button {
            flex: 1;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        .form-actions .btn-submit {
            background: linear-gradient(135deg, var(--accent-green, #6aaa64) 0%, #5a9b6a 100%);
            color: white;
        }
        .form-actions .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 170, 100, 0.3);
        }
        .form-actions .btn-cancel {
            background: #f0f2f5;
            color: #333;
        }
        .form-actions .btn-cancel:hover {
            background: #e8ecf1;
        }
        .form-actions button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Stars rating */
        .star-rating {
            display: flex;
            gap: 0.5rem;
            font-size: 1.8rem;
        }
        .star-rating .star {
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s ease;
        }
        .star-rating .star:hover,
        .star-rating .star.active {
            color: #f6b042;
            transform: scale(1.2);
        }

        /* Success message */
        .success-message {
            text-align: center;
            padding: 2rem;
        }
        .success-message .success-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .success-message h3 {
            margin: 0 0 0.5rem 0;
            color: #1a1a1a;
        }
        .success-message p {
            margin: 0;
            color: #666;
        }

        /* Email field (optional) */
        #reviewForm .optional-note {
            font-size: 0.85rem;
            color: #999;
            margin-top: -0.3rem;
        }
        `;
        document.head.appendChild(style);
    }

    function createFloatingButtonHTML() {
        // Create button container
        const button = document.createElement('button');
        button.id = 'floatingReviewBtn';
        button.title = 'Dejar una valoración';
        button.innerHTML = '⭐';
        button.type = 'button';
        document.body.appendChild(button);

        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'reviewModalOverlay';
        overlay.innerHTML = `
            <div id="reviewModal">
                <button type="button" class="close-btn" id="closeModalBtn">&times;</button>
                <h2>Valoración</h2>
                <p class="modal-subtitle">¿Qué te pareció? Tu feedback nos ayuda a mejorar.</p>
                
                <form id="reviewForm">
                    <div class="form-group">
                        <label for="reviewName">Nombre *</label>
                        <input type="text" id="reviewName" name="name" placeholder="Tu nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="reviewEmail">Email (opcional)</label>
                        <input type="email" id="reviewEmail" name="email" placeholder="tu@email.com">
                        <span class="optional-note">No se compartirá públicamente</span>
                    </div>

                    <div class="form-group">
                        <label>Calificación *</label>
                        <div class="star-rating" id="starRating">
                            <span class="star" data-rating="1">★</span>
                            <span class="star" data-rating="2">★</span>
                            <span class="star" data-rating="3">★</span>
                            <span class="star" data-rating="4">★</span>
                            <span class="star" data-rating="5">★</span>
                        </div>
                        <input type="hidden" id="reviewRating" name="rating" required>
                    </div>

                    <div class="form-group">
                        <label for="reviewComment">Comentario *</label>
                        <textarea id="reviewComment" name="comment" placeholder="Cuéntanos tu experiencia..." required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelReviewBtn">Cerrar</button>
                        <button type="submit" class="btn-submit" id="submitReviewBtn">Enviar</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    function initializeEvents() {
        const button = document.getElementById('floatingReviewBtn');
        const overlay = document.getElementById('reviewModalOverlay');
        const closeBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelReviewBtn');
        const form = document.getElementById('reviewForm');
        const starRating = document.getElementById('starRating');
        const ratingInput = document.getElementById('reviewRating');

        // Open modal
        button.addEventListener('click', () => {
            overlay.classList.add('active');
        });

        // Close modal
        const closeModal = () => {
            overlay.classList.remove('active');
            form.reset();
            document.querySelectorAll('#starRating .star').forEach(s => s.classList.remove('active'));
            ratingInput.value = '';
        };

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Close on overlay click (outside modal)
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal();
            }
        });

        // Star rating
        starRating.addEventListener('click', (e) => {
            if (e.target.classList.contains('star')) {
                const rating = e.target.dataset.rating;
                ratingInput.value = rating;
                
                // Update star display
                document.querySelectorAll('#starRating .star').forEach(s => {
                    s.classList.toggle('active', parseInt(s.dataset.rating) <= rating);
                });
            }
        });

        // Star hover effect
        starRating.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('star')) {
                const rating = e.target.dataset.rating;
                document.querySelectorAll('#starRating .star').forEach(s => {
                    s.style.opacity = parseInt(s.dataset.rating) <= rating ? '1' : '0.3';
                });
            }
        });

        starRating.addEventListener('mouseout', () => {
            document.querySelectorAll('#starRating .star').forEach(s => {
                s.style.opacity = '1';
            });
        });

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!ratingInput.value) {
                alert('Por favor selecciona una calificación');
                return;
            }

            const submitBtn = document.getElementById('submitReviewBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            const payload = {
                name: document.getElementById('reviewName').value.trim(),
                email: document.getElementById('reviewEmail').value.trim() || null,
                rating: parseInt(ratingInput.value),
                comment: document.getElementById('reviewComment').value.trim(),
                page: window.location.pathname.split('/').pop() || 'unknown'
            };

            try {
                const res = await fetch('api/save_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();

                if (json && json.success) {
                    // Show success message
                    const modal = document.getElementById('reviewModal');
                    const originalContent = modal.innerHTML;
                    modal.innerHTML = `
                        <div class="success-message">
                            <div class="success-icon">✓</div>
                            <h3>¡Gracias por tu valoración!</h3>
                            <p>Tu feedback es muy importante para nosotros.</p>
                        </div>
                    `;

                    setTimeout(() => {
                        closeModal();
                        modal.innerHTML = originalContent;
                        initializeFormEvents();
                    }, 2000);
                } else {
                    alert('Error: ' + (json.message || 'No se pudo enviar la valoración'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (err) {
                alert('Error de red: ' + err.message);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    function initializeFormEvents() {
        initializeEvents();
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        createFloatingButton();
        createFloatingButtonHTML();
        initializeEvents();
    });

})();
