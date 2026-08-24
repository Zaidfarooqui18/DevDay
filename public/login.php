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

<main class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 flex-1">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <h1 class="font-hand font-bold text-4xl text-ink tracking-tight flex items-center justify-center gap-2">
            <span class="text-ink-brown font-mono font-black text-3xl">DEV</span>day
            <span class="text-lg text-ink-brown font-sans font-bold">✎</span>
        </h1>
        <p class="font-hand text-xl text-ink-pencil mt-1">welcome back.</p>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="paper-card p-8 bg-paper tilt-subtle space-y-6">
            <form id="login-form" onsubmit="handleLogin(event)" class="space-y-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autocomplete="email" 
                        placeholder="you@example.com" 
                        class="text-sm font-medium"
                    >
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password" 
                        placeholder="••••••••" 
                        class="text-sm font-medium"
                    >
                </div>

                <div class="pt-2">
                    <button type="submit" id="login-btn" class="w-full sketch-btn sketch-btn-primary py-2.5 text-sm">
                        <span>log in →</span>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-dashed border-[#D4C4A8] text-center text-xs text-ink-pencil">
                Don't have an account? 
                <a href="/register.php" class="font-bold text-ink-brown hover:underline ml-1">make one</a>
            </div>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script>
async function handleLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        window.DevDayUI.showToast('Please enter both email and password.', 'error');
        return;
    }

    try {
        btn.disabled = true;
        btn.innerHTML = '<span>logging in...</span>';

        const res = await window.DevDayUI.request('/api/auth.php?action=login', {
            method: 'POST',
            body: { email, password }
        });

        window.DevDayUI.showToast('Logged in successfully.', 'success');
        setTimeout(() => {
            window.location.href = '/index.php';
        }, 250);
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Invalid email or password.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span>log in →</span>';
    }
}
</script>
</body>
</html>
