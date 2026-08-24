/**
 * DevDay Reports History Controller
 * Anti-Digital Paper Edition: Historical reports table, snapshot viewer, and resend workflows
 */

(function () {
    let reportsList = [];

    window.DevDayReportsHistory = {
        async init() {
            await this.loadReports();
        },

        async loadReports() {
            const tbody = document.getElementById('reports-table-body');
            if (!tbody) return;

            try {
                const response = await window.DevDayUI.request('/api/reports.php?action=history');
                reportsList = response.data || [];
                this.renderTable(reportsList);
            } catch (err) {
                tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-stamp-red font-mono text-xs">Failed to load reports: ${err.message}</td></tr>`;
            }
        },

        renderTable(reports) {
            const tbody = document.getElementById('reports-table-body');
            if (!tbody) return;

            if (reports.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="p-8 text-center text-ink-muted">
                            <div class="font-hand font-bold text-xl text-ink">no reports archived yet.</div>
                            <p class="text-xs text-ink-pencil mt-1">Generate your first daily report on the Today page to see it logged here.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            reports.forEach((r, idx) => {
                const isSent = r.status === 'SENT';
                const isFailed = r.status === 'FAILED';

                let statusBadge = '';
                if (isSent) {
                    statusBadge = '<span class="stamp stamp-green">sent</span>';
                } else if (isFailed) {
                    statusBadge = '<span class="stamp stamp-red" title="' + (r.error_message || '') + '">failed</span>';
                } else {
                    statusBadge = '<span class="stamp stamp-neutral">draft</span>';
                }

                const total = parseInt(r.total_tasks || 0);
                const completed = parseInt(r.completed_tasks || 0);
                const percent = Math.round(parseFloat(r.completion_percentage || 0));

                html += `
                    <tr class="border-b border-[#E2D9CB] hover:bg-paper-warm transition-colors ${idx % 2 === 1 ? 'bg-paper-warm/40' : 'bg-paper'}">
                        <td class="py-3.5 px-4 font-mono font-bold text-ink">${r.report_date}</td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <span class="font-mono">${completed}/${total} (${percent}%)</span>
                                <div class="w-16 bg-paper border border-ink h-1.5 rounded overflow-hidden">
                                    <div class="bg-[#2D5A43] h-full" style="width: ${percent}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-bold text-ink-brown">${window.DevDayUI.formatMinutes(r.focus_minutes)}</td>
                        <td class="py-3.5 px-4 font-mono text-ink-pencil text-[11px]">${r.recipient_email}</td>
                        <td class="py-3.5 px-4">${statusBadge}</td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button onclick="window.DevDayReportsHistory.viewSnapshot(${r.id})" class="sketch-btn sketch-btn-sm" title="View Snapshot">
                                    <span>view</span>
                                </button>
                                <button onclick="window.DevDayReportsHistory.resendReport(${r.id})" class="sketch-btn sketch-btn-sm sketch-btn-brown" title="Resend Report Email">
                                    <span>resend</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        },

        async viewSnapshot(id) {
            try {
                const response = await window.DevDayUI.request(`/api/reports.php?action=view&id=${id}`);
                const r = response.data;

                document.getElementById('view-report-modal-title').textContent = `Report: ${r.report_date}`;
                document.getElementById('view-report-modal-subtitle').textContent = `To: ${r.recipient_email} · Status: ${r.status}`;
                document.getElementById('view-report-status').textContent = `Logged on: ${r.created_at}`;

                const iframe = document.getElementById('view-report-iframe');
                if (iframe) {
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(r.html_content);
                    doc.close();
                }

                const resendBtn = document.getElementById('view-report-resend-btn');
                if (resendBtn) {
                    resendBtn.onclick = () => this.resendReport(id);
                }

                window.DevDayUI.openModal('view-report-modal');
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to load report snapshot.', 'error');
            }
        },

        async resendReport(id) {
            const report = reportsList.find(item => parseInt(item.id) === parseInt(id));
            const promptEmail = prompt('Confirm recipient manager email:', report ? report.recipient_email : '');
            if (!promptEmail) return;

            try {
                window.DevDayUI.showToast('Handing report to mail server...', 'info');
                const response = await window.DevDayUI.request('/api/reports.php?action=resend', {
                    method: 'POST',
                    body: {
                        id: id,
                        recipient_email: promptEmail
                    }
                });

                window.DevDayUI.showToast(response.message || 'Report handed to the mail server.', 'success');
                this.loadReports();
            } catch (err) {
                window.DevDayUI.showToast(err.message || "Couldn't send the report. Please check your email configuration.", 'error');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        if (window.DevDay?.activePage === 'reports') {
            window.DevDayReportsHistory.init();
        }
    });
})();
