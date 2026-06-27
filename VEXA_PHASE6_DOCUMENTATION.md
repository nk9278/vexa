# VEXA - PHASE 6 DOCUMENTATION
## MASTER PAGE INVENTORY, SCREEN MAPPING & NAVIGATION STRUCTURE

---

## 1. Module Wise Page List & 2. Screen Mapping

This section outlines every screen mapped within the application, categorized by functional module.

### Authentication Module
*   `Login`: Entry point. (All Roles)
*   `Forgot Password`: Reset request. (All Roles)
*   `Reset Password`: Create new password. (All Roles)
*   `MFA Verify (Future)`: Second-step verification. (All Roles)

### Super Admin Module
*   `SaaS Dashboard`: Global metrics (MRR, Tenants).
*   `Companies List`: Manage all SaaS tenants.
*   `View Company`: Details, active subscription, resource usage.
*   `Subscription Plans`: Manage SaaS tiers.
*   `Global Settings`: White-labeling, SMTP, API limits.

### Dashboard Module
*   `Owner Dashboard`: Revenue, company health, active projects.
*   `Manager Dashboard`: Department load, overdue tasks.
*   `CRM Dashboard`: Lead funnel, pipeline value.
*   `Employee Dashboard`: My tasks, attendance punch.

### Users & Roles Module (Tenant Level)
*   `Employee List`: Directory of all staff.
*   `View/Edit Employee`: Personal details, assigned department, attendance history.
*   `Roles List`: Custom access templates.
*   `Role Permissions`: Matrix of checkboxes for granular access control.
*   `Departments`: Group management.

### CRM & Clients Module
*   `Leads Board`: Kanban view of potential clients.
*   `Client List`: Data table of active clients.
*   `View Client (Master)`: Tabs for Timeline, Documents, Credentials, Deliverables, Notes, Payments, Activity.
*   `Client Renewal`: Subscription/service renewal management.

### Projects & Tasks Module
*   `Project List`: Active/Archived projects.
*   `View Project`: High-level progress, milestone tracker.
*   `Task Board`: Kanban / List view of all tasks.
*   `Task Details Drawer`: Slide-out panel for task description, assignee, status, comments, uploads.
*   `Task Revisions`: History of rejected/re-opened tasks.

### HR & Operations Module
*   `Attendance Log`: Monthly grid of punch-ins/outs.
*   `My Leave Requests`: Employee view to apply for leave.
*   `Leave Approvals`: Manager/Owner view to approve/reject.
*   `Holidays List`: Company calendar dates.

### Financials Module
*   `Invoices List`: All generated invoices and statuses.
*   `View Invoice`: Printable/Emailable view.
*   `Payments Received`: Log of all transactions.

### Utilities & Logs
*   `Notifications Center`: Full history of alerts.
*   `Calendar`: Global view of deadlines and holidays.
*   `Activity Logs`: Immutable audit trail.
*   `Reports Center`: Exportable data views.
*   `Settings`: Tenant-level configuration (theme, business hours).

---

## 3. Navigation Tree & 4. Parent Child Relationships

**Dashboard (Parent)**
  *   Owner / Manager / CRM / Employee (Children based on role)

**CRM (Parent)**
  *   Leads -> Convert to Client (Flow)
  *   Clients -> `View Client` -> Client Projects -> Client Invoices (Children)

**Production (Parent)**
  *   Projects -> `View Project` -> Project Tasks -> Deliverables
  *   Tasks -> `Task Details` -> Uploads -> Approvals

**Team (Parent)**
  *   Employees -> `View Employee`
  *   Attendance
  *   Leave -> Approvals

**Finance (Parent)**
  *   Invoices -> Payments
  *   Expenses

**Administration (Parent)**
  *   Roles & Permissions
  *   Activity Logs
  *   Settings

*Rule of thumb:* Avoid dead-end pages. A `View Client` page must always have a breadcrumb or back button returning to the `Client List`.

---

## 5. Access Matrix

A simplified mapping of Create/Read/Update/Delete (CRUD) & Approval rights by default roles.

| Page / Module | Super Admin | Owner | Manager | CRM | Employee | Client (Future Portal)|
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **SaaS Companies**| CRUD | None | None | None | None | None |
| **Tenant Settings** | None | CRUD | Read | None | None | None |
| **Roles & Perms** | None | CRUD | Read | None | None | None |
| **Employees** | None | CRUD | CRU | Read | Read (Self) | None |
| **Clients & Leads** | None | CRUD | Read | CRUD | None | Read (Self) |
| **Projects** | None | CRUD | CRUD | CRU | Read | Read (Self) |
| **Tasks** | None | CRUD | CRUD | Read | CRU | None |
| **Leave Approvals** | None | Approve | Approve | None | Apply Only| None |
| **Invoices/Payments**| None | CRUD | Read | CRUD | None | Read (Self) |

---

## 6. Quick Actions

Global Quick Actions (+) located in the header for instant access:
*   `+ Create Task` (Opens Modal)
*   `+ Add Lead/Client` (Opens Modal)
*   `+ New Project` (Redirects to Create Page)
*   `Punch In/Out` (Button on Employee Dashboard)
*   `Quick Upload` (Inside Task Drawer)

---

## 7. Global Search Planning

The header search bar queries multiple domains simultaneously:
*   **Search "John"** -> Returns Client (John Doe), Employee (John Smith), Task ("Call John").
*   **Search "#INV-102"** -> Returns Invoice 102.
*   **Search domains:** Clients, Employees, Projects, Tasks, Invoices, Leads.

---

## 8. Filter Planning

Filters available on major list/table pages:
*   **Tasks:** Status, Priority, Assignee, Project, Due Date.
*   **Clients:** Status (Active/Paused), Category, Assigned CRM, Joined Date.
*   **Projects:** Status, Client, Deadline Month.
*   **Attendance:** Employee, Department, Month, Year.
*   **Invoices:** Status (Paid/Overdue), Client, Month.

---

## 9. Bulk Action Planning

Available on data tables via multi-select checkboxes:
*   **Tasks:** Bulk Delete, Bulk Assign User, Bulk Change Status (e.g., Mark all 'Completed').
*   **Leads:** Bulk Delete, Bulk Assign CRM.
*   **Invoices:** Bulk Export (CSV/PDF).
*   **Notifications:** Mark All as Read.

---

## 10. Desktop Navigation

*   **Fixed Sidebar:** Primary modules (Dashboard, CRM, Projects, Team, Finance, Settings). Collapsible to icons.
*   **Fixed Header:** Global Search, Quick Action (+), Notification Bell, Profile Dropdown.
*   **Page Header:** Breadcrumbs (e.g., `CRM > Clients > John Doe`), H1 Title, Right-aligned Primary Actions (e.g., "Edit Client").
*   **Page Content:** Standardized Cards, Data Tables with sticky headers.

---

## 11. Mobile Navigation

*   **Bottom Navigation Bar:** Dashboard, Tasks, Notifications, Menu (Hamburger).
*   **Drawer Menu:** Slides from the left containing the full list of modules (replaces Sidebar).
*   **Sticky Header:** Logo, Search Icon, Profile Avatar.
*   **Floating Action Button (FAB):** Bottom right corner for context-aware quick actions (e.g., "+" on Task List adds a task).
*   **Swipe Actions:** Swipe left on a task to reveal "Complete" or "Delete" buttons.

---

## 12. Future Reserved Screens

The navigation architecture leaves room for:
*   `HR & Payroll`: Appends to the "Team" navigation group.
*   `Inventory`: Appends to the "Administration" navigation group.
*   `Client Portal Dashboard`: Separate lightweight interface for external clients.
*   `Vendor/Freelancer Portal`: Restricted interface for external contractors viewing specific assigned tasks.
*   `AI Assistant Tab`: A persistent chat interface sliding from the right sidebar.

---

## 13. Verification Report

**Cross-Check Validation:**
- [x] All Phase 1, 2, 4 modules mapped to physical screens.
- [x] Navigation hierarchy prevents dead-ends.
- [x] Matrix logically separates Super Admin from Tenant roles.
- [x] Mobile UX defined separately from Desktop UX.
- [x] Quick actions and global search scoped.
- [x] No PHP, HTML, CSS, or SQL code generated.

**Phase 6 Objective Achieved:** The Master Page Inventory and Navigation Structure is locked. The UI/UX mapping is perfectly aligned with the database architecture and ready for development scoping.