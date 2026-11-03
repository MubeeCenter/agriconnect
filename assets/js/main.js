/* ============================================================
   AGRICONNECT — Main JS (Public Site)
   Vanilla JS. No dependencies.
   ============================================================ */

(function (window, document) {
    'use strict';

    // ============================================================
    // NAMESPACE
    // ============================================================
    const Agri = window.Agri = window.Agri || {};

    // ============================================================
    // DOM READY HELPER
    // ============================================================
    Agri.ready = function (fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    };

    // ============================================================
    // 1. TOAST NOTIFICATIONS
    // Usage: Agri.toast('Saved successfully', 'success')
    // Types: success | warning | danger | info
    // ============================================================
    Agri.toast = function (message, type = 'info', duration = 3500) {
        let container = document.getElementById('agri-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'agri-toast-container';
            container.style.cssText = [
                'position:fixed',
                'top:80px',
                'right:20px',
                'z-index:9999',
                'display:flex',
                'flex-direction:column',
                'gap:10px',
                'max-width:360px',
                'pointer-events:none'
            ].join(';');
            document.body.appendChild(container);
        }

        const colors = {
            success: { bg: '#EAF3EC', border: '#2C963F', text: '#22612E' },
            warning: { bg: '#FEF3E6', border: '#F18114', text: '#A8590D' },
            danger:  { bg: '#FDECEC', border: '#E7202A', text: '#B81820' },
            info:    { bg: '#E6F0FA', border: '#1E6FBF', text: '#1E6FBF' }
        };
        const c = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.style.cssText = [
            'background:' + c.bg,
            'border-left:4px solid ' + c.border,
            'color:' + c.text,
            'padding:14px 18px',
            'border-radius:8px',
            'box-shadow:0 4px 12px rgba(20,24,29,0.12)',
            'font-size:14px',
            'font-weight:500',
            'line-height:1.4',
            'pointer-events:auto',
            'opacity:0',
            'transform:translateX(30px)',
            'transition:all 250ms ease'
        ].join(';');
        toast.textContent = message;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        // Auto-remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(30px)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    // ============================================================
    // 2. MOBILE NAV TOGGLE
    // ============================================================
    Agri.initMobileNav = function () {
        const toggle = document.querySelector('[data-nav-toggle]');
        const links  = document.querySelector('.agri-nav-links');
        if (!toggle || !links) return;

        // Add mobile menu styles once
        if (!document.getElementById('agri-mobile-nav-style')) {
            const style = document.createElement('style');
            style.id = 'agri-mobile-nav-style';
            style.textContent = `
                @media (max-width: 768px) {
                    .agri-nav-links.is-open {
                        display: flex !important;
                        flex-direction: column;
                        position: absolute;
                        top: 64px;
                        left: 0;
                        right: 0;
                        background: #fff;
                        padding: 16px 24px;
                        box-shadow: 0 8px 20px rgba(20,24,29,0.08);
                        gap: 4px;
                        border-bottom: 1px solid #E1E5E9;
                        align-items: stretch;
                    }
                    .agri-nav-links.is-open a {
                        padding: 12px 0;
                        border-bottom: 1px solid #F1F3F5;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        toggle.addEventListener('click', () => {
            links.classList.toggle('is-open');
            const isOpen = links.classList.contains('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close on link click
        links.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => links.classList.remove('is-open'));
        });
    };

    // ============================================================
    // 3. COPY TO CLIPBOARD
    // Usage: <button data-copy="text to copy">Copy</button>
    // ============================================================
    Agri.initCopyButtons = function () {
        document.querySelectorAll('[data-copy]').forEach(btn => {
            if (btn.dataset.copyBound === '1') return;
            btn.dataset.copyBound = '1';

            btn.addEventListener('click', async function () {
                const text = this.dataset.copy;
                const original = this.textContent;

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                    } else {
                        // Fallback for non-HTTPS
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                    }

                    this.textContent = 'Copied!';
                    Agri.toast('Copied to clipboard', 'success', 2000);
                    setTimeout(() => { this.textContent = original; }, 1500);
                } catch (err) {
                    Agri.toast('Copy failed', 'danger');
                }
            });
        });
    };

    // ============================================================
    // 4. SMOOTH SCROLL FOR ANCHORS
    // ============================================================
    Agri.initSmoothScroll = function () {
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#' || targetId.length < 2) return;

                const target = document.querySelector(targetId);
                if (!target) return;

                e.preventDefault();
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            });
        });
    };

    // ============================================================
    // 5. ACTIVE NAV LINK (based on current path)
    // ============================================================
    Agri.initActiveNav = function () {
        const path = window.location.pathname.split('/').pop() || 'index.php';
        document.querySelectorAll('.agri-nav-links a').forEach(link => {
            const href = (link.getAttribute('href') || '').split('/').pop();
            if (href === path) {
                link.classList.add('is-active');
            }
        });
    };

    // ============================================================
    // 6. LAZY LOAD IMAGES (native fallback)
    // ============================================================
    Agri.initLazyImages = function () {
        if ('loading' in HTMLImageElement.prototype) {
            document.querySelectorAll('img:not([loading])').forEach(img => {
                img.setAttribute('loading', 'lazy');
            });
        }
    };

    // ============================================================
    // 7. AUTO-DISMISS ALERTS
    // ============================================================
    Agri.initAutoDismissAlerts = function () {
        document.querySelectorAll('.agri-alert[data-auto-dismiss]').forEach(alert => {
            const delay = parseInt(alert.dataset.autoDismiss, 10) || 5000;
            setTimeout(() => {
                alert.style.transition = 'opacity 300ms, transform 300ms';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 300);
            }, delay);
        });
    };

    // ============================================================
    // 8. CONFIRM LINKS
    // Usage: <a href="..." data-confirm="Are you sure?">Delete</a>
    // ============================================================
    Agri.initConfirmLinks = function () {
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function (e) {
                if (!window.confirm(this.dataset.confirm)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    };

    // ============================================================
    // 9. FORMAT HELPERS
    // ============================================================
    Agri.formatNaira = function (amount) {
        return 'N' + Number(amount).toLocaleString('en-NG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    };

    Agri.formatDate = function (dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-NG', {
            day: '2-digit', month: 'short', year: 'numeric'
        });
    };

    // ============================================================
    // 10. DEBOUNCE
    // ============================================================
    Agri.debounce = function (fn, wait = 300) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), wait);
        };
    };

    // ============================================================
    // BOOTSTRAP — run on DOM ready
    // ============================================================
    Agri.ready(function () {
        Agri.initMobileNav();
        Agri.initCopyButtons();
        Agri.initSmoothScroll();
        Agri.initActiveNav();
        Agri.initLazyImages();
        Agri.initAutoDismissAlerts();
        Agri.initConfirmLinks();
    });

})(window, document);