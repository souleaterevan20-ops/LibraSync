# LibraSync Upgrade — What Changed & How to Run It

This upgrade implements the "LibraSync System Upgrade Requirements" doc across all 8 sections.
Since this sandbox has no PHP/Composer/network, none of this was executed or tested here —
run the steps below locally.

## 1. Setup
```bash
composer install
npm install && npm run build
php artisan migrate
php artisan storage:link   # needed for book covers & profile avatars
```

## 2. What was built

### Self-Registration (Section 1)
- Step 2 now shows role-specific fields only: Students get Department/Course dropdowns
  (exact option lists from the spec), School ID, Year Level; Teachers get Employee ID only.
- Date of Birth removed entirely.
- New registrations notify Super Admins & Library Staffs in-app.

### Notifications (Sections 2–4)
- New `notifications` table + `Notification` model + `Notifier` service.
- Notification Center at `/notifications` for all 3 roles (bell icon in every layout now works).
- Automatic notifications for: new registration, approval, borrow approved/rejected, book
  returned, penalties, rewards/clearance, password reset, and Library Staff login/logout.
- `php artisan library:send-borrow-reminders` (scheduled daily 7am) sends "due in 2 days" and
  "overdue" reminders — make sure your server's cron runs `php artisan schedule:run` every minute.

### Reading Points & Leaderboard (Section 4)
- Points now update automatically on return: +10 on time, -10 overdue, -20 lost/damaged.
- `/leaderboard` masks every name to initials except your own row (Super Admin/Library Staff
  see full names for moderation).

### Clearance Management (Section 5)
- New `clearance_requests` table. Students apply; Assistants verify payment; Super Admin
  approves/rejects. `/clearance` adapts its actions to the logged-in role.

### Reports (Section 6) & Audit Trail (Section 7)
- `/reports` — Daily, Borrow, Return, Penalty, Inventory for everyone; Semester, Leaderboard,
  and Audit Logs for Super Admin only. Each report has a date filter and a CSV export.
- New `audit_logs` table + `AuditLogger` service. Every sensitive action (approvals, deletes,
  disables, password resets, logins/logouts, returns) is recorded and surfaced on each user's
  "View Activity" page.

### Super Admin Dashboard (Section 2)
- The 5 stat cards are now clickable: Total Books → catalog, Total Available → available list,
  Total Borrowed → active loans, Total Users → full directory, Pending Registrations → approval
  queue.
- New full user profile page (`/admin/users/{id}`) with borrow/return history, current loans,
  points, penalties, login history, bio — plus Delete / Disable / Reset Password / View Activity.
- New Super Admin Management (`/admin/super-admins`) and Library Staff Management
  (`/admin/assistants`) pages — create/edit/deactivate/delete, reset password, view activity.
- Book detail page now shows every spec field (cover, publisher, year, category, circulation
  type, copies, borrowers, remarks).

### Library Staff Dashboard (Section 3) & Permission Matrix (Section 8)
- A new `role` middleware enforces the permission matrix at the route level — e.g. only
  `super_admin` can delete users/books, create other admins/assistants, or approve/reject
  clearance; `student_assistant` is limited to processing, verifying, and reporting.
- Fixed a pre-existing bug where assistant login/logout tracking silently never fired because
  it checked for roles that don't exist in this schema (`assistant`/`admin` instead of
  `student_assistant`/`super_admin`).

### Student / Teacher Dashboard
- Profile now supports avatar upload, bio, and favorite genre.
- Sidebar Clearance widget links straight to the clearance page.

## 3. Known gaps / left as-is
Given the sandbox had no PHP runtime and no network access, none of this could be executed,
migrated, or click-tested — please do a run-through locally before deploying. A few lower-priority
items from the spec were intentionally left as "Coming Soon" placeholders to keep this change set
reviewable: Archive, Disposal (5-year), Backup/Restore, Excel import, and System Settings. The
permission matrix already blocks Library Staffs from all of these.
