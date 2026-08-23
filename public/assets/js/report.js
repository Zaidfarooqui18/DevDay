/**
 * DevDay Daily Report Engine
 * Readiness verification, HTML snapshot generator, and SMTP dispatching
 */

(function () {
    window.DevDayReport = {
        async checkReadiness() {
            try {
                const response = await window.DevDayUI.request('/api/reports.php?action=readiness');
                this.renderReadinessWidget(response.data);
            } catch (err) {
                console.error('[Report Readiness]', err);
            }
        },

        renderReadinessWidget(readiness) {
            const container = document.getElementById('report-readiness-container');
            if (!container) return;

            const checklist = readiness.checklist;
            let html = `
                <div class="p-5 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full ${readiness.can_send ? 'bg-emerald-400' : 'bg-amber-400'}"></div>
                            <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">End-of-Day Report Readiness</h3>
                        </div>
                        <span class="text-xs font-mono font-bold ${readiness.can_send ? 'text-emerald-400' : 'text-amber-400'}">
                            ${readiness.can_send ? 'Ready to Send' : 'Action Needed'}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 text-xs">
            `;

            for (const [key, item] of Object.entries(checklist)) {
                const icon = item.status ? 'check-circle' : 'circle-dashed';
                const color = item.status ? 'text-emerald-400' : 'text-slate-500';
                const bg = item.status ? 'bg-emerald-950/20 border-emerald-900/40' : 'bg-[#090d16] border-slate-800';

                html += `
                    <div class="flex items-start gap-2 p-2.5 rounded-xl border ${bg}">
                        <i data-lucide="${icon}" class="w-4 h-4 mt-0.5 ${color} shrink-0"></i>
                        <div class="truncate">
                            <div class="font-semibold text-slate-200">${item.label}</div>
                            <div class="text-[11px] text-slate-400 truncate">${item.message}</div>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                        <div class="text-xs text-slate-400">
                            ${readiness.can_send ? 'All key reporting metrics compiled.' : 'Please configure manager email in Settings to dispatch.'}
                        </div>
                        <button onclick="window.DevDayReport.openPreview()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all hover:scale-[1.02]">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Generate &amp; Preview Report</span>
                        </button>
                    </div>
                </div>
            `;

            container.innerHTML = html;
            if (window.lucide) lucide.createIcons();
        },

        async openPreview() {
            try {
                window.DevDayUI.showToast('Generating report snapshot...', 'info');

                const response = await window.DevDayUI.request('/api/reports.php?action=generate');
                const data = response.data;

                // Fill recipient & subject
                const recipInput = document.getElementById('report-recipient-email');
                const subjInput = document.getElementById('report-subject-line');

                if (recipInput) recipInput.value = data.recipient_email || '';
                if (subjInput) subjInput.value = data.email_subject || '';

                // Inject HTML into iframe
                const iframe = document.getElementById('report-iframe');
                if (iframe) {
                    iframe.srcdoc = data.html_content;
                }

                window.DevDayUI.openModal('report-preview-drawer');
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to generate report preview.', 'error');
            }
        },

        async sendReport() {
            const sendBtn = document.getElementById('send-report-btn');
            const sendText = document.getElementById('send-report-text');
            const recipient = document.getElementById('report-recipient-email')?.value;
            const subject = document.getElementById('report-subject-line')?.value;

            if (!recipient) {
                window.DevDayUI.showToast('Please enter a recipient manager email.', 'error');
                return;
            }

            try {
                if (sendBtn) sendBtn.disabled = true;
                if (sendText) sendText.textContent = 'Sending report...';

                const response = await window.DevDayUI.request('/api/reports.php?action=send', {
                    method: 'POST',
                    body: {
                        recipient_email: recipient,
                        email_subject: subject,
                    }
                });

                window.DevDayUI.showToast(response.message || 'Report sent successfully to your manager!', 'success');
                window.DevDayUI.closeModal('report-preview-drawer');

                // Refresh readiness
                this.checkReadiness();
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to send report. Please check SMTP settings.', 'error');
            } finally {
                if (sendBtn) sendBtn.disabled = false;
                if (sendText) sendText.textContent = 'Send Report to Manager';
            }
        }
    };
})();
