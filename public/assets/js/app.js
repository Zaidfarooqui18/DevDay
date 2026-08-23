/**
 * DevDay Core Application Framework
 * Handles CSRF, Fetch wrappers, Toasts, Modals, and Keyboard Shortcuts
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
                const data = await response.json();

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

        // Toast notifications
        showToast(message, type = 'info', duration = 3500) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast-item flex items-center gap-2.5 px-4 py-3 rounded-xl border text-xs font-medium shadow-2xl backdrop-blur-md transition-all`;

            let bgClass = 'bg-[#111726]/95 border-slate-700 text-white';
            let icon = 'info';

            if (type === 'success') {
                bgClass = 'bg-emerald-950/90 border-emerald-500/40 text-emerald-200';
                icon = 'check-circle';
            } else if (type === 'error') {
                bgClass = 'bg-rose-950/90 border-rose-500/40 text-rose-200';
                icon = 'alert-circle';
            } else if (type === 'warning') {
                bgClass = 'bg-amber-950/90 border-amber-500/40 text-amber-200';
                icon = 'alert-triangle';
            }

            toast.className += ` ${bgClass}`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-4 h-4 shrink-0"></i>
                <span class="flex-1">${message}</span>
            `;

            container.appendChild(toast);
            if (window.lucide) lucide.createIcons();

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 250);
            }, duration);
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
            
            document.getElementById('assignment-modal-title').textContent = 'Create Daily Assignment';
            document.getElementById('assignment-id').value = '';
            document.getElementById('assignment-submit-btn').textContent = 'Create Assignment';

            if (preset.category) document.getElementById('assign-category').value = preset.category;
            if (preset.project_id) document.getElementById('assign-project').value = preset.project_id;

            this.openModal('assignment-modal');
            setTimeout(() => document.getElementById('assign-title')?.focus(), 50);
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

    // Keyboard Shortcuts (N = new task, Esc = close modal)
    document.addEventListener('keydown', (e) => {
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
