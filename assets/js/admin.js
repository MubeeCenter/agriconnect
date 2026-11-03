/* ============================================================
   AGRICONNECT — Admin JS
   Depends on: main.js (uses Agri namespace + toast)
   ============================================================ */

(function (window, document) {
    'use strict';

    const Agri = window.Agri = window.Agri || {};

    // ============================================================
    // 1. SIDEBAR TOGGLE (mobile)
    // ============================================================
    Agri.initAdminSidebar = function () {
        const toggle   = document.querySelector('[data-admin-menu-toggle]');
        const sidebar  = document.querySelector('.admin-sidebar');
        const backdrop = document.querySelector('.admin-sidebar-backdrop');

        if (!toggle || !sidebar) return;

        // Create backdrop if missing
        let bd = backdrop;
        if (!bd) {
            bd = document.createElement('div');
            bd.className = 'admin-sidebar-backdrop';
            document.body.appendChild(bd);
        }

        const open = () => {
            sidebar.classList.add('is-open');
            bd.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        };

        const close = () => {
            sidebar.classList.remove('is-open');
            bd.classList.remove('is-open');
            document.body.style.overflow = '';
        };

        toggle.addEventListener('click', () => {
            sidebar.classList.contains('is-open') ? close() : open();
        });

        bd.addEventListener('click', close);

        // Close on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                close();
            }
        });

        // Close on resize to desktop
        window.addEventListener('resize', Agri.debounce(() => {
            if (window.innerWidth > 768 && sidebar.classList.contains('is-open')) {
                close();
            }
        }, 200));
    };

    // ============================================================
    // 2. MODAL SYSTEM
    // Usage:
    //   <button data-modal-open="modal-id">Open</button>
    //   <div class="admin-modal-backdrop" id="modal-id">
    //       <div class="admin-modal">...</div>
    //   </div>
    // ============================================================
    Agri.initModals = function () {
        // Open triggers
        document.querySelectorAll('[data-modal-open]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const id = btn.dataset.modalOpen;
                const backdrop = document.getElementById(id);
                if (!backdrop) return;
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close triggers
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const backdrop = btn.closest('.admin-modal-backdrop');
                if (backdrop) {
                    backdrop.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
        });

        // Click outside to close
        document.querySelectorAll('.admin-modal-backdrop').forEach(bd => {
            bd.addEventListener('click', (e) => {
                if (e.target === bd) {
                    bd.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
        });

        // ESC to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.admin-modal-backdrop.is-open').forEach(bd => {
                    bd.classList.remove('is-open');
                });
                document.body.style.overflow = '';
            }
        });
    };

    // ============================================================
    // 3. LIVE TABLE SEARCH
    // Usage: <input data-table-search="tableId">
    // ============================================================
    Agri.initTableSearch = function () {
        document.querySelectorAll('[data-table-search]').forEach(input => {
            const tableId = input.dataset.tableSearch;
            const table = document.getElementById(tableId);
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr'));

            const filter = Agri.debounce(function () {
                const q = input.value.trim().toLowerCase();
                let visible = 0;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const show = q === '' || text.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                // Empty state toggle
                const emptyRow = table.querySelector('[data-empty-row]');
                if (emptyRow) {
                    emptyRow.style.display = visible === 0 ? '' : 'none';
                }
            }, 200);

            input.addEventListener('input', filter);
        });
    };

    // ============================================================
    // 4. TABLE FILTERS (dropdowns)
    // Usage: <select data-table-filter="tableId" data-filter-col="2">
    // ============================================================
    Agri.initTableFilters = function () {
        document.querySelectorAll('[data-table-filter]').forEach(select => {
            const tableId = select.dataset.tableFilter;
            const colIndex = parseInt(select.dataset.filterCol || '0', 10);
            const table = document.getElementById(tableId);
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr'));

            select.addEventListener('change', () => {
                const value = select.value.toLowerCase();
                rows.forEach(row => {
                    const cell = row.cells[colIndex];
                    if (!cell) return;
                    const text = cell.textContent.trim().toLowerCase();
                    row.style.display = (value === '' || text === value) ? '' : 'none';
                });
            });
        });
    };

    // ============================================================
    // 5. CONFIRM DELETE (works for links and buttons)
    // Usage: <a href="delete.php?id=1" data-confirm-delete="Delete this listing?">
    // ============================================================
    Agri.initConfirmDelete = function () {
        document.querySelectorAll('[data-confirm-delete]').forEach(el => {
            el.addEventListener('click', function (e) {
                const msg = this.dataset.confirmDelete || 'Are you sure you want to delete this?';
                if (!window.confirm(msg)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    };

    // ============================================================
    // 6. AUTO-SUBMIT FORM ON SELECT CHANGE
    // Usage: <form data-auto-submit><select name="status" ...>
    // ============================================================
    Agri.initAutoSubmitForms = function () {
        document.querySelectorAll('[data-auto-submit] select, [data-auto-submit] input[type="checkbox"]').forEach(el => {
            el.addEventListener('change', function () {
                this.form.submit();
            });
        });
    };

    // ============================================================
    // 7. FORM LOADING STATE
    // Adds spinner + disables button on submit
    // Usage: <form data-loading-form>
    // ============================================================
    Agri.initFormLoading = function () {
        document.querySelectorAll('[data-loading-form]').forEach(form => {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;

                const original = btn.innerHTML;
                btn.disabled = true;
                btn.dataset.originalText = original;
                btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:agri-spin 0.6s linear infinite;vertical-align:middle;margin-right:6px;"></span> Processing...';

                // Re-enable after 15s as a safety net
                setTimeout(() => {
                    if (btn.disabled) {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    }
                }, 15000);
            });
        });

        // Inject spinner animation once
        if (!document.getElementById('agri-spin-style')) {
            const style = document.createElement('style');
            style.id = 'agri-spin-style';
            style.textContent = '@keyframes agri-spin{to{transform:rotate(360deg)}}';
            document.head.appendChild(style);
        }
    };

    // ============================================================
    // 8. FLASH MESSAGE FROM URL
    // Reads ?msg=... and shows a toast
    // ============================================================
    Agri.initFlashFromUrl = function () {
        const params = new URLSearchParams(window.location.search);
        const msg = params.get('msg');
        const type = params.get('type') || 'success';
        if (msg) {
            Agri.toast(decodeURIComponent(msg), type);
        }
    };

    // ============================================================
    // 9. COPY-TO-CLIPBOARD (already in main.js, but ensures admin has it)
    // ============================================================
    // (Reuses main.js initCopyButtons)

    // ============================================================
    // 10. LIVE CLOCK (optional, for dashboard)
    // Usage: <span data-live-clock></span>
    // ============================================================
    Agri.initLiveClock = function () {
        const el = document.querySelector('[data-live-clock]');
        if (!el) return;

        const update = () => {
            const now = new Date();
            el.textContent = now.toLocaleString('en-NG', {
                weekday: 'short', day: '2-digit', month: 'short',
                hour: '2-digit', minute: '2-digit'
            });
        };

        update();
        setInterval(update, 60000);
    };

    // ============================================================
    // 11. SHOW/HIDE PASSWORD TOGGLE
    // Usage: <button data-toggle-password="fieldId">Show</button>
    // ============================================================
    Agri.initPasswordToggle = function () {
        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const field = document.getElementById(btn.dataset.togglePassword);
                if (!field) return;

                const isPassword = field.type === 'password';
                field.type = isPassword ? 'text' : 'password';
                btn.textContent = isPassword ? 'Hide' : 'Show';
            });
        });
    };

    // ============================================================
    // BOOTSTRAP
    // ============================================================
    Agri.ready(function () {
        Agri.initAdminSidebar();
        Agri.initModals();
        Agri.initTableSearch();
        Agri.initTableFilters();
        Agri.initConfirmDelete();
        Agri.initAutoSubmitForms();
        Agri.initFormLoading();
        Agri.initFlashFromUrl();
        Agri.initLiveClock();
        Agri.initPasswordToggle();
    });

})(window, document);