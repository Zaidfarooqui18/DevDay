<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
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
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/templates/layout/nav.php'; ?>

<main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Settings &amp; Configuration</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage your developer profile, manager report recipient, and report templates.</p>
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

        <!-- 4. SYSTEM & SMTP STATUS STRIP -->
        <div class="p-5 rounded-2xl bg-[#0d1322] border border-slate-800 flex items-center justify-between text-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="font-bold text-white">SMTP Delivery Backend</div>
                    <div class="text-[11px] text-slate-400">Credentials configured in server environment (.env)</div>
                </div>
            </div>
            <span class="font-mono text-[11px] text-emerald-400 font-semibold px-2.5 py-1 bg-emerald-950/60 rounded-lg border border-emerald-500/30">
                PHPMailer Ready
            </span>
        </div>
    </div>
</main>

<script>
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
</script>

<?php include dirname(__DIR__) . '/templates/layout/footer.php'; ?>
