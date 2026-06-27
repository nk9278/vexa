# VEXA - PHASE 3 DOCUMENTATION
## COMPLETE UI/UX DESIGN SYSTEM & PAGE EXPERIENCE

---

## 1. Design Philosophy
VEXA is designed to be a premium, enterprise-grade SaaS product. The design philosophy strictly avoids the "traditional PHP Admin Panel" aesthetic (e.g., AdminLTE, heavy borders, dark nested sidebars).
*   **Minimal & Clean:** Focus on content over chrome.
*   **Premium Feel:** Generous whitespace, subtle drop shadows, and high-quality typography.
*   **High Readability:** Soft colors for backgrounds, high contrast for text.
*   **Modern & Fast:** AJAX-driven interactions must feel instant, supported by skeleton loaders rather than full-page refreshes.

## 2. Color System
The color system uses Tailwind CSS standard scales for consistency.
*   **Primary:** Deep Indigo (`indigo-600` / `#4F46E5`). Used for primary buttons, active states, and focus rings.
*   **Secondary:** Slate Gray (`slate-500` / `#64748B`). Used for secondary buttons, borders, and subtext.
*   **Background (Light Mode):** Very Light Gray (`slate-50` / `#F8FAFC`).
*   **Surface (Cards/Modals):** Pure White (`white` / `#FFFFFF`).
*   **Text:** Dark Slate (`slate-900` / `#0F172A`) for headings; Medium Slate (`slate-600` / `#475569`) for body.

**Status Colors:**
*   **Success / Completed:** Emerald (`emerald-500` / `#10B981`)
*   **Warning / Pending / Review:** Amber (`amber-500` / `#F59E0B`)
*   **Danger / Cancelled / Overdue:** Rose (`rose-500` / `#F43F5E`)
*   **Information / Active:** Sky Blue (`sky-500` / `#0EA5E9`)
*   **Archived / Draft:** Slate (`slate-400` / `#94A3B8`)

## 3. Typography
*   **Primary Font:** Inter (Sans-Serif). Optimized for highly legible UI at all sizes.
*   **Hierarchy:**
    *   `H1` (Page Title): 24px, Semi-Bold, `slate-900`.
    *   `H2` (Section/Card Title): 18px, Medium, `slate-800`.
    *   `Body` (Standard Text): 14px, Regular, `slate-600`.
    *   `Small` (Meta/Helper Text): 12px, Regular, `slate-500`.
*   **Styling:** Clean lines, zero text-shadows, comfortable line-height (`leading-relaxed` for body, `leading-tight` for headings).

## 4. Layout Standards
All pages must follow a strict, predictable layout structure to minimize cognitive load.
1.  **Header:** Sticky top, contains global actions.
2.  **Breadcrumb / Page Title:** Directly below header.
3.  **Global Actions:** "Add New" or "Export" buttons aligned to the right of the Page Title.
4.  **Filters / Search:** A clean horizontal bar for data manipulation.
5.  **Main Content Area:** Cards, Charts, or Tables.
6.  **Pagination:** Centered at the bottom of the table.

## 5. Header Design
*   **Logo Position:** Top-left, flush with the sidebar.
*   **Company Switch:** A subtle dropdown next to the logo for Super Admin or multi-tenant owners.
*   **Search Bar:** Centered, expansive, soft gray background (`bg-slate-100`), searches across all modules.
*   **Quick Actions (+):** Primary Indigo button/icon for instant creation (Task, Lead, Project).
*   **Notifications:** Bell icon with a red dot indicator. Opens a right-aligned dropdown.
*   **Profile:** Top-right avatar. Clicking reveals Settings, Profile, and Logout.

## 6. Sidebar Design
*   **Style:** Clean white background (`bg-white`), separated from the main content by a subtle 1px border (`border-slate-200`).
*   **Behavior:**
    *   Desktop: Fixed width (e.g., 250px). Can be collapsed to icons-only (e.g., 80px).
    *   Mobile: Hidden by default, slides in from left via Drawer.
*   **Items:** Soft rounded hover states (`hover:bg-slate-50`). Active state gets a primary color tint (`bg-indigo-50 text-indigo-600`).
*   **Nested Menus:** Accordion style, indented slightly, smooth height transition.

## 7. Mobile Design (Critical Priority)
The mobile experience is uniquely designed, NOT just a squished desktop view.
*   **Bottom Navigation:** Primary modules (Dashboard, Tasks, Notifications, Menu) fixed at the bottom for one-handed thumb reach.
*   **Floating Action Button (FAB):** Fixed bottom-right for primary page action (e.g., "+" to add a task).
*   **Drawer Menu:** Replaces the sidebar, accessible via the "Menu" item in the bottom nav.
*   **Card Interactions:** Swipe left/right on list items for quick actions (e.g., Swipe to Complete Task).
*   **Touch Targets:** Minimum 44x44px for all buttons and links.

## 8. Dashboard Standards
Dashboards are composed of modular widgets using a CSS Grid layout.
*   **KPI Cards (Top Row):** 4 columns on desktop, 2 on tablet, 1 on mobile. Example: Total Revenue, Pending Tasks.
*   **Main Chart (Middle Left):** 2/3 width. Example: Revenue Trend (Line Chart).
*   **Secondary Chart (Middle Right):** 1/3 width. Example: Task Completion (Donut Chart).
*   **Data Table (Bottom):** Full width. Example: Recent Activity or Overdue Tasks.

## 9. Card Design
Cards are the fundamental container for all content.
*   **Background:** White.
*   **Border:** None, or a very subtle 1px border (`border-slate-100`).
*   **Shadow:** Soft, diffused shadow (`shadow-sm` moving to `shadow-md` on hover for interactive cards).
*   **Corners:** Generously rounded (`rounded-xl` or `rounded-2xl`).
*   **Padding:** Spacious inner padding (`p-6`).

## 10. Table Design
Tables must be modern and easy to read.
*   **Structure:** No vertical borders. Soft horizontal borders (`border-b border-slate-100`) between rows.
*   **Header:** Sticky top, slightly different background (`bg-slate-50`), uppercase small text (`text-xs font-semibold text-slate-500 uppercase tracking-wider`).
*   **Row Actions:** Hidden by default, appears on row hover, or accessible via an ellipsis (`...`) dropdown menu.
*   **Status:** Always represented by a pill-shaped Badge (see Status Colors).
*   **Responsiveness:** On mobile, tables convert to stacked Cards.

## 11. Form Design
*   **Structure:** Labels positioned above inputs.
*   **Inputs:** High contrast border (`border-slate-300`), rounded (`rounded-lg`), soft focus ring (`focus:ring-2 focus:ring-indigo-500`).
*   **Validation:** Inline red text below the input. Input border turns red (`border-rose-500`).
*   **Multi-Step:** For complex forms (Client Onboarding), use a top horizontal progress indicator.
*   **File Upload:** Drag-and-drop dashed bordered zone with a clear "Browse" button. Image previews appear as small square thumbnails.

## 12. Modal Design
Used to keep users in context without navigating away.
*   **Backdrop:** Dark semi-transparent blur (`bg-slate-900/50 backdrop-blur-sm`).
*   **Container:** White, `rounded-2xl`, centered vertically and horizontally.
*   **Sizes:**
    *   Small (Confirmations/Deletes).
    *   Medium (Standard Forms - Add Task).
    *   Large (Complex Forms/Previews).
    *   Fullscreen (Detailed Document Views).
*   **Footer:** Right-aligned actions (Cancel / Submit).

## 13. Notification Design
*   **In-App Dropdown:** Clean list with avatar/icon, title, timestamp, and unread indicator (blue dot).
*   **Toast Messages:** Used for transient success/error feedback. Slides in from bottom-right (Desktop) or top (Mobile).
    *   Disappears automatically after 3 seconds.
    *   Includes an icon (check for success, alert for error).

## 14. Loading States
*   **Page Load:** Skeleton screens mimicking the layout of the destination page (Cards, Table rows) using a pulsing gray animation (`animate-pulse bg-slate-200`).
*   **Button Load:** Text changes to "Loading..." and a small SVG spinner replaces the icon. Button becomes disabled.
*   **Data Processing:** Thin, indeterminate progress bar at the very top edge of the screen or specific card.

## 15. Empty States
Empty states must guide the user on what to do next.
*   **Visual:** Centered high-quality, soft-colored SVG illustration.
*   **Text:** A clear, friendly H2 ("No Tasks Yet") and a helpful subtext ("Create your first task to get your team moving.").
*   **Action:** A prominent primary button ("+ Create Task").

## 16. Animation Guidelines
Animations must be purposeful, fast, and smooth (max 200-300ms).
*   **Hover:** Buttons and cards elevate slightly and smoothly transition shadow/color.
*   **Modals:** Scale up slightly (95% to 100%) and fade in (`opacity-0` to `opacity-100`).
*   **Dropdowns:** Slide down and fade in from the origin point.
*   **Sidebar:** Smooth width transition when collapsing/expanding.

## 17. Accessibility Standards
*   **Contrast:** Ensure text against background meets WCAG AA standards.
*   **Keyboard Navigation:** All interactive elements must be reachable via `Tab`. Modals must trap focus. `Esc` key must close modals and dropdowns.
*   **Focus States:** Never remove outlines without providing a custom focus ring (`focus:ring`).
*   **ARIA Labels:** Use standard ARIA labels for icon-only buttons.

## 18. UI Consistency Rules
*   **Rule of One:** One icon set (e.g., Feather or Heroicons), one primary font, one color scale.
*   **Padding Uniformity:** All main content containers share the exact same outer padding (`p-4` mobile, `p-8` desktop).
*   **Button Placement:** Primary action is always on the right (or top on mobile stacks). Cancel/Secondary is on the left.

## 19. Future UI Expansion
*   **Dark Mode:** The system uses Tailwind, meaning dark mode will be implemented seamlessly using `dark:bg-slate-900`, `dark:text-white` prefixes in a later phase.
*   **Custom Theming:** Companies may eventually set a custom "Primary Color" replacing the default Indigo for white-labeling purposes.
*   **Kanban Boards:** Advanced drag-and-drop interfaces for CRM pipelines and Task management.

## 20. Phase Completion Report

**Phase 3 Objective Achieved:** The complete UI/UX blueprint and design system documentation has been established. VEXA's visual identity is defined as a modern, premium SaaS platform, distinctly separate from legacy PHP admin templates.

**Validations Complete:**
- [x] No PHP, HTML, CSS, or code generated.
- [x] Premium SaaS aesthetic planned (clean, minimal, ample whitespace).
- [x] Distinct mobile experience engineered (Bottom Nav, FAB, Swipe actions).
- [x] Comprehensive definitions for typography, colors, layouts, and components.
- [x] Standardization rules set for future development consistency.

**Ready for Phase 4.**
