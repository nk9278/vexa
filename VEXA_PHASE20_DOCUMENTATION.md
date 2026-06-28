# VEXA - PHASE 20 DOCUMENTATION
## TEAM MANAGEMENT ENGINE

### 1. Overview
In accordance with the Strategic Architecture Pivot (Pre-Phase 20), Phase 20 successfully introduces the **Team Management Engine**. Unlike traditional HR modules, this system treats team members as *Production Resources* (e.g., Content Writers, Graphic Designers, Video Editors) tailored for a Digital Marketing Agency workflow.

### 2. Database Adjustments
- Updated `setup_db.php` to include:
  - `team_members`: Stores core profile information, assigned roles, departments, employment types, and availability status (`Available`, `Busy`, `On Leave`, `Offline`).
  - `skills`: A dynamic dictionary of production skills specific to the company.
  - `team_member_skills`: Pivot table mapping resources to their specific competencies (e.g., Photoshop, React, SEO).

### 3. Onboarding & Provisioning Integrations
- Safely extended `includes/onboarding.php` to ensure backward compatibility while provisioning new companies.
- **New Default Roles:** Automatically generates roles such as "Content Writer", "Graphic Designer", "Video Editor", "SEO Executive", and "Ads Manager" for new tenants.
- **New Permissions:** `view_team`, `create_team`, `edit_team`, `delete_team`, `manage_skills`. These are intelligently mapped to the default `Owner` and `Manager` roles upon company creation.

### 4. Backend APIs (`/api/owner/team/`)
- `create.php`: Securely adds a new team member, enforcing unique emails per tenant workspace, and mapping selected skills.
- `update.php`: Allows modification of profile data, role assignments, and availability statuses.
- `action.php`: Robust bulk action handler supporting `archive`, `restore`, `delete` (soft delete), `activate`, and `deactivate`.
- `skills.php`: Micro-API for the rapid creation and deletion of custom company skills.
- `upload-photo.php`: Secure file upload handler specifically for member avatars, restricting valid extensions and preventing execution.

### 5. Frontend UI (`/owner/team/`)
- `index.php`: The primary Team Dashboard. Features real-time stat cards (Total, Available, Busy, Inactive) and a highly responsive data table with advanced filtering (by Status, Role) and AJAX bulk actions.
- `create.php` & `edit.php`: Clean, Tailwind-styled multi-section forms capturing Basic Info, Roles & Assignment, Status, and Production Skills.
- `profile.php`: A modern "Identity Card" view showcasing the member's profile, availability badge, active skills, a placeholder section for Phase 21/22 workloads (Active Clients, Pending Tasks), and an active system timeline logging their actions.
- `skills.php`: A streamlined interface to define custom agency skills.
- **Navigation:** Safely incrementally added "Team Management" to `owner/includes/sidebar.php` leveraging existing active-state URL matching and permission checks.

### 6. Security and Verification
- **Company Isolation:** Strictly enforced. Every query across the API includes `WHERE company_id = ?`.
- **Syntax and Validation:** All newly added PHP scripts have successfully passed `php -l` linting.
- **Permissions:** Full adherence to the Phase 19 Role Engine (`requirePermission()` and `hasPermission()`).
- No generic HR logic (payroll, attendance, recruitment) was created, faithfully adhering to the architectural directives.

The foundation is now solidly prepared for Phase 21: Client & CRM Workflow.