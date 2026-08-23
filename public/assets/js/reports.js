/**
 * DevDay Historical Reports Controller
 */

(function () {
    let currentSelectedReport = null;

    window.DevDayReportsHistory = {
        async init() {
            await this.loadHistory();
        },

        async loadHistory() {
            try {
                const response = await window.DevDayUI.request('/api/reports.php?action=history');
                this.renderHistory(response.data || []);
            } catch (err) {
                window.DevDayUI.showToast('Failed to load report history.', 'error');
            }
        },

        renderHistory(reports) {
            const tbody = document.getElementById('reports-table-body');
            if (!tbody) return;

            if (reports.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-950/50 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="mail-search" class="w-6 h-6"></i>
                            </div>
                            <h4 class="text-sm font-bold text-white mb-1">No reports submitted yet</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">Your generated daily reports and email history snapshots will appear here.</p>
                        </td>
                    </tr>
                `;
                if (window.lucide) lucide.createIcons();
                return;
            }

            let html = '';
            reports.forEach(r => {
                const dateObj = new Date(r.report_date + 'T00:00:00');
                const dateFormatted = dateObj.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
                const timeFormatted = r.sent_at ? new Date(r.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '-';

                let statusBadge = '';
                if (r.status === 'SENT') {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30 flex items-center gap-1 inline-flex"><i data-lucide="check" class="w-3 h-3"></i> Sent</span>';
                } else if (r.status === 'FAILED') {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-950 text-rose-300 border border-rose-500/30 flex items-center gap-1 inline-flex"><i data-lucide="alert-circle" class="w-3 h-3"></i> Failed</span>';
                } else {
                    statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700 flex items-center gap-1 inline-flex">Draft</span>';
                }

                html += `
                    <tr class="border-b border-slate-800/60 hover:bg-white/[0.02] transition-colors text-xs">
                        <td class="py-3.5 px-4 font-semibold text-white">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-indigo-400"></i>
                                <span>${dateFormatted}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300 font-mono">
                            <div class="flex items-center gap-2">
                                <span>${Math.round(r.completion_percentage)}%</span>
                                <span class="text-slate-500">(${r.completed_tasks}/${r.total_tasks})</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-cyan-400 font-medium">
                            ${window.DevDayUI.formatMinutes(r.focus_minutes)}
                        </td>
                        <td class="py-3.5 px-4 text-slate-300 truncate max-w-[160px]" title="${r.recipient_email}">
                            ${r.recipient_email}
                        </td>
                        <td class="py-3.5 px-4">
                            ${statusBadge}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="window.DevDayReportsHistory.viewReportSnapshot(${r.id})" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200 text-[11px] font-medium transition-colors">
                                    View
                                </button>
                                <button onclick="window.DevDayReportsHistory.promptResend(${r.id})" class="px-2.5 py-1 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-300 border border-indigo-500/30 text-[11px] font-medium transition-colors">
                                    Resend
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            if (window.lucide) lucide.createIcons();
        },

        async viewReportSnapshot(id) {
            try {
                const response = await window.DevDayUI.request(`/api/reports.php?action=view&id=${id}`);
                const r = response.data;
                currentSelectedReport = r;

                const iframe = document.getElementById('history-report-iframe');
                if (iframe) {
                    iframe.srcdoc = r.html_content;
                }

                document.getElementById('history-report-title').textContent = `Report Archive: ${r.report_date}`;
                document.getElementById('history-report-meta').textContent = `To: ${r.recipient_email} | Subject: ${r.email_subject}`;

                window.DevDayUI.openModal('history-viewer-modal');
            } catch (err) {
                window.DevDayUI.showToast('Failed to load stored report snapshot.', 'error');
            }
        },

        async promptResend(id) {
            if (!confirm('Resend this exact report snapshot to the configured recipient?')) return;

            try {
                window.DevDayUI.showToast('Resending report snapshot...', 'info');
                const response = await window.DevDayUI.request('/api/reports.php?action=resend', {
                    method: 'POST',
                    body: { id }
                });

                window.DevDayUI.showToast(response.message || 'Report resent successfully!', 'success');
                this.loadHistory();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to resend report.', 'error');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'reports') {
            window.DevDayReportsHistory.init();
        }
    });
})();
