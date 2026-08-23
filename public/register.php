<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
AuthMiddleware::redirectIfAuthenticated();
$pageTitle = 'Register — DevDay';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 flex-1">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-xl shadow-indigo-500/30 mx-auto mb-4">
            <span class="tracking-tight text-lg font-mono font-extrabold">D</span>
        </div>
        <h2 class="text-2xl font-extrabold text-white tracking-tight">Create your DevDay account</h2>
        <p class="text-xs text-slate-400 mt-1.5">Track your daily work and report directly to your manager</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg px-4 sm:px-0">
        <div class="bg-[#111726] py-8 px-6 sm:px-10 rounded-2xl border border-slate-800 shadow-2xl space-y-6">
            
            <form id="register-form" onsubmit="handleRegister(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Your Full Name <span class="text-rose-400">*</span></label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Zaid Farooqui" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Your Work Email <span class="text-rose-400">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="zaid@example.com" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password <span class="text-rose-400">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>

                <!-- Manager Info Section -->
                <div class="pt-3 border-t border-slate-800/80 space-y-3">
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-indigo-400"></i>
                        <span>Reporting Manager Setup (Optional)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="manager_name" class="block text-xs font-semibold text-slate-400 mb-1">Manager's Name</label>
                            <input type="text" id="manager_name" name="manager_name" placeholder="e.g. Alex Vance" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="manager_email" class="block text-xs font-semibold text-slate-400 mb-1">Manager's Email</label>
                            <input type="email" id="manager_email" name="manager_email" placeholder="alex.vance@techcorp.io" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-500">You can edit manager details and add custom report recipients anytime in Settings.</p>
                </div>

                <div class="pt-4">
                    <button type="submit" id="register-btn" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>Complete Registration</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-800/80 text-center">
                <p class="text-xs text-slate-400">
                    Already have an account? 
                    <a href="/login.php" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors ml-1">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
async function handleRegister(e) {
    e.preventDefault();
    const btn = document.getElementById('register-btn');
    const payload = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        manager_name: document.getElementById('manager_name').value,
        manager_email: document.getElementById('manager_email').value,
    };

    try {
        btn.disabled = true;
        btn.innerHTML = '<span>Creating account...</span>';

        await window.DevDayUI.request('/api/auth.php?action=register', {
            method: 'POST',
            body: payload
        });

        window.DevDayUI.showToast('Account created! Welcome to DevDay.', 'success');
        setTimeout(() => {
            window.location.href = '/index.php';
        }, 400);
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Registration failed.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span>Complete Registration</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>';
        if (window.lucide) lucide.createIcons();
    }
}
</script>
</body>
</html>
