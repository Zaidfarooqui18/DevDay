/**
 * DevDay Report Generation & Email Dispatch Controller
 * Genuine loading states, fast generation, live preview, and strict SMTP dispatch feedback
 */

(function () {
    let currentReportData = null;

    window.DevDayReport = {
        async checkReadiness() {
            const container = document.getElementById('report-readiness-container');
            if (!container) return;

            try {
                const response = await window.DevDayUI.request('/api/reports.php?action=readiness');
                const r = response.data;

                const hasTasks = r.stats.total_tasks > 0;
                const percentDone = Math.round(r.stats.completion_percentage);

                let checklistHtml = '';
                if (r.checklist && r.checklist.length > 0) {
                    checklistHtml = `
                        <div class="flex items-center gap-2 flex-wrap text-xs text-ink-pencil mt-1">
                            ${r.checklist.map(item => `
                                <span class="flex items-center gap-1 font-medium">
                                    <span class="${item.status === 'ok' ? 'text-stamp-green font-bold' : (item.status === 'warning' ? 'text-ink-amber' : 'text-stamp-red')}">
                                        ${item.status === 'ok' ? '✓' : (item.status === 'warning' ? '▲' : '✕')}
                                    </span>
                                    ${item.label}
                                </span>
                            `).join('&middot;')}
                        </div>
                    `;
                }

                container.innerHTML = `
                    <div class="paper-card p-6 bg-paper-warm space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="font-hand font-bold text-2xl text-ink">
                                    ${hasTasks ? (percentDone === 100 ? "all done! your report is ready." : "ready to wrap up today's work?") : "start tracking today's tasks to build your report"}
                                </div>
                                ${checklistHtml}
                            </div>

                            <button 
                                type="button"
                                id="preview-report-btn"
                                onclick="window.DevDayReport.generateAndPreview()" 
                                class="sketch-btn sketch-btn-brown shrink-0"
                            >
                                <span id="preview-report-btn-text">make my report &rarr;</span>
                            </button>
                        </div>
                    </div>
                `;
            } catch (err) {
                console.warn('[Report Readiness Error]', err);
            }
        },

        async generateAndPreview() {
            const btn = document.getElementById('preview-report-btn');
            const btnText = document.getElementById('preview-report-btn-text');

            try {
                if (btn) btn.disabled = true;
                if (btnText) btnText.textContent = "putting today's work together...";

                const response = await window.DevDayUI.request('/api/reports.php?action=generate');
                currentReportData = response.data;

                // Configure recipient and subject inputs
                const emailInput = document.getElementById('report-recipient-email');
                const subjectInput = document.getElementById('report-subject-line');
                const iframe = document.getElementById('report-iframe');

                if (emailInput) emailInput.value = currentReportData.recipient_email || '';
                if (subjectInput) subjectInput.value = currentReportData.email_subject || '';

                // Inject rendered email HTML into preview iframe
                if (iframe) {
                    const doc = iframe.contentWindow.document;
                    doc.open();
                    doc.write(currentReportData.html_content);
                    doc.close();
                }

                window.DevDayUI.openModal('report-preview-drawer');
            } catch (err) {
                window.DevDayUI.showToast(err.message || 'Failed to generate report preview.', 'error');
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = "make my report →";
            }
        },

        async sendReport() {
            const btn = document.getElementById('send-report-btn');
            const btnText = document.getElementById('send-report-text');

            const recipientEmail = document.getElementById('report-recipient-email')?.value.trim();
            const subject = document.getElementById('report-subject-line')?.value.trim();

            if (!recipientEmail) {
                window.DevDayUI.showToast('Please enter a recipient manager email.', 'error');
                return;
            }

            try {
                if (btn) btn.disabled = true;
                if (btnText) btnText.textContent = "handing to mail server...";

                const response = await window.DevDayUI.request('/api/reports.php?action=send', {
                    method: 'POST',
                    body: {
                        recipient_email: recipientEmail,
                        email_subject: subject
                    }
                });

                window.DevDayUI.showToast(response.message || 'Report handed to the mail server.', 'success');
                window.DevDayUI.closeModal('report-preview-drawer');
                this.checkReadiness();
            } catch (err) {
                window.DevDayUI.showToast(err.message || "Couldn't send the report. Please check your email configuration.", 'error');
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = "Send Report to Manager →";
            }
        }
    };
})();
