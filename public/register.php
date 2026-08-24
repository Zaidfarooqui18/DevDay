<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use DevDay\Config\App;
use DevDay\Middleware\AuthMiddleware;

App::init();
AuthMiddleware::redirectIfAuthenticated();
$pageTitle = 'Create Account — DevDay';
?>
<?php include dirname(__DIR__) . '/templates/layout/header.php'; ?>

<main class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 flex-1">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center mb-6">
        <h1 class="font-hand font-bold text-4xl text-ink tracking-tight flex items-center justify-center gap-2">
            <span class="text-ink-brown font-mono font-black text-3xl">DEV</span>day
            <span class="text-lg text-ink-brown font-sans font-bold">✎</span>
        </h1>
        <p class="font-hand text-xl text-ink-pencil mt-1">let's get started.</p>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="paper-card p-8 bg-paper tilt-subtle space-y-6">
            <form id="register-form" onsubmit="handleRegister(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                            Full Name <span class="text-stamp-red">*</span>
                        </label>
                        <input type="text" id="name" name="name" required placeholder="e.g. Zaid Farooqui">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                            Work Email <span class="text-stamp-red">*</span>
                        </label>
                        <input type="email" id="email" name="email" required placeholder="developer@example.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-ink uppercase tracking-wider mb-1.5">
                        Password <span class="text-stamp-red">*</span>
                    </label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters">
                </div>

                <!-- Manager Reporting Section (Optional) -->
                <div class="pt-3 border-t border-dashed border-[#D4C4A8] space-y-3">
                    <div class="text-xs font-bold text-ink-brown uppercase tracking-wider">
                        Direct Manager Setup (Optional)
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="manager_name" class="block text-xs font-bold text-ink-muted uppercase tracking-wider mb-1">
                                Manager Name
                            </label>
                            <input type="text" id="manager_name" name="manager_name" placeholder="e.g. Alex Vance">
                        </div>

                        <div>
                            <label for="manager_email" class="block text-xs font-bold text-ink-muted uppercase tracking-wider mb-1">
                                Manager Email
                            </label>
                            <input type="email" id="manager_email" name="manager_email" placeholder="manager@example.com">
                        </div>
                    </div>
                    <p class="text-[11px] text-ink-muted italic">You can change manager details anytime in Settings.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" id="register-btn" class="w-full sketch-btn sketch-btn-primary py-2.5 text-sm">
                        <span>create account →</span>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-dashed border-[#D4C4A8] text-center text-xs text-ink-pencil">
                Already have an account? 
                <a href="/login.php" class="font-bold text-ink-brown hover:underline ml-1">log in here</a>
            </div>
        </div>
    </div>
</main>

<script src="/assets/js/app.js"></script>
<script>
async function handleRegister(e) {
    e.preventDefault();
    const btn = document.getElementById('register-btn');
    const payload = {
        name: document.getElementById('name').value.trim(),
        email: document.getElementById('email').value.trim(),
        password: document.getElementById('password').value,
        manager_name: document.getElementById('manager_name').value.trim(),
        manager_email: document.getElementById('manager_email').value.trim(),
    };

    if (!payload.name || !payload.email || !payload.password) {
        window.DevDayUI.showToast('Please fill out all required fields.', 'error');
        return;
    }

    try {
        btn.disabled = true;
        btn.innerHTML = '<span>creating account...</span>';

        await window.DevDayUI.request('/api/auth.php?action=register', {
            method: 'POST',
            body: payload
        });

        window.DevDayUI.showToast('Account created! Welcome to DevDay.', 'success');
        setTimeout(() => {
            window.location.href = '/index.php';
        }, 300);
    } catch (err) {
        window.DevDayUI.showToast(err.message || 'Registration failed.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<span>create account →</span>';
    }
}
</script>
</body>
</html>
