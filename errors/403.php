<?php
// errors/403.php
require_once __DIR__ . '/../includes/constants.php';
define('PAGE_TITLE', 'Access Denied');
require_once BASE_PATH . '/includes/header.php';
?>
<div class="min-h-screen w-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <h1 class="text-9xl font-bold text-rose-500">403</h1>
        <h2 class="mt-4 text-2xl font-bold text-slate-900">Access Denied</h2>
        <p class="mt-2 text-sm text-slate-600">You do not have permission to view this resource.</p>
        <div class="mt-6">
            <a href="<?= BASE_URL ?>" class="btn-primary">Return to Safety</a>
        </div>
    </div>
</div>
<?php require_once BASE_PATH . '/includes/footer.php'; ?>
