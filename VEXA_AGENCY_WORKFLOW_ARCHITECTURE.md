# VEXA - DIGITAL MARKETING AGENCY WORKFLOW ARCHITECTURE
## Strategic Pivot & Future Module Design

### Overview
VEXA is fundamentally a multi-tenant project management and workflow platform tailored specifically for **Digital Marketing Agencies**.

Based on the architectural review, future module development will intentionally diverge from generic ERP/HR structures. Team members within the system (e.g., Content Writers, Graphic Designers, Photographers, Video Editors, SEO Executives) will be treated as **Production Resources** rather than traditional HR employees.

The system is designed to facilitate the rapid onboarding of clients, the generation of deliverables, and a streamlined, multi-tier approval flow.

---

### Core Business Modules (Phases 20+)

#### 1. Team Management (Replacing Generic HR/Employee Module)
- **Concept:** Manage production resources.
- **Roles:** Content Writer, Graphic Designer, Video Editor, SEO Executive, Social Media Manager, Account Manager.
- **Data Structure Shift:** Focus on capacity, skill sets, and assigned client portfolios rather than payroll, leave, or attendance.
- **Responsibilities:**
  - Resource allocation (who is working on which client/deliverable).
  - Production bandwidth tracking.

#### 2. CRM Workflow (Client Onboarding & Management)
- **Concept:** Agency-focused Client Relationship Management.
- **Workflow:** Lead -> Prospect -> Active Client.
- **Key Entities:**
  - `brands` or `client_profiles` (instead of generic `customers`).
  - Social media integrations/links (Facebook, Instagram, LinkedIn).
  - Retainer vs. Project-based billing indicators.
  - Assigned Account Manager / Client Success Manager.

#### 3. Client Workspace
- **Concept:** A dedicated, isolated portal or view for clients to interact with the agency.
- **Responsibilities:**
  - View upcoming content calendars.
  - Review and approve deliverables.
  - Provide centralized feedback (eliminating scattered email threads).
  - Access asset libraries (Logos, Brand Guidelines).

#### 4. Deliverables & Production Pipeline
- **Concept:** The core unit of work in an agency is a *Deliverable* (e.g., 4 Social Media Posts, 1 Blog Article, 1 Promotional Video).
- **Workflow:**
  1. **Briefing:** Strategy / Account Manager creates the brief.
  2. **Production:** Assigned to Writer/Designer.
  3. **Internal QA:** Reviewed by Art Director / Content Lead.
  4. **Client Review:** Sent to the Client Workspace for approval.
  5. **Publishing/Delivery:** Final handoff.

#### 5. Task Management
- **Concept:** Micro-actions required to complete a Deliverable.
- **Implementation:** Kanban boards (To Do, In Progress, Review, Done).
- **Association:** Tasks must strictly link to a Deliverable, which links to a Client, which is isolated within the Company Tenant.

#### 6. Approval Flow Engine
- **Concept:** A rigid, status-driven state machine.
- **States:** `Draft` -> `Pending Internal Review` -> `Pending Client Approval` -> `Client Changes Requested` -> `Approved` -> `Published`.
- **Implementation:** Database triggers or application-level state tracking ensuring a deliverable cannot bypass internal review before reaching the client.

---

### Future Database Implications (Drafting)
*Note: No existing tables from Phases 1-19 will be modified. The following are planned additions.*

- `teams` (replaces generic `departments`)
- `production_resources` (users scoped by skill/capacity)
- `clients` & `client_brands`
- `deliverables` (Title, Type, Due Date, Status)
- `tasks` (Sub-units of Deliverables)
- `approval_logs` (Audit trail of who approved what and when)

### Development Directives
- **No HR Logic:** Avoid building payroll, leave tracking, or complex attendance systems unless specifically requested as a secondary feature later.
- **Action-Oriented UI:** Dashboards should highlight actionable items ("3 Designs Pending Approval", "2 Briefs Missing").
- **Strict Isolation:** Ensure `client` portals are entirely restricted from viewing agency internal metadata or other clients' assets.