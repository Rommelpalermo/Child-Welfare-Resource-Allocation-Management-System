/* ============================================================
   Bahay Pag-asa — App JavaScript
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ---- Sidebar toggle ------------------------------------
    const toggleBtn   = document.getElementById('sidebarToggle');
    const sidebar     = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                if (mainContent) mainContent.classList.toggle('expanded');
            }
        });
    }

    // ---- Auto-init DataTables ------------------------------
    if (typeof $.fn.DataTable !== 'undefined') {
        $('table.datatable').DataTable({
            responsive: true,
            pageLength: 15,
            language: { search: 'Search:', paginate: { previous: '&laquo;', next: '&raquo;' } }
        });
    }

    // ---- Auto-dismiss flash messages -----------------------
    setTimeout(function () {
        const flash = document.querySelector('.flash-container .alert');
        if (flash) {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 600);
        }
    }, 4000);

    // ---- Confirm delete ------------------------------------
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // ---- Print button --------------------------------------
    document.querySelectorAll('.btn-print').forEach(function (btn) {
        btn.addEventListener('click', function () { window.print(); });
    });
});
