document.addEventListener('DOMContentLoaded', () => {
    // Add a simple loading indicator
    const loader = document.createElement('div');
    loader.style.position = 'fixed';
    loader.style.top = '0';
    loader.style.left = '0';
    loader.style.height = '3px';
    loader.style.width = '0%';
    loader.style.background = 'var(--primary-color, #22c55e)';
    loader.style.transition = 'width 0.3s ease, opacity 0.3s ease';
    loader.style.zIndex = '9999';
    loader.style.opacity = '0';
    document.body.appendChild(loader);

    function showLoader() {
        loader.style.opacity = '1';
        loader.style.width = '30%';
        setTimeout(() => { if (loader.style.opacity === '1') loader.style.width = '60%'; }, 200);
    }

    function hideLoader() {
        loader.style.width = '100%';
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => { loader.style.width = '0%'; }, 300);
        }, 300);
    }

    async function loadPage(url, options = {}) {
        showLoader();
        try {
            const response = await fetch(url, options);
            if (!response.ok) throw new Error('Network response was not ok');
            const htmlString = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlString, 'text/html');
            
            // Replace <main> content
            const newMain = doc.querySelector('main');
            const currentMain = document.querySelector('main');
            
            if (newMain && currentMain) {
                // Fade effect
                currentMain.style.opacity = '0';
                currentMain.style.transition = 'opacity 0.2s ease';
                
                setTimeout(() => {
                    currentMain.innerHTML = newMain.innerHTML;
                    currentMain.style.opacity = '1';
                    
                    // Update Title
                    document.title = doc.title;
                    
                    // Update active nav links
                    const newNav = doc.querySelector('#nav-links');
                    const currentNav = document.querySelector('#nav-links');
                    if (newNav && currentNav) {
                        currentNav.innerHTML = newNav.innerHTML;
                    }
                    
                    // Scroll to top safely
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 200);
            } else {
                // Fallback if no <main> is found (e.g., error page)
                window.location.href = url;
            }
        } catch (error) {
            console.error('SPA Load Error:', error);
            window.location.href = url; // Fallback to normal navigation
        } finally {
            hideLoader();
        }
    }

    // Intercept link clicks
    document.body.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        
        const url = link.href;
        const origin = window.location.origin;
        
        // Only intercept internal links, ignore admin and specific file types
        if (url.startsWith(origin) && 
            !url.includes('/admin') && 
            !link.hasAttribute('target') &&
            !link.hasAttribute('download')) {
            
            // Allow default behavior for some links (like anchor links on the same page)
            if (url.includes('#') && url.split('#')[0] === window.location.href.split('#')[0]) {
                const navLinks = document.getElementById('nav-links');
                if (navLinks) navLinks.classList.remove('open');
                return;
            }
            
            e.preventDefault();
            
            // Close mobile menu
            const navLinks = document.getElementById('nav-links');
            if (navLinks) navLinks.classList.remove('open');
            
            if (window.location.href !== url) {
                window.history.pushState({ url }, '', url);
                loadPage(url);
            }
        }
    });

    // Intercept form submissions (e.g., GET search form)
    document.body.addEventListener('submit', (e) => {
        const form = e.target.closest('form');
        // Only intercept GET forms on the front-end (like search)
        if (form && form.method.toUpperCase() === 'GET' && !form.action.includes('/admin')) {
            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            const url = (form.action || window.location.pathname) + '?' + params;
            
            window.history.pushState({ url }, '', url);
            loadPage(url);
        }
    });

    // Handle back/forward browser buttons
    window.addEventListener('popstate', () => {
        loadPage(window.location.href);
    });
});
