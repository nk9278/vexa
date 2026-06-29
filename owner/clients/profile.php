<?php
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/database.php';
require_once '../../includes/helpers.php';

requireLogin();
requirePermission('view_client');

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /owner/clients/");
    exit;
}

$db = Database::getInstance()->getConnection();
$company_id = $_SESSION['company_id'];

// Get Client
$stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND company_id = ? AND deleted_at IS NULL");
$stmt->execute([$id, $company_id]);
$client = $stmt->fetch();

if (!$client) {
    header("Location: /owner/clients/");
    exit;
}

// Get Assigned CRMs
$stmtCrm = $db->prepare("
    SELECT t.id, t.full_name, t.email, t.mobile, t.profile_photo, r.display_name as role_name
    FROM client_assignments ca
    JOIN team_members t ON ca.crm_id = t.id
    LEFT JOIN roles r ON t.role_id = r.id
    WHERE ca.client_id = ? AND ca.company_id = ?
");
$stmtCrm->execute([$id, $company_id]);
$crms = $stmtCrm->fetchAll();

// Get Assigned Production Team
$stmtTeam = $db->prepare("
    SELECT t.id, t.full_name, t.email, t.profile_photo, r.display_name as role_name
    FROM client_team_assignments cta
    JOIN team_members t ON cta.team_member_id = t.id
    LEFT JOIN roles r ON t.role_id = r.id
    WHERE cta.client_id = ? AND cta.company_id = ?
");
$stmtTeam->execute([$id, $company_id]);
$team = $stmtTeam->fetchAll();

// Get Credentials
$credentials = [];
if (hasPermission('view_credentials')) {
    $stmtCred = $db->prepare("SELECT id, platform, username, login_url, notes, created_at FROM client_credentials WHERE client_id = ? AND company_id = ? ORDER BY platform ASC");
    $stmtCred->execute([$id, $company_id]);
    $credentials = $stmtCred->fetchAll();
}

// Get Packages
$stmtPkg = $db->prepare("SELECT * FROM client_packages WHERE client_id = ? AND company_id = ? ORDER BY created_at DESC");
$stmtPkg->execute([$id, $company_id]);
$packages = $stmtPkg->fetchAll();

// Get Notes
$stmtNotes = $db->prepare("
    SELECT n.*, u.first_name, u.last_name
    FROM client_notes n
    JOIN users u ON n.created_by = u.id
    WHERE n.client_id = ? AND n.company_id = ?
    ORDER BY n.created_at DESC
");
$stmtNotes->execute([$id, $company_id]);
$notes = $stmtNotes->fetchAll();

// Get Attachments
$stmtAtt = $db->prepare("
    SELECT a.*, u.first_name, u.last_name
    FROM client_attachments a
    JOIN users u ON a.uploaded_by = u.id
    WHERE a.client_id = ? AND a.company_id = ?
    ORDER BY a.created_at DESC
");
$stmtAtt->execute([$id, $company_id]);
$attachments = $stmtAtt->fetchAll();

// Activity Log
$stmtAct = $db->prepare("
    SELECT action, details, created_at
    FROM system_logs
    WHERE company_id = ? AND module = 'client' AND record_id = ?
    ORDER BY created_at DESC LIMIT 15
");
$stmtAct->execute([$company_id, $id]);
$activities = $stmtAct->fetchAll();

// Current Active Tab Logic
$activeTab = $_GET['tab'] ?? 'overview';

require_once '../../includes/header.php';
require_once '../includes/topbar.php';
require_once '../includes/sidebar.php';
?>

<div class="flex-1 ml-64 mt-16 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between md:items-start gap-4">
                <div class="flex items-center">
                    <div class="w-16 h-16 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-2xl border-2 border-indigo-200 shadow-sm mr-5">
                        <?= strtoupper(substr($client['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800"><?= esc($client['name']) ?></h1>
                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                            <?php if ($client['brand_name']): ?>
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg> <?= esc($client['brand_name']) ?></span>
                            <?php endif; ?>
                            <?php if ($client['business_category']): ?>
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> <?= esc($client['business_category']) ?></span>
                            <?php endif; ?>
                            <?php if ($client['website']): ?>
                                <a href="<?= esc($client['website']) ?>" target="_blank" class="flex items-center text-primary hover:underline"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg> Website</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <?php
                        $s = $client['status'];
                        $sc = 'bg-gray-100 text-gray-700';
                        if ($s === 'active') $sc = 'bg-green-100 text-green-700';
                        if ($s === 'lead' || $s === 'pending') $sc = 'bg-blue-100 text-blue-700';
                        if ($s === 'paused') $sc = 'bg-yellow-100 text-yellow-700';
                    ?>
                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full <?= $sc ?> border border-gray-200"><?= esc($s) ?></span>
                    <?php if (hasPermission('edit_client')): ?>
                        <a href="/owner/clients/edit.php?id=<?= $id ?>" class="text-sm text-primary hover:text-primary-dark transition-colors font-medium mt-1">Edit Client Profile</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-t-xl shadow-sm border-b border-gray-200 px-6 flex overflow-x-auto no-scrollbar">
            <?php
            $tabs = [
                'overview' => 'Overview',
                'credentials' => 'Credential Vault',
                'packages' => 'Packages & Billing',
                'team' => 'Assigned Team',
                'notes' => 'Notes',
                'attachments' => 'Attachments',
                'timeline' => 'Timeline'
            ];
            foreach ($tabs as $key => $label) {
                $isActive = $activeTab === $key;
                $activeClasses = $isActive ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
                echo "<a href=\"?id={$id}&tab={$key}\" class=\"whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors {$activeClasses}\">{$label}</a>";
            }
            ?>
        </div>

        <div class="bg-white rounded-b-xl shadow-sm border border-t-0 border-gray-100 p-6 min-h-[500px]">

            <?php if ($activeTab === 'overview'): ?>
            <!-- OVERVIEW TAB -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Contact Info -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider flex items-center"><svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Contact Details</h3>
                    <div class="space-y-3 text-sm">
                        <?php if($client['owner_name']): ?><div class="flex"><span class="w-24 text-gray-500">Owner:</span> <span class="font-medium text-gray-800"><?= esc($client['owner_name']) ?></span></div><?php endif; ?>
                        <?php if($client['contact_person']): ?><div class="flex"><span class="w-24 text-gray-500">Contact:</span> <span class="font-medium text-gray-800"><?= esc($client['contact_person']) ?></span></div><?php endif; ?>
                        <?php if($client['email']): ?><div class="flex"><span class="w-24 text-gray-500">Email:</span> <span class="font-medium text-gray-800 break-all"><?= esc($client['email']) ?></span></div><?php endif; ?>
                        <?php if($client['business_email']): ?><div class="flex"><span class="w-24 text-gray-500">Biz Email:</span> <span class="font-medium text-gray-800 break-all"><?= esc($client['business_email']) ?></span></div><?php endif; ?>
                        <?php if($client['mobile']): ?><div class="flex"><span class="w-24 text-gray-500">Mobile:</span> <span class="font-medium text-gray-800"><?= esc($client['mobile']) ?></span></div><?php endif; ?>
                    </div>
                </div>

                <!-- Business Details -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider flex items-center"><svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> Business Details</h3>
                    <div class="space-y-3 text-sm">
                        <?php if($client['gst_number']): ?><div class="flex"><span class="w-24 text-gray-500">GST:</span> <span class="font-medium text-gray-800"><?= esc($client['gst_number']) ?></span></div><?php endif; ?>
                        <?php if($client['address']): ?><div class="flex flex-col mb-2"><span class="text-gray-500 mb-1">Address:</span> <span class="font-medium text-gray-800"><?= esc($client['address']) ?><br><?= esc($client['city']) ?>, <?= esc($client['state']) ?> <?= esc($client['pin_code']) ?></span></div><?php endif; ?>
                        <?php if($client['business_description']): ?><div class="flex flex-col mt-2"><span class="text-gray-500 mb-1">Description:</span> <span class="text-gray-700 italic border-l-2 border-gray-300 pl-2"><?= nl2br(esc($client['business_description'])) ?></span></div><?php endif; ?>
                    </div>
                </div>

                <!-- Assignments Quick View -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider flex items-center"><svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg> Active Personnel</h3>

                    <div class="mb-4">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">CRM Managers</span>
                        <?php if(empty($crms)): ?><span class="text-sm text-gray-400 italic">None assigned</span><?php endif; ?>
                        <div class="space-y-2">
                            <?php foreach($crms as $c): ?>
                                <div class="flex items-center text-sm"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold mr-2"><?= substr($c['full_name'],0,1) ?></span> <span class="font-medium text-gray-800"><?= esc($c['full_name']) ?></span></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 block">Production Team</span>
                        <?php if(empty($team)): ?><span class="text-sm text-gray-400 italic">None assigned</span><?php endif; ?>
                        <div class="space-y-2">
                            <?php foreach($team as $t): ?>
                                <div class="flex items-center text-sm"><span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-bold mr-2"><?= substr($t['full_name'],0,1) ?></span> <div><span class="font-medium text-gray-800 block"><?= esc($t['full_name']) ?></span><span class="text-[10px] text-gray-500"><?= esc($t['role_name']) ?></span></div></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future placeholders -->
            <div class="mt-8 border border-dashed border-gray-300 rounded-xl bg-gray-50/50 p-8 text-center">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="text-lg font-medium text-gray-800">Project & Deliverable Tracking</h3>
                <p class="text-gray-500 text-sm mt-1">This module will connect to the Task Engine in Phase 22 & 23 to show active projects and pending deliverables for this client.</p>
            </div>
            <?php endif; ?>

            <?php if ($activeTab === 'credentials'): ?>
            <!-- CREDENTIALS TAB -->
            <div class="mb-6 flex justify-between items-center">
                <p class="text-sm text-gray-600">Securely store and manage client passwords and access tokens.</p>
                <?php if(hasPermission('edit_credentials')): ?>
                    <button onclick="$('#addCredModal').removeClass('hidden')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium text-sm">+ Add Credential</button>
                <?php endif; ?>
            </div>

            <?php if(!hasPermission('view_credentials')): ?>
                <div class="p-8 text-center border border-red-100 bg-red-50 rounded-xl text-red-600">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <p class="font-bold">Access Denied</p>
                    <p class="text-sm mt-1">You do not have permission to view the credential vault.</p>
                </div>
            <?php elseif(empty($credentials)): ?>
                <div class="p-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-xl">
                    <p class="text-sm">No credentials stored yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($credentials as $c): ?>
                        <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow bg-white relative group">
                            <?php if(hasPermission('edit_credentials')): ?>
                                <button class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors delete-cred-btn opacity-0 group-hover:opacity-100" data-id="<?= $c['id'] ?>" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            <?php endif; ?>
                            <div class="flex items-center mb-3">
                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center mr-3 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                </div>
                                <h4 class="font-bold text-gray-800"><?= esc($c['platform']) ?></h4>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div><span class="text-gray-400 block text-xs">Username</span> <span class="font-medium text-gray-800"><?= esc($c['username']) ?></span></div>
                                <div>
                                    <span class="text-gray-400 block text-xs">Password</span>
                                    <div class="flex items-center gap-2">
                                        <input type="password" value="********" readonly class="bg-gray-50 border border-gray-200 rounded px-2 py-1 w-full text-gray-500 outline-none" id="pw-<?= $c['id'] ?>">
                                        <button type="button" class="text-primary hover:text-primary-dark p-1 view-pw-btn" data-id="<?= $c['id'] ?>" title="View Password">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <?php if($c['login_url']): ?>
                                    <div><span class="text-gray-400 block text-xs">URL</span> <a href="<?= esc($c['login_url']) ?>" target="_blank" class="text-primary hover:underline truncate block"><?= esc($c['login_url']) ?></a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Add Credential Modal -->
            <div id="addCredModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-gray-800">Add Secure Credential</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="$('#addCredModal').addClass('hidden')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form id="addCredForm" class="p-6 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="client_id" value="<?= $id ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Platform *</label>
                            <input type="text" name="platform" placeholder="e.g. Facebook, HostGator" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username / Email</label>
                            <input type="text" name="username" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="text" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Login URL</label>
                            <input type="url" name="login_url" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <input type="text" name="notes" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div class="pt-4 text-right">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-dark">Save Securely</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($activeTab === 'packages'): ?>
            <!-- PACKAGES TAB -->
            <div class="mb-6 flex justify-between items-center">
                <p class="text-sm text-gray-600">Manage client billing packages and retainers.</p>
                <?php if(hasPermission('edit_client')): ?>
                    <button onclick="$('#addPkgModal').removeClass('hidden')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium text-sm">+ Add Package</button>
                <?php endif; ?>
            </div>

            <?php if(empty($packages)): ?>
                <div class="p-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-xl">
                    <p class="text-sm">No packages defined.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="p-3 font-medium text-gray-600 text-sm">Package</th>
                                <th class="p-3 font-medium text-gray-600 text-sm text-right">Price</th>
                                <th class="p-3 font-medium text-gray-600 text-sm">Cycle</th>
                                <th class="p-3 font-medium text-gray-600 text-sm">Next Renewal</th>
                                <th class="p-3 font-medium text-gray-600 text-sm">Status</th>
                                <th class="p-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($packages as $p): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 font-medium text-gray-800"><?= esc($p['package_name']) ?></td>
                                    <td class="p-3 text-right font-medium text-gray-700"><?= number_format($p['package_price'], 2) ?></td>
                                    <td class="p-3 text-sm text-gray-600"><?= esc($p['billing_cycle']) ?></td>
                                    <td class="p-3 text-sm text-gray-600"><?= $p['renewal_date'] ? date('M j, Y', strtotime($p['renewal_date'])) : '-' ?></td>
                                    <td class="p-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?= $p['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>"><?= esc($p['status']) ?></span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <?php if(hasPermission('edit_client')): ?>
                                            <button class="text-red-400 hover:text-red-600 delete-pkg-btn" data-id="<?= $p['id'] ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Add Pkg Modal -->
            <div id="addPkgModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-gray-800">Add Package / Retainer</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="$('#addPkgModal').addClass('hidden')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form id="addPkgForm" class="p-6 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="client_id" value="<?= $id ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Package Name *</label>
                            <input type="text" name="package_name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                                <input type="number" step="0.01" name="package_price" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cycle</label>
                                <select name="billing_cycle" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none bg-white">
                                    <option value="Monthly">Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Yearly">Yearly</option>
                                    <option value="One-time">One-time</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Renewal Date</label>
                                <input type="date" name="renewal_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none">
                            </div>
                        </div>
                        <div class="pt-4 text-right">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-dark">Save Package</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>


            <?php if ($activeTab === 'notes'): ?>
            <!-- NOTES TAB -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-4">
                    <?php if(empty($notes)): ?>
                        <div class="p-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-xl">
                            <p class="text-sm">No notes added.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notes as $n): ?>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 relative group">
                                <?php if(hasPermission('edit_client')): ?>
                                    <button class="absolute top-4 right-4 text-gray-300 hover:text-red-500 transition-colors delete-note-btn opacity-0 group-hover:opacity-100" data-id="<?= $n['id'] ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                <?php endif; ?>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 rounded text-[10px] font-bold uppercase tracking-wider text-gray-500"><?= esc($n['note_type']) ?></span>
                                    <span class="text-xs text-gray-400"><?= date('M j, g:i a', strtotime($n['created_at'])) ?> by <?= esc($n['first_name'].' '.$n['last_name']) ?></span>
                                </div>
                                <div class="text-sm text-gray-800 whitespace-pre-wrap"><?= esc($n['content']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if(hasPermission('edit_client')): ?>
                <div class="lg:col-span-1">
                    <form id="addNoteForm" class="bg-gray-50 p-4 rounded-xl border border-gray-100 sticky top-20">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="client_id" value="<?= $id ?>">
                        <h4 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider">Add Note</h4>
                        <div class="mb-3">
                            <select name="note_type" class="w-full border border-gray-300 rounded text-sm px-3 py-1.5 focus:border-primary outline-none">
                                <option value="Internal">Internal Note</option>
                                <option value="Instruction">Client Instruction</option>
                                <option value="Meeting">Meeting Note</option>
                                <option value="Follow-up">Follow-up</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <textarea name="content" rows="4" required class="w-full border border-gray-300 rounded text-sm px-3 py-2 focus:border-primary outline-none resize-none" placeholder="Type note here..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white rounded py-2 text-sm font-medium hover:bg-primary-dark transition-colors">Post Note</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($activeTab === 'attachments'): ?>
            <!-- ATTACHMENTS TAB -->
            <div class="mb-6 flex justify-between items-center">
                <p class="text-sm text-gray-600">Store reference files, contracts, and brand assets.</p>
                <?php if(hasPermission('edit_client')): ?>
                    <button onclick="$('#addAttModal').removeClass('hidden')" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors font-medium text-sm">+ Upload File</button>
                <?php endif; ?>
            </div>

            <?php if(empty($attachments)): ?>
                <div class="p-8 text-center text-gray-500 border border-dashed border-gray-300 rounded-xl">
                    <p class="text-sm">No files uploaded.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach($attachments as $a): ?>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 flex flex-col justify-between group">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <span class="px-2 py-0.5 bg-white border border-gray-200 rounded text-[10px] font-bold uppercase tracking-wider text-gray-500"><?= esc($a['file_type']) ?></span>
                                    <?php if(hasPermission('edit_client')): ?>
                                        <button class="text-gray-400 hover:text-red-500 transition-colors delete-att-btn opacity-0 group-hover:opacity-100" data-id="<?= $a['id'] ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= esc($a['file_path']) ?>" target="_blank" class="text-sm font-medium text-primary hover:underline break-words line-clamp-2" title="<?= esc($a['file_name']) ?>"><?= esc($a['file_name']) ?></a>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-3 pt-3 border-t border-gray-200 flex justify-between">
                                <span><?= number_format($a['file_size'] / 1024, 1) ?> KB</span>
                                <span><?= date('M j, Y', strtotime($a['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Add Attachment Modal -->
            <div id="addAttModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex items-center justify-center">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h3 class="font-bold text-gray-800">Upload Attachment</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="$('#addAttModal').addClass('hidden')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <form id="addAttForm" class="p-6 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="upload">
                        <input type="hidden" name="client_id" value="<?= $id ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                            <select name="file_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary outline-none bg-white">
                                <option value="Document">Document</option>
                                <option value="Logo">Logo / Branding</option>
                                <option value="Contract">Contract</option>
                                <option value="Image">Reference Image</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select File</label>
                            <input type="file" name="attachment" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none">
                            <p class="text-[10px] text-gray-500 mt-1">Allowed: jpg, png, pdf, docx, zip (Max 5MB)</p>
                        </div>

                        <div class="pt-4 text-right">
                            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-dark">Upload File</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>


            <?php if ($activeTab === 'timeline'): ?>
            <!-- TIMELINE TAB -->
            <div class="max-w-3xl mx-auto py-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Activity Timeline</h3>
                <?php if (empty($activities)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">No activity found.</p>
                <?php else: ?>
                    <div class="relative border-l-2 border-indigo-100 ml-4 space-y-8 pb-8">
                        <?php foreach ($activities as $act): ?>
                            <div class="relative pl-8">
                                <span class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-indigo-500 ring-4 ring-white"></span>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="text-sm font-bold text-gray-800 mb-1">
                                        <?= esc(ucwords(str_replace('_', ' ', $act['action']))) ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mb-2">
                                        <?= date('F j, Y \a\t g:i a', strtotime($act['created_at'])) ?>
                                    </div>
                                    <?php if($act['details']): ?>
                                        <div class="text-xs text-gray-600 bg-white p-2 rounded border border-gray-200 font-mono">
                                            <?= esc($act['details']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($activeTab === 'team'): ?>
                <!-- FALLBACK FOR TEAM ASSIGNMENTS -->
                <div class="max-w-3xl mx-auto py-4 text-center">
                    <p class="text-gray-500 mb-4">Team and CRM Assignments are currently managed via the <strong>CRM Workspace</strong> module by authorized CRM Managers.</p>
                    <?php if (hasPermission('view_crm')): ?>
                        <a href="/owner/crm/" class="text-primary hover:underline font-medium">Go to CRM Workspace &rarr;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // --- CREDENTIALS API ---
    $('#addCredForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: '/api/owner/clients/credentials.php', method: 'POST', data: $(this).serialize(),
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('Save Securely'); } },
            error: function() { alert('Error.'); $btn.prop('disabled', false).text('Save Securely'); }
        });
    });

    $('.delete-cred-btn').on('click', function() {
        if(!confirm('Delete this credential?')) return;
        $.post('/api/owner/clients/credentials.php', { action: 'delete', id: $(this).data('id'), client_id: <?= $id ?>, csrf_token: '<?= generateCsrfToken() ?>' }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    $('.view-pw-btn').on('click', function() {
        const id = $(this).data('id');
        const $input = $('#pw-' + id);

        if ($input.attr('type') === 'text') {
            $input.attr('type', 'password').val('********');
            return;
        }

        $.post('/api/owner/clients/credentials.php', { action: 'view_password', id: id, client_id: <?= $id ?>, csrf_token: '<?= generateCsrfToken() ?>' }, function(res) {
            if(res.status === 'success') {
                $input.attr('type', 'text').val(res.data.password);
            } else alert(res.message);
        });
    });

    // --- PACKAGES API ---
    $('#addPkgForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');
        $.ajax({
            url: '/api/owner/clients/package.php', method: 'POST', data: $(this).serialize(),
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('Save Package'); } },
            error: function() { alert('Error.'); $btn.prop('disabled', false).text('Save Package'); }
        });
    });

    $('.delete-pkg-btn').on('click', function() {
        if(!confirm('Delete this package?')) return;
        $.post('/api/owner/clients/package.php', { action: 'delete', id: $(this).data('id'), client_id: <?= $id ?>, csrf_token: '<?= generateCsrfToken() ?>' }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    // --- NOTES API ---
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Posting...');
        $.ajax({
            url: '/api/owner/clients/notes.php', method: 'POST', data: $(this).serialize(),
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('Post Note'); } },
            error: function() { alert('Error.'); $btn.prop('disabled', false).text('Post Note'); }
        });
    });

    $('.delete-note-btn').on('click', function() {
        if(!confirm('Delete this note?')) return;
        $.post('/api/owner/clients/notes.php', { action: 'delete', id: $(this).data('id'), client_id: <?= $id ?>, csrf_token: '<?= generateCsrfToken() ?>' }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

    // --- ATTACHMENTS API ---
    $('#addAttForm').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Uploading...');
        const formData = new FormData(this);
        $.ajax({
            url: '/api/owner/clients/attachments.php', method: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) { if(res.status === 'success') location.reload(); else { alert(res.message); $btn.prop('disabled', false).text('Upload File'); } },
            error: function() { alert('Error.'); $btn.prop('disabled', false).text('Upload File'); }
        });
    });

    $('.delete-att-btn').on('click', function() {
        if(!confirm('Delete this attachment permanently?')) return;
        $.post('/api/owner/clients/attachments.php', { action: 'delete', id: $(this).data('id'), client_id: <?= $id ?>, csrf_token: '<?= generateCsrfToken() ?>' }, function(res) {
            if(res.status === 'success') location.reload(); else alert(res.message);
        });
    });

});
</script>

<?php require_once '../../includes/footer.php'; ?>
