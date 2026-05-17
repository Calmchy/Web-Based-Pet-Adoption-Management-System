<script>
(function () {
    // ############ Theme ############
    const themeBtn = document.getElementById('themeToggle');
    function applyTheme(dark) {
        document.body.classList.toggle('dark-mode', dark);
        if (themeBtn) themeBtn.textContent = dark ? '☀️ Light' : '🌙 Dark';
        localStorage.setItem('adminTheme', dark ? 'dark' : 'light');
    }
    applyTheme(localStorage.getItem('adminTheme') === 'dark');
    if (themeBtn) themeBtn.addEventListener('click', () => applyTheme(!document.body.classList.contains('dark-mode')));
    // ############ Sidebar toggle (mobile) ############
    const sidebarEl = document.getElementById('sidebar');
    const toggleEl = document.getElementById('sidebarToggle');
    const overlayEl = document.getElementById('sidebarOverlay');
    if (toggleEl && sidebarEl) {
        const open = () => { sidebarEl.classList.add('open'); toggleEl.classList.add('open'); if(overlayEl) overlayEl.classList.add('show'); };
        const close = () => { sidebarEl.classList.remove('open'); toggleEl.classList.remove('open'); if(overlayEl) overlayEl.classList.remove('show'); };
        toggleEl.addEventListener('click', () => sidebarEl.classList.contains('open') ? close() : open());
        if (overlayEl) overlayEl.addEventListener('click', close);
        document.querySelectorAll('.sidebar-nav a').forEach(a => a.addEventListener('click', close));
    }

    // ############ Notification bell toggle ############
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
                    notifDropdown.classList.remove('open');
                });
        });
    }
})();
</script>
</body>
</html>