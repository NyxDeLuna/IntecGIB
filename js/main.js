// Main JavaScript file for IntecGIB website
// Complete version without problematic animations

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    initCookiesBanner();
    initLoginSystem();
    initContactForm();
    initScrollAnimations();
    initParallaxEffects();
    
    // Initialize projects page if we're on projects.html
    if (window.location.pathname.includes('projects.html')) {
        initProjectsPage();
    }
    
    // Initialize services page if we're on services.html
    if (window.location.pathname.includes('services.html')) {
        
    }
    
    // Debug auth system
    setTimeout(debugAuth, 2000);
});

// ============================================
// SCROLL ANIMATIONS - Corregidas
// ============================================

function initScrollAnimations() {
    // Add fade-in class to elements that should animate on scroll
    const animatedElements = document.querySelectorAll('.service-card, .info-item, .project-card, .section-title');
    
    animatedElements.forEach(element => {
        element.classList.add('fade-in');
    });
    
    // Check if elements are in view on load
    checkScroll();
    
    // Check on scroll
    window.addEventListener('scroll', checkScroll);
    
    // Also check on resize
    window.addEventListener('resize', checkScroll);
}

function checkScroll() {
    const triggerBottom = window.innerHeight * 0.85;
    
    document.querySelectorAll('.fade-in').forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        
        if (elementTop < triggerBottom) {
            element.classList.add('visible');
        }
    });
}

// ============================================
// PARALLAX EFFECTS - Corregidas
// ============================================

function initParallaxEffects() {
    // Solo aplicar parallax suave a secciones hero
    const heroSections = document.querySelectorAll('.hero');
    
    if (heroSections.length > 0) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            
            heroSections.forEach(hero => {
                // Efecto parallax muy sutil
                const rate = scrolled * 0.3;
                hero.style.transform = `translate3d(0px, ${rate}px, 0px)`;
            });
        });
    }
}

// ============================================
// PRELOADER/SPINNER FOR PROJECTS
// ============================================

function showLoader(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = `
        <div class="loader-container">
            <div class="loader"></div>
        </div>
    `;
}

function hideLoader(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const loaderContainer = container.querySelector('.loader-container');
    if (loaderContainer) {
        loaderContainer.style.display = 'none';
    }
}

// ============================================
// PROJECTS PAGE MODIFICATIONS
// ============================================

// Cache for loaded projects so filters work client-side
let projectsCache = [];
// Track login state globally (updated by auth functions)
window.isLoggedIn = false;

function initProjectsPage() {
    // Show loader before loading projects
    showLoader('projectsContainer');
    
    // Setup filters UI listeners
    setupProjectFilters();

    // Add a small delay to show the loader
    setTimeout(() => {
        loadProjects();
    }, 500);
    
    initAddProjectModal();
}

function loadProjects() {
    
    
    fetch('get_projects.php?' + new Date().getTime())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.projects) {
                
                // Cache projects for client-side filtering
                projectsCache = data.projects;
                // Display filtered/sorted projects (initially unfiltered)
                displayProjects(getFilteredAndSortedProjects());
            } else {
                document.getElementById('projectsContainer').innerHTML = 
                    '<p class="text-center">No projects found. Add your first project!</p>';
            }
        })
        .catch(error => {
            document.getElementById('projectsContainer').innerHTML = 
                '<p class="text-center">Error loading projects. Please try again later.</p>';
        })
        .finally(() => {
            // Hide loader when done
            hideLoader('projectsContainer');
        });
}

function displayProjects(projects) {
    const container = document.getElementById('projectsContainer');
    container.innerHTML = '';
    
    if (!projects || projects.length === 0) {
        container.innerHTML = '<p>No projects found. Add your first project!</p>';
        return;
    }
    
    
    
    const grid = document.createElement('div');
    grid.className = 'projects-grid';
    grid.style.cssText = `
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    `;
    
    projects.forEach(project => {
        const projectCard = createProjectCard(project);
        grid.appendChild(projectCard);
    });
    
    container.appendChild(grid);
}

// Apply filters and sorting to the cached projects and return the result
function getFilteredAndSortedProjects() {
    if (!projectsCache || projectsCache.length === 0) return [];

    const nameFilterEl = document.getElementById('filterName');
    const sortByEl = document.getElementById('sortBy');
    const statusEl = document.getElementById('statusFilter');

    const nameFilter = nameFilterEl ? nameFilterEl.value.trim().toLowerCase() : '';
    const sortBy = sortByEl ? sortByEl.value : 'alphabetical';
    const statusFilter = statusEl ? statusEl.value : 'all';

    let filtered = projectsCache.filter(p => {
        // Name filter
        if (nameFilter) {
            const name = (p.nombre || '').toLowerCase();
            if (!name.includes(nameFilter)) return false;
        }

        // Status filter
        if (statusFilter && statusFilter !== 'all') {
            if (statusFilter === 'in_progress') {
                // Hide Completed (1) and Future (3) when In Progress selected
                return Number(p.estado_proyecto) === 2;
            } else if (statusFilter === 'completed') {
                return Number(p.estado_proyecto) === 1;
            } else if (statusFilter === 'future') {
                return Number(p.estado_proyecto) === 3;
            }
        }

        return true;
    });

    // Sorting
    filtered.sort((a, b) => {
        if (sortBy === 'alphabetical') {
            const an = (a.nombre || '').toLowerCase();
            const bn = (b.nombre || '').toLowerCase();
            return an.localeCompare(bn);
        } else if (sortBy === 'updated') {
            // Try several common date fields, fallback to zero
            const aDate = new Date(a.updated_at || a.updated || a.fecha_actualizacion || a.fecha || 0).getTime() || 0;
            const bDate = new Date(b.updated_at || b.updated || b.fecha_actualizacion || b.fecha || 0).getTime() || 0;
            // Newest first
            return bDate - aDate;
        }

        return 0;
    });

    return filtered;
}

function setupProjectFilters() {
    const nameEl = document.getElementById('filterName');
    const sortEl = document.getElementById('sortBy');
    const statusEl = document.getElementById('statusFilter');

    const applyFilters = () => {
        const results = getFilteredAndSortedProjects();
        displayProjects(results);
    };

    if (nameEl) {
        nameEl.addEventListener('input', () => {
            applyFilters();
        });
    }

    if (sortEl) {
        sortEl.addEventListener('change', () => {
            applyFilters();
        });
    }

    if (statusEl) {
        statusEl.addEventListener('change', () => {
            applyFilters();
        });
    }
}

function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card hover-elevate animate-fade-in';
    // determine project id from common fields
    const projectId = project.id || project.id_proyecto || project.ID || project.idProyecto || project.project_id || project.idProyecto || null;
    if (projectId) card.dataset.projectId = projectId;
    card.style.cssText = `
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    `;
    
    const mainImage = project.images && project.images[0] ? project.images[0] : 'img/projects/placeholder.jpg';
    
    card.innerHTML = `
        <div style="height: 200px; overflow: hidden; border-radius: 8px; margin-bottom: 1rem;">
            <img src="${mainImage}" 
                 alt="${project.nombre}" 
                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                 onerror="this.src='img/projects/placeholder.jpg'">
        </div>
        <h4 style="margin: 0 0 0.5rem 0; color: #333;">${project.nombre}</h4>
        <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem; height: 60px; overflow: hidden;">
            ${project.descripcion || 'No description available'}
        </p>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="padding: 0.3rem 0.8rem; background: #f0f0f0; border-radius: 20px; font-size: 0.8rem; color: #333;">
                ${getStatusText(project.estado_proyecto)}
            </span>
            <button class="view-project-btn">
                View Details
            </button>
        </div>
    `;
    
    // Add hover effect
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
        const img = card.querySelector('img');
        if (img) img.style.transform = 'scale(1.05)';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
        const img = card.querySelector('img');
        if (img) img.style.transform = 'scale(1)';
    });
    
    const viewBtn = card.querySelector('.view-project-btn');
    viewBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showProjectDetails(project);
    });
    
    card.addEventListener('click', () => {
        showProjectDetails(project);
    });

    // If user is logged in, append edit/delete buttons
    // DISABLED: Edit/Delete buttons removed from public projects view
    // if (window.isLoggedIn) {
    //     addAdminButtonsToCard(card, project);
    // }
    
    return card;
}

// Append edit/delete buttons to a project card (if not already present)
function addAdminButtonsToCard(card, project) {
    if (!card || !project) return;
    if (card.querySelector('.project-actions')) return; // already added

    const id = card.dataset.projectId || project.id || project.id_proyecto || project.ID || project.idProyecto || project.project_id || '';
    const actions = document.createElement('div');
    actions.className = 'project-actions';

    const editLink = document.createElement('a');
    editLink.className = 'edit-btn';
    editLink.href = `edit_project.php?id=${encodeURIComponent(id)}`;
    editLink.textContent = 'Edit';

    const deleteLink = document.createElement('a');
    deleteLink.className = 'delete-btn';
    deleteLink.href = `delete_project.php?id=${encodeURIComponent(id)}`;
    deleteLink.textContent = 'Delete';

    actions.appendChild(editLink);
    actions.appendChild(deleteLink);

    // Append under the button container area (try to find the action area)
    const bottomRow = card.querySelector('div[style*="display: flex;"]');
    if (bottomRow) {
        bottomRow.appendChild(actions);
    } else {
        card.appendChild(actions);
    }
}

function addAdminButtonsToAllCards() {
    const cards = document.querySelectorAll('.project-card');
    cards.forEach(card => {
        const id = card.dataset.projectId;
        // try to find project in cache
        let project = null;
        if (id && projectsCache && projectsCache.length) {
            project = projectsCache.find(p => String(p.id || p.id_proyecto || p.ID || p.idProyecto || p.project_id) === String(id));
        }
        // DISABLED: Edit/Delete buttons removed from public projects view
        // addAdminButtonsToCard(card, project || {});
    });
}

function getStatusText(status) {
    switch(status) {
        case 1: return 'Completed';
        case 2: return 'In Progress';
        case 3: return 'Future';
        default: return 'Unknown';
    }
}

function showProjectDetails(project) {
    const modal = document.getElementById('projectModal');
    const title = document.getElementById('projectTitle');
    const description = document.getElementById('projectDescription');
    
    if (!modal || !title || !description) {
        return;
    }
    
    title.textContent = project.nombre;
    description.textContent = project.descripcion || 'No description available';
    
    const imagesToShow = project.images && project.images.length > 0 ? project.images : ['img/projects/placeholder.jpg'];
    
    
    
    // Initialize carousel with the images
    initCarousel(imagesToShow);
    
    modal.style.display = 'block';
    
    const closeBtn = modal.querySelector('.close');
    if (closeBtn) {
        closeBtn.onclick = () => {
            modal.style.display = 'none';
        };
    }
    
    window.onclick = (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    };
}

// Carousel functionality
let carouselState = {
    images: [],
    currentIndex: 0
};

function initCarousel(images) {
    carouselState.images = images;
    carouselState.currentIndex = 0;
    
    // Display first image
    updateCarouselDisplay();
    
    // Create thumbnails
    createCarouselThumbnails();
    
    // Setup event listeners
    setupCarouselListeners();
}

function updateCarouselDisplay() {
    const mainImage = document.getElementById('carouselMainImage');
    const counter = document.getElementById('carouselCounter');
    
    if (!mainImage || !counter) return;
    
    const image = carouselState.images[carouselState.currentIndex];
    mainImage.src = image;
    mainImage.onerror = function() {
        this.src = 'img/projects/placeholder.jpg';
    };
    
    counter.textContent = `${carouselState.currentIndex + 1} / ${carouselState.images.length}`;
    
    // Update thumbnail selection
    updateThumbnailSelection();
}

function createCarouselThumbnails() {
    const container = document.getElementById('carouselThumbnails');
    if (!container) return;
    
    container.innerHTML = '';
    
    carouselState.images.forEach((image, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = 'carousel-thumbnail';
        if (index === carouselState.currentIndex) {
            thumbnail.classList.add('active');
        }
        
        const img = document.createElement('img');
        img.src = image;
        img.onerror = function() {
            this.src = 'img/projects/placeholder.jpg';
        };
        
        thumbnail.appendChild(img);
        thumbnail.addEventListener('click', () => {
            carouselState.currentIndex = index;
            updateCarouselDisplay();
        });
        
        container.appendChild(thumbnail);
    });
}

function updateThumbnailSelection() {
    const thumbnails = document.querySelectorAll('.carousel-thumbnail');
    thumbnails.forEach((thumb, index) => {
        if (index === carouselState.currentIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

function setupCarouselListeners() {
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');
    const zoomBtn = document.getElementById('carouselZoom');
    const mainImage = document.getElementById('carouselMainImage');
    
    if (prevBtn) {
        prevBtn.onclick = () => {
            carouselState.currentIndex = (carouselState.currentIndex - 1 + carouselState.images.length) % carouselState.images.length;
            updateCarouselDisplay();
        };
    }
    
    if (nextBtn) {
        nextBtn.onclick = () => {
            carouselState.currentIndex = (carouselState.currentIndex + 1) % carouselState.images.length;
            updateCarouselDisplay();
        };
    }
    
    if (zoomBtn) {
        zoomBtn.onclick = () => {
            openLightbox();
        };
    }
    
    if (mainImage) {
        mainImage.onclick = () => {
            openLightbox();
        };
    }
    
    // Keyboard navigation
    document.removeEventListener('keydown', handleCarouselKeyboard);
    document.addEventListener('keydown', handleCarouselKeyboard);
}

function handleCarouselKeyboard(e) {
    const modal = document.getElementById('projectModal');
    if (!modal || modal.style.display === 'none') return;
    
    if (e.key === 'ArrowLeft') {
        carouselState.currentIndex = (carouselState.currentIndex - 1 + carouselState.images.length) % carouselState.images.length;
        updateCarouselDisplay();
    } else if (e.key === 'ArrowRight') {
        carouselState.currentIndex = (carouselState.currentIndex + 1) % carouselState.images.length;
        updateCarouselDisplay();
    }
}

function openLightbox() {
    const lightbox = document.getElementById('lightboxModal');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCounter = document.getElementById('lightboxCounter');
    
    if (!lightbox || !lightboxImage || !lightboxCounter) return;
    
    const image = carouselState.images[carouselState.currentIndex];
    lightboxImage.src = image;
    lightboxImage.onerror = function() {
        this.src = 'img/projects/placeholder.jpg';
    };
    
    lightboxCounter.textContent = `${carouselState.currentIndex + 1} / ${carouselState.images.length}`;
    
    lightbox.style.display = 'block';
    
    // Setup lightbox controls
    setupLightboxListeners();
}

function setupLightboxListeners() {
    const lightbox = document.getElementById('lightboxModal');
    const prevBtn = document.getElementById('lightboxPrev');
    const nextBtn = document.getElementById('lightboxNext');
    const closeBtn = lightbox.querySelector('.close');
    
    if (prevBtn) {
        prevBtn.onclick = (e) => {
            e.stopPropagation();
            carouselState.currentIndex = (carouselState.currentIndex - 1 + carouselState.images.length) % carouselState.images.length;
            updateLightboxDisplay();
        };
    }
    
    if (nextBtn) {
        nextBtn.onclick = (e) => {
            e.stopPropagation();
            carouselState.currentIndex = (carouselState.currentIndex + 1) % carouselState.images.length;
            updateLightboxDisplay();
        };
    }
    
    if (closeBtn) {
        closeBtn.onclick = () => {
            lightbox.style.display = 'none';
        };
    }
    
    window.onkeydown = (e) => {
        if (lightbox.style.display !== 'block') return;
        
        if (e.key === 'ArrowLeft') {
            carouselState.currentIndex = (carouselState.currentIndex - 1 + carouselState.images.length) % carouselState.images.length;
            updateLightboxDisplay();
        } else if (e.key === 'ArrowRight') {
            carouselState.currentIndex = (carouselState.currentIndex + 1) % carouselState.images.length;
            updateLightboxDisplay();
        } else if (e.key === 'Escape') {
            lightbox.style.display = 'none';
        }
    };
    
    lightbox.onclick = (e) => {
        if (e.target === lightbox) {
            lightbox.style.display = 'none';
        }
    };
}

function updateLightboxDisplay() {
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCounter = document.getElementById('lightboxCounter');
    
    if (!lightboxImage || !lightboxCounter) return;
    
    const image = carouselState.images[carouselState.currentIndex];
    lightboxImage.src = image;
    lightboxImage.onerror = function() {
        this.src = 'img/projects/placeholder.jpg';
    };
    
    lightboxCounter.textContent = `${carouselState.currentIndex + 1} / ${carouselState.images.length}`;
}

function initAddProjectModal() {
    const addProjectBtn = document.getElementById('addProjectButton');
    const addProjectModal = document.getElementById('addProjectModal');
    const addProjectForm = document.getElementById('addProjectForm');
    
    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', () => {
            addProjectModal.style.display = 'block';
        });
    }
    
    const closeBtn = addProjectModal.querySelector('.close');
    if (closeBtn) {
        closeBtn.onclick = () => {
            addProjectModal.style.display = 'none';
        };
    }
    
    window.addEventListener('click', (e) => {
        if (e.target === addProjectModal) {
            addProjectModal.style.display = 'none';
        }
    });
    
    if (addProjectForm) {
        addProjectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAddProject();
        });
    }
}

function handleAddProject() {
    
    
    const form = document.getElementById('addProjectForm');
    if (!form) {
        return;
    }
    
    const fileInput = form.querySelector('input[type="file"]');
    const fileCount = fileInput.files.length;
    
    if (fileCount < 1) {
        showNotification('Please upload at least 1 image', 'error');
        return;
    }
    
    if (fileCount > 6) {
        showNotification('Maximum 6 images allowed', 'error');
        return;
    }
    
    let totalSize = 0;
    for (let i = 0; i < fileCount; i++) {
        const file = fileInput.files[i];
        totalSize += file.size;
        
        if (file.size > 5 * 1024 * 1024) {
            showNotification(`Image "${file.name}" is too large (max 5MB)`, 'error');
            return;
        }
        
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showNotification(`Image "${file.name}" has invalid format. Use JPG, PNG or GIF.`, 'error');
            return;
        }
    }
    
    if (totalSize > 30 * 1024 * 1024) {
        showNotification('Total images size exceeds 30MB limit', 'error');
        return;
    }
    
    const formData = new FormData(form);
    
    for (let [key, value] of formData.entries()) {
        // Intentionally no debug logging to keep console clean
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Adding...';
    submitBtn.disabled = true;
    
    const startTime = Date.now();
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 60000);
    
    fetch('add_project.php', {
        method: 'POST',
        body: formData,
        signal: controller.signal
    })
    .then(response => {
    clearTimeout(timeoutId);
    const duration = Date.now() - startTime;
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                // Avoid logging raw server replies in console in production
                throw new Error('Server returned non-JSON response');
            });
        }
        
        return response.json();
    })
    .then(data => {
        
        if (data.success) {
            showNotification('Project added successfully!', 'success');
            document.getElementById('addProjectModal').style.display = 'none';
            form.reset();
            
            setTimeout(() => {
                
                loadProjects();
                
                setTimeout(() => {
                    if (window.location.href.includes('projects.html')) {
                        window.location.reload();
                    }
                }, 1000);
                
            }, 500);
        } else {
            showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        const duration = Date.now() - startTime;
        
        if (error.name === 'AbortError') {
            showNotification('Request timeout. The server is taking too long to respond.', 'error');
        } else {
            showNotification('Error: ' + (error.message || 'Unknown error occurred'), 'error');
        }
        
        checkServerStatus();
    })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
    });
}

// ============================================
// COOKIES BANNER
// ============================================

function initCookiesBanner() {
    const cookiesBanner = document.getElementById('cookiesBanner');
    const acceptCookies = document.getElementById('acceptCookies');
    
    // Show banner if cookies haven't been accepted
    if (!localStorage.getItem('cookiesAccepted')) {
        setTimeout(() => {
            if (cookiesBanner) {
                cookiesBanner.style.display = 'block';
            }
        }, 1000);
    }
    
    // Handle accept cookies button click
    if (acceptCookies) {
        acceptCookies.addEventListener('click', function() {
            localStorage.setItem('cookiesAccepted', 'true');
            if (cookiesBanner) {
                cookiesBanner.style.display = 'none';
            }
        });
    }
}

// ============================================
// LOGIN SYSTEM - PAGE BASED (REDIRECT)
// ============================================

function initLoginSystem() {
    
    
    const userLogin = document.getElementById('userLogin');
    
    // Verificar estado de autenticación al cargar
    checkAuthStatus();
    
    // Solo agregar el evento si NO estamos en la página de login
    if (userLogin && !window.location.pathname.includes('login.php')) {
        userLogin.addEventListener('click', function(e) {
            // Solo prevenir el comportamiento por defecto si el usuario no está logueado
            checkAuthStatus().then(isLoggedIn => {
                if (!isLoggedIn) {
                    e.preventDefault();
                    
                    // Obtener la página actual (sin la ruta completa)
                    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
                    
                    // Redirigir al login con parámetro de redirección
                    
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(currentPage);
                }
                // Si está logueado, dejar que el href normal funcione
            });
        });
    }
}

function checkAuthStatus() {
    
    
    return new Promise((resolve) => {
        // Agregar timestamp para evitar cache
        fetch('auth.php?' + new Date().getTime())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            
            
            if (data.loggedIn) {
                
                updateUIForLoggedInUser(data);
                resolve(true);
            } else {
                
                updateUIForLoggedOutUser();
                resolve(false);
            }
        })
        .catch(error => {
            updateUIForLoggedOutUser();
            resolve(false);
        });
    });
}

function updateUIForLoggedInUser(userData) {
    const userIcon = document.getElementById('userLogin');
    const adminControlsEl = document.getElementById('adminControls');
    
    
    
    if (userIcon) {
        userIcon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span style="margin-left: 5px; font-size: 14px;">${userData.name}</span>
        `;
        userIcon.href = '#';
        userIcon.onclick = function(e) {
            e.preventDefault();
            showUserMenu(userData);
        };
    }
    
    if (adminControlsEl) {
        // Hide the Add / Edit project controls on the public projects page
        // even when an admin is logged in — we use the separate Admin panel.
        adminControlsEl.style.display = 'none';
    }
    // Mark global login state and add admin buttons to existing cards
    window.isLoggedIn = true;
    if (typeof addAdminButtonsToAllCards === 'function') {
        addAdminButtonsToAllCards();
    }
}

function updateUIForLoggedOutUser() {
    const userIcon = document.getElementById('userLogin');
    const adminControlsEl = document.getElementById('adminControls');
    
    
    
    if (userIcon) {
        userIcon.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        `;
        userIcon.href = 'login.php';
        userIcon.onclick = null;
    }
    
    if (adminControlsEl) {
        adminControlsEl.style.display = 'none';
    }
    // Update global login state
    window.isLoggedIn = false;
}

function showUserMenu(userData) {
    
    
    let userMenu = document.getElementById('userMenu');
    
    if (!userMenu) {
        userMenu = document.createElement('div');
        userMenu.id = 'userMenu';
        userMenu.style.cssText = `
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 150px;
        `;
        
        userMenu.innerHTML = `
            <div style="padding: 10px; border-bottom: 1px solid #eee;">
                <small>${userData.name}</small>
                <br>
                <small style="color: #666;">${userData.role || 'Usuario'}</small>
            </div>
            <button id="logoutBtn" style="width: 100%; padding: 10px; border: none; background: none; cursor: pointer; text-align: left;">
                Cerrar Sesión
            </button>
        `;
        
        const navItem = document.querySelector('.nav-item:last-child');
        if (navItem) {
            navItem.style.position = 'relative';
            navItem.appendChild(userMenu);
            
            document.getElementById('logoutBtn').addEventListener('click', function() {
                logout();
                userMenu.remove();
            });
            
            // Cerrar menú al hacer click fuera
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!e.target.closest('.nav-item:last-child')) {
                        userMenu.remove();
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        }
    } else {
        userMenu.remove();
    }
}

function logout() {
    
    
    fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('' + data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            showNotification('Logout error: ', 'error');
        });
}

function debugAuth() {
    
    
    // Verificar cookies de sesión
        fetch('auth.php?' + new Date().getTime())
        .then(r => r.json())
        .then(data => {
            // no debug logging in production
        })
        .catch(err => {
            showNotification('Auth endpoint error: ', 'error');
        });
}

// ============================================
// NOTIFICATION SYSTEM
// ============================================

function showNotification(message, type = 'info') {
    
    
    // Remove existing notifications
    const existingNotification = document.querySelector('.custom-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'custom-notification';
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
        background: ${type === 'error' ? '#dc3545' : type === 'success' ? '#28a745' : '#17a2b8'};
    `;
    
    notification.innerHTML = `
        <span>${message}</span>
        <button style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; margin-left: 10px;" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add animation styles if not exists
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// ============================================
// CONTACT FORM WITH mailto:
// ============================================

function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleContactFormMailto();
        });
    }
}

function handleContactFormMailto() {
    const name = document.getElementById('contactName').value.trim();
    const email = document.getElementById('contactEmail').value.trim();
    const subject = document.getElementById('contactSubject').value.trim();
    const message = document.getElementById('contactMessage').value.trim();
    
    // Validaciones
    if (!name || !email || !subject || !message) {
        showNotification('Please fill all fields', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Crear el cuerpo del email con formato profesional
    const emailBody = `Dear IntecGIB Team,

${message}

---
Best regards,
${name}
Email: ${email}
Sent from IntecGIB Contact Form
Date: ${new Date().toLocaleDateString()}`;
    
    // Codificar para URL (UTF-8 seguro)
    const encodedSubject = encodeURIComponent(subject);
    const encodedBody = encodeURIComponent(emailBody);
    
    // Mostrar panel de copia
    document.getElementById('copySubject').textContent = subject;
    document.getElementById('copyBody').textContent = emailBody;
    document.getElementById('emailDataCopy').style.display = 'block';
    
    // Configurar botón de copia al portapapeles
    const copyAllBtn = document.getElementById('copyAllBtn');
    if (copyAllBtn) {
        copyAllBtn.onclick = function() {
            const textToCopy = `To: support@intecgib.com\nSubject: ${subject}\n\n${emailBody}`;
            
            if (navigator.clipboard && window.isSecureContext) {
                // Método moderno (HTTPS o localhost)
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showNotification('Email details copied to clipboard!', 'success');
                }).catch(err => {
                    // Fallback para navegadores antiguos
                    copyToClipboardFallback(textToCopy);
                });
            } else {
                // Fallback para navegadores sin clipboard API
                copyToClipboardFallback(textToCopy);
            }
        };
    }
    
    // Crear el enlace mailto:
    const mailtoLink = `mailto:support@intecgib.com?subject=${encodedSubject}&body=${encodedBody}`;
    
    // Abrir el cliente de correo en nueva pestaña
    setTimeout(() => {
        window.open(mailtoLink, '_blank');
        showNotification('Opening your email client... Please click "Send" to complete', 'success');
    }, 300);
    
    // Guardar localmente para registro
    saveContactLocally(name, email, subject, message);
    
    // Opcional: Limpiar el formulario después de 2 segundos
    setTimeout(() => {
        document.getElementById('contactForm').reset();
    }, 2000);
}

function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email.toLowerCase());
}

function copyToClipboardFallback(text) {
    // Método fallback para copiar al portapapeles
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy. Please select and copy manually.', 'error');
    }
    
    document.body.removeChild(textArea);
}

function saveContactLocally(name, email, subject, message) {
    // Guardar en localStorage para tener registro (opcional)
    const contact = {
        name: name,
        email: email,
        subject: subject,
        message: message,
        date: new Date().toISOString()
    };
    
    try {
        // Obtener contactos existentes
        let contacts = JSON.parse(localStorage.getItem('intecgib_contacts') || '[]');
        contacts.push(contact);
        
        // Guardar máximo 50 contactos
        if (contacts.length > 50) {
            contacts = contacts.slice(-50);
        }
        
        localStorage.setItem('intecgib_contacts', JSON.stringify(contacts));
        
        
    } catch (e) {
        // suppressed localStorage error
    }
}

function checkServerStatus() {
    fetch('auth.php?' + new Date().getTime(), {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
        .then(response => {
            if (response.ok) {
                // server responded OK
            } else {
                showNotification('Server error: ' + response.status, 'error');
            }
        })
        .catch(error => {
            showNotification('Server is not reachable. Please check your connection.', 'error');
        });
}

// Make functions globally available for HTML onclick handlers
window.showNotification = showNotification;