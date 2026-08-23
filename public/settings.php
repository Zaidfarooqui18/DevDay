<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Config\Mail;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Models\User;
use DevDay\Helpers\Sanitizer;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'Settings — DevDay';
$activePage = 'settings';

$userModel = new User();
$user = $userModel->findById((int)$currentUser['id']);
$prefs = $userModel->getPreferences((int)$currentUser['id']);
$mailConfig = Mail::getConfig();
$hasMailPassword = !empty($mailConfig['password']);
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Settings &amp; Configuration</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage your developer profile, manager report recipient, workday rules, and email delivery settings.</p>
    </div>

    <div class="space-y-6">
        
        <!-- 1. PROFILE SETTINGS -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-800/80">
                <i data-lucide="user" class="w-4 h-4 text-indigo-400"></i>
                <h2 class="text-sm font-bold text-white">Developer Profile</h2>
            </div>

            <form onsubmit="handleUpdateProfile(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prof-name" class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name</label>
                        <input type="text" id="prof-name" value="<?= Sanitizer::e($user['name']) ?>" required class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="prof-email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" id="prof-email" value="<?= Sanitizer::e($user['email']) ?>" required class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="prof-save-btn" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition-all">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. REPORTING MANAGER CONFIGURATION -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                <div class="flex items-center gap-2">
                    <i data-lucide="mail-check" class="w-4 h-4 text-cyan-400"></i>
                    <h2 class="text-sm font-bold text-white">Direct Manager &amp; Report Recipient</h2>
                </div>
                <span class="text-[11px] text-slate-500">Primary report recipient</span>
            </div>

            <form onsubmit="handleUpdateManager(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mgr-name" class="block text-xs font-semibold text-slate-300 mb-1.5">Manager's Name</label>
                        <input type="text" id="mgr-name" value="<?= Sanitizer::e($user['manager_name'] ?? '') ?>" placeholder="e.g. Alex Vance" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="mgr-email" class="block text-xs font-semibold text-slate-300 mb-1.5">Manager's Email Address <span class="text-rose-400">*</span></label>
                        <input type="email" id="mgr-email" value="<?= Sanitizer::e($user['manager_email'] ?? '') ?>" required placeholder="alex.vance@techcorp.io" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="mgr-save-btn" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md shadow-cyan-600/30 transition-all">
                        Save Manager Info
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. REPORT TEMPLATE & WORKDAY PREFERENCES -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-800/80">
                <i data-lucide="sliders" class="w-4 h-4 text-purple-400"></i>
                <h2 class="text-sm font-bold text-white">Report Format &amp; Workday Preferences</h2>
            </div>

            <form onsubmit="handleUpdatePreferences(event)" class="space-y-4">
                <div>
                    <label for="pref-subject" class="block text-xs font-semibold text-slate-300 mb-1.5">Default Email Subject Template</label>
                    <input type="text" id="pref-subject" value="<?= Sanitizer::e($prefs['default_subject_template'] ?? 'Daily Work Report — {name} — {date}') ?>" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs font-mono text-white focus:outline-none focus:border-purple-500">
                    <p class="text-[11px] text-slate-500 mt-1">Available placeholders: <code class="text-indigo-300">{name}</code>, <code class="text-indigo-300">{date}</code>, <code class="text-indigo-300">{email}</code></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="pref-start" class="block text-xs font-semibold text-slate-300 mb-1.5">Workday Start Time</label>
                        <input type="time" id="pref-start" value="<?= Sanitizer::e($prefs['default_workday_start'] ?? '09:00') ?>" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label for="pref-end" class="block text-xs font-semibold text-slate-300 mb-1.5">Workday End Time</label>
                        <input type="time" id="pref-end" value="<?= Sanitizer::e($prefs['default_workday_end'] ?? '18:00') ?>" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="pref-save-btn" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30 transition-all">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- 4. SMTP CONFIGURATION & LIVE DIAGNOSTICS -->
        <div class="p-6 rounded-2xl bg-[#111726] border border-slate-800 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-800/80">
                <div class="flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4 text-emerald-400"></i>
                    <h2 class="text-sm font-bold text-white">SMTP Email Delivery Setup</h2>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-slate-400">Quick preset:</span>
                    <button type="button" onclick="applySmtpPreset('gmail')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium transition-colors">Gmail</button>
                    <button type="button" onclick="applySmtpPreset('brevo')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium transition-colors">Brevo</button>
                    <button type="button" onclick="applySmtpPreset('mailtrap')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium transition-colors">Mailtrap</button>
                    <button type="button" onclick="applySmtpPreset('outlook')" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-medium transition-colors">Outlook</button>
                </div>
            </div>

            <!-- Provider Tips Notice -->
            <div class="p-3.5 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-xs space-y-1">
                <div class="font-semibold text-indigo-300 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    <span>Important Provider Notes:</span>
                </div>
                <ul class="list-disc list-inside text-slate-300 text-[11px] space-y-0.5 ml-1">
                    <li><strong class="text-white">Gmail:</strong> You MUST use a 16-character <span class="text-indigo-300 underline"><a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">Google App Password</a></span> (regular account password will fail).</li>
                    <li><strong class="text-white">Brevo / SendGrid / Mailtrap:</strong> Use the API key or SMTP username/password from your account dashboard.</li>
                    <li><strong class="text-white">Ports:</strong> Use port <code class="text-cyan-300 font-mono">587</code> (TLS) or port <code class="text-cyan-300 font-mono">465</code> (SSL).</li>
                </ul>
            </div>

            <form onsubmit="handleSaveSmtp(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="smtp-host" class="block text-xs font-semibold text-slate-300 mb-1.5">SMTP Host <span class="text-rose-400">*</span></label>
                        <input type="text" id="smtp-host" value="<?= Sanitizer::e($mailConfig['host']) ?>" required placeholder="smtp.gmail.com" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label for="smtp-port" class="block text-xs font-semibold text-slate-300 mb-1.5">Port <span class="text-rose-400">*</span></label>
                        <input type="number" id="smtp-port" value="<?= (int)$mailConfig['port'] ?>" required placeholder="587" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="smtp-username" class="block text-xs font-semibold text-slate-300 mb-1.5">Username / Email <span class="text-rose-400">*</span></label>
                        <input type="text" id="smtp-username" value="<?= Sanitizer::e($mailConfig['username']) ?>" required placeholder="your.email@gmail.com" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs font-mono text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label for="smtp-password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Password / App Password <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="smtp-password" value="<?= $hasMailPassword ? '••••••••' : '' ?>" placeholder="<?= $hasMailPassword ? '•••••••• (unchanged)' : '16-character App Password' ?>" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs font-mono text-white focus:outline-none focus:border-emerald-500 pr-9">
                            <button type="button" onclick="togglePasswordVisibility('smtp-password')" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-200">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="smtp-encryption" class="block text-xs font-semibold text-slate-300 mb-1.5">Encryption</label>
                        <select id="smtp-encryption" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="tls" <?= ($mailConfig['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (Port 587)</option>
                            <option value="ssl" <?= ($mailConfig['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL / SMTPS (Port 465)</option>
                            <option value="" <?= ($mailConfig['encryption'] ?? '') === '' ? 'selected' : '' ?>>None (Plain / Local)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="smtp-from-email" class="block text-xs font-semibold text-slate-300 mb-1.5">From Email Address</label>
                        <input type="email" id="smtp-from-email" value="<?= Sanitizer::e($mailConfig['from_email']) ?>" placeholder="reports@yourdomain.com" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label for="smtp-from-name" class="block text-xs font-semibold text-slate-300 mb-1.5">From Display Name</label>
                        <input type="text" id="smtp-from-name" value="<?= Sanitizer::e($mailConfig['from_name']) ?>" placeholder="DevDay Work Reports" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span id="smtp-save-status" class="text-xs text-slate-400"></span>
                    <button type="submit" id="smtp-save-btn" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition-all">
                        Save SMTP Settings
                    </button>
                </div>
            </form>

            <!-- Test Connection Sub-Panel -->
            <div class="pt-4 border-t border-slate-800/80 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="radio" class="w-3.5 h-3.5 text-cyan-400"></i>
                        <span class="text-xs font-bold text-white">Live SMTP Diagnostic Test</span>
                    </div>
                    <span class="text-[11px] text-slate-500">Verify handshake &amp; delivery</span>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="email" id="smtp-test-email" value="<?= Sanitizer::e($user['email']) ?>" placeholder="Recipient test email" class="flex-1 bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-cyan-500">
                    <button type="button" id="smtp-test-btn" onclick="handleTestSmtp()" class="flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-md shadow-cyan-600/30 transition-all">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Send Test Email</span>
                    </button>
                </div>

                <!-- Live Debug Log Container -->
                <div id="smtp-debug-container" class="hidden space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Connection Log / Diagnostics:</span>
                        <button type="button" onclick="document.getElementById('smtp-debug-container').classList.add('hidden')" class="hover:text-slate-200">Close</button>
                    </div>
                    <pre id="smtp-debug-log" class="p-3 bg-[#080c14] border border-slate-800 rounded-xl text-[11px] font-mono text-slate-300 max-h-48 overflow-y-auto whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
function togglePasswordVisibility(inputId) {
    const el = document.getElementById(inputId);
    el.type = el.type === 'password' ? 'text' : 'password';
}

function applySmtpPreset(provider) {
    const host = document.getElementById('smtp-host');
    const port = document.getElementById('smtp-port');
    const enc = document.getElementById('smtp-encryption');
    
    if (provider === 'gmail') {
        host.value = 'smtp.gmail.com';
        port.value = '587';
        enc.value = 'tls';
        window.DevDayUI.showToast('Gmail preset applied. Use your 16-character App Password.', 'info');
    } else if (provider === 'brevo') {
        host.value = 'smtp-relay.brevo.com';
        port.value = '587';
        enc.value = 'tls';
        window.DevDayUI.showToast('Brevo preset applied. Enter your Brevo SMTP login & master password/key.', 'info');
    } else if (provider === 'mailtrap') {
        host.value = 'sandbox.smtp.mailtrap.io';
        port.value = '2525';
        enc.value = 'tls';
        window.DevDayUI.showToast('Mailtrap sandbox preset applied.', 'info');
    } else if (provider === 'outlook') {
        host.value = 'smtp.office365.com';
        port.value = '587';
        enc.value = 'tls';
        window.DevDayUI.showToast('Outlook / Office 365 preset applied.', 'info');
    }
}

async function handleUpdateProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('prof-save-btn');
    const name = document.getElementById('prof-name').value;
    const email = document.getElementById('prof-email').value;

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_profile', {
            method: 'POST',
            body: { name, email }
        });
        window.DevDayUI.showToast('Profile updated successfully!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to update profile.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleUpdateManager(e) {
    e.preventDefault();
    const btn = document.getElementById('mgr-save-btn');
    const manager_name = document.getElementById('mgr-name').value;
    const manager_email = document.getElementById('mgr-email').value;

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_manager', {
            method: 'POST',
            body: { manager_name, manager_email }
        });
        window.DevDayUI.showToast('Manager details saved!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to save manager info.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleUpdatePreferences(e) {
    e.preventDefault();
    const btn = document.getElementById('pref-save-btn');
    const default_subject_template = document.getElementById('pref-subject').value;
    const default_workday_start = document.getElementById('pref-start').value;
    const default_workday_end = document.getElementById('pref-end').value;

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_preferences', {
            method: 'POST',
            body: { default_subject_template, default_workday_start, default_workday_end }
        });
        window.DevDayUI.showToast('Preferences updated successfully!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to update preferences.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleSaveSmtp(e) {
    e.preventDefault();
    const btn = document.getElementById('smtp-save-btn');
    const host = document.getElementById('smtp-host').value;
    const port = document.getElementById('smtp-port').value;
    const username = document.getElementById('smtp-username').value;
    const password = document.getElementById('smtp-password').value;
    const encryption = document.getElementById('smtp-encryption').value;
    const from_email = document.getElementById('smtp-from-email').value;
    const from_name = document.getElementById('smtp-from-name').value;

    try {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Saving...';
        await window.DevDayUI.request('/api/settings.php?action=save_smtp', {
            method: 'POST',
            body: { host, port, username, password, encryption, from_email, from_name }
        });
        window.DevDayUI.showToast('SMTP configuration saved!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to save SMTP settings.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Save SMTP Settings';
        if (window.lucide) window.lucide.createIcons();
    }
}

async function handleTestSmtp() {
    const btn = document.getElementById('smtp-test-btn');
    const test_email = document.getElementById('smtp-test-email').value;
    const host = document.getElementById('smtp-host').value;
    const port = document.getElementById('smtp-port').value;
    const username = document.getElementById('smtp-username').value;
    const password = document.getElementById('smtp-password').value;
    const encryption = document.getElementById('smtp-encryption').value;
    const from_email = document.getElementById('smtp-from-email').value;
    const from_name = document.getElementById('smtp-from-name').value;

    const debugBox = document.getElementById('smtp-debug-container');
    const debugLog = document.getElementById('smtp-debug-log');

    if (!test_email) {
        window.DevDayUI.showToast('Please enter an email address for testing.', 'error');
        return;
    }

    try {
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Testing Handshake...';
        if (window.lucide) window.lucide.createIcons();
        debugBox.classList.remove('hidden');
        debugLog.textContent = 'Connecting to ' + host + ':' + port + ' (' + encryption + ')...\nAuthenticating as ' + username + '...';

        const res = await window.DevDayUI.request('/api/settings.php?action=test_smtp', {
            method: 'POST',
            body: { test_email, host, port, username, password, encryption, from_email, from_name }
        });

        debugLog.textContent = (res.data && res.data.debug_log) ? res.data.debug_log : 'Handshake succeeded! Email delivered.';
        window.DevDayUI.showToast(res.message || 'Test email dispatched successfully!', 'success');
    } catch (err) {
        const errorData = err.data || {};
        debugLog.textContent = (errorData.debug_log || '') + '\n[FAILED]: ' + (err.message || 'Connection refused.');
        window.DevDayUI.showToast(err.message || 'SMTP Connection failed.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="zap" class="w-3.5 h-3.5"></i><span>Send Test Email</span>';
        if (window.lucide) window.lucide.createIcons();
    }
}
</script>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
