/**
 * DevDay Focus Timer Engine
 * Timestamp-driven accuracy, reload resilience, and multi-session synchronization
 */

(function () {
    let activeSession = null;
    let timerInterval = null;

    window.DevDayTimer = {
        async init() {
            try {
                const response = await window.DevDayUI.request('/api/focus.php?action=active');
                if (response.data) {
                    this.setActiveSession(response.data);
                } else {
                    this.clearActiveSession();
                }
            } catch (err) {
                console.warn('[Timer Init]', err);
            }
        },

        setActiveSession(session) {
            activeSession = session;
            this.startTicker();
            this.updateUI();
        },

        clearActiveSession() {
            activeSession = null;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            this.updateUI();
        },

        getActive() {
            return activeSession;
        },

        startTicker() {
            if (timerInterval) clearInterval(timerInterval);

            const tick = () => {
                if (!activeSession) return;
                
                // Calculate elapsed time from server timestamp
                const startedTime = new Date(activeSession.started_at.replace(' ', 'T')).getTime();
                const now = Date.now();
                const elapsedSeconds = Math.max(0, Math.floor((now - startedTime) / 1000));
                
                this.renderTime(elapsedSeconds);
            };

            tick();
            timerInterval = setInterval(tick, 1000);
        },

        renderTime(totalSeconds) {
            const formatted = window.DevDayUI.formatSeconds(totalSeconds);
            
            // 1. Update top nav timer
            const navTimer = document.getElementById('nav-active-timer');
            const navClock = document.getElementById('nav-timer-clock');
            const navTask = document.getElementById('nav-timer-task');
            if (navTimer && navClock && navTask) {
                navTimer.classList.remove('hidden');
                navTimer.classList.add('flex');
                navClock.textContent = formatted;
                navTask.textContent = activeSession.assignment_title || 'Focusing';
            }

            // 2. Update dashboard hero focus widget if present
            const heroWidget = document.getElementById('dashboard-focus-widget');
            const heroClock = document.getElementById('dashboard-focus-clock');
            const heroTask = document.getElementById('dashboard-focus-task');
            if (heroWidget && heroClock && heroTask) {
                heroWidget.classList.remove('hidden');
                heroClock.textContent = formatted;
                heroTask.textContent = activeSession.assignment_title || 'Active Session';
            }

            // 3. Update document title
            document.title = `[${formatted}] DevDay Focus`;
        },

        updateUI() {
            const navTimer = document.getElementById('nav-active-timer');
            const heroWidget = document.getElementById('dashboard-focus-widget');

            if (!activeSession) {
                if (navTimer) {
                    navTimer.classList.add('hidden');
                    navTimer.classList.remove('flex');
                }
                if (heroWidget) {
                    heroWidget.classList.add('hidden');
                }
                document.title = 'DevDay — Developer Daily Work & Reporting System';
            }

            // Refresh dashboard UI state if present to show focus badges
            if (window.DevDayDashboard && typeof window.DevDayDashboard.refreshUIState === 'function') {
                window.DevDayDashboard.refreshUIState();
            }
        },

        async start(assignmentId) {
            try {
                const response = await window.DevDayUI.request('/api/focus.php?action=start', {
                    method: 'POST',
                    body: { assignment_id: assignmentId }
                });

                window.DevDayUI.showToast('Focus session started.', 'info');
                this.setActiveSession(response.data.session);

                if (window.DevDayDashboard && typeof window.DevDayDashboard.loadTodayData === 'function') {
                    window.DevDayDashboard.loadTodayData();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to start timer.', 'error');
            }
        },

        async stop(sessionId = null) {
            try {
                const response = await window.DevDayUI.request('/api/focus.php?action=stop', {
                    method: 'POST',
                    body: { session_id: sessionId || activeSession?.id }
                });

                const durationSec = response.data?.session?.duration_seconds || 0;
                const minutesFormatted = window.DevDayUI.formatMinutes(Math.round(durationSec / 60));
                window.DevDayUI.showToast(`Focus session saved (${minutesFormatted}).`, 'success');
                
                this.clearActiveSession();

                if (window.DevDayDashboard && typeof window.DevDayDashboard.loadTodayData === 'function') {
                    window.DevDayDashboard.loadTodayData();
                }
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to stop timer.', 'error');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.user) {
            window.DevDayTimer.init();
        }
    });
})();
