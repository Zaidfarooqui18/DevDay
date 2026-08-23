<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;
use DevDay\Helpers\CSRF;

App::init();
AuthMiddleware::redirectIfAuthenticated();
$pageTitle = 'Log In — DevDay';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 flex-1">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white shadow-xl shadow-indigo-500/30 mx-auto mb-4">
            <span class="tracking-tight text-lg font-mono font-extrabold">D</span>
        </div>
        <h2 class="text-2xl font-extrabold text-white tracking-tight">Sign in to DevDay</h2>
        <p class="text-xs text-slate-400 mt-1.5">Personal daily work management &amp; report generation</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-[#111726] py-8 px-6 sm:px-10 rounded-2xl border border-slate-800 shadow-2xl space-y-6">
            
            <form id="login-form" onsubmit="handleLogin(event)" class="space-y-4">
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email address</label>
                    <input type="email" id="email" name="email" required autocomplete="email" value="zaid@example.com" placeholder="developer@example.com" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" value="password123" placeholder="••••••••" class="w-full bg-[#090d16] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                </div>

                <div class="pt-2">
                    <button type="submit" id="login-btn" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                        <span>Sign In</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-800/80 text-center">
                <p class="text-xs text-slate-400">
                    Don't have an account? 
                    <a href="/register.php" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors ml-1">Create one</a>
                </p>
            </div>

            <!-- Demo Credentials Helper Strip -->
            <div class="p-3 rounded-xl bg-indigo-950/30 border border-indigo-500/20 text-[11px] text-indigo-200">
                <div class="font-semibold text-indigo-300 mb-1">Development Demo Credentials:</div>
                <div class="flex justify-between">
                    <span>Email: <strong class="font-mono text-white">zaid@example.com</strong></span>
                    <span>Pass: <strong class="font-mono text-white">password123</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/assets/js/app.js"></script>
<script>
async function handleLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        btn.disabled = true;
        btn.innerHTML = '<span>Signing in...</span>';

        const res = await window.DevDayUI.request('/api/auth.php?action=login', {
            method: 'POST',
            body: { email, password }
        });

        window.DevDayUI.showToast('Logged in successfully! Redirecting...', 'success');
        setTimeout(() => {
            window.location.href = '/index.php';
        }, 300);
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Login failed.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span>Sign In</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>';
        if (window.lucide) lucide.createIcons();
    }
}
</script>
</body>
</html>
