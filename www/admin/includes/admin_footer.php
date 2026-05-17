<script>
(function () {
    // ── Theme ────────────────────────────────────────────
    const themeBtn = document.getElementById('themeToggle');
    function applyTheme(dark) {
        document.body.classList.toggle('dark-mode', dark);
        if (themeBtn) themeBtn.textContent = dark ? '☀️ Light' : '🌙 Dark';
        localStorage.setItem('adminTheme', dark ? 'dark' : 'light');
    }
    applyTheme(localStorage.getItem('adminTheme') === 'dark');
    if (themeBtn) themeBtn.addEventListener('click', () => applyTheme(!document.body.classList.contains('dark-mode')));

    // ── Sidebar ──────────────────────────────────────────
    const sidebarEl = document.getElementById('sidebar');
    const toggleEl  = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebarEl.classList.add('open');
        toggleEl.classList.add('open');
    }
    function closeSidebar() {
        sidebarEl.classList.remove('open');
        toggleEl.classList.remove('open');
    }

    if (toggleEl && sidebarEl) {
        toggleEl.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebarEl.classList.contains('open') ? closeSidebar() : openSidebar();
        });

        // Close when tapping anywhere OUTSIDE the sidebar
        document.addEventListener('touchstart', function(e) {
            if (sidebarEl.classList.contains('open') &&
                !sidebarEl.contains(e.target) &&
                !toggleEl.contains(e.target)) {
                closeSidebar();
            }
        }, { passive: true });

        document.addEventListener('click', function(e) {
            if (sidebarEl.classList.contains('open') &&
                !sidebarEl.contains(e.target) &&
                !toggleEl.contains(e.target)) {
                closeSidebar();
            }
        });
    }

    // ── Notifications ────────────────────────────────────
    const notifBtn      = document.getElementById('adminNotifBtn');
    const notifDropdown = document.getElementById('adminNotifDropdown');
    const markAllBtn    = document.getElementById('adminMarkAllRead');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', e => {
            e.stopPropagation();
            notifDropdown.classList.toggle('open');
        });
        document.addEventListener('click', e => {
            if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('open');
            }
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('../../actions/admin/mark_notifications_read.php', { method: 'POST' })
                .then(() => {
                    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
                    const badge = document.querySelector('.notif-badge');
                    if (badge) badge.remove();
                    if (notifDropdown) notifDropdown.classList.remove('open');
                });
        });
    }
})();
</script>
</body>
</html>