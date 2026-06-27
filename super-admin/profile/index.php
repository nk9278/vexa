<?php
// super-admin/profile/index.php
require_once __DIR__ . '/../../includes/functions.php';
requireSuperAdmin();

define('PAGE_TITLE', 'Master Profile');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/super-admin/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/super-admin/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">Master Profile</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your root access credentials and personal information.</p>
            </div>

            <div class="card mb-8">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-medium text-slate-900">Profile Information</h2>
                </div>
                <div class="card-body flex flex-col md:flex-row gap-8">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="h-32 w-32 rounded-full bg-slate-200 border-4 border-white shadow-md flex items-center justify-center text-4xl font-bold text-slate-400">
                            SA
                        </div>
                        <button class="btn-secondary text-xs">Change Picture</button>
                    </div>

                    <form class="flex-1 space-y-4" onsubmit="event.preventDefault(); showToast('success', 'Profile updated successfully.');">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-input" value="System" required>
                            </div>
                            <div>
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-input" value="Admin" required>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Email Address (Root)</label>
                            <input type="email" class="form-input" value="admin@vexa.app" required>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-rose-200">
                <div class="border-b border-rose-100 px-6 py-4 bg-rose-50/50">
                    <h2 class="text-lg font-medium text-rose-800">Security</h2>
                </div>
                <div class="card-body">
                    <form class="space-y-4" onsubmit="event.preventDefault(); showToast('success', 'Password updated successfully.');">
                        <div>
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-input" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-input" required>
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn-danger">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
