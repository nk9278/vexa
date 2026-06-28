# VEXA - PHASE 21 DOCUMENTATION
## CRM WORKSPACE & CLIENT ASSIGNMENT ENGINE

### 1. Overview
Phase 21 introduces the CRM Workspace, bridging the gap between external Clients and the internal Production Team. CRM users in VEXA are specialized internal users who manage specific client portfolios and assign production resources to execute deliverables. This module strictly follows the Digital Marketing Agency workflow architecture.

### 2. Database Adjustments
- `setup_db.php` was safely extended:
  - Added `is_crm` flag (INTEGER DEFAULT 0) to the existing `team_members` table to differentiate CRM users from standard production resources.
  - Created `clients` table: Tracks basic client information and status (`active`, `paused`, `archived`).
  - Created `client_assignments` table: A pivot mapping `clients` to specific CRM users.
  - Created `client_team_assignments` table: A pivot mapping `clients` to specific `team_members` (Production Resources) for executing work.
  - Created `deliverables` placeholder table for Phase 22.

### 3. Onboarding Integrations
- `includes/onboarding.php` was incrementally updated:
  - Added new CRM permissions: `view_crm`, `manage_crm`, `assign_clients`, `assign_team`.
  - Appended these new permissions to the system-generated Owner and Manager roles.
  - Assigned CRM-specific permissions to the default `CRM` role.

### 4. Backend APIs (`/api/owner/crm/`)
- `create.php`: Securely provisions a new team member with the `is_crm` flag set to 1.
- `update.php`: Allows updating profile data for CRM users.
- `assign_client.php`: Manages the dynamic linking/unlinking between a CRM user and the `clients` table.
- `assign_team.php`: Manages assigning standard `team_members` (Production Resources) to specific clients.
- Enforces strict CSRF, Permission checking, and Company Isolation across all endpoints.

### 5. Frontend UI (`/owner/crm/`)
- `index.php`: The CRM Dashboard displaying overview metrics (Total CRMs, Active Clients, Pending Deliverables) and a responsive data grid of CRM users with their respective assigned client count.
- `create.php` / `edit.php`: Tailwind-styled forms to manage CRM basic details, roles, and status.
- `profile.php`: The central CRM hub.
  - Displays the CRM's identity card.
  - **Client Assignment Engine**: Includes an interactive dropdown to map available unassigned clients to the CRM user.
  - Lists currently assigned clients with options to quickly remove assignments.
  - Prepares layout placeholders for performance metrics and upcoming Phase 22 deliverables.

### 6. Security and Quality Control
- **Navigation:** Updated `/owner/includes/sidebar.php` carefully without overwriting existing structure. Replaced the "Clients & CRM" placeholder with the functional `CRM Workspace` route protected by `view_crm`.
- **Validation:** Syntax checks (`php -l`) passed for all new files. File upload handlers were not re-implemented as CRM users utilize the `upload-photo.php` API created in Phase 20 for Team Members.
- **Activity Logging:** `assign_client` and `remove_client` actions are written to the database for auditability.