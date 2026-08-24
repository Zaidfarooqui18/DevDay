    <!-- GLOBAL PAPER MODALS & DRAWERS -->

    <!-- 1. ADD / EDIT ASSIGNMENT MODAL -->
    <dialog id="assignment-modal" class="paper-card p-0 overflow-hidden max-w-lg w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-ink-brown font-bold text-base font-hand">✎</span>
                <h3 id="assignment-modal-title" class="font-hand font-bold text-xl text-ink">Add Daily Assignment</h3>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('assignment-modal')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <form id="assignment-form" onsubmit="window.DevDayDashboard.saveAssignment(event)" class="p-6 space-y-4 bg-paper">
            <input type="hidden" id="assignment-id" name="id" value="">

            <!-- Title -->
            <div>
                <label for="assign-title" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    Assignment Title <span class="text-stamp-red">*</span>
                </label>
                <input type="text" id="assign-title" name="title" required placeholder="e.g. Build JWT Authentication API" class="font-medium">
            </div>

            <!-- Description -->
            <div>
                <label for="assign-desc" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    Description / Scope
                </label>
                <textarea id="assign-desc" name="description" rows="2" placeholder="Key implementation notes, requirements, or endpoints..."></textarea>
            </div>

            <!-- Project & Category Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="assign-project" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Project</label>
                    <select id="assign-project" name="project_id">
                        <option value="">-- No Project (General) --</option>
                    </select>
                </div>

                <div>
                    <label for="assign-category" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Category</label>
                    <select id="assign-category" name="category">
                        <option value="Coding">Coding</option>
                        <option value="DSA">DSA</option>
                        <option value="College">College</option>
                        <option value="Research">Research</option>
                        <option value="Project">Project</option>
                        <option value="Interview">Interview</option>
                        <option value="DevOps">DevOps</option>
                        <option value="Learning">Learning</option>
                        <option value="Documentation">Documentation</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Priority, Estimated Time & Deadline Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label for="assign-priority" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Priority</label>
                    <select id="assign-priority" name="priority">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>

                <div>
                    <label for="assign-est" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Est. (min)</label>
                    <input type="number" id="assign-est" name="estimated_minutes" min="0" placeholder="e.g. 60" class="font-mono">
                </div>

                <div>
                    <label for="assign-deadline" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Deadline</label>
                    <input type="datetime-local" id="assign-deadline" name="deadline" class="text-xs">
                </div>
            </div>

            <!-- Expected Deliverable -->
            <div>
                <label for="assign-output" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Expected Deliverable / Output</label>
                <input type="text" id="assign-output" name="expected_output" placeholder="e.g. PR merged, passing unit tests, or documentation">
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t-2 border-dashed border-[#D4C4A8]">
                <button type="button" onclick="window.DevDayUI.closeModal('assignment-modal')" class="sketch-btn sketch-btn-sm">
                    Cancel
                </button>
                <button type="submit" id="assignment-submit-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                    Save Task
                </button>
            </div>
        </form>
    </dialog>

    <!-- 2. ASSIGNMENT DETAIL & FOCUS DRAWER MODAL -->
    <dialog id="detail-drawer" class="paper-card p-0 overflow-hidden max-w-xl w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-start justify-between">
            <div class="space-y-1 pr-4">
                <div id="detail-badges" class="flex items-center gap-1.5 flex-wrap">
                    <!-- Badges injected dynamically -->
                </div>
                <h2 id="detail-title" class="font-hand font-bold text-2xl text-ink leading-tight mt-1">Task Title</h2>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('detail-drawer')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <div class="p-6 overflow-y-auto max-h-[70vh] space-y-4 bg-paper text-sm">
            <!-- Description -->
            <div id="detail-desc-container">
                <div class="text-xs font-bold text-ink-muted uppercase tracking-wider mb-1">Notes / Description</div>
                <p id="detail-description" class="p-3 bg-paper-warm border border-ink rounded text-xs text-ink leading-relaxed whitespace-pre-wrap">No description provided.</p>
            </div>

            <!-- Expected Deliverable -->
            <div id="detail-output-container">
                <div class="text-xs font-bold text-ink-muted uppercase tracking-wider mb-1">Expected Deliverable</div>
                <div id="detail-expected-output" class="p-2.5 bg-paper-warm border border-ink rounded text-xs text-ink flex items-center gap-2">
                    <span class="text-ink-brown font-bold font-mono">&bull;</span>
                    <span>-</span>
                </div>
            </div>

            <!-- Metrics Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                <div class="p-2.5 bg-paper-warm border border-ink rounded">
                    <div class="text-[10px] font-bold text-ink-muted uppercase">Estimated</div>
                    <div id="detail-estimated" class="text-sm font-bold font-mono text-ink mt-0.5">0m</div>
                </div>
                <div class="p-2.5 bg-paper-warm border border-ink rounded">
                    <div class="text-[10px] font-bold text-ink-muted uppercase">Actual Time</div>
                    <div id="detail-actual" class="text-sm font-bold font-mono text-ink-brown mt-0.5">0m</div>
                </div>
                <div class="p-2.5 bg-paper-warm border border-ink rounded">
                    <div class="text-[10px] font-bold text-ink-muted uppercase">Deadline</div>
                    <div id="detail-deadline" class="text-xs font-bold text-ink mt-0.5 truncate">None</div>
                </div>
                <div class="p-2.5 bg-paper-warm border border-ink rounded">
                    <div class="text-[10px] font-bold text-ink-muted uppercase">Status</div>
                    <div id="detail-status" class="text-xs font-bold text-ink mt-0.5">TODO</div>
                </div>
            </div>

            <!-- Focus Sessions History -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="text-xs font-bold text-ink-muted uppercase tracking-wider">Focus Sessions History</div>
                    <span id="detail-sessions-total" class="text-xs font-mono font-bold text-ink-brown">Total: 0m</span>
                </div>
                <div id="detail-sessions-list" class="space-y-1">
                    <div class="text-xs text-ink-muted italic p-2.5 bg-paper-warm rounded border border-[#D4C4A8] text-center">No focus sessions recorded yet.</div>
                </div>
            </div>
        </div>

        <div class="border-t-2 border-ink px-6 py-3.5 bg-paper-warm flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <button id="drawer-focus-btn" onclick="window.DevDayDashboard.toggleFocusFromDrawer()" class="sketch-btn sketch-btn-sm sketch-btn-brown">
                    <span>▶ Start Focus</span>
                </button>
                <button id="drawer-complete-btn" onclick="window.DevDayDashboard.toggleCompleteFromDrawer()" class="sketch-btn sketch-btn-sm">
                    <span>✓ Mark Done</span>
                </button>
                <button id="drawer-learning-btn" onclick="window.DevDayDashboard.openLearningLogFromDrawer()" class="sketch-btn sketch-btn-sm">
                    <span>✎ Learning Log</span>
                </button>
            </div>

            <div class="flex items-center gap-1.5">
                <button onclick="window.DevDayDashboard.editAssignmentFromDrawer()" class="p-1.5 rounded border border-ink bg-paper hover:bg-paper-warm text-ink text-xs font-bold" title="Edit Assignment">
                    Edit
                </button>
                <button onclick="window.DevDayDashboard.deleteAssignmentFromDrawer()" class="p-1.5 rounded border border-stamp-red bg-[#FDF1EF] hover:bg-[#FBE4E1] text-stamp-red text-xs font-bold" title="Delete Assignment">
                    Delete
                </button>
            </div>
        </div>
    </dialog>

    <!-- 3. LEARNING LOG MODAL -->
    <dialog id="learning-modal" class="paper-card p-0 overflow-hidden max-w-lg w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-ink-brown font-bold text-lg font-hand">★</span>
                <h3 class="font-hand font-bold text-xl text-ink">Record What You Learned</h3>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('learning-modal')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <form id="learning-form" onsubmit="window.DevDayDashboard.saveLearningLog(event)" class="p-6 space-y-4 bg-paper">
            <input type="hidden" id="learning-assignment-id" name="assignment_id" value="">

            <div>
                <label class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">
                    What did you learn? <span class="text-stamp-red">*</span>
                </label>
                <textarea id="learning-what-learned" name="what_learned" rows="2" required placeholder="Core concept, architecture decision, debugging finding..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">What did you build?</label>
                <textarea id="learning-what-built" name="what_built" rows="2" placeholder="Endpoints, UI components, tests, configuration..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Difficulty Level</label>
                    <select id="learning-difficulty" name="difficulty">
                        <option value="Easy">Easy</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Blocker / Challenge (if any)</label>
                    <input type="text" id="learning-blocker" name="blocker" placeholder="Any remaining friction or blockers...">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t-2 border-dashed border-[#D4C4A8]">
                <button type="button" onclick="window.DevDayUI.closeModal('learning-modal')" class="sketch-btn sketch-btn-sm">
                    Cancel
                </button>
                <button type="submit" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                    Save to Report
                </button>
            </div>
        </form>
    </dialog>

    <!-- 4. REPORT PREVIEW & DISPATCH DRAWER -->
    <dialog id="report-preview-drawer" class="paper-card p-0 overflow-hidden max-w-3xl w-full bg-paper">
        <div class="border-b-2 border-ink px-6 py-4 bg-paper-warm flex items-center justify-between">
            <div>
                <h3 class="font-hand font-bold text-2xl text-ink">Daily Report Preview</h3>
                <p class="text-xs text-ink-muted">Review exact email snapshot before dispatching to manager</p>
            </div>
            <button type="button" onclick="window.DevDayUI.closeModal('report-preview-drawer')" class="p-1 text-ink-muted hover:text-ink font-bold text-lg leading-none" title="Close">
                ✕
            </button>
        </div>

        <!-- Recipient & Subject Configuration Strip -->
        <div class="p-4 px-6 bg-paper-warm border-b border-ink grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-ink uppercase tracking-wider mb-1">Recipient Manager Email</label>
                <input type="email" id="report-recipient-email" class="font-mono text-xs" placeholder="manager@example.com">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-ink uppercase tracking-wider mb-1">Email Subject Line</label>
                <input type="text" id="report-subject-line" class="text-xs" placeholder="Daily Work Report — Zaid — 24 Aug 2026">
            </div>
        </div>

        <!-- HTML Email Live Preview iframe -->
        <div class="p-4 bg-[#F4F0EA] overflow-hidden" style="height: 480px;">
            <iframe id="report-iframe" class="w-full h-full border-2 border-ink bg-white rounded" title="Daily Report Email Preview"></iframe>
        </div>

        <div class="border-t-2 border-ink px-6 py-3.5 bg-paper-warm flex items-center justify-between">
            <div class="text-xs text-ink-muted">
                Report will be logged in <a href="/reports.php" class="underline font-bold text-ink-brown">Reports History</a>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" onclick="window.DevDayUI.closeModal('report-preview-drawer')" class="sketch-btn sketch-btn-sm">
                    Back to Edit
                </button>
                <button type="button" id="send-report-btn" onclick="window.DevDayReport.sendReport()" class="sketch-btn sketch-btn-sm sketch-btn-brown">
                    <span id="send-report-text">Send Report to Manager →</span>
                </button>
            </div>
        </div>
    </dialog>

    <!-- Core App JS Scripts -->
    <script src="/assets/js/app.js"></script>
    <script src="/assets/js/timer.js"></script>
    <script src="/assets/js/report.js"></script>

    <!-- Page Specific Script Hook -->
    <?php if (isset($pageScript)): ?>
        <script src="<?= $pageScript ?>"></script>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
