# Ajath PMT - Basecamp-Style Project Management Platform

A pure **PHP 8.3 / Laravel 11** project management platform inspired by **Basecamp 4**, built for shared hosting environments with MySQL.

- **Live URL**: [https://workspace.ajath.com](https://workspace.ajath.com)
- **Document Root**: `/home4/ajathufs/workspace.ajath.com`
- **Zero Node.js / npm Dependencies**: All styling and interactivity utilize CDN assets (Tailwind CSS, Lucide Icons, and Alpine.js) with Laravel Blade views.

---

## Key Features

1. **Multi-Tenancy & Company Workspaces**:
   - Register a company workspace or switch between multiple organizations.
   - Dynamic organization and team hierarchy.

2. **Strict Project Access Control**:
   - **Company Owner / Administrator**: Full access across all projects, team members, client setups, and company settings.
   - **Employee / Team Member**: Access strictly isolated to assigned projects.
   - **Client Partner**: Custom client dashboard showing only projects explicitly shared with the client. Internal agency tools, internal projects, and employee rosters are completely hidden.

3. **The 6 Iconic Basecamp Tools**:
   - **To-dos**: Checklists, priority indicators (`LOW`, `MEDIUM`, `HIGH`, `URGENT`), due dates, assignees, and completion tracking.
   - **Message Board**: Categorized discussions (`Pitch`, `Announcement`, `Question`, `FYI`, `General`) with threaded comments.
   - **Docs & Files**: Project specs, documentation briefs, notes, and scope definitions.
   - **Campfire**: Casual group chat rooms for each project.
   - **Schedule**: Automatic due date and milestone tracking.
   - **Automatic Check-ins**: Team standups and recurring check-in questions.

4. **People & Client Management (`/people`)**:
   - Separate directory tabs for **Our Team** and **Clients & Partners**.
   - Create accounts with individual login credentials (email & password), roles, departments/client company names, and multi-project access assignments.

---

## Directory Structure

```text
├── app/
│   ├── Http/Controllers/Web/   # Auth, Dashboard, Project, and People controllers
│   ├── Http/Controllers/Api/   # REST API controllers
│   └── Models/                 # Eloquent models (Organization, Project, Task, etc.)
├── bootstrap/                  # Framework bootstrapping
├── config/                     # Application configurations
├── database/
│   ├── migrations/             # Full multi-tenant schema migrations
│   └── seeders/                # Database seeders with test personas
├── public/                     # Static assets and front controller
├── resources/
│   └── views/                  # Blade templates (auth, layouts, projects, people)
├── routes/
│   ├── web.php                 # Web session routes
│   └── api.php                 # API routes
├── storage/                    # Framework storage
├── .htaccess                   # Apache rewrite rules & security lockdown
├── artisan                     # Laravel CLI runner
├── composer.json               # PHP dependencies
└── index.php                   # Document root front controller
```

---

## Database Configuration

Configured for MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ajathufs_pmt
DB_USERNAME=ajathufs_pmt
DB_PASSWORD=AjathPMT_230037!@#
```

---

## Seeded Test Personas

| Role | Email | Password | Access Scope |
| :--- | :--- | :--- | :--- |
| **Owner** | `claire.watson@ajath.com` | `password123` | Full administrative access to *Ajath Infotech Pvt Ltd* |
| **Employee** | `marcus.dev@worksphere.io` | `password123` | Full-stack developer assigned to internal & client projects |
| **Client** | `edward.client@partner.com` | `password123` | Client partner isolated strictly to *Ride My Cars* |
