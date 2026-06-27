# VEXA - PHASE 7 DOCUMENTATION
## MASTER MODULE SPECIFICATION & FEATURE DOCUMENTATION

---

## 1. Module Specifications

This section defines the high-level purpose and business goals for every core module in VEXA.

### Authentication Module
*   **Purpose:** Secure entry point to the system.
*   **Business Goal:** Prevent unauthorized access while providing a frictionless login experience.
*   **Main Functions:** Login, Logout, Forgot Password, Reset Password.
*   **Dependencies:** Users Table, Tenant Isolation Layer.
*   **Access Levels:** All Users.

### CRM & Clients Module
*   **Purpose:** Manage the sales pipeline and maintain active client relationships.
*   **Business Goal:** Increase conversion rates and improve client retention through organized data.
*   **Main Functions:** Lead Tracking, Client Onboarding, Note Taking, Service Assignment.
*   **Dependencies:** Projects, Payments.
*   **Access Levels:** Owner, CRM, Manager.

### Projects & Tasks Module
*   **Purpose:** The operational core of the agency. Tracks deliverables from start to finish.
*   **Business Goal:** Ensure on-time delivery of client work and maintain high quality through approval workflows.
*   **Main Functions:** Project creation, Task Assignment, Kanban boards, File Attachments, Status tracking.
*   **Dependencies:** Clients, Employees, Google Drive (Future).
*   **Access Levels:** Owner, Manager, Employee, CRM.

### HR (Attendance & Leaves) Module
*   **Purpose:** Track employee availability and working hours.
*   **Business Goal:** Ensure accurate capacity planning and simplify payroll preparation.
*   **Main Functions:** Daily punch-in/out, Leave request submission, Leave approval.
*   **Dependencies:** Employees, Calendar.
*   **Access Levels:** All internal users.

### Financials (Payments) Module
*   **Purpose:** Track revenue generated from clients.
*   **Business Goal:** Reduce unpaid invoices and forecast monthly recurring revenue (MRR).
*   **Main Functions:** Invoice generation, Payment logging, Overdue tracking.
*   **Dependencies:** Clients.
*   **Access Levels:** Owner, CRM.

---

## 2. Page Specifications

A detailed breakdown of critical pages to guide frontend development.

### Page: Client List (`/clients/`)
*   **Purpose:** Master view of all active and archived clients.
*   **Entry Point:** Sidebar > Clients.
*   **Exit Point:** Click on a Client Row -> `View Client`. Click '+ Add Client' -> Modal.
*   **Actions:** Add Client, Edit, Archive, Export CSV.
*   **Filters:** Status (Active/Paused), Assigned CRM.
*   **Search:** By Client Name, Company Name, Email.
*   **Pagination:** 20 records per page via AJAX.

### Page: Task Board (`/tasks/`)
*   **Purpose:** Manage day-to-day work execution.
*   **Entry Point:** Sidebar > Tasks.
*   **Exit Point:** Click Task Card -> `Task Details Drawer`.
*   **Actions:** Drag-and-drop status change, Create Task, Re-assign.
*   **Filters:** Assignee, Project, Priority, Due Date.
*   **Search:** By Task Title, ID.

---

## 3. Button Specifications

Behavioral definitions for UI interactions.

### Button: `+ Create Task`
*   **Purpose:** Opens the task creation modal.
*   **Visibility:** Header (Quick Action) & Task Board.
*   **Permission:** `create_task`.
*   **Expected Action:** Opens Modal.
*   **Confirmation Required:** No.

### Button: `Approve Deliverable`
*   **Purpose:** Marks a task as ready for the client.
*   **Visibility:** Task Details Drawer (when status is 'Pending Review').
*   **Permission:** `approve_task`.
*   **Expected Action:** Changes status to 'Approved', logs activity, triggers notification.
*   **Confirmation Required:** Yes ("Are you sure you want to approve this work?").
*   **Success Behavior:** Toast notification ("Task Approved"), UI updates instantly.

### Button: `Delete Client`
*   **Purpose:** Soft-deletes a client record.
*   **Visibility:** Client List (Row Action), View Client Page.
*   **Permission:** `delete_client`.
*   **Expected Action:** Triggers soft-delete, removes from active views.
*   **Confirmation Required:** Yes, requires typing the client's name to confirm.
*   **Success Behavior:** Toast notification, redirect to Client List if on View page.

---

## 4. Form Specifications

### Form: Create Lead / Client
*   **Input Fields:**
    *   Company Name (Text, Required)
    *   Contact Person (Text, Required)
    *   Email (Email, Required, Valid Format)
    *   Phone (Text, Optional)
    *   Category (Dropdown: Real Estate, E-commerce, etc., Optional)
    *   Assigned CRM (Dropdown of Users, Required)
*   **Validation:** Email uniqueness check within the Tenant.
*   **Future API:** Webhook ingestion from Meta Lead Ads.

### Form: Create Task
*   **Input Fields:**
    *   Task Title (Text, Required)
    *   Project (Dropdown, Required)
    *   Assignees (Multi-select Dropdown, Required)
    *   Priority (Dropdown: Low, Medium, High, Required)
    *   Due Date (Date Picker, Required)
    *   Description (Rich Text, Optional)
    *   Attachments (File Upload drag-and-drop, Optional)
*   **Validation:** Due Date cannot be in the past.

---

## 5. Table Specifications

### Table: Employee List
*   **Columns:** Avatar, Name, Department, Role, Current Status (Online/Offline), Actions.
*   **Searchable:** Name, Role.
*   **Sortable:** Name, Department.
*   **Filters:** Department, Status.
*   **Bulk Actions:** None.
*   **Clickable Rows:** Yes, redirects to Employee Profile.

### Table: Invoice List
*   **Columns:** Invoice ID, Client, Issue Date, Due Date, Amount, Status.
*   **Status Colors:** Paid (Emerald), Pending (Amber), Overdue (Rose).
*   **Searchable:** Invoice ID, Client Name.
*   **Sortable:** Issue Date, Amount.
*   **Row Actions:** Download PDF, Record Payment, Void.

---

## 6. Dashboard Specifications

### Dashboard: Manager
*   **KPI Cards:** Team Capacity (%), Tasks Pending Review (Count), Overdue Tasks (Count).
*   **Charts:** Weekly Task Completion (Bar Chart), Department Workload (Donut Chart).
*   **Pending Work:** List of Leave Requests requiring approval. List of Tasks in 'Pending Review'.
*   **Alerts:** "3 Projects are at risk of missing deadlines."

### Dashboard: Employee
*   **KPI Cards:** Tasks Due Today, Total Hours Logged This Week, Leave Balance.
*   **Quick Actions:** Big "Punch In" / "Punch Out" toggle button.
*   **Pending Work:** Prioritized list of "My Tasks" (Working -> Pending).
*   **Performance Indicators:** Personal Task Completion %.

---

## 7. Workflow Specifications

### Workflow: Task Execution & Approval
*   **Start:** Manager creates a task and assigns it to an Employee. (Notification triggered).
*   **Middle:** Employee accepts task. Status changes to 'Working'. Employee uploads files and adds comments.
*   **Approval:** Employee clicks 'Submit for Review'. Status -> 'Pending Review'. Manager notified.
*   **Revision (If needed):** Manager clicks 'Reject'. Status -> 'Revisions Required'. Employee notified with feedback.
*   **Completion:** Manager clicks 'Approve'. Status -> 'Approved'.
*   **Archive:** Project closes, all associated tasks are archived automatically.
*   **Error Handling:** If an Employee tries to 'Submit' without attaching files (if required by task type), form validation prevents submission.

---

## 8. Validation Rules

Global validation standards across all modules.
*   **Passwords:** Minimum 8 characters, 1 uppercase, 1 number.
*   **Emails:** Must conform to standard RFC format. Checked for uniqueness per tenant where applicable (e.g., Users).
*   **Dates:** "End Date" / "Due Date" must always be logically greater than or equal to "Start Date".
*   **File Uploads:** Max size 50MB (configurable by Super Admin). Allowed MIME types: Images (png, jpg, webp), Documents (pdf, docx, xlsx), Videos (mp4, compressed).
*   **Sanitization:** All text inputs run through `htmlspecialchars` to prevent XSS.

---

## 9. Notification Rules

Triggers for system alerts.
*   **Task Assigned:** In-app (Push) to Assignee.
*   **Task Status Changed to 'Pending Review':** In-app (Push) to Task Creator/Manager.
*   **Leave Requested:** Email & In-app to Employee's Manager.
*   **Invoice Overdue:** Email to CRM Manager & Owner.
*   **Mentioned in Comment (`@username`):** In-app (Push) to Mentioned User.

---

## 10. Future Expansion

The specifications are written to easily absorb future features:
*   **REST API:** Form inputs and validation rules map directly to future JSON payload requirements.
*   **Google Drive:** File upload fields will eventually feature a "Select from Google Drive" picker alongside local uploads.
*   **Client Portal:** Client Views and Invoice tables are designed so they can be securely exposed to a restricted "Client Viewer" role in the future.

---

## 11. Verification Report

**Cross-Check Validation:**
- [x] All modules from Phase 6 mapped with business goals.
- [x] Critical pages defined with entry/exit points and actions.
- [x] Button behaviors, confirmations, and permissions documented.
- [x] Form inputs, validations, and table structures detailed.
- [x] Approval workflows strictly defined to prevent deadlocks.
- [x] No PHP, HTML, CSS, or SQL code generated.

**Phase 7 Objective Achieved:** The Master Module Specification and Feature Documentation is complete. Every screen, form, button, and workflow is conceptually locked, serving as the definitive blueprint for software development.