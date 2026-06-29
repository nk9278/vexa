<?php
// includes/onboarding.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) die('Direct access denied.');

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/logger.php';

/**
 * Company Provisioning Engine
 * Automates the creation of the workspace structure, owner account, default roles,
 * permissions, departments, and master data when a new company is created.
 */
function initializeCompanyWorkspace($companyId, $ownerData) {
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        // 1. Create Default Permissions (Global if they don't exist yet)
        $defaultPermissions = [
            ['dashboard', 'view_dashboard'],
            ['team', 'manage_team'],
            ['team', 'approve_leaves'],
            ['team', 'view_team'],
            ['team', 'create_team'],
            ['team', 'edit_team'],
            ['team', 'delete_team'],
            ['team', 'manage_skills'],
            ['crm', 'view_crm'],
            ['crm', 'manage_crm'],
            ['crm', 'assign_clients'],
            ['crm', 'assign_team'],
            ['crm', 'manage_clients'],
            ['client', 'view_client'],
            ['client', 'create_client'],
            ['client', 'edit_client'],
            ['client', 'delete_client'],
            ['client', 'view_credentials'],
            ['client', 'edit_credentials'],
            ['deliverables', 'view_deliverables'],
            ['deliverables', 'create_deliverables'],
            ['deliverables', 'edit_deliverables'],
            ['deliverables', 'delete_deliverables'],
            ['deliverables', 'manage_deliverable_types'],
            ['tasks', 'view_tasks'],
            ['tasks', 'create_tasks'],
            ['tasks', 'edit_tasks'],
            ['tasks', 'assign_tasks'],
            ['tasks', 'reassign_tasks'],
            ['tasks', 'delete_tasks'],
            ['projects', 'manage_projects'],
            ['finance', 'manage_finance'],
            ['settings', 'manage_settings']
        ];

        $permIds = [];
        foreach ($defaultPermissions as $permArr) {
            $module = $permArr[0];
            $perm = $permArr[1];
            $stmt = $db->prepare("SELECT id FROM permissions WHERE permission_key = ?");
            $stmt->execute([$perm]);
            $res = $stmt->fetch();
            if ($res) {
                $permIds[$perm] = $res['id'];
            } else {
                $stmt = $db->prepare("INSERT INTO permissions (module, permission_key, description) VALUES (?, ?, ?)");
                $stmt->execute([$module, $perm, ucwords(str_replace('_', ' ', $perm))]);
                $permIds[$perm] = $db->lastInsertId();
            }
        }

        // 2. Create Default Roles
        $allPerms = array_map(function($p) { return $p[1]; }, $defaultPermissions);

        $defaultRoles = [
            'Owner' => $allPerms, // Owner gets everything
            'Manager' => ['view_dashboard', 'manage_team', 'view_team', 'create_team', 'edit_team', 'manage_skills', 'view_crm', 'manage_crm', 'assign_clients', 'assign_team', 'view_client', 'create_client', 'edit_client', 'view_credentials', 'view_deliverables', 'create_deliverables', 'edit_deliverables', 'delete_deliverables', 'manage_deliverable_types', 'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks', 'reassign_tasks', 'delete_tasks', 'manage_projects'],
            'CRM' => ['view_dashboard', 'view_crm', 'manage_crm', 'assign_clients', 'assign_team', 'view_client', 'create_client', 'edit_client', 'view_credentials', 'edit_credentials', 'manage_clients', 'view_deliverables', 'create_deliverables', 'edit_deliverables', 'delete_deliverables', 'view_tasks', 'create_tasks', 'edit_tasks', 'assign_tasks', 'reassign_tasks', 'manage_projects'],
            'Graphic Designer' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Video Editor' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Content Writer' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Photographer' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'SEO Executive' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Ads Manager' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Web Developer' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'App Developer' => ['view_dashboard', 'view_tasks', 'edit_tasks'],
            'Account Executive' => ['view_dashboard', 'manage_finance'],
            'Reception' => ['view_dashboard'],
            'Intern' => ['view_dashboard']
        ];

        $ownerRoleId = null;

        foreach ($defaultRoles as $roleName => $perms) {
            $isSystem = ($roleName === 'Owner') ? 1 : 0;
            $stmt = $db->prepare("INSERT INTO roles (company_id, role_name, display_name, is_system) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $roleName, $roleName, $isSystem]);
            $roleId = $db->lastInsertId();

            if ($roleName === 'Owner') $ownerRoleId = $roleId;

            // Map permissions to role
            foreach ($perms as $permKey) {
                if (isset($permIds[$permKey])) {
                    $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$roleId, $permIds[$permKey]]);
                }
            }
        }

        // 3. Create Default Departments
        $defaultDepartments = [
            'Management', 'Sales', 'CRM', 'Design', 'Video', 'Content',
            'Photography', 'SEO', 'Development', 'Accounts', 'Administration'
        ];

        foreach ($defaultDepartments as $deptName) {
            $stmt = $db->prepare("INSERT INTO departments (company_id, department_name) VALUES (?, ?)");
            $stmt->execute([$companyId, $deptName]);
        }

        // 4. Create Master Data
        $masterData = [
            ['Task Priority', 'Low', 'info'], ['Task Priority', 'Medium', 'warning'], ['Task Priority', 'High', 'danger'],
            ['Task Status', 'Pending', 'warning'], ['Task Status', 'Working', 'info'], ['Task Status', 'Completed', 'success'],
            ['Project Status', 'Active', 'success'], ['Project Status', 'On Hold', 'warning'], ['Project Status', 'Completed', 'success'],
            ['Client Category', 'E-commerce', 'neutral'], ['Client Category', 'Real Estate', 'neutral'], ['Client Category', 'Healthcare', 'neutral'],
            ['Business Category', 'B2B', 'neutral'], ['Business Category', 'B2C', 'neutral'],
            ['Leave Types', 'Sick Leave', 'danger'], ['Leave Types', 'Casual Leave', 'info'], ['Leave Types', 'Paid Time Off', 'success'],
            ['Payment Status', 'Paid', 'success'], ['Payment Status', 'Pending', 'warning'], ['Payment Status', 'Overdue', 'danger']
        ];

        foreach ($masterData as $md) {
            $stmt = $db->prepare("INSERT INTO master_data (company_id, category, item_value, color_badge) VALUES (?, ?, ?, ?)");
            $stmt->execute([$companyId, $md[0], $md[1], $md[2]]);
        }

        // 5. Create Owner Account
        $hashedPassword = hashPassword($ownerData['password']);
        $stmt = $db->prepare("
            INSERT INTO users (company_id, role_id, first_name, last_name, email, mobile, password, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");

        // Split name for Owner
        $nameParts = explode(' ', trim($ownerData['owner_name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $stmt->execute([
            $companyId,
            $ownerRoleId,
            $firstName,
            $lastName,
            $ownerData['owner_email'],
            $ownerData['owner_mobile'] ?? null,
            $hashedPassword
        ]);
        $ownerUserId = $db->lastInsertId();

        // 6. Create Default Company Settings
        $defaultSettings = [
            'theme_primary_color' => '#4F46E5',
            'timezone' => 'UTC',
            'language' => 'en',
            'currency' => 'USD',
            'business_hours_start' => '09:00',
            'business_hours_end' => '17:00',
            'working_days' => '1,2,3,4,5' // Mon-Fri
        ];

        foreach ($defaultSettings as $key => $val) {
            $stmt = $db->prepare("INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?)");
            $stmt->execute([$companyId, $key, $val]);
        }

        // 7. Create Physical Storage Folders
        $baseCompanyDir = UPLOADS_PATH . '/' . $companyId;
        $folders = ['documents', 'graphics', 'videos', 'photos', 'temp', 'reports', 'backup'];

        foreach ($folders as $folder) {
            $dir = $baseCompanyDir . '/' . $folder;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                // Secure empty folders for git tracking and web access prevention
                file_put_contents($dir . '/.gitkeep', '');
                file_put_contents($dir . '/index.html', ''); // Prevent directory listing
            }
        }

        $db->commit();

        activityLog('workspace_initialized', 'company', $companyId, [], [], $companyId, $ownerUserId);

        return ['success' => true, 'owner_id' => $ownerUserId];

    } catch (Exception $e) {
        if (isset($db)) $db->rollBack();
        writeSysLog('error', "Onboarding Engine Failed for Company ID {$companyId}: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
