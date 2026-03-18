/**
 * spa_loader.js — SPA-like navigation for sucesiones sidebar.
 *
 * Intercepts sidebar link clicks and loads page content via AJAX
 * instead of full page reloads. Falls back to normal navigation
 * if anything goes wrong.
 *
 * Anti-crash: everything wrapped in try/catch with fallback to
 * window.location.href for any error.
 */
(function () {
    'use strict';

    // ─── Content container where page HTML is injected ────────────────
    var CONTENT_ID = 'spaContentArea';

    // ─── Execute <script> tags inside injected HTML ──────────────────
    // Patches document.addEventListener so DOMContentLoaded callbacks
    // execute immediately (since the DOM is already loaded).
    function executeScripts(container) {
        try {
            // Temporarily patch addEventListener to catch DOMContentLoaded
            var origAdd = document.addEventListener;
            document.addEventListener = function (type, fn, opts) {
                if (type === 'DOMContentLoaded') {
                    // DOM already loaded — run immediately
                    try { fn(); } catch (err) {
                        console.error('[SPA] DOMContentLoaded callback error:', err);
                    }
                } else {
                    origAdd.call(document, type, fn, opts);
                }
            };

            var scripts = container.querySelectorAll('script');
            for (var i = 0; i < scripts.length; i++) {
                var old = scripts[i];
                var s = document.createElement('script');

                // Copy attributes (src, type, etc.)
                for (var j = 0; j < old.attributes.length; j++) {
                    s.setAttribute(old.attributes[j].name, old.attributes[j].value);
                }

                // Copy inline content
                if (old.textContent) {
                    s.textContent = old.textContent;
                }

                old.parentNode.replaceChild(s, old);
            }

            // Restore original addEventListener
            document.addEventListener = origAdd;
        } catch (err) {
            console.error('[SPA] executeScripts error:', err);
            // Always restore even on error
            if (typeof origAdd === 'function') {
                document.addEventListener = origAdd;
            }
        }
    }

    // ─── Update sidebar active state ─────────────────────────────────
    function updateSidebar(activeMenu, activeItem) {
        try {
            var accordion = document.getElementById('accordionFlushExample');
            if (!accordion) return;

            // Collapse all panels, remove active styles
            accordion.querySelectorAll('.accordion-collapse').forEach(function (p) {
                p.classList.remove('show');
            });
            accordion.querySelectorAll('.accordion-button').forEach(function (b) {
                b.classList.add('collapsed');
            });
            accordion.querySelectorAll('.active-link').forEach(function (a) {
                a.classList.remove('active-link');
            });

            // Open the active panel
            if (activeMenu) {
                var panel = accordion.querySelector('[data-panel="' + activeMenu + '"]');
                var btn = accordion.querySelector('[data-section="' + activeMenu + '"]');
                if (panel) panel.classList.add('show');
                if (btn) btn.classList.remove('collapsed');
            }

            // Mark the active item
            if (activeItem) {
                accordion.querySelectorAll('.list-group-item a').forEach(function (a) {
                    if (a.textContent.trim() === activeItem) {
                        a.classList.add('active-link');
                    }
                });
            }
        } catch (err) {
            console.error('[SPA] updateSidebar error:', err);
        }
    }

    // ─── Load page content via AJAX ──────────────────────────────────
    function loadPage(url, pushState) {
        try {
            // Show minimal loading indicator
            var container = document.getElementById(CONTENT_ID);
            if (!container) {
                // Fallback: no container found
                window.location.href = url;
                return;
            }

            fetch(url, {
                method: 'GET',
                headers: { 'X-SPA-REQUEST': '1' },
                credentials: 'same-origin'
            })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                var ct = response.headers.get('content-type') || '';
                if (ct.indexOf('application/json') === -1) throw new Error('Not JSON');
                return response.json();
            })
            .then(function (data) {
                try {
                    if (!data || !data.html) throw new Error('Empty response');

                    // Inject HTML
                    container.innerHTML = data.html;

                    // Execute inline scripts
                    executeScripts(container);

                    // Re-init plugins for new content
                    if (typeof initFlatpickrs === 'function') initFlatpickrs();

                    // Update sidebar
                    updateSidebar(data.activeMenu, data.activeItem);

                    // Update browser URL
                    if (pushState) {
                        history.pushState({ spaUrl: url }, '', url);
                    }

                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'instant' });
                } catch (err) {
                    console.error('[SPA] render error:', err);
                    window.location.href = url;
                }
            })
            .catch(function (err) {
                console.error('[SPA] fetch error:', err);
                // Fallback to normal navigation
                window.location.href = url;
            });
        } catch (err) {
            console.error('[SPA] loadPage error:', err);
            window.location.href = url;
        }
    }

    // ─── Intercept sidebar clicks ────────────────────────────────────
    function initSpa() {
        try {
            var accordion = document.getElementById('accordionFlushExample');
            if (!accordion) return;

            // Use event delegation on the accordion (sidebar)
            accordion.addEventListener('click', function (e) {
                var link = e.target.closest('.list-group-item a');
                if (!link) return;

                var href = link.getAttribute('href');
                if (!href || href === '#') return;

                // Only intercept sucesion links (not dashboard, logout, etc.)
                if (href.indexOf('/simulador/sucesion/') === -1) return;

                e.preventDefault();
                loadPage(href, true);
            });

            // Also intercept links inside page content (e.g., Anverso→Reverso buttons)
            var contentArea = document.getElementById(CONTENT_ID);
            if (contentArea) {
                contentArea.addEventListener('click', function (e) {
                    var link = e.target.closest('a[href]');
                    if (!link) return;

                    var href = link.getAttribute('href');
                    if (!href || href === '#') return;

                    // Only intercept sucesion links
                    if (href.indexOf('/simulador/sucesion/') === -1) return;

                    // Skip links that have special behavior (download, target=_blank, etc.)
                    if (link.getAttribute('target') === '_blank') return;
                    if (link.getAttribute('download') !== null) return;

                    e.preventDefault();
                    loadPage(href, true);
                });
            }

            // Handle browser back/forward buttons
            window.addEventListener('popstate', function (e) {
                if (e.state && e.state.spaUrl) {
                    loadPage(e.state.spaUrl, false);
                }
            });

            // Mark current page in history state
            history.replaceState({ spaUrl: window.location.href }, '', window.location.href);

        } catch (err) {
            console.error('[SPA] initSpa error:', err);
        }
    }

    // ─── Start after DOM is ready ────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSpa);
    } else {
        initSpa();
    }
})();
