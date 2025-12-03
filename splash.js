// splash.js
(function() {
    // --- 1. CHECK: Should we show the splash? ---
    
    // We check if the "internal_navigation" flag is saved in the browser
    const isInternal = sessionStorage.getItem('internal_nav');

    if (isInternal === 'true') {
        // RESULT: The user clicked a link to get here.
        // Action: Clear the flag so it resets for next time.
        sessionStorage.removeItem('internal_nav');
        // STOP HERE. Do not create the splash screen.
        return;
    }

    // --- 2. CREATE: The user is new (or reloaded), so show splash ---

    const splashDiv = document.createElement('div');
    splashDiv.id = 'global-splash-screen';
    
    // Inject the HTML content
    splashDiv.innerHTML = `
        <div class="splash-content">
            <div class="splash-logo-container">
                <img src="light_blue1 512.png" alt="Logo" class="splash-logo">
            </div>
            <h1 class="splash-title">Rook</h1>
            <p class="splash-subtitle">READ. RETURN. REPEAT.</p>
            
            <div class="splash-loader">
                <div class="splash-progress"></div>
            </div>
            
            <div class="splash-status">
                <i class="fas fa-shield-alt"></i> Loading Library Resources...
            </div>
        </div>
    `;
    
    // Add to the top of the page
    document.body.prepend(splashDiv);

    // Fade out logic
    setTimeout(() => {
        splashDiv.classList.add('splash-hidden');
    }, 1000); // Visible for 1 seconds

    setTimeout(() => {
        splashDiv.remove();
    }, 3100); // Remove from DOM completely

})();

// --- 3. LISTEN: Watch for link clicks ---
// This runs on every page to prepare for the NEXT click.

document.addEventListener('DOMContentLoaded', () => {
    // Find all 'a' tags (links)
    const links = document.querySelectorAll('a');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // If the link points to the same website (hostname matches)
            if (this.hostname === window.location.hostname) {
                // Set the flag! 
                sessionStorage.setItem('internal_nav', 'true');
            }
        });
    });
});