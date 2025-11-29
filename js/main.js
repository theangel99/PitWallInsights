// PitWall - F1 Website JavaScript

// Tab switching for standings page
function showTab(tabName) {
    // Hide all tab contents
    const driversTab = document.getElementById('drivers-tab');
    const constructorsTab = document.getElementById('constructors-tab');
    
    if (driversTab && constructorsTab) {
        if (tabName === 'drivers') {
            driversTab.style.display = 'block';
            constructorsTab.style.display = 'none';
        } else {
            driversTab.style.display = 'none';
            constructorsTab.style.display = 'block';
        }

        // Update active button
        const buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(button => {
            button.classList.remove('active');
            if ((tabName === 'drivers' && button.textContent === 'Dirkači') ||
                (tabName === 'constructors' && button.textContent === 'Konstruktorji')) {
                button.classList.add('active');
            }
        });
    }
}

// Highlight active navigation link
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});

// Add smooth scrolling for any anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Load More Races functionality
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.querySelector('.load-more-btn');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // In a real implementation, this would load more races from an API or database
            // For now, we'll just show a message
            alert('Funkcionalnost nalaganja dodatnih dirk bo na voljo kmalu. V produkcijski verziji bi to naložilo starejše dirke iz sezone.');

            // Example of how you might handle this in production:
            // fetch('/api/races?offset=3&limit=3')
            //     .then(response => response.json())
            //     .then(races => {
            //         // Append races to the timeline
            //     });
        });
    }
});

// Add hover effect data (can be extended later for dynamic content)
console.log('PitWall F1 Website Loaded');
