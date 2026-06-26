# VEXA - PHASE 1 DOCUMENTATION
## MASTER FOUNDATION, SYSTEM BLUEPRINT & PROJECT ARCHITECTURE

---

## 1. Project Architecture

### 1.1 Overview
VEXA is a Multi-Tenant SaaS Project Management Software designed primarily for Digital Marketing Agencies, built with a simple, robust, and scalable architecture using Core PHP (PDO), MySQL, Tailwind CSS, and jQuery (AJAX). It does not use any MVC framework or routing engine.

### 1.2 Hierarchy Architecture

**Company Hierarchy**
* **Super Admin**: Master control over the entire SaaS platform. Can manage multiple companies.
* **Company**: Isolated tenant within the system. Data is strictly separated from other companies.

**User Hierarchy (Per Company)**
1. **Company Owner**: Full access to the company's workspace.
2. **Manager**: High-level access to oversee multiple departments, projects, and employees.
3. **CRM / Sales / Relationship Manager**: Handles clients, leads, and revenue tracking.
4. **Employee**: Access limited to assigned tasks, personal attendance, and leave.
5. **Client**: Restricted external access to view project status and specific communications.

**Module Hierarchy**
* **Core Foundation**: Authentication, Multi-Tenant Isolation, Settings.
* **Operations**: Projects, Tasks, CRM, Employees, Clients.
* **Administration**: Roles & Permissions, Attendance, Leave, Payments, Reports.
* **Utilities**: Notifications, Calendar, Analytics, Future integrations (Google Drive, WhatsApp API).

**Navigation Hierarchy**
* Left Sidebar for primary module navigation.
* Top Header for user profile, settings, quick actions, and notifications.

### 1.3 Scalability & Data Isolation Plan
* **Data Isolation**: Every database table related to tenant data will include a `company_id`. All queries MUST explicitly filter by `company_id` using PDO bound parameters.
* **No Framework Dependency**: Written in pure Core PHP 8+ ensuring longevity, independent from framework update cycles, and perfect compatibility with standard shared hosting environments.
* **Performance**: Optimized SQL queries, AJAX-driven seamless interactions, and minimal external dependencies.

---

## 2. Folder Structure

The directory structure is organized into semantic, self-contained module directories without a complex routing layer.

```text
/
├── api/                     # Core AJAX endpoints & internal APIs
├── assets/                  # Public assets
│   ├── css/                 # Tailwind builds and custom CSS
│   ├── js/                  # Global JavaScript and AJAX handlers
│   ├── images/              # Static images
│   ├── icons/               # SVG icons and font icons
│   └── fonts/               # Web fonts
├── attendance/              # Attendance and leave management
├── auth/                    # Authentication (login, logout, password reset)
├── backup/                  # Database and system backup scripts/archives
├── calendar/                # Global calendar view and events
├── clients/                 # Client management and portal entry
├── crm/                     # Leads, pipeline, and sales management
├── cron/                    # Cron jobs and scheduled tasks
├── dashboard/               # Role-specific dashboard views
├── database/                # Database schemas and migration scripts
├── employees/               # Employee records and management
├── google-drive/            # Future Google Drive API integration scripts
├── includes/                # Shared components (header, sidebar, footer, config, db connect)
├── logs/                    # Error and access logs
├── modules/                 # Extensible core business logic classes/functions
├── notifications/           # System and user notifications
├── owner/                   # Company owner specific settings and views
├── payments/                # Invoices, transactions, and payment tracking
├── projects/                # Project management views
├── reports/                 # Static and dynamic report generators
├── roles/                   # Role and permission management
├── settings/                # System and company settings
├── super-admin/             # SaaS Super Admin controls (Manage Companies)
├── tasks/                   # Task creation, assignment, and kanban/list views
└── uploads/                 # User-uploaded files (isolated by company_id internally)
```

---

## 3. Module Structure

### 3.1 Authentication Module (`/auth/`)
* **Purpose**: Handle user login, logout, password resets, and session initialization.
* **Responsibilities**: Verify credentials, establish secure sessions, enforce multi-tenant context (`company_id`).
* **Dependencies**: User Database, Core PHP Sessions, Database Connection.
* **Related Modules**: All modules (requires active session).

### 3.2 Super Admin Module (`/super-admin/`)
* **Purpose**: Manage the overall SaaS platform and multiple tenant companies.
* **Responsibilities**: Create/edit companies, suspend accounts, view global platform analytics.
* **Dependencies**: Authentication.

### 3.3 Dashboard Module (`/dashboard/`)
* **Purpose**: Provide a centralized, role-based overview of relevant metrics and quick actions.
* **Responsibilities**: Aggregate data from Tasks, Projects, CRM, Attendance.
* **Future Expansion**: Customizable widgets.
* **Dependencies**: Analytics, Tasks, Projects, CRM.

### 3.4 CRM Module (`/crm/`)
* **Purpose**: Manage leads, sales pipelines, and client acquisition.
* **Responsibilities**: Track lead status, conversions, communications, and follow-ups.
* **Related Modules**: Clients, Projects.

### 3.5 Projects & Tasks Module (`/projects/`, `/tasks/`)
* **Purpose**: Core operational management of deliverables.
* **Responsibilities**: Project lifecycle, task assignment, status tracking, deadlines, file attachments.
* **Future Expansion**: Gantt charts, advanced dependencies.
* **Related Modules**: Employees, Clients.

### 3.6 Employees & Attendance Module (`/employees/`, `/attendance/`)
* **Purpose**: HR management basics.
* **Responsibilities**: Employee profiles, daily check-in/out, leave requests, and approvals.
* **Future Expansion**: Advanced HR and Payroll integration.
* **Related Modules**: Roles, Reports.

### 3.7 Roles & Permissions Module (`/roles/`)
* **Purpose**: Define granular access control.
* **Responsibilities**: Create custom roles, map permissions to specific UI/actions.
* **Dependencies**: Settings, Auth.

### 3.8 Utilities (Reports, Calendar, Payments, Notifications)
* **Purpose**: Supporting functionalities for daily operations.
* **Responsibilities**: Generate analytics, schedule events, track invoices/revenue, alert users.
* **Future Expansion**: Automation, external payment gateways (Stripe/PayPal).

---

## 4. Page Structure

| Page Name | File Path | Purpose | Who Can Access | Parent Module | Expected Features |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Login** | `/auth/login.php` | User authentication | All | Auth | Login form, CSRF protection |
| **Forgot Password** | `/auth/forgot-password.php` | Password recovery | All | Auth | Email reset link |
| **Super Admin Dashboard**| `/super-admin/index.php` | Global SaaS metrics | Super Admin | Super Admin | Total companies, active users, revenue |
| **Owner Dashboard** | `/dashboard/owner.php` | Company high-level metrics | Owner | Dashboard | Revenue, project health, team attendance |
| **Manager Dashboard** | `/dashboard/manager.php` | Department metrics | Manager | Dashboard | Project progress, pending tasks, team load |
| **Employee Dashboard** | `/dashboard/employee.php`| Personal workspace | Employee | Dashboard | My Tasks, my attendance, quick punch-in |
| **Client List** | `/clients/index.php` | Manage clients | Owner, Manager, CRM| Clients | Data table, add/edit/delete, status |
| **CRM Pipeline** | `/crm/index.php` | Lead tracking | Owner, CRM | CRM | Kanban board for leads, conversion rate |
| **Project List** | `/projects/index.php` | Manage active projects | Owner, Manager, Emp| Projects | List view, status badges, progress bars |
| **Task Board** | `/tasks/index.php` | Task management | Owner, Manager, Emp| Tasks | Kanban/List view, filters, assignees |
| **Employee Directory** | `/employees/index.php` | Manage staff | Owner, Manager | Employees | Profile cards, active/inactive toggle |
| **Attendance Log** | `/attendance/index.php` | Track work hours | Owner, Manager, Emp| Attendance | Monthly view, punch records, leave requests |
| **Role Management** | `/roles/index.php` | Custom role definition | Owner | Roles | Permission matrix, rename roles |
| **Payments & Invoices**| `/payments/index.php` | Financial tracking | Owner, CRM | Payments | Invoice generation, payment status |
| **Company Settings** | `/settings/index.php` | Configure workspace | Owner | Settings | White-labeling, timezone, basic config |

---

## 5. Dashboard Planning

**1. Super Admin Dashboard**
* Total Companies / Tenants
* Platform Revenue (MRR/ARR)
* Active vs Suspended Accounts
* System Resource / Storage Usage
* Global Support Tickets (Future)

**2. Owner Dashboard**
* Total Active Projects & Completion Rate
* Monthly Revenue & Unpaid Invoices
* Employee Attendance Overview (Present/Absent today)
* Overall CRM Conversion Rate
* Recent System Activity / Audit Log

**3. Manager Dashboard**
* Department Project Health (On Track vs Delayed)
* Task Completion Rates by Team Member
* Upcoming Deadlines (Next 7 days)
* Pending Leave Approvals
* Team Workload Distribution

**4. CRM Dashboard**
* Total Leads & Active Opportunities
* Pipeline Value (Potential Revenue)
* Monthly Conversion Target vs Actual
* Follow-ups Scheduled for Today
* Recent Client Onboarding

**5. Employee Dashboard**
* Personal Pending Tasks (Prioritized)
* Today's Schedule & Upcoming Deadlines
* Quick Punch-in/Punch-out Widget
* Personal Leave Balance
* Recent Notifications

---

## 6. Navigation Planning

**Primary Navigation (Left Sidebar)**
* Dashboard (Dynamic based on role)
* CRM (Leads, Pipeline, Clients)
* Projects (All Projects, Templates)
* Tasks (My Tasks, Team Tasks)
* Team (Employees, Attendance, Leave)
* Financials (Payments, Invoices, Expenses)
* Analytics (Reports)
* Calendar
* Settings (Roles, Profile, Company Setup)

**Top Header (Global)**
* Global Search Bar (Search projects, clients, tasks)
* Quick Add Button (+) -> (New Task, New Project, New Lead)
* Notifications Dropdown
* User Profile Dropdown (My Profile, Preferences, Logout)

**Bottom Navigation (Mobile Only)**
* Dashboard, Tasks, Notifications, Menu (Hamburger)

---

## 7. UI Planning

* **Desktop Layout**: Fixed Left Sidebar (collapsible), Fixed Top Header, Scrollable Main Content Area.
* **Mobile Layout**: Hidden sidebar (accessible via hamburger menu), Bottom Navigation bar for core actions.
* **Quick Actions**: Prominent `+` button in the header and Floating Action Button (FAB) on mobile.
* **Cards**: Used for metric summaries, employee profiles, and Kanban items. Soft shadows, rounded corners.
* **Tables**: Clean, borderless rows with sticky headers, pagination, and inline action menus (ellipsis).
* **Forms**: Modern floating labels or clean top-labels. AJAX submission with inline validation errors.
* **Modals**: Used for quick creations (e.g., "Add Task", "Quick Lead") without leaving the current page.
* **Drawer Panels**: Used for deep-dives (e.g., clicking a task opens a right-side drawer to view details, comments, and files).
* **Dark Mode**: Fully supported via Tailwind's `dark:` classes, controlled by user preference in settings.

---

## 8. Design System

* **Colors**:
  * Primary: Deep Indigo (`#4F46E5`) / Tailwind `indigo-600`
  * Secondary: Slate Gray (`#64748B`) / Tailwind `slate-500`
  * Background: Very Light Gray (`#F8FAFC`) / Tailwind `slate-50`
  * Surface (Cards): White (`#FFFFFF`)
  * Text (Main): Dark Slate (`#0F172A`) / Tailwind `slate-900`
* **Status Colors**:
  * Success / Completed: Emerald Green (`#10B981`)
  * Warning / Pending: Amber (`#F59E0B`)
  * Danger / Overdue: Rose Red (`#E11D48`)
  * Info / In Progress: Sky Blue (`#0EA5E9`)
* **Typography**: Primary font - Inter or Roboto (sans-serif), clean hierarchy (H1-H6), readable body text (14px/16px).
* **Components**:
  * **Buttons**: Rounded-md, subtle hover effects, active states. Primary (solid), Secondary (outline), Ghost (text only).
  * **Badges**: Pill-shaped with soft background colors (e.g., `bg-emerald-100 text-emerald-700`).
  * **Progress Bars**: Smooth transitions, rounded edges.
  * **Empty States**: Centered illustrations (SVG) with clear call-to-action buttons.
  * **Loading Screens**: Skeleton loaders for content areas, subtle spinners for button states.
* **Icons**: Feather Icons or Heroicons (SVG format).
* **Charts**: Chart.js customized to match the color palette (clean lines, minimal gridlines).

---

## 9. Analytics Planning

* **Employee Performance**: Measured by tasks completed vs overdue, hours logged (future). Shown in Manager/Owner dashboards.
* **CRM Performance**: Lead conversion rates, average time to close. Shown as funnel charts.
* **Client Progress**: Project completion percentages per client.
* **Company Performance**: Monthly revenue growth, churn rate (if applicable), active vs idle projects.
* **Task Completion**: Burn-down charts, completed vs pending bar charts.
* **Monthly Delivery**: Projects delivered on time vs delayed.
* **UI Integration**:
  * Circular progress indicators on Project Cards.
  * Line/Bar charts on Dashboards.
  * Percentage indicators (e.g., "+5% from last month") next to key metrics.

---

## 10. Permission Planning

* **Core Concept**: Role-Based Access Control (RBAC).
* **Unlimited Custom Roles**: The system will allow the Owner to create roles dynamically (e.g., "Client Success Manager", "Junior Dev").
* **Permission Granularity**: Permissions are defined at the module and action level (e.g., `view_projects`, `create_projects`, `edit_projects`, `delete_projects`).
* **Fixed Permissions List**: The master list of available permissions is hardcoded and cannot be altered, ensuring system integrity. Owners only map these permissions to roles.
* **Role Renaming**: Owners can rename custom roles at any time without affecting the underlying permissions.

---

## 11. Development Standards

* **Coding Standards**: PSR-12 compliant for PHP. Clean, commented, and modular JavaScript. Use strict types where possible in PHP 8+.
* **Security Standards**:
  * All database queries MUST use PDO prepared statements (No raw string concatenation).
  * CSRF tokens on all state-changing forms/AJAX requests.
  * XSS prevention via `htmlspecialchars()` on all user-generated content output.
  * Strict `company_id` verification on every query for multi-tenant data isolation.
* **Performance Standards**:
  * Minified CSS/JS in production.
  * Database indexing on heavily queried columns (`company_id`, `status`, `created_at`).
  * Efficient AJAX loading (only return necessary JSON, not full HTML payloads).
* **Naming Conventions**:
  * Folders: lowercase, hyphenated (e.g., `super-admin`).
  * Files: lowercase, hyphenated (e.g., `forgot-password.php`).
  * PHP Classes/Functions: PascalCase for Classes (`ProjectManager`), camelCase for functions (`getProjectDetails()`).
  * Database: lowercase, snake_case (e.g., `company_settings`, `project_tasks`).
  * Variables: camelCase in JS/PHP (e.g., `$activeProjects`, `userData`).

---

## 12. Future Expansion Plan

The architecture allows seamless integration of future modules without breaking the core:
* **HR & Payroll**: Integrate with Attendance module for automated salary calculation.
* **Inventory**: Add an `/inventory/` module for physical asset tracking.
* **WhatsApp API / Email Automation**: Add to `/utilities/` or `/crm/` for automated follow-ups.
* **AI Assistant**: Integrate AI endpoints in `/api/` for summarizing tasks or drafting emails.
* **Mobile App / REST API**: The `/api/` folder is pre-planned to handle external stateless REST requests using JWT authentication.
* **Client & Vendor Portals**: Dedicated restricted interfaces where external users can log in to view specific project deliverables or invoices.
* **Google Drive API**: Pre-allocated `/google-drive/` folder for seamless document syncing in tasks and projects.

---

## 13. Phase Completion Report

**Phase 1 Objective Achieved**: The complete master foundation, system blueprint, and project architecture have been thoroughly documented according to the VEXA SaaS Project Management Software requirements.

**Validations Complete**:
- [x] No business logic written.
- [x] No CRUD, Auth, or DB tables created.
- [x] Folder and Module structures defined for shared-hosting friendly Core PHP deployment.
- [x] URL routing deliberately excluded; simple URL structure enforced.
- [x] Multi-tenancy isolation and scalability built into the core plan.

**Ready for Phase 2.**
