# VEXA - PHASE 24 DOCUMENTATION
## TASK GENERATION ENGINE & SMART ASSIGNMENT SYSTEM

### 1. Overview
Phase 24 establishes the core Task Generation Engine. Serving as the primary pulse of the application's production workflow, this engine bridges the gap between high-level client deliverables (Phase 23) and granular execution tracking. It supports both automatic task generation directly from monthly plans and manual ad-hoc task creation. The module also introduces a "Smart Assignment" API to evaluate production team workloads and availability.

### 2. Database Modifications
- Updated `setup_db.php` safely:
  - Created `tasks` table: Stores core task metadata including `client_id`, `deliverable_id` (optional), `priority`, `status`, `due_date`, and `assigned_team_id`.
  - Created `task_timeline` table: Provides a strict, chronological audit trail for every status change, assignment, and update made to a task.

### 3. Onboarding Integrations
- Updated `includes/onboarding.php` to provision Task Engine permissions (`view_tasks`, `create_tasks`, `edit_tasks`, `assign_tasks`, `reassign_tasks`, `delete_tasks`).
- Permissions were applied incrementally to Owner, Manager, CRM, and generalized Production Roles (e.g., Graphic Designer, Video Editor) to ensure a functional baseline across the SaaS tenants.

### 4. Backend APIs (`/api/owner/tasks/`)
- `create.php`: API for generating manual/ad-hoc tasks. Validates associations with the CRM and Team modules.
- `update.php`: API for updating details, status, and priorities. Automatically generates a timeline record upon status change.
- `action.php`: Bulk action API supporting mass deletion, cancellation, and team reassignment.
- `generate.php`: **Auto-Generation Engine.** Accepts a `monthly_plan_id` and iterates over pending deliverable items, dynamically inserting corresponding Task records while ensuring duplicates are not created.
- `smart_assign.php`: **Smart Assignment Engine.** Accepts a `client_id` and calculates real-time workloads (number of open tasks) and availability states (`Available`, `Busy`, `On Leave`) for all active production members mapped to the client. This data feeds directly into the UI assignment modals.

### 5. Frontend UI (`/owner/tasks/`)
- `index.php`: The master Task Dashboard. Showcases global KPIs (Total, Pending, Overdue, Completed) and a robust data grid listing tasks with priority badges, status indicators, dynamic assignee labels, and multi-select bulk actions.
- `create.php` & `edit.php`: Clean, standard input interfaces for task metadata. The creation form heavily integrates the Smart Assignment Engine via an AJAX-powered dynamic selection container.
- `view.php`: The granular task workspace. Features a Kanban-aligned status update dropdown, an active assignment card with quick-reassign tooling, and a vertically rendered `task_timeline` to audit user actions.

### 6. Security and Quality Control
- **CSRF Upgrades:** Replaced legacy/invalid `generateCsrfToken()` occurrences globally with the appropriate `getCsrfToken()`.
- **IDOR Protection:** All relationships (`client_id`, `crm_id`, `team_member_id`) are structurally verified against `company_id` before modifications or inserts are committed.
- **Validation:** PHP syntax checks (`php -l`) passed flawlessly.

The platform is now primed to connect Tasks to advanced Approval Workflows and future Cloud File Integrations.