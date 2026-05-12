<script>
(function () {
    // Theme
    const themeBtn = document.getElementById('themeToggle');
    function applyTheme(dark) {
        document.body.classList.toggle('dark-mode', dark);
        if (themeBtn) themeBtn.textContent = dark ? '☀️ Light' : '🌙 Dark';
        localStorage.setItem('adminTheme', dark ? 'dark' : 'light');
    }
    applyTheme(localStorage.getItem('adminTheme') === 'dark');
    if (themeBtn) themeBtn.addEventListener('click', () => applyTheme(!document.body.classList.contains('dark-mode')));

    // Sidebar toggle (mobile)
    const sidebarEl  = document.getElementById('sidebar');
    const toggleEl   = document.getElementById('sidebarToggle');
    const overlayEl  = document.getElementById('sidebarOverlay');
    if (toggleEl && sidebarEl) {
        const open  = () => { sidebarEl.classList.add('open');    toggleEl.classList.add('open');    if(overlayEl) overlayEl.classList.add('show'); };
        const close = () => { sidebarEl.classList.remove('open'); toggleEl.classList.remove('open'); if(overlayEl) overlayEl.classList.remove('show'); };
        toggleEl.addEventListener('click', () => sidebarEl.classList.contains('open') ? close() : open());
        if (overlayEl) overlayEl.addEventListener('click', close);
        document.querySelectorAll('.sidebar-nav a').forEach(a => a.addEventListener('click', close));
    }
})();
</script>
</body>
</html>