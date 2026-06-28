<?php
// owner/permissions/matrix.php
require_once __DIR__ . '/../../includes/functions.php';
requireOwner();

$roleId = $_GET['role_id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();

    // Fetch Role
    $stmt = $db->prepare("SELECT * FROM roles WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
    $stmt->execute([$roleId, $_SESSION['company_id']]);
    $role = $stmt->fetch();

    if (!$role) redirect(BASE_URL . 'errors/404.php');

    // Fetch Global Permissions grouped by module
    $stmt = $db->query("SELECT * FROM permissions ORDER BY module ASC, permission_key ASC");
    $permissionsRaw = $stmt->fetchAll();

    $modules = [];
    foreach ($permissionsRaw as $p) {
        $modules[$p['module']][] = $p;
    }

    // Fetch Active Role Permissions
    $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$roleId]);
    $activePermIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    redirect(BASE_URL . 'errors/500.php');
}

define('PAGE_TITLE', 'Permission Matrix');
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/owner/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    <?php require_once BASE_PATH . '/owner/includes/topbar.php'; ?>

    <main class="flex-1 overflow-y-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <a href="../roles/index.php" class="mr-4 text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Permission Matrix: <?= esc($role['display_name']) ?></h1>
                        <p class="mt-1 text-sm text-slate-500">Enable or disable specific system actions for this role.</p>
                    </div>
                </div>
            </div>

            <?php if ($role['role_name'] === 'Owner'): ?>
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl mb-6">
                <p class="font-medium text-sm text-center">The master 'Owner' role has irrevocable full system access. Permissions cannot be modified.</p>
            </div>
            <?php endif; ?>

            <div class="card overflow-hidden">
                <form id="matrixForm">
                    <input type="hidden" name="role_id" value="<?= $role['id'] ?>">

                    <div class="bg-white px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <div class="text-sm font-medium text-slate-700">Module Access Level</div>
                        <?php if ($role['role_name'] !== 'Owner'): ?>
                            <button type="button" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider" onclick="$('.perm-check').prop('checked', true);">Check All</button>
                        <?php endif; ?>
                    </div>

                    <div class="card-body p-0">
                        <?php foreach ($modules as $moduleName => $perms): ?>
                        <div class="border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                            <div class="px-6 py-4 flex flex-col sm:flex-row gap-4">
                                <!-- Module Name Col -->
                                <div class="w-full sm:w-1/4 pt-1">
                                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider"><?= esc($moduleName) ?></h3>
                                </div>
                                <!-- Permissions Grid Col -->
                                <div class="w-full sm:w-3/4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach($perms as $p): ?>
                                    <label class="flex items-start space-x-3 cursor-pointer group">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="perm-check h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 transition-colors"
                                                <?= in_array($p['id'], $activePermIds) || $role['role_name'] === 'Owner' ? 'checked' : '' ?>
                                                <?= $role['role_name'] === 'Owner' ? 'disabled' : '' ?>
                                            >
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-slate-900 group-hover:text-indigo-600 transition-colors"><?= esc($p['description'] ?: $p['permission_key']) ?></span>
                                            <span class="text-xs font-mono text-slate-400 mt-0.5"><?= esc($p['permission_key']) ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($role['role_name'] !== 'Owner'): ?>
                    <div class="card-body border-t border-slate-100 bg-slate-50">
                        <div class="flex justify-between items-center">
                            <button type="button" class="btn-secondary" onclick="$('.perm-check').prop('checked', false);">Clear All</button>
                            <div class="flex gap-3">
                                <a href="../roles/index.php" class="btn-secondary">Cancel</a>
                                <button type="submit" class="btn-primary">Save Matrix</button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
<script>
$(document).ready(function() {
    $('#matrixForm').on('submit', function(e) {
        e.preventDefault();
        let submitBtn = $(this).find('button[type="submit"]');
        let originalText = submitBtn.text();
        submitBtn.html('<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mx-auto"></div>').prop('disabled', true);

        $.post('<?= BASE_URL ?>api/owner/roles/permissions.php', $(this).serialize(), function(response) {
            if(response.status === 'success') {
                showToast('success', response.message);
            } else {
                showToast('error', response.message);
            }
            submitBtn.text(originalText).prop('disabled', false);
        }).fail(function(xhr) {
            showToast('error', xhr.responseJSON ? xhr.responseJSON.message : 'Network error.');
            submitBtn.text(originalText).prop('disabled', false);
        });
    });
});
</script>
