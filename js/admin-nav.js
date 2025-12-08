// Admin link visibility management
// Run on all pages to show/hide Admin link based on login status

document.addEventListener('DOMContentLoaded', function() {
    checkAndUpdateAdminLink();
});

async function checkAndUpdateAdminLink() {
    try {
        const res = await fetch('auth.php');
        const json = await res.json();
        
        if (json.loggedIn) {
            // Show Admin link
            const adminLink = document.getElementById('adminNavLink');
            if (adminLink) {
                adminLink.style.display = 'block';
            }
        } else {
            // Hide Admin link
            const adminLink = document.getElementById('adminNavLink');
            if (adminLink) {
                adminLink.style.display = 'none';
            }
        }
    } catch (error) {
        // Hide by default on error
        const adminLink = document.getElementById('adminNavLink');
        if (adminLink) {
            adminLink.style.display = 'none';
        }
    }
}
