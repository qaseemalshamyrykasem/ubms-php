# 📦 Changelog

All notable changes to UBMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-06-17

### 🎉 Initial Release

#### Added
- ✅ **Authentication System**
  - Login, Register, Forgot Password
  - Remember Me functionality
  - Email verification ready
  - Laravel Sanctum token-based auth for API
  - Session-based auth for mobile (NativePHP)
  
- ✅ **User Roles** (4 roles)
  - Super Admin: complete system management
  - College Admin: manages one college
  - Batch Representative: responsible for one batch
  - Student: register, login, join one batch

- ✅ **University Hierarchy**
  - University → College → Department → Level → Section → Batch → Students
  - 6-level deep structure
  - Admin can create/edit/delete at any level

- ✅ **Announcements Module**
  - 9 announcement types (holiday, assignment, lecture, schedule, general, urgent, emergency, meeting, important)
  - Pin/unpin announcements
  - Schedule for future publishing
  - File attachments (images, PDFs, documents)
  - Telegram broadcast integration
  - Read statistics per announcement
  - Search and filter by type/course
  - Templates support

- ✅ **Attendance System**
  - Create lecture sessions with QR codes
  - QR codes expire automatically (configurable TTL)
  - Prevent duplicate attendance
  - 4 attendance statuses: Present, Absent, Late, Excused
  - Manual attendance entry by representative
  - Lock attendance after lecture ends
  - Per-student statistics and attendance rate
  - Batch-level statistics
  - History with filters (course, date range)

- ✅ **QR Code Attendance**
  - Native camera scanning (mobile)
  - HTML5 QR library fallback (web)
  - Verification via unique tokens
  - Late detection (configurable threshold)
  - Vibration feedback on mobile

- ✅ **Course Management**
  - CRUD for courses
  - Assign to batches
  - Course files (syllabus, lectures, references)
  - Instructor information
  - Credit hours

- ✅ **Class Schedule**
  - Weekly recurring schedule
  - Day-by-day view
  - Room and building info
  - Schedule exceptions (cancel/reschedule)
  - Today highlight

- ✅ **Assignment Management**
  - Create with deadlines
  - Multiple attachments (PDF, ZIP, DOCX, PPTX)
  - Late submission with penalty
  - Telegram notifications
  - Download tracking
  - Overdue indicators

- ✅ **Telegram Bot Integration**
  - Bot webhook support
  - Account linking via 6-digit code + deep link
  - Broadcast announcements to batch
  - Assignment notifications
  - Delivery status tracking
  - Disconnect option

- ✅ **File Management**
  - Images, PDF, DOCX, ZIP, RAR, Excel, PowerPoint
  - Configurable max upload size
  - Secure downloads with logging
  - Organized folder structure per entity

- ✅ **Search**
  - Global search across announcements, assignments, students, batches
  - Per-module search with filters

- ✅ **Dashboards**
  - Role-specific dashboards (student, rep, admin)
  - Modern charts (Bar, Pie) via Recharts
  - Attendance rate visualization
  - Daily activity tracking
  - Announcements by type breakdown
  - Recent activity feed
  - Upcoming assignments

- ✅ **Reports & Export**
  - Excel export (Maatwebsite Excel) with RTL support
  - PDF export (DomPDF) with Arabic fonts
  - Attendance reports (with filters)
  - Student lists
  - Announcements reports
  - Assignments reports
  - Statistics reports
  - Professional formatting with university logo

- ✅ **Notifications**
  - In-app notifications with unread counter
  - Scheduled notifications
  - Mark as read (single/all)
  - Notification history
  - Native notifications (mobile via NativePHP)

- ✅ **Security**
  - CSRF protection
  - XSS protection (Blade + React auto-escaping)
  - SQL injection protection (Eloquent ORM)
  - Password hashing (bcrypt)
  - Rate limiting (60 req/min default, 5 for login)
  - Role-based access control (Spatie Permission)
  - Audit logs for all sensitive operations
  - Secure file upload validation
  - Sanctum token authentication

- ✅ **Database**
  - 25+ normalized tables
  - Foreign keys with proper cascade rules
  - Indexes for performance
  - Migrations (10 files)
  - Factories + Seeders with realistic demo data
  - 30 demo students, 5 courses, 9 announcements, 14 days of attendance

- ✅ **API**
  - Complete REST API (60+ endpoints)
  - Versioned (`/api/v1/`)
  - Postman collection included
  - Comprehensive error handling
  - JSON responses with Arabic messages

- ✅ **Localization**
  - Arabic (default) with full RTL support
  - English support
  - Language switcher
  - Separated translation files per module

- ✅ **Frontend (Web)**
  - React 19 + Vite 6 + TypeScript 5.7
  - TailwindCSS 3.4 + Shadcn UI components
  - Dark mode (default) + Light mode
  - Glassmorphism design
  - Framer Motion animations
  - Recharts for data visualization
  - React Query for server state
  - Zustand for client state
  - React Router 7
  - 14 page components
  - Fully responsive (mobile to desktop)

- ✅ **Mobile (NativePHP)**
  - NativePHP Mobile integration
  - 18 Blade views for mobile
  - Bottom navigation
  - Native camera for QR scanning
  - Native notifications
  - SQLite for offline support
  - Deep links (`ubms://`)
  - Dark mode default
  - Touch-optimized UI
  - Safe area support (notch)

- ✅ **Documentation**
  - README (Arabic + English)
  - Installation guide
  - Configuration guide
  - Shared hosting (cPanel) deployment guide
  - Telegram bot setup guide
  - API documentation (60+ endpoints)
  - Database design with ER diagram
  - Project structure documentation
  - User manual (student)
  - User manual (representative)
  - Admin manual
  - NativePHP mobile guide

- ✅ **CI/CD**
  - GitHub Actions workflow for building Android APK
  - Issue templates (bug report, feature request)
  - Pull request template
  - Code owners file
  - Security policy
  - Contributing guide

#### Technical Stack
- **Backend**: Laravel 12, PHP 8.3, MySQL 8.0 / SQLite
- **Frontend**: React 19, TypeScript 5.7, Vite 6, TailwindCSS 3.4
- **Mobile**: NativePHP Mobile (Beta), Livewire 3.5
- **Auth**: Laravel Sanctum 4, Spatie Permission 6
- **Export**: Maatwebsite Excel 3.1, DomPDF 3.0
- **QR**: SimpleSoftware QrCode 2.0

#### Demo Data
- 1 university (University of Aden)
- 1 college (Engineering)
- 1 department (Computer Science)
- 1 level (Level 3)
- 1 section (Section A)
- 1 batch (CS-2024-A, 2024-2025)
- 5 courses
- 30 students
- 1 representative
- 9 announcements (various types)
- 14 days of attendance records
- 4 assignments
- Full weekly schedule (Sunday-Thursday)

---

## Version History

- **1.0.0** (2025-06-17) - Initial release with full features

---

## Format

- `Added` for new features
- `Changed` for changes in existing functionality
- `Deprecated` for soon-to-be removed features
- `Removed` for now removed features
- `Fixed` for any bug fixes
- `Security` for vulnerability fixes
