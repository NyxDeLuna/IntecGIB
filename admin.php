<?php
// Simple admin panel - requires login
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Determine current view
$view = isset($_GET['view']) ? $_GET['view'] : 'reviews';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - IntecGIB</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            display: flex;
            gap: 2rem;
            margin-top: 90px;
            min-height: calc(100vh - 90px);
        }
        .admin-sidebar {
            flex: 0 0 280px;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 2rem 1rem;
            position: sticky;
            top: 90px;
            height: fit-content;
            border-radius: 0 20px 20px 0;
        }
        .admin-sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-sidebar nav li {
            margin-bottom: 1rem;
        }
        .admin-sidebar nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            text-decoration: none;
            color: #aaa;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .admin-sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }
        .admin-sidebar nav a.active {
            background: var(--accent-green);
            color: white;
            box-shadow: 0 4px 12px rgba(106, 170, 100, 0.3);
        }
        .admin-content {
            flex: 1;
            padding: 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            border-radius: 20px 0 0 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .admin-header h1 {
            color: #1a1a1a;
            margin: 0;
            font-size: 2rem;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        
        /* Welcome card */
        .welcome-card {
            background: linear-gradient(135deg, var(--accent-green) 0%, #5a9b6a 100%);
            border-radius: 15px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 8px 24px rgba(106, 170, 100, 0.2);
        }
        .welcome-card h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
        }
        .welcome-card p {
            margin: 0;
            opacity: 0.95;
            font-size: 1rem;
        }
        
        /* Dashboard cards */
        .admin-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        .stat-card-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-card-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent-green);
        }
        
        /* Filters section */
        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .filter-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
        }
        .filter-group input,
        .filter-group select {
            padding: 0.6rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.9rem;
            min-width: 150px;
            transition: all 0.3s ease;
        }
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 8px rgba(106, 170, 100, 0.2);
        }
        .filters-section button {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filters-section .cta-button {
            background: var(--accent-green);
            color: white;
        }
        .filters-section .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 170, 100, 0.3);
        }
        /* Reviews management */
        .reviews-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .reviews-table thead {
            background: linear-gradient(135deg, #f0f2f5 0%, #e8ecf1 100%);
            border-bottom: 2px solid #ddd;
        }
        .reviews-table th {
            padding: 1.2rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        .reviews-table td {
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #f0f2f5;
        }
        .reviews-table tbody tr {
            transition: all 0.2s ease;
        }
        .reviews-table tbody tr:hover {
            background: #f8f9fa;
        }
        .review-rating {
            color: #f6b042;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .review-status {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .review-status.pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
            color: #856404;
        }
        .review-status.approved {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        .action-btn {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-approve {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .btn-approve:hover {
            background: linear-gradient(135deg, #218838 0%, #17a2b8 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333 0%, #d73227 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            color: #999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-box h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #1a1a1a;
            font-size: 1.3rem;
        }
        .modal-box button {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="nav-logo">
                <img src="img/misc/logo_intecgib.png" alt="IntecGIB Logo">
            </a>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="admin.php" class="nav-link active">Admin</a></li>
                <li class="nav-item">
                    <a href="logout.php" class="logout-btn">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <nav>
                <ul>
                    <li><a href="?view=reviews" class="<?php echo $view === 'reviews' ? 'active' : ''; ?>"><span style="font-size: 1.3rem;">⭐</span> Reviews</a></li>
                    <li><a href="?view=projects" class="<?php echo $view === 'projects' ? 'active' : ''; ?>"><span style="font-size: 1.3rem;">🏗️</span> Projects</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="welcome-card">
                <h2>Welcome back! 👋</h2>
                <p>Manage your reviews and projects from this dashboard</p>
            </div>

            <div class="admin-cards" id="statsCards">
                <!-- Stats will be loaded here -->
            </div>

            <div class="admin-header">
                <h1><?php echo $view === 'reviews' ? '⭐ Reviews Management' : '🏗️ Projects Management'; ?></h1>
            </div>

            <?php if ($view === 'reviews'): ?>
                <div class="filters-section">
                    <div class="filter-group">
                        <label>📅 From Date:</label>
                        <input type="date" id="filterDateFrom" placeholder="From date">
                    </div>
                    <div class="filter-group">
                        <label>📅 To Date:</label>
                        <input type="date" id="filterDateTo" placeholder="To date">
                    </div>
                    <div class="filter-group">
                        <label>⭐ Min Rating:</label>
                        <select id="filterMinRating">
                            <option value="0">All ratings</option>
                            <option value="1">1+ stars</option>
                            <option value="2">2+ stars</option>
                            <option value="3">3+ stars</option>
                            <option value="4">4+ stars</option>
                            <option value="5">5 stars only</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>📊 Status:</label>
                        <select id="filterStatus">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    <button onclick="applyFilters()" class="cta-button">🔍 Apply Filters</button>
                    <button onclick="exportReviewsPDF()" class="cta-button" style="background: #e74c3c;">📥 Export PDF</button>
                </div>
                <div id="reviewsView">
                    <!-- Reviews will be loaded here -->
                </div>
            <?php elseif ($view === 'projects'): ?>
                <div id="projectsView">
                    <!-- Projects management will be here -->
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="confirmModal" class="modal-overlay">
        <div class="modal-box">
            <h3>Confirm Action</h3>
            <p id="confirmText"></p>
            <button id="confirmYes" class="action-btn btn-delete">Delete</button>
            <button id="confirmNo" class="action-btn" style="background: #6c757d; color: white;">Cancel</button>
        </div>
    </div>

    <script>
        // Admin panel JS
        const currentView = '<?php echo $view; ?>';

        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            if (currentView === 'reviews') {
                loadReviews();
            } else if (currentView === 'projects') {
                loadProjects();
            }
        });

        // ============= LOAD STATS =============
        async function loadStats() {
            try {
                const reviewsRes = await fetch('api/get_reviews.php?all=1');
                const projectsRes = await fetch('get_projects.php');
                
                const reviewsJson = await reviewsRes.json();
                const projectsJson = await projectsRes.json();
                
                const totalReviews = (reviewsJson.reviews || []).length;
                const pendingReviews = (reviewsJson.reviews || []).filter(r => !r.approved).length;
                const totalProjects = (projectsJson.projects || []).length;
                
                const approvedReviews = totalReviews - pendingReviews;
                
                const statsHTML = `
                    <div class="stat-card">
                        <div class="stat-card-icon">⭐</div>
                        <div class="stat-card-label">Total Reviews</div>
                        <div class="stat-card-value">${totalReviews}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon">✅</div>
                        <div class="stat-card-label">Approved</div>
                        <div class="stat-card-value">${approvedReviews}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon">⏳</div>
                        <div class="stat-card-label">Pending Moderation</div>
                        <div class="stat-card-value">${pendingReviews}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon">🏗️</div>
                        <div class="stat-card-label">Total Projects</div>
                        <div class="stat-card-value">${totalProjects}</div>
                    </div>
                `;
                document.getElementById('statsCards').innerHTML = statsHTML;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // ============= REVIEWS MANAGEMENT =============
        async function loadReviews(filtered = false) {
            try {
                let url = filtered ? 'api/filter_reviews.php' : 'api/get_reviews.php?all=1';
                
                if (filtered) {
                    const dateFrom = document.getElementById('filterDateFrom').value;
                    const dateTo = document.getElementById('filterDateTo').value;
                    const minRating = document.getElementById('filterMinRating').value;
                    const status = document.getElementById('filterStatus').value;
                    
                    const params = new URLSearchParams();
                    if (dateFrom) params.append('dateFrom', dateFrom);
                    if (dateTo) params.append('dateTo', dateTo);
                    if (minRating) params.append('minRating', minRating);
                    if (status) params.append('status', status);
                    
                    url += '?' + params.toString();
                }
                
                const res = await fetch(url);
                const json = await res.json();
                const reviews = json.reviews || [];

                const container = document.getElementById('reviewsView');
                if (reviews.length === 0) {
                    container.innerHTML = '<div class="empty-state">📭 No reviews match your filters.</div>';
                    return;
                }

                // Sort by date desc
                reviews.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                let html = '<table class="reviews-table"><thead><tr><th>👤 Name</th><th>⭐ Rating</th><th>💬 Comment</th><th>📅 Date</th><th>📊 Status</th><th>⚙️ Actions</th></tr></thead><tbody>';
                reviews.forEach(r => {
                    const stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
                    const status = r.approved ? 'approved' : 'pending';
                    const statusText = r.approved ? 'Approved' : 'Pending';
                    const date = new Date(r.created_at).toLocaleDateString('es-ES');
                    const comment = r.comment.substring(0, 60) + (r.comment.length > 60 ? '...' : '');
                    html += `<tr>
                        <td><strong>${r.name}</strong></td>
                        <td class="review-rating">${stars}</td>
                        <td title="${r.comment}"><em>${comment}</em></td>
                        <td>${date}</td>
                        <td><span class="review-status ${status}">${statusText}</span></td>
                        <td>`;
                    if (!r.approved) {
                        html += `<button class="action-btn btn-approve" onclick="approveReview('${r.id}')">✓ Approve</button>`;
                    }
                    html += `<button class="action-btn btn-delete" onclick="deleteReview('${r.id}')">🗑️ Delete</button></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                document.getElementById('reviewsView').innerHTML = '<div class="empty-state">❌ Error loading reviews.</div>';
            }
        }

        async function approveReview(id) {
            const res = await fetch('api/approve_review.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const json = await res.json();
            if (json.success) {
                loadReviews();
            } else {
                alert('Error approving review');
            }
        }

        function deleteReview(id) {
            showConfirm('Are you sure you want to delete this review?', () => {
                fetch('api/delete_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                }).then(res => res.json()).then(json => {
                    if (json.success) {
                        loadReviews();
                    } else {
                        alert('Error deleting review');
                    }
                });
            });
        }

        // ============= PROJECTS MANAGEMENT =============
        async function loadProjects() {
            try {
                const res = await fetch('get_projects.php');
                const json = await res.json();
                const projects = json.projects || [];

                const container = document.getElementById('projectsView');
                if (projects.length === 0) {
                    container.innerHTML = '<div class="empty-state">🏗️ No projects yet. <a href="projects.html" class="cta-button" style="display: inline-block; margin-top: 1rem; padding: 0.6rem 1.2rem; background: var(--accent-green); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">Add Your First Project</a></div>';
                    return;
                }

                let html = '<a href="projects.html" class="cta-button" style="display: inline-block; margin-bottom: 1.5rem; padding: 0.7rem 1.5rem; background: var(--accent-green); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s;">+ Add New Project</a>';
                html += '<table class="reviews-table"><thead><tr><th>📌 Project Name</th><th>📊 Status</th><th>🖼️ Images</th><th>⚙️ Actions</th></tr></thead><tbody>';
                projects.forEach(p => {
                    const statusText = p.estado_proyecto == 1 ? '✅ Completed' : (p.estado_proyecto == 2 ? '🔄 In Progress' : '🎯 Future');
                    const imageCount = (p.images || []).length;
                    const id = p.id || p.id_proyecto || p.ID || p.idProyecto || p.project_id;
                    html += `<tr>
                        <td><strong>${p.nombre}</strong></td>
                        <td>${statusText}</td>
                        <td><span style="background: #f0f2f5; padding: 0.3rem 0.7rem; border-radius: 4px; font-weight: 500;">${imageCount} image${imageCount !== 1 ? 's' : ''}</span></td>
                        <td>
                            <a href="edit_project.php?id=${encodeURIComponent(id)}" class="action-btn" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; text-decoration: none;">✏️ Edit</a>
                            <button class="action-btn btn-delete" onclick="deleteProject('${encodeURIComponent(id)}')">🗑️ Delete</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                document.getElementById('projectsView').innerHTML = '<div class="empty-state">❌ Error loading projects.</div>';
            }
        }

        function showAddProjectForm() {
            // Redirect to projects page with modal (existing functionality)
            window.location.href = 'projects.html';
        }

        function deleteProject(id) {
            showConfirm('Are you sure you want to delete this project?', () => {
                fetch('delete_project.php?id=' + encodeURIComponent(id))
                    .then(() => loadProjects())
                    .catch(() => alert('Error deleting project'));
            });
        }

        // ============= MODAL UTILITIES =============
        function showConfirm(text, onConfirm) {
            document.getElementById('confirmText').textContent = text;
            const modal = document.getElementById('confirmModal');
            modal.classList.add('active');

            document.getElementById('confirmYes').onclick = () => {
                onConfirm();
                modal.classList.remove('active');
            };
            document.getElementById('confirmNo').onclick = () => {
                modal.classList.remove('active');
            };
        }

        // ============= FILTERS & EXPORT =============
        function applyFilters() {
            loadReviews(true);
        }

        function exportReviewsPDF() {
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;
            const minRating = document.getElementById('filterMinRating').value;
            const status = document.getElementById('filterStatus').value;
            
            const params = new URLSearchParams();
            if (dateFrom) params.append('dateFrom', dateFrom);
            if (dateTo) params.append('dateTo', dateTo);
            if (minRating) params.append('minRating', minRating);
            if (status) params.append('status', status);
            
            const url = 'api/export_reviews_pdf.php?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
