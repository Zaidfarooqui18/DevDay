/**
 * DevDay Core Application Framework
 * Handles CSRF, Fetch wrappers, Toasts, Modals, Profile Dropdown, and Keyboard Shortcuts
 */

(function () {
    window.DevDayUI = {
        // Standardized Fetch API with automatic CSRF & Error Handling
        async request(url, options = {}) {
            const defaultHeaders = {
                'Accept': 'application/json',
                'X-CSRF-Token': window.DevDay?.csrfToken || '',
            };

            // If payload is object, encode as JSON
            if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
                defaultHeaders['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.body);
            }

            options.headers = { ...defaultHeaders, ...options.headers };

            try {
                const response = await fetch(url, options);
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error(`[DevDay Raw Server Response] ${url}:`, text);
                    const cleanText = text.replace(/<[^>]*>?/gm, ' ').trim();
                    throw new Error(cleanText && cleanText.length < 150 ? cleanText : `Server returned an unexpected response (${response.status}).`);
                }

                if (!response.ok || !data.success) {
                    const message = data.message || `Request failed with status ${response.status}`;
                    const err = new Error(message);
                    err.data = data;
                    err.status = response.status;
                    throw err;
                }

                return data;
            } catch (error) {
                console.error(`[DevDay API Error] ${url}:`, error);
                throw error;
            }
        },

        // Paper Toast notifications
        showToast(message, type = 'info', duration = 3500) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            let typeClass = 'toast-info';
            let icon = 'info';

            if (type === 'success') {
                typeClass = 'toast-success';
                icon = 'check-circle';
            } else if (type === 'error') {
                typeClass = 'toast-error';
                icon = 'alert-circle';
            } else if (type === 'warning') {
                typeClass = 'toast-info';
                icon = 'alert-triangle';
            }

            toast.className = `toast-item ${typeClass}`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-4 h-4 shrink-0"></i>
                <span class="flex-1">${message}</span>
            `;

            container.appendChild(toast);
            if (window.lucide) lucide.createIcons();

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(12px)';
                setTimeout(() => toast.remove(), 200);
            }, duration);
        },

        // Profile Dropdown Toggle & State Management
        toggleProfileMenu(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            const menu = document.getElementById('profile-dropdown-menu');
            const btn = document.getElementById('profile-toggle-btn');
            if (!menu) return;

            const isShown = menu.classList.contains('show');
            if (isShown) {
                menu.classList.remove('show');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            } else {
                menu.classList.add('show');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            }
            if (window.lucide) lucide.createIcons();
        },

        closeProfileMenu() {
            const menu = document.getElementById('profile-dropdown-menu');
            const btn = document.getElementById('profile-toggle-btn');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        },

        // Modal helpers
        openModal(id) {
            const dialog = document.getElementById(id);
            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
                if (window.lucide) lucide.createIcons();
            }
        },

        closeModal(id) {
            const dialog = document.getElementById(id);
            if (dialog && typeof dialog.close === 'function') {
                dialog.close();
            }
        },

        openAddAssignmentModal(preset = {}) {
            const form = document.getElementById('assignment-form');
            if (form) form.reset();
            
            const titleEl = document.getElementById('assignment-modal-title');
            if (titleEl) titleEl.textContent = 'Add Daily Assignment';
            
            const idEl = document.getElementById('assignment-id');
            if (idEl) idEl.value = '';
            
            const submitBtn = document.getElementById('assignment-submit-btn');
            if (submitBtn) submitBtn.textContent = 'Add Task';

            if (preset.category && document.getElementById('assign-category')) {
                document.getElementById('assign-category').value = preset.category;
            }
            if (preset.project_id && document.getElementById('assign-project')) {
                document.getElementById('assign-project').value = preset.project_id;
            }

            this.openModal('assignment-modal');
            setTimeout(() => document.getElementById('assign-title')?.focus(), 60);
        },

        formatMinutes(minutes) {
            if (!minutes || minutes <= 0) return '0m';
            const h = Math.floor(minutes / 60);
            const m = Math.round(minutes % 60);
            if (h > 0 && m > 0) return `${h}h ${m}m`;
            if (h > 0) return `${h}h`;
            return `${m}m`;
        },

        formatSeconds(seconds) {
            if (!seconds || seconds <= 0) return '00:00:00';
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
    };

    // Global Click Listener for Dropdown Dismissal
    document.addEventListener('click', (e) => {
        const container = document.getElementById('profile-container');
        if (container && !container.contains(e.target)) {
            window.DevDayUI.closeProfileMenu();
        }
    });

    // Global Keyboard Shortcuts (N = new task, Esc = close modal / close dropdown)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.DevDayUI.closeProfileMenu();
            // Close any open dialogs if open
            document.querySelectorAll('dialog[open]').forEach(dialog => {
                dialog.close();
            });
            return;
        }

        // If user is typing in an input or textarea, don't trigger global shortcuts
        const tag = e.target.tagName.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable) {
            return;
        }

        if (e.key === 'n' || e.key === 'N') {
            e.preventDefault();
            window.DevDayUI.openAddAssignmentModal();
        }
    });
})();
