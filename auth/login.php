<?php
// auth/login.php
require_once __DIR__ . '/../includes/functions.php';
requireGuest();

define('PAGE_TITLE', 'Sign In');
require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Optional: Subtle background illustration/pattern could go here -->

    <!-- Mobile-friendly Container -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md animate-fade-in">

        <!-- Logo / App Name -->
        <div class="text-center mb-8">
            <span class="text-3xl font-bold text-indigo-600 tracking-wider"><?= esc(APP_NAME) ?></span>
            <h2 class="mt-4 text-2xl font-bold text-slate-900">Sign in to your account</h2>
            <p class="mt-2 text-sm text-slate-600">Welcome back! Please enter your details.</p>
        </div>

        <!-- Auth Card -->
        <div class="bg-white py-8 px-4 shadow-sm sm:rounded-2xl border border-slate-100 sm:px-10">
            <form id="loginForm" class="space-y-6">

                <!-- Email Input -->
                <div>
                    <label for="email" class="form-label">Email address <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required class="form-input pl-10" placeholder="you@company.com">
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="form-label">Password <span class="text-rose-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input pl-10 pr-10" placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-slate-400 hover:text-indigo-600 transition-colors" onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password';">
                            <!-- Toggle View Icon Placeholder -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded cursor-pointer transition-colors">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-900 cursor-pointer">
                            Remember me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= BASE_URL ?>auth/forgot-password.php" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Sign in
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Meta -->
        <div class="mt-8 text-center text-xs text-slate-500">
            <p>&copy; <?= date('Y') ?> <?= esc(APP_NAME) ?>. All rights reserved.</p>
            <p class="mt-1">Version <?= esc(APP_VERSION) ?></p>
        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();

        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.ajax({
            url: '<?= BASE_URL ?>api/auth/login.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if(response.status === 'success') {
                    showToast('success', response.message);
                    window.location.href = response.data.redirect;
                } else {
                    showToast('error', response.message || 'An error occurred.');
                    submitBtn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                showToast('error', res ? res.message : 'Network error. Please try again.');
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
