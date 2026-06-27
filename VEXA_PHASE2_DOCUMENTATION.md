# VEXA - PHASE 2 DOCUMENTATION
## DATABASE ARCHITECTURE, MODULE RELATIONSHIP & SYSTEM BLUEPRINT

---

## 1. Entity Planning

This section identifies every major entity within the software, defining their core purpose, relationships, owner module, and future expansion paths.

| Entity | Purpose | Relationship | Owner Module | Future Expansion |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Master user managing SaaS instances. | 1:N with Companies. | Core System | Multi-tiered SaaS admin roles. |
| **Company (Tenant)** | Top-level container for all tenant data. | 1:N with everything else (isolated). | Super Admin | White-label domains, custom SMTP. |
| **User** | Represents all staff, managers, and owners. | Belongs to 1 Company, 1:N with Roles/Logs/Tasks. | Authentication / HR | Multi-factor Auth (MFA), SSO. |
| **Role** | Custom permission templates. | 1:N with Users. | Roles & Permissions | Modular permission sets. |
| **Permission** | Specific granular access rights. | N:N with Roles. | Roles & Permissions | Dynamic UI rendering based on ACL. |
| **Client** | External business/individual served by the company. | 1:N with Projects, Tasks, Drive Folders. | CRM / Clients | Client self-service portal access. |
| **Lead** | Potential client in the pipeline. | Converts to Client. | CRM | API integrations with lead sources (e.g., Meta, Web). |
| **Department** | Logical grouping of employees (e.g., SEO, Design). | 1:N with Users, Projects. | HR / Operations | Inter-departmental project sharing. |
| **Project** | High-level container for client deliverables. | Belongs to Client. 1:N with Tasks. | Projects | Cross-client template sharing. |
| **Service** | Standardized offerings (e.g., "SEO Package"). | Belongs to Company. Linked to Projects/Leads. | Sales / CRM | Subscription-based auto-billing. |
| **Task / Deliverable** | Atomic unit of work assigned to users. | Belongs to Project. Assigned to User. | Tasks | Sub-tasks, dependencies (Gantt). |
| **Media/Upload** | Files attached to tasks, projects, or clients. | Linked polymorphically to Task/Project. | File Management | Direct cloud storage syncing (AWS S3). |
| **Google Drive Folder** | External synced storage container. | Mapped to Client/Project/Month. | Google Drive Sync | Auto-creation of specific subfolders. |
| **Attendance Record** | Daily punch-in/out timestamps. | Belongs to User. | Attendance | Geofencing, IP restriction. |
| **Leave Request** | Employee request for time off. | Belongs to User. Approved by Manager/Owner. | Attendance / Leave | Multi-level approval chains. |
| **Holiday** | Company-wide non-working days. | Belongs to Company. | Calendar / Admin | Import localized bank holidays. |
| **Notification** | System alerts pushed to users. | Belongs to User. | Notifications | Push notifications, SMS integration. |
| **Payment / Invoice** | Financial transaction records. | Belongs to Client/Company. | Payments | Automated late payment reminders. |
| **Subscription** | SaaS recurring billing details for the Tenant. | Belongs to Company. | Super Admin | Plan upgrades/downgrades, overage tracking. |
| **Activity Log** | Immutable audit trail of system events. | Belongs to Company. Linked to User. | Core System | Export to external SIEM/compliance tools. |
| **Dashboard Widget** | Modular UI components for dashboards. | Associated with Roles/Users. | Dashboard | User-customizable drag-and-drop layouts. |

---

## 2. Module Relationships

The core architecture is built upon a highly relational model where data flows top-down from the Company and laterally across functional modules.

**Core Top-Down Hierarchy:**
```text
Company (Tenant Container)
 ├── Owner (Full Access)
 │    └── System Settings, Roles, Permissions
 ├── Manager (Department Level)
 │    └── Employee Management, Resource Allocation
 ├── CRM / Sales
 │    └── Lead Pipeline, Client Onboarding, Revenue Tracking
 ├── Operations / Production
 │    └── Projects, Services, Tasks, Deliverables
 └── Utilities
      └── Activity Logs, Reports, Notifications, Calendar
```

**Lateral Module Communication:**
*   **Auth Module ↔ All Modules:** Injects the active `company_id` and `user_id` into the session to scope all subsequent database queries.
*   **CRM ↔ Projects:** Once a Lead converts to a Client, the CRM triggers the creation of the initial onboarding Project.
*   **Projects ↔ Tasks:** Projects distribute budgets and timelines into actionable Tasks.
*   **Tasks ↔ Employees:** Tasks are assigned to specific Employees, integrating with the Calendar for deadlines and Attendance for availability.
*   **Tasks ↔ Google Drive / File Manager:** Task completion often requires file uploads, triggering Google Drive folder mapping and syncing.
*   **Tasks ↔ Notifications:** Status changes (Review, Approved, Rejected) trigger targeted alerts to CRM, Managers, or Clients.
*   **Attendance ↔ Analytics:** Employee punch times feed into capacity planning and performance scoring algorithms.

---

## 3. Workflow Diagrams

### 3.1 Client Lifecycle Workflow
```text
[Lead Captured] -> [Lead Nurturing (CRM)] -> [Deal Won]
                                                │
                                                ▼
[Client Created] <- [Service/Package Selected] <- [Contract Signed]
      │
      ▼
[Initial Project & Monthly Deliverables Setup]
      │
      ▼
[Task Assignment (To Employees)] -> [Production Phase (Working)]
                                                │
                                                ▼
[Internal Review (Manager/CRM)] <- [Revisions Requested]
      │
      ▼
[Approval (Client/CRM)] -> [Delivery / Drive Syncing]
      │
      ▼
[Monthly Renewal/Invoicing] -> [Project Archive (End of Term)]
```

### 3.2 Task & Deliverable Workflow
```text
[Task Created] -> [Assigned to Employee] -> [Accepted]
                                               │
                                               ▼
                                          [In Progress / Working]
                                               │
               ┌───────────────────────────────┴───────────────────────────────┐
               ▼                                                               ▼
[Review Requested]                                                  [Blocker / Issue Raised]
               │                                                               │
               ▼                                                               ▼
[Manager / CRM Review] ── (Rejected) ──> [Revisions Required] <── (Resolved) ──┘
               │
           (Approved)
               │
               ▼
[Sent to Client (Optional)] ── (Rejected) ──> [Revisions Required]
               │
           (Approved)
               │
               ▼
[Delivered] -> [Completed] -> [Archived]
```

### 3.3 Approval Workflow (Content/Creative Pipeline)
```text
[Employee Finishes Work]
          ↓
[Manager Reviews Technical Quality] -> (Rejects to Employee)
          ↓ (Approves)
[CRM Reviews Client Alignment] -> (Rejects to Manager/Employee)
          ↓ (Approves)
[Owner Review (Optional/High Value)]
          ↓ (Approves)
[Client Review (External Portal)] -> (Rejects with Feedback)
          ↓ (Approves)
[Posting / Final Delivery]
          ↓
[Task Marked Completed]
```

---

## 4. Dashboard Data Flow

Dashboards aggregate data dynamically from multiple underlying modules based on the active user's Role.

### Data Sources per Persona

*   **Super Admin:**
    *   *KPIs:* Total MRR, Active Companies, System Uptime, Storage Load.
    *   *Charts:* Revenue growth over 12 months, Tenant acquisition trend.
    *   *Tables:* List of top 10 companies by resource usage.

*   **Company Owner:**
    *   *KPIs:* Total Revenue, Active Projects, Department Efficiency (%), Current Headcount.
    *   *Charts:* Monthly Delivery Percentage (vs target), Revenue Pipeline.
    *   *Alerts:* Overdue high-value projects, critical staff absences.
    *   *Quick Actions:* Add Client, Quick Invoice, Broadcast Message.

*   **Manager:**
    *   *KPIs:* Team capacity (hours booked vs available), Tasks Pending Review, Project Health.
    *   *Tables:* Team member current status (Working on X, Absent, Idle).
    *   *Alerts:* Tasks missing deadlines, bottlenecks in production.

*   **CRM / Sales:**
    *   *KPIs:* Pipeline Value, Leads Converted, Renewal Rate.
    *   *Charts:* Lead Funnel, Monthly Sales vs Target.
    *   *Recent Activity:* Client interactions, upcoming follow-ups.

*   **Employee:**
    *   *KPIs:* My Tasks Today, Completion %, Attendance Hours Logged.
    *   *Tables:* To-Do List (ordered by priority/deadline).
    *   *Quick Actions:* Punch In/Out, Quick Time Log, Request Leave.

---

## 5. Notification Flow

The system pushes real-time alerts based on specific entity lifecycle events.

| Trigger Event | Recipients | Notification Type | Module Source |
| :--- | :--- | :--- | :--- |
| **Task Assigned** | Assignee | In-app, Email | Tasks |
| **Task Status Change (Review)**| Manager, CRM | In-app | Tasks |
| **Task Approved/Rejected**| Assignee, Manager | In-app, Email | Tasks |
| **Project Deadline Approaching**| Assignee, Manager | In-app, Email (48h prior)| Projects |
| **Client Payment Overdue** | Owner, CRM | In-app, SMS (Future) | Payments |
| **Leave Request Submitted**| Manager, Owner | In-app | Attendance |
| **Leave Request Decided** | Employee | In-app, Email | Attendance |
| **New File Uploaded** | Followers of Task | In-app | File Manager |
| **User Mentioned (@Name)** | Mentioned User | In-app | Comments/Activity |
| **Contract Renewal Date** | CRM | In-app, Reminder | Clients |

---

## 6. Analytics Flow

Analytics aggregates transactional data into actionable insights and percentages.

*   **Employee Performance:** Calculated via `(Tasks Completed On Time / Total Assigned Tasks) * 100`. Adjusted by manager ratings on deliverables.
*   **CRM Performance:** `(Won Deals / Total Qualified Leads) * 100`. Time-to-close metrics tracking from `Lead Created` to `Client Created`.
*   **Department Performance:** Aggregate of all Employee Performance scores within the department + `(Department Deliverables On Time / Total Deliverables)`.
*   **Client Health:** Calculated based on response times, revision requests (fewer = better), and on-time payments.
*   **Revenue Trend:** Aggregation of `Payments` over time against recurring `Services` definitions.
*   **Delivery Performance:** Tracks the delta between `Task Estimated Completion Date` and `Task Actual Completion Date`.

---

## 7. Google Drive Structure

To maintain a clean and standardized digital asset ecosystem, the system will programmatically map and generate specific folder hierarchies via API (Future Integration).

**Standardized Drive Hierarchy (Per Tenant):**
```text
[Tenant Root Directory] (e.g., "VEXA_AgencyName")
 └── Clients
      └── [Client Name / ID]
           └── [Year] (e.g., 2024)
                └── [Month] (e.g., 10_October)
                     ├── 1_Briefs_and_Docs
                     ├── 2_Assets_and_Photos
                     ├── 3_Working_Files
                     │    ├── Graphics
                     │    └── Videos
                     ├── 4_Client_Review
                     └── 5_Final_Deliverables
```
*   **Rules:** The system strictly maps the `Task ID` and `Project ID` to the specific `[Month]` or `5_Final_Deliverables` folder to ensure seamless syncing. Archiving a client revokes write permissions automatically.

---

## 8. Activity Log Planning

Every state-changing action (POST, PUT, DELETE) will record an immutable log entry.

**Log Structure:**
`Log ID | Timestamp | Tenant ID | User ID | IP Address | Module | Action Type | Target Entity ID | Old Value | New Value`

**Key Events Tracked:**
*   **Auth:** Successful Login, Failed Login, Logout, Password Reset.
*   **CRUD:** Create User, Update Client Details, Delete Project.
*   **Operational:** Assign Task, Change Task Status, Upload File.
*   **Financial:** Generate Invoice, Mark Paid, Process Refund.
*   **Administrative:** Change Role Permissions, Modify System Settings.

---

## 9. Future Integration Planning

The core foundation is designed to securely accept external webhooks and process API calls.

*   **Google Drive API:** Two-way sync. Uploading a file in VEXA pushes to Drive; dropping a file in Drive updates the VEXA task.
*   **WhatsApp API (Meta):** Push notifications to clients for task approvals or to employees for urgent alerts.
*   **Email Automation (SendGrid/Mailgun):** Drip campaigns for CRM Leads and automated billing reminders.
*   **REST API / JWT Auth:** A dedicated `/api/v1/` endpoint structure will allow seamless headless communication for future iOS/Android Apps.
*   **AI Assistant:** Integration with OpenAI/Claude to analyze project delays, draft client emails, or summarize meeting notes inside tasks.

---

## 10. Risk Analysis

| Risk Factor | Probability | Impact | Mitigation Strategy |
| :--- | :--- | :--- | :--- |
| **Data Bleed (Cross-Tenant)** | Low | Critical | Strict implementation of `WHERE company_id = ?` on ALL queries. Code reviews mandate this check. |
| **Performance with Large Datasets**| Medium | High | Implement database indexing on foreign keys (`company_id`, `client_id`). Use AJAX pagination and avoid `SELECT *`. |
| **Heavy Storage / Uploads** | High | Medium | Store files externally (S3/Google Drive) whenever possible. Implement strict file size limits and MIME-type validation. |
| **Complex Permissions Logic** | Medium | High | Cache compiled permission matrices in user sessions to avoid redundant DB hits on every page load. |
| **Scaling the Database** | Low | High | Design schemas to be easily sharded by `company_id` if the user base grows into the thousands. |

---

## 11. Verification Report

**Cross-Check Validation:**
- [x] **Entity completeness:** All entities requested (Company, Client, Task, Attendance, etc.) mapped and purposed.
- [x] **Relationship integrity:** Top-down and lateral data flows confirmed. No orphan modules exist.
- [x] **Workflow logic:** Lead to Archive, Task Creation to Archive, and multi-tier approval chains are logically sound.
- [x] **Analytics & Percentages:** Mathematical basis for dashboard percentages defined.
- [x] **Compliance:** No PHP, SQL, or placeholder code was generated.

**Phase 2 Objective Achieved:** The complete database architecture, module relationships, and system blueprints are defined. The project is ready for precise technical schema design.