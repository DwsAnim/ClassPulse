// ClassPulse Theme System — include on every page
(function(){
    const KEY = 'cp_theme';
    const root = document.documentElement;

    // Apply theme immediately (before paint) to avoid flash
    function apply(theme) {
        root.setAttribute('data-theme', theme);
        localStorage.setItem(KEY, theme);
    }

    // Read saved preference; default = dark
    const saved = localStorage.getItem(KEY) || 'dark';
    apply(saved);

    // Expose toggle globally
    window.toggleTheme = function() {
        const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        apply(next);
        // Update all toggle buttons on the page
        document.querySelectorAll('[data-theme-btn]').forEach(updateBtn);
    };

    window.currentTheme = function() { return root.getAttribute('data-theme'); };

    function updateBtn(btn) {
        const isDark = root.getAttribute('data-theme') === 'dark';
        btn.innerHTML = isDark
            ? '<i class="fas fa-sun"></i>'
            : '<i class="fas fa-moon"></i>';
        btn.title = isDark ? 'Switch to Light mode' : 'Switch to Dark mode';
    }

    // Wire buttons once DOM is ready
    function wireButtons() {
        document.querySelectorAll('[data-theme-btn]').forEach(btn => {
            updateBtn(btn);
            btn.addEventListener('click', window.toggleTheme);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wireButtons);
    } else {
        wireButtons();
    }
})();