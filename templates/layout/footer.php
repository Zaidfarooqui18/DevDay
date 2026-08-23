    <!-- GLOBAL MODALS & DRAWERS -->

    <!-- 1. ADD / EDIT ASSIGNMENT MODAL -->
    <dialog id="assignment-modal" class="p-0 bg-transparent rounded-2xl w-full max-w-xl shadow-2xl backdrop:bg-slate-950/80">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#162035]/50">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                    <h3 id="assignment-modal-title" class="text-sm font-bold text-white">Create Assignment</h3>
                </div>
                <button onclick="window.DevDayUI.closeModal('assignment-modal')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="assignment-form" onsubmit="window.DevDayDashboard.saveAssignment(event)" class="p-6 space-y-4">
                <input type="hidden" id="assignment-id" name="id" value="">

                <!-- Title (Required) -->
                <div>
                    <label for="assign-title" class="block text-xs font-semibold text-slate-300 mb-1.5">Assignment Title <span class="text-rose-400">*</span></label>
                    <input type="text" id="assign-title" name="title" required placeholder="e.g., Build JWT Authentication API" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>

                <!-- Description -->
                <div>
                    <label for="assign-desc" class="block text-xs font-semibold text-slate-300 mb-1.5">Description / Scope</label>
                    <textarea id="assign-desc" name="description" rows="2" placeholder="Key implementation requirements, endpoints, or notes..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"></textarea>
                </div>

                <!-- Project & Category Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="assign-project" class="block text-xs font-semibold text-slate-300 mb-1.5">Project</label>
                        <select id="assign-project" name="project_id" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="">-- No Project (General) --</option>
                        </select>
                    </div>

                    <div>
                        <label for="assign-category" class="block text-xs font-semibold text-slate-300 mb-1.5">Category</label>
                        <select id="assign-category" name="category" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
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

                <!-- Priority, Estimated Duration & Deadline Row -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="assign-priority" class="block text-xs font-semibold text-slate-300 mb-1.5">Priority</label>
                        <select id="assign-priority" name="priority" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label for="assign-est" class="block text-xs font-semibold text-slate-300 mb-1.5">Est. Time (min)</label>
                        <input type="number" id="assign-est" name="estimated_minutes" min="0" placeholder="e.g. 90" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="assign-deadline" class="block text-xs font-semibold text-slate-300 mb-1.5">Deadline</label>
                        <input type="datetime-local" id="assign-deadline" name="deadline" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-2.5 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <!-- Expected Output -->
                <div>
                    <label for="assign-output" class="block text-xs font-semibold text-slate-300 mb-1.5">Expected Output / Deliverable</label>
                    <input type="text" id="assign-output" name="expected_output" placeholder="e.g. PR merged, passing unit tests, or document" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-all">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="window.DevDayUI.closeModal('assignment-modal')" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="assignment-submit-btn" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-md shadow-indigo-600/30 transition-all">
                        Save Assignment
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- 2. ASSIGNMENT DETAIL & FOCUS DRAWER MODAL -->
    <dialog id="detail-drawer" class="p-0 bg-transparent rounded-2xl w-full max-w-2xl shadow-2xl backdrop:bg-slate-950/80">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Drawer Header -->
            <div class="flex items-start justify-between p-6 border-b border-slate-800 bg-[#162035]/40">
                <div class="space-y-1.5 pr-6">
                    <div class="flex items-center gap-2 flex-wrap" id="detail-badges">
                        <!-- Dynamic Badges -->
                    </div>
                    <h2 id="detail-title" class="text-lg font-bold text-white leading-snug">Task Title</h2>
                </div>
                <button onclick="window.DevDayUI.closeModal('detail-drawer')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 text-sm">
                <!-- Description -->
                <div id="detail-desc-container">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</h4>
                    <p id="detail-description" class="text-slate-200 leading-relaxed bg-[#090d16] p-3.5 rounded-xl border border-slate-800/80">No description provided.</p>
                </div>

                <!-- Deliverable / Expected Output -->
                <div id="detail-output-container">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expected Deliverable</h4>
                    <div id="detail-expected-output" class="text-slate-200 bg-[#090d16] p-3 rounded-xl border border-slate-800/80 flex items-center gap-2">
                        <i data-lucide="target" class="w-4 h-4 text-cyan-400"></i>
                        <span>-</span>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-[#090d16] p-3 rounded-xl border border-slate-800/80">
                        <div class="text-[11px] text-slate-400 font-medium">Estimated</div>
                        <div id="detail-estimated" class="text-sm font-bold text-white font-mono mt-0.5">0m</div>
                    </div>
                    <div class="bg-[#090d16] p-3 rounded-xl border border-slate-800/80">
                        <div class="text-[11px] text-slate-400 font-medium">Actual Time</div>
                        <div id="detail-actual" class="text-sm font-bold text-cyan-400 font-mono mt-0.5">0m</div>
                    </div>
                    <div class="bg-[#090d16] p-3 rounded-xl border border-slate-800/80">
                        <div class="text-[11px] text-slate-400 font-medium">Deadline</div>
                        <div id="detail-deadline" class="text-xs font-semibold text-slate-200 mt-1 truncate">None</div>
                    </div>
                    <div class="bg-[#090d16] p-3 rounded-xl border border-slate-800/80">
                        <div class="text-[11px] text-slate-400 font-medium">Status</div>
                        <div id="detail-status" class="text-xs font-bold text-slate-200 mt-1">TODO</div>
                    </div>
                </div>

                <!-- Focus Sessions List -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Focus Sessions History</h4>
                        <span id="detail-sessions-total" class="text-xs font-mono font-semibold text-cyan-400">Total: 0m</span>
                    </div>
                    <div id="detail-sessions-list" class="space-y-1.5">
                        <div class="text-xs text-slate-500 italic p-3 bg-[#090d16] rounded-xl border border-slate-800/80 text-center">No focus sessions recorded yet for this task.</div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer Actions -->
            <div class="p-4 px-6 border-t border-slate-800 bg-[#162035]/40 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <button id="drawer-focus-btn" onclick="window.DevDayDashboard.toggleFocusFromDrawer()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md shadow-cyan-600/20 transition-all">
                        <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                        <span>Start Focus</span>
                    </button>
                    <button id="drawer-complete-btn" onclick="window.DevDayDashboard.toggleCompleteFromDrawer()" class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Mark Complete</span>
                    </button>
                    <button id="drawer-learning-btn" onclick="window.DevDayDashboard.openLearningLogFromDrawer()" class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border border-purple-500/30 text-xs font-medium transition-all">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>Learning Log</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="window.DevDayDashboard.editAssignmentFromDrawer()" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors" title="Edit Assignment">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                    <button onclick="window.DevDayDashboard.deleteAssignmentFromDrawer()" class="p-2 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-950/30 transition-colors" title="Delete Assignment">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </dialog>

    <!-- 3. LEARNING LOG MODAL -->
    <dialog id="learning-modal" class="p-0 bg-transparent rounded-2xl w-full max-w-xl shadow-2xl backdrop:bg-slate-950/80">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#162035]/50">
                <div class="flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-purple-400"></i>
                    <h3 class="text-sm font-bold text-white">Record What You Learned</h3>
                </div>
                <button onclick="window.DevDayUI.closeModal('learning-modal')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="learning-form" onsubmit="window.DevDayDashboard.saveLearningLog(event)" class="p-6 space-y-4">
                <input type="hidden" id="learning-assignment-id" name="assignment_id" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">What did you learn? <span class="text-purple-400">*</span></label>
                    <textarea id="learning-what-learned" name="what_learned" rows="2" required placeholder="Core concept, architecture pattern, debugging breakthrough..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition-all"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">What did you build?</label>
                    <textarea id="learning-what-built" name="what_built" rows="2" placeholder="Endpoints, UI components, tests, configuration..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition-all"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Difficulty Level</label>
                        <select id="learning-difficulty" name="difficulty" class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                            <option value="Easy">Easy</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Blocker / Challenge (if any)</label>
                        <input type="text" id="learning-blocker" name="blocker" placeholder="Any remaining friction..." class="w-full bg-[#090d16] border border-slate-700/80 rounded-lg px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                    <button type="button" onclick="window.DevDayUI.closeModal('learning-modal')" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold shadow-md shadow-purple-600/30 transition-all">
                        Save to Daily Report
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- 4. REPORT PREVIEW & DISPATCH DRAWER -->
    <dialog id="report-preview-drawer" class="p-0 bg-transparent rounded-2xl w-full max-w-4xl shadow-2xl backdrop:bg-slate-950/85">
        <div class="bg-[#111726] border border-slate-800 rounded-2xl overflow-hidden flex flex-col h-[90vh]">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-[#162035]/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Daily Report Preview</h3>
                        <p class="text-[11px] text-slate-400">Review exact HTML rendering before emailing your manager</p>
                    </div>
                </div>
                <button onclick="window.DevDayUI.closeModal('report-preview-drawer')" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Email Subject and Recipient Configuration Strip -->
            <div class="p-4 px-6 bg-[#0d1322] border-b border-slate-800/80 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Recipient Manager Email</label>
                    <input type="email" id="report-recipient-email" class="w-full bg-[#111726] border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Subject Line</label>
                    <input type="text" id="report-subject-line" class="w-full bg-[#111726] border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <!-- HTML Email Live Preview iframe -->
            <div class="flex-1 bg-[#090d16] p-4 overflow-hidden flex flex-col">
                <div class="w-full h-full rounded-xl overflow-hidden border border-slate-800 bg-[#090d16]">
                    <iframe id="report-iframe" class="w-full h-full border-0 bg-[#090d16]" title="Daily Report Email Preview"></iframe>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="p-4 px-6 border-t border-slate-800 bg-[#162035]/50 flex items-center justify-between">
                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Saved snapshot will be archived in <a href="/reports.php" class="text-indigo-400 hover:underline">Reports History</a></span>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="window.DevDayUI.closeModal('report-preview-drawer')" class="px-4 py-2 rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-white/5">
                        Edit More
                    </button>
                    <button id="send-report-btn" onclick="window.DevDayReport.sendReport()" class="flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span id="send-report-text">Send Report to Manager</span>
                    </button>
                </div>
            </div>
        </div>
    </dialog>

    <!-- Core App JS -->
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
