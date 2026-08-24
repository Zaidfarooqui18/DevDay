<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Config\Mail;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Models\User;
use DevDay\Helpers\Sanitizer;

App::init();
$currentUser = AuthMiddleware::requireAuth();
$pageTitle = 'settings — DevDay';
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

    <div class="space-y-1 pb-4 border-b-2 border-ink">
        <h1 class="font-hand font-bold text-3xl sm:text-4xl text-ink tracking-tight">
            settings &amp; configuration ✎
        </h1>
        <p class="font-hand text-xl text-ink-pencil mt-0.5">
            manage your developer profile, manager details, and SMTP delivery.
        </p>
    </div>

    <div class="space-y-6">
        
        <!-- 1. PROFILE SETTINGS -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b-2 border-ink">
                <span class="text-ink-brown font-bold text-base font-hand">✎</span>
                <h2 class="font-hand font-bold text-xl text-ink">Developer Profile</h2>
            </div>

            <form onsubmit="handleUpdateProfile(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="prof-name" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" id="prof-name" value="<?= Sanitizer::e($user['name']) ?>" required>
                    </div>

                    <div>
                        <label for="prof-email" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" id="prof-email" value="<?= Sanitizer::e($user['email']) ?>" required>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="prof-save-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. REPORTING MANAGER CONFIGURATION -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
                <div class="flex items-center gap-2">
                    <span class="text-ink-brown font-bold text-base font-hand">✉</span>
                    <h2 class="font-hand font-bold text-xl text-ink">Direct Manager &amp; Report Recipient</h2>
                </div>
                <span class="text-xs text-ink-muted italic font-serif">Primary report recipient</span>
            </div>

            <form onsubmit="handleUpdateManager(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mgr-name" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Manager / Lead Name</label>
                        <input type="text" id="mgr-name" value="<?= Sanitizer::e($user['manager_name'] ?? '') ?>" placeholder="e.g. Alex Vance">
                    </div>

                    <div>
                        <label for="mgr-email" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Manager Work Email</label>
                        <input type="email" id="mgr-email" value="<?= Sanitizer::e($user['manager_email'] ?? '') ?>" placeholder="manager@company.com">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="mgr-save-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                        Save Manager Info
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. REPORT TEMPLATE & WORKDAY PREFERENCES -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b-2 border-ink">
                <span class="text-ink-brown font-bold text-base font-hand">★</span>
                <h2 class="font-hand font-bold text-xl text-ink">Report Subject Template &amp; Workday Rules</h2>
            </div>

            <form onsubmit="handleUpdatePreferences(event)" class="space-y-4">
                <div>
                    <label for="pref-subject" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Default Report Subject Template</label>
                    <input type="text" id="pref-subject" value="<?= Sanitizer::e($prefs['default_subject_template'] ?? 'Daily Work Report — {name} — {date}') ?>" required class="font-mono text-xs">
                    <p class="text-[11px] text-ink-muted mt-1 italic">Available dynamic tags: <code class="bg-paper-warm px-1 rounded border border-[#D4C4A8]">{name}</code>, <code class="bg-paper-warm px-1 rounded border border-[#D4C4A8]">{date}</code></p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="pref-start" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Expected Day Start</label>
                        <input type="time" id="pref-start" value="<?= Sanitizer::e($prefs['default_workday_start'] ?? '09:00') ?>">
                    </div>

                    <div>
                        <label for="pref-end" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Expected Day End</label>
                        <input type="time" id="pref-end" value="<?= Sanitizer::e($prefs['default_workday_end'] ?? '18:00') ?>">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="pref-save-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- 4. SMTP DELIVERY CONFIGURATION & DIAGNOSTICS -->
        <div class="paper-card p-6 bg-paper space-y-4">
            <div class="flex items-center justify-between pb-3 border-b-2 border-ink">
                <div class="flex items-center gap-2">
                    <span class="text-ink-brown font-bold text-base font-hand">⚡</span>
                    <h2 class="font-hand font-bold text-xl text-ink">SMTP Email Delivery &amp; Live Diagnostics</h2>
                </div>
                <span class="stamp stamp-neutral font-mono text-[10px]">PHPMailer</span>
            </div>

            <p class="text-xs text-ink-pencil">
                Configure your SMTP gateway (e.g. Gmail, Mailtrap, AWS SES, Resend SMTP) so daily reports are delivered straight into your manager's inbox.
            </p>

            <form onsubmit="handleUpdateSMTP(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label for="smtp-host" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">SMTP Host</label>
                        <input type="text" id="smtp-host" value="<?= Sanitizer::e($mailConfig['host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com" class="font-mono text-xs">
                    </div>

                    <div>
                        <label for="smtp-port" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Port</label>
                        <input type="number" id="smtp-port" value="<?= Sanitizer::e((string)($mailConfig['port'] ?? 587)) ?>" placeholder="587" class="font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="smtp-username" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Username / Email</label>
                        <input type="text" id="smtp-username" value="<?= Sanitizer::e($mailConfig['username'] ?? '') ?>" placeholder="you@gmail.com" class="font-mono text-xs">
                    </div>

                    <div>
                        <label for="smtp-password" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Password / App Password</label>
                        <input type="password" id="smtp-password" placeholder="<?= $hasMailPassword ? '•••••••• (Stored Securely)' : 'Enter SMTP password' ?>" class="font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="smtp-encryption" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">Encryption</label>
                        <select id="smtp-encryption" class="text-xs font-mono">
                            <option value="tls" <?= ($mailConfig['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (STARTTLS)</option>
                            <option value="ssl" <?= ($mailConfig['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= ($mailConfig['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>

                    <div>
                        <label for="smtp-from-email" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">From Email</label>
                        <input type="email" id="smtp-from-email" value="<?= Sanitizer::e($mailConfig['from_email'] ?? '') ?>" placeholder="noreply@domain.com" class="font-mono text-xs">
                    </div>

                    <div>
                        <label for="smtp-from-name" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1">From Sender Name</label>
                        <input type="text" id="smtp-from-name" value="<?= Sanitizer::e($mailConfig['from_name'] ?? 'DevDay Work Reports') ?>" placeholder="DevDay Reports" class="text-xs">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 flex-wrap gap-2">
                    <button type="button" onclick="handleTestSMTP()" id="smtp-test-btn" class="sketch-btn sketch-btn-sm sketch-btn-brown">
                        <span>⚡ Test SMTP Connection</span>
                    </button>

                    <button type="submit" id="smtp-save-btn" class="sketch-btn sketch-btn-sm sketch-btn-primary">
                        Save SMTP Settings
                    </button>
                </div>
            </form>

            <!-- Live Diagnostic Output Container -->
            <div id="smtp-diagnostic-box" class="hidden p-4 rounded bg-paper-warm border-2 border-ink space-y-2">
                <div class="flex items-center justify-between">
                    <span id="diagnostic-status-badge" class="stamp stamp-neutral">Diagnostic Running...</span>
                    <button onclick="document.getElementById('smtp-diagnostic-box').classList.add('hidden')" class="text-xs text-ink-muted hover:text-ink font-bold">✕ Close</button>
                </div>
                <div id="diagnostic-summary" class="text-xs text-ink font-bold"></div>
                <pre id="diagnostic-log" class="p-3 bg-[#FAFAF8] border border-ink rounded text-[11px] font-mono text-ink-pencil whitespace-pre-wrap max-h-48 overflow-y-auto"></pre>
            </div>
        </div>

    </div>

</main>

<script>
async function handleUpdateProfile(e) {
    e.preventDefault();
    const btn = document.getElementById('prof-save-btn');
    const name = document.getElementById('prof-name').value.trim();
    const email = document.getElementById('prof-email').value.trim();

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_profile', {
            method: 'POST',
            body: { name, email }
        });
        window.DevDayUI.showToast('Profile updated!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to update profile.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleUpdateManager(e) {
    e.preventDefault();
    const btn = document.getElementById('mgr-save-btn');
    const manager_name = document.getElementById('mgr-name').value.trim();
    const manager_email = document.getElementById('mgr-email').value.trim();

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_manager', {
            method: 'POST',
            body: { manager_name, manager_email }
        });
        window.DevDayUI.showToast('Manager details updated!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to update manager info.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleUpdatePreferences(e) {
    e.preventDefault();
    const btn = document.getElementById('pref-save-btn');
    const payload = {
        default_subject_template: document.getElementById('pref-subject').value.trim(),
        default_workday_start: document.getElementById('pref-start').value,
        default_workday_end: document.getElementById('pref-end').value,
    };

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_preferences', {
            method: 'POST',
            body: payload
        });
        window.DevDayUI.showToast('Workday preferences updated!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to update preferences.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleUpdateSMTP(e) {
    e.preventDefault();
    const btn = document.getElementById('smtp-save-btn');
    const payload = {
        host: document.getElementById('smtp-host').value.trim(),
        port: document.getElementById('smtp-port').value,
        username: document.getElementById('smtp-username').value.trim(),
        password: document.getElementById('smtp-password').value,
        encryption: document.getElementById('smtp-encryption').value,
        from_email: document.getElementById('smtp-from-email').value.trim(),
        from_name: document.getElementById('smtp-from-name').value.trim(),
    };

    try {
        btn.disabled = true;
        await window.DevDayUI.request('/api/settings.php?action=update_smtp', {
            method: 'POST',
            body: payload
        });
        window.DevDayUI.showToast('SMTP settings saved to server configuration!', 'success');
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Failed to save SMTP settings.', 'error');
    } finally {
        btn.disabled = false;
    }
}

async function handleTestSMTP() {
    const btn = document.getElementById('smtp-test-btn');
    const diagBox = document.getElementById('smtp-diagnostic-box');
    const badge = document.getElementById('diagnostic-status-badge');
    const summary = document.getElementById('diagnostic-summary');
    const log = document.getElementById('diagnostic-log');

    const testEmail = prompt('Enter recipient email to receive SMTP test message:', document.getElementById('prof-email').value.trim());
    if (!testEmail) return;

    const payload = {
        test_email: testEmail,
        host: document.getElementById('smtp-host').value.trim(),
        port: document.getElementById('smtp-port').value,
        username: document.getElementById('smtp-username').value.trim(),
        password: document.getElementById('smtp-password').value,
        encryption: document.getElementById('smtp-encryption').value,
        from_email: document.getElementById('smtp-from-email').value.trim(),
        from_name: document.getElementById('smtp-from-name').value.trim(),
    };

    diagBox.classList.remove('hidden');
    badge.className = 'stamp stamp-amber';
    badge.textContent = 'Testing Handshake...';
    summary.textContent = `Connecting to ${payload.host}:${payload.port}...`;
    log.textContent = 'Initiating SMTP connection with PHPMailer...';

    try {
        btn.disabled = true;
        const res = await window.DevDayUI.request('/api/settings.php?action=test_smtp', {
            method: 'POST',
            body: payload
        });

        badge.className = 'stamp stamp-green';
        badge.textContent = 'Connection Succeeded';
        summary.textContent = res.message || `Test email dispatched to ${testEmail}!`;
        log.textContent = res.data?.debug_log || 'SMTP Handshake & 250 OK acknowledgment confirmed.';
        window.DevDayUI.showToast('SMTP test email delivered!', 'success');
    } catch (err) {
        badge.className = 'stamp stamp-red';
        badge.textContent = 'Connection Failed';
        summary.textContent = err.message || 'Unable to connect to SMTP server.';
        log.textContent = err.data?.data?.debug_log || err.data?.error || err.message || 'Error occurred during SMTP handshake.';
        window.DevDayUI.showToast("SMTP Connection Failed. Check diagnostic log.", 'error');
    } finally {
        btn.disabled = false;
    }
}
</script>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
