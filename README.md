<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
    </a>
</p>

<h1 align="center">HES Connect</h1>

<p align="center">
    <strong>Health Ecosystem System</strong> — A comprehensive healthcare platform connecting patients, doctors, facilities, and pharmacies.
</p>

<p align="center">
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel"></a>
    <a href="https://vuejs.org"><img src="https://img.shields.io/badge/Vue.js-4.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue.js"></a>
    <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP"></a>
    <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
    <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"></a>
    <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License"></a>
</p>

---

## Table of Contents

- [Project Overview](#project-overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Folder Structure](#folder-structure)
- [Installation](#installation)
- [Environment Variables](#environment-variables)
- [Database](#database)
- [Authentication](#authentication)
- [Roles & Permissions](#roles--permissions)
- [API](#api)
- [Frontend](#frontend)
- [AI Features](#ai-features)
- [Notifications](#notifications)
- [File Storage](#file-storage)
- [Testing](#testing)
- [Development](#development)
- [Deployment](#deployment)
- [Security](#security)
- [Performance](#performance)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)
- [Screenshots](#screenshots)
- [Future Improvements](#future-improvements)

---

## Project Overview

**HES Connect** is a full-stack healthcare ecosystem platform designed to connect patients with medical facilities, doctors, and pharmacies. It provides a multi-role system where patients can book appointments, receive AI-powered medical triage consultations, manage prescriptions, and read/write health articles. Facility owners and administrators can manage their organizations, staff, schedules, and patients through dedicated dashboards. The platform supports real-time messaging, notifications, donations, job postings, and comprehensive analytics.

**Who it is for:**

- **Patients** — Find doctors, book appointments, receive AI health guidance, manage prescriptions, and read health content
- **Doctors / Staff** — Manage schedules, write articles, handle prescriptions and medication requests, review patients
- **Facility Owners** — Manage facilities, departments, staff assignments, reviews, and facility-level reports
- **Platform Administrators** — Oversee the entire ecosystem: users, organizations, facilities, content moderation, analytics, and system-wide reports

---

## Features

### Authentication & User Management
- Registration and login via Laravel Fortify
- Email verification
- Password reset
- Two-factor authentication (2FA)
- Social login support (Google provider configured)
- User profiles with translatable fields
- User status management (active/inactive/suspended)
- Soft deletes for user recovery

### Roles & Permissions
- Granular permission-based access control (13 resource modules, 80+ individual permissions)
- Role management (create, update, delete, assign)
- Custom permission keys per resource (CRUD + domain-specific actions)
- Middleware-based authorization (`permission:*`, `dashboard.access:*`)

### Organizations & Facilities
- Organization management with multiple facilities
- Facility types: hospital, clinic, pharmacy, laboratory
- Facility approval workflow (pending → approved/rejected)
- Facility images and document management
- Facility reviews with admin moderation (approve/reject/hide/show)
- City-based facility lookup
- Facility-specific dashboards with analytics

### Staff Management
- Staff profile management with professions and specializations
- Staff-facility assignments with role-based permissions
- Staff schedules (weekly recurring + one-off)
- Staff unavailability management with approval workflow
- Staff position management
- Staff termination
- Email uniqueness validation

### Appointments
- Public appointment booking
- Dashboard appointment management
- Appointment statuses (pending, confirmed, completed, cancelled, rescheduled)
- Appointment rescheduling
- Appointment file attachments
- Calendar view
- Appointment analytics

### Patients
- Patient records linked to users
- Patient list per facility
- Patient dashboard

### Departments
- Department management per facility
- Facility-department relationships

### Prescriptions & Medication
- Prescription creation with items (medicine, dosage, frequency, duration, route, notes)
- Prescription statuses (pending, dispensed, partially_dispensed, cancelled)
- Pharmacy selection by patients
- Available pharmacies lookup
- Pharmacy medicine inventory with pricing
- Medication request workflow (patient → facility → accept/reject)
- Medicine catalog with translatable fields (name, description, side effects, contraindications, dosage guidelines)

### Content Management
- Articles with approval workflow (draft → pending → approved/rejected)
- Article categories and tags (many-to-many)
- Article comments with moderation (hide/show)
- Patient stories with approval workflow
- Story donations
- Story soft deletes with trash/restore/force delete
- CMS pages with slug-based routing

### Medical Specialties & Symptoms
- Specialization management with symptoms
- Symptom-specialization relationships
- Staff specialization assignments
- Specialization-based doctor discovery

### Donations & Payments
- Story-based donation system
- Stripe payment integration (Checkout Sessions)
- Stripe webhook handling
- Payment status tracking
- Invoice generation
- Payment gateway abstraction (interface-based)

### Subscriptions
- Email subscription with verification flow
- Unsubscribe via token

### Reviews
- Facility reviews (public, with moderation)
- Platform reviews (admin moderation, reply system)
- Appointment-based reviews
- Review notifications

### Job Posts
- Job posting with approval workflow
- Facility-scoped job management

### Search & Favorites
- Search history tracking with trending analysis
- Favorites system (facilities, articles, doctors)

### Conversations & Messaging
- Real-time conversations between users
- Conversation participants
- Message attachments
- Read receipts
- Conversation management (archive, lock, stats)
- Doctor-patient conversations

### AI Features
- **Patient Health Assistant** — AI-powered medical triage conversation guide
- **Medical Triage Agent** — Analyzes conversations and matches to medical specialties
- **Doctor Recommendation** — AI suggests relevant doctors based on triage analysis
- **HES Dashboard Assistant** — Admin AI assistant for querying platform data (users, facilities, donations, reports)
- **SEO Agent** — Generates SEO metadata for articles
- **Writer Agent** — AI content writing assistance
- **Multi-provider support** — OpenAI (GPT-4o) and Anthropic (Claude 3.5 Sonnet)
- **AI tool system** — Function-calling tools for querying doctors, specialties, facilities, and system data
- **Conversation context management** — Token limits, context compression, summary generation

### Notifications
- Database notifications (primary)
- Email notifications (Mailable classes)
- Broadcast notifications via Pusher (real-time)
- SMS notifications via Twilio
- Notification matrix system (configurable per event × recipient)
- Notification preferences per user (email, push, SMS)
- Notification logging

### Dashboard & Analytics
- Super Admin dashboard with overview stats
- Facility-level dashboard with analytics
- Dashboard reports with Excel and PDF export
- Organization statistics
- Prescription and medication request analytics
- Doctor performance metrics
- Live appointment tracking

### Audit & Logging
- Audit logging via `Auditable` trait
- Business event logging
- AI prompt logging
- Audit log viewer in admin dashboard

### Localization
- Bilingual support: English (`en`) and Arabic (`ar`)
- Spatie Translatable for model fields
- Locale middleware for request-level locale switching
- Translatable permissions and configurations

### File Storage
- Local filesystem storage (default)
- AWS S3 support (configured)
- Facility images and documents
- Appointment file attachments
- Cover images for stories and job posts

### Webhooks
- Stripe webhook endpoint for payment events

### Broadcasting
- Pusher-based real-time broadcasting
- Laravel Echo client integration

---

## Tech Stack

### Backend

| Technology | Version | Purpose |
|---|---|---|
| PHP | 8.3+ | Server-side language |
| Laravel | 13.x | Application framework |
| MySQL | 8.4 | Primary database |
| SQLite | — | Default/development database |
| Laravel Fortify | 1.36+ | Authentication (registration, login, 2FA, password reset) |
| Laravel Sanctum | 4.0+ | API token authentication |
| Laravel Sail | 1.58+ | Docker development environment |

### Frontend

| Technology | Version | Purpose |
|---|---|---|
| Vite | 8.0+ | Build tool and dev server |
| Tailwind CSS | 4.0+ | Utility-first CSS framework |
| Laravel Echo | 2.3+ | Real-time broadcasting client |
| Pusher.js | 8.5+ | WebSocket client |

### Infrastructure

| Technology | Purpose |
|---|---|
| Docker | Containerization |
| Laravel Sail | Development environment orchestration |
| MySQL 8.4 | Database service |
| Redis | Caching and session storage |
| Meilisearch | Full-text search engine |
| Mailpit | Email testing (development) |
| Selenium | Browser testing |

### Third-Party Services

| Service | Purpose |
|---|---|
| OpenAI (GPT-4o) | AI health assistant and dashboard assistant |
| Anthropic (Claude 3.5 Sonnet) | Alternative AI provider |
| Stripe | Payment processing |
| Twilio | SMS notifications |
| Pusher | Real-time broadcasting |

---

## Architecture

### Service Layer

The application follows a **Service Layer pattern** where business logic is encapsulated in dedicated service classes under `app/Services/`. Each domain entity (appointments, facilities, prescriptions, etc.) has its own service class that handles complex operations, keeping controllers thin and focused on HTTP concerns.

Key service groups:
- **Core Services** — `AppointmentService`, `FacilityService`, `PrescriptionService`, `MedicineService`, etc.
- **Medical Triage Services** — `MedicalTriageService`, `SpecialtyMatcherService`, `DoctorRecommendationService`, `ConversationContextService`, `ConversationSummaryService`
- **Dashboard AI Services** — `AiChatService`, `ConversationService`, `MessageService`
- **Notification Services** — `NotificationService`, `NotificationMatrix`, `NotificationLogService`, `NotificationPreferenceService`, `NotificationRecipientResolver`
- **Payment Services** — `PaymentService`, `StripePaymentService`, `WebhookService` (with `PaymentGatewayInterface` abstraction)
- **Reporting Services** — `DashboardReportService`, `FacilityReportService`, `DashboardAnalyticsService`

### API Architecture

- **RESTful JSON API** — All routes return JSON responses
- **Resource-based organization** — Controllers grouped by domain (Dashboard, Facility, Staff, Patient, Public, Admin)
- **Form Request validation** — Dedicated request classes per action
- **API Resources** — Consistent response transformation via Laravel API Resources
- **Route file splitting** — Main `api.php` delegates to partial route files in `routes/api/dashboard/` and `routes/api/facility/`

### Frontend Architecture

- **SPA-first** — Laravel serves as an API backend; the frontend is a separate Vue.js application
- **Blade minimal** — Only email templates and a welcome page use Blade
- **Vite build** — Assets compiled via Vite with Tailwind CSS plugin

### Event-Driven Design

- **57 events** covering all major domain actions
- **43 listeners** dispatching notifications, logging, and side effects
- **Observers** for article and audit log lifecycle management
- **Event Subscribers** for cross-cutting concerns (audit, notifications)

---

## Folder Structure

```
├── app/
│   ├── Actions/                  # Single-purpose action classes
│   │   ├── Conversation/         # Conversation CRUD actions
│   │   ├── Dashboard/            # Dashboard management actions
│   │   └── Fortify/              # Authentication actions
│   ├── Ai/                       # AI system
│   │   ├── Agents/               # AI agent definitions (5 agents)
│   │   ├── Contracts/            # Agent & Provider interfaces
│   │   ├── Providers/            # OpenAI & Anthropic implementations
│   │   └── Tools/                # Function-calling tools
│   ├── Data/                     # Data transfer objects
│   ├── DTOs/                     # Triage & recommendation DTOs
│   ├── Enums/                    # 35 enum classes
│   ├── Events/                   # 57 domain events
│   ├── Http/
│   │   ├── Controllers/Api/      # API controllers (Dashboard, Facility, Staff, Patient, Public, Admin)
│   │   ├── Middleware/           # DashboardAccess, Permission, SetLocale
│   │   ├── Requests/            # 40+ Form Request classes
│   │   └── Resources/           # 73 API Resource classes
│   ├── Jobs/                     # Background jobs
│   ├── Listeners/               # 43 event listeners
│   ├── Mail/                    # 5 Mailable classes
│   ├── Models/                  # 62 Eloquent models
│   ├── Notifications/           # 20+ notification classes
│   ├── Observers/               # Article & Audit observers
│   ├── Policies/                # 4 authorization policies
│   ├── Providers/               # 4 service providers
│   ├── Services/                # 66 service classes
│   │   ├── Dashboard/           # Dashboard AI services
│   │   ├── MedicalTriage/       # Medical triage services
│   │   ├── Notification/        # Notification infrastructure
│   │   └── PaymentGateways/     # Payment gateway abstraction
│   └── Traits/                  # Auditable trait
├── config/                      # 18 configuration files
├── database/
│   ├── factories/               # 33 model factories
│   ├── migrations/              # 124 migration files
│   └── seeders/                 # 40 seeder classes
├── lang/                        # en/ and ar/ translations
├── resources/
│   ├── views/emails/            # Blade email templates
│   └── js/                      # Echo.js (Pusher client)
├── routes/
│   ├── api.php                  # Main API routes (737 lines)
│   ├── api/dashboard/           # Dashboard sub-routes
│   ├── api/facility/            # Facility sub-routes
│   ├── web.php                  # Minimal web routes
│   ├── channels.php             # Broadcast channel definitions
│   └── console.php              # Console commands
└── tests/
    ├── Feature/                 # Feature tests
    └── Unit/                    # Unit tests
```

---

## Installation

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8.4 (or SQLite for development)
- Docker & Docker Compose (optional, for Laravel Sail)

### Quick Start (Composer Script)

```bash
composer setup
```

This runs `composer install`, copies `.env`, generates an app key, runs migrations, installs npm dependencies, and builds frontend assets.

### Manual Installation

```bash
# Clone the repository
git clone <repository-url>
cd health-ecosystem

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Seed the database (optional)
php artisan db:seed

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

### Docker Installation (Laravel Sail)

```bash
# Start all services (MySQL, Redis, Meilisearch, Mailpit, Selenium)
./vendor/bin/sail up -d

# Run migrations inside the container
./vendor/bin/sail artisan migrate --force

# Seed the database
./vendor/bin/sail artisan db:seed

# Install and build frontend assets
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Running the Application

```bash
# Option 1: Run all services concurrently via Composer
composer dev

# Option 2: Run individually
php artisan serve              # HTTP server
php artisan queue:listen       # Queue worker
php artisan pail               # Log viewer
npm run dev                    # Vite dev server
```

### Queue Worker

```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Scheduler

The project does not currently define custom scheduled commands in `routes/console.php`. Add scheduled tasks as needed:

```bash
# Add to crontab or run manually
php artisan schedule:run
```

---

## Environment Variables

All environment variables are defined in `.env.example`. Copy it to `.env` and configure as needed.

### Application

| Variable | Description | Default |
|---|---|---|
| `APP_NAME` | Application name | `Laravel` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_KEY` | Encryption key (auto-generated) | — |
| `APP_DEBUG` | Debug mode | `true` |
| `APP_URL` | Application URL | `http://localhost` |
| `APP_LOCALE` | Default locale | `en` |
| `APP_FALLBACK_LOCALE` | Fallback locale | `en` |
| `APP_FAKER_LOCALE` | Faker locale for testing | `en_US` |
| `BCRYPT_ROUNDS` | Password hashing rounds | `12` |

### Database

| Variable | Description | Default |
|---|---|---|
| `DB_CONNECTION` | Database driver | `sqlite` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `laravel` |
| `DB_USERNAME` | Database username | `root` |
| `DB_PASSWORD` | Database password | — |

### Session & Cache

| Variable | Description | Default |
|---|---|---|
| `SESSION_DRIVER` | Session storage driver | `database` |
| `SESSION_LIFETIME` | Session lifetime (minutes) | `120` |
| `CACHE_STORE` | Cache driver | `database` |
| `QUEUE_CONNECTION` | Queue driver | `database` |

### Broadcasting (Pusher)

| Variable | Description | Default |
|---|---|---|
| `BROADCAST_CONNECTION` | Broadcast driver | `log` |
| `PUSHER_APP_ID` | Pusher application ID | — |
| `PUSHER_APP_KEY` | Pusher app key | — |
| `PUSHER_APP_SECRET` | Pusher app secret | — |
| `PUSHER_APP_CLUSTER` | Pusher cluster | `mt1` |
| `PUSHER_PORT` | Pusher port | `443` |
| `PUSHER_SCHEME` | Pusher scheme | `https` |

### Mail

| Variable | Description | Default |
|---|---|---|
| `MAIL_MAILER` | Mail driver | `log` |
| `MAIL_HOST` | SMTP host | `127.0.0.1` |
| `MAIL_PORT` | SMTP port | `2525` |
| `MAIL_USERNAME` | SMTP username | — |
| `MAIL_PASSWORD` | SMTP password | — |
| `MAIL_FROM_ADDRESS` | Sender email | `hello@example.com` |
| `MAIL_FROM_NAME` | Sender name | `${APP_NAME}` |

### AI Providers

| Variable | Description | Default |
|---|---|---|
| `AI_PROVIDER` | Default AI provider (`openai`, `anthropic`) | `openai` |
| `OPENAI_API_KEY` | OpenAI API key | — |
| `OPENAI_MODEL` | OpenAI model | `gpt-4o` |
| `OPENAI_BASE_URL` | OpenAI base URL (optional) | — |
| `ANTHROPIC_API_KEY` | Anthropic API key | — |
| `ANTHROPIC_MODEL` | Anthropic model | `claude-3-5-sonnet-20241022` |
| `ANTHROPIC_BASE_URL` | Anthropic base URL (optional) | — |

### AI Chat

| Variable | Description | Default |
|---|---|---|
| `CHAT_MAX_MESSAGES` | Max messages per conversation | `40` |
| `CHAT_MAX_CONTEXT_TOKENS` | Max context tokens before compression | `4000` |
| `CHAT_CONTEXT_RECENT_MESSAGES` | Recent messages kept during compression | `15` |
| `CHAT_MAX_TOTAL_TOKENS` | Max total tokens before new conversation | `32000` |
| `CHAT_MIN_MESSAGES_FOR_RECOMMENDATION` | Min messages before doctor recommendation | `4` |
| `CHAT_TRIAGE_CONFIDENCE_THRESHOLD` | Min confidence for recommendation button | `0.5` |

### Third-Party Services

| Variable | Description | Default |
|---|---|---|
| `STRIPE_SECRET` | Stripe secret key | — |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret | — |
| `TWILIO_SID` | Twilio account SID | — |
| `TWILIO_AUTH_TOKEN` | Twilio auth token | — |
| `TWILIO_FROM` | Twilio phone number | — |
| `POSTMARK_API_KEY` | Postmark API key (optional) | — |
| `RESEND_API_KEY` | Resend API key (optional) | — |
| `SLACK_BOT_USER_OAUTH_TOKEN` | Slack bot token (optional) | — |
| `SLACK_BOT_USER_DEFAULT_CHANNEL` | Slack channel (optional) | — |

### Storage (AWS S3)

| Variable | Description | Default |
|---|---|---|
| `AWS_ACCESS_KEY_ID` | AWS access key | — |
| `AWS_SECRET_ACCESS_KEY` | AWS secret key | — |
| `AWS_DEFAULT_REGION` | AWS region | `us-east-1` |
| `AWS_BUCKET` | S3 bucket name | — |

### Redis

| Variable | Description | Default |
|---|---|---|
| `REDIS_HOST` | Redis host | `127.0.0.1` |
| `REDIS_PASSWORD` | Redis password | `null` |
| `REDIS_PORT` | Redis port | `6379` |

---

## Database

### Major Entities

| Entity | Description |
|---|---|
| `users` | System users with roles, profiles, and preferences |
| `organizations` | Healthcare organizations |
| `facilities` | Medical facilities (hospitals, clinics, pharmacies, labs) |
| `staff` | Medical staff members (doctors, nurses, etc.) |
| `patients` | Patient records linked to users |
| `departments` | Facility departments |
| `appointments` | Patient-doctor appointments |
| `prescriptions` | Medical prescriptions with line items |
| `medicines` | Medicine catalog |
| `medication_requests` | Patient medication fulfillment requests |
| `specializations` | Medical specialties |
| `symptoms` | Medical symptoms |
| `articles` | Health articles with approval workflow |
| `stories` | Patient stories with donation support |
| `donations` | Story donations |
| `payments` | Payment records (Stripe) |
| `invoices` | Generated invoices |
| `conversations` | User conversations |
| `messages` | Conversation messages |
| `roles` / `permissions` | RBAC system |
| `facility_staff` | Staff-facility assignments |
| `facility_reviews` | Facility reviews |
| `platform_reviews` | Platform-wide reviews |
| `job_posts` | Job postings |
| `subscriptions` | Email subscriptions |
| `cities` | City lookup data |
| `ai_medical_conversations` | AI triage conversations |
| `ai_medical_messages` | AI triage messages |
| `ai_conversations` | Admin AI assistant conversations |
| `ai_messages` | Admin AI assistant messages |
| `search_histories` | User search history |
| `audit_logs` | System audit trail |

### Key Relationships

- **User** hasOne `UserProfiles`, hasOne `Staff`, hasOne `Patient`, hasMany `systemRoles`
- **Organization** hasMany `Facility`, hasMany `OrganizationUser`
- **Facility** belongsTo `Organization`, hasMany `FacilityStaff`, `Department`, `Appointment`, `FacilityReview`, `JobPost`
- **Staff** belongsTo `User`, hasOne `Specialization`, hasMany `FacilityStaff`, `StaffSchedule`
- **Appointment** belongsTo `Facility`, hasMany `AppointmentFile`, `AppointmentReschedule`
- **Prescription** belongsTo `Appointment`, hasMany `PrescriptionItem`, `PharmacyMedicine`
- **Specialization** hasMany `Symptom` (many-to-many), hasMany `Staff`
- **Conversation** hasMany `ConversationParticipant`, `Message`
- **Article** belongsTo `Category`, hasMany `Tag` (many-to-many), `Comment`

---

## Authentication

Authentication is handled by **Laravel Fortify** with **Laravel Sanctum** for API token management.

### How It Works

- **Registration** — Users register via Fortify's registration feature (prefixed under `/api`)
- **Login** — Session-based authentication via the `web` guard
- **API Tokens** — Sanctum personal access tokens for SPA and third-party API access
- **Email Verification** — Custom SPA-friendly verification with resend throttle (6 per minute)
- **Password Reset** — Fortify-managed password reset flow
- **Two-Factor Authentication** — Optional 2FA with confirmation and recovery codes
- **Social Login** — Provider-based authentication (Google configured)

### Guards

- `web` — Session-based guard using Eloquent user provider

### Middleware

- `auth:sanctum` — Protects authenticated API routes
- `dashboard.access:admin` — Admin dashboard access
- `dashboard.access:facility` — Facility owner dashboard access
- `dashboard.access:doctor` — Doctor dashboard access
- `dashboard.access:patient` — Patient dashboard access
- `permission:*` — Granular permission checks

---

## Roles & Permissions

### Detected Roles

| Role | Slug | Description |
|---|---|---|
| Super Admin | `super_admin` | Full platform access and administration |
| Facility Admin | `facility_admin` | Facility-level management |
| Doctor | `doctor_portal_user` | Medical staff with schedule and patient access |
| Patient | `patient` | End-user seeking healthcare services |

### Permission Modules

The system defines **80+ granular permissions** across 16 resource modules:

| Module | Permissions |
|---|---|
| Dashboard | `view_dashboard_statistics` |
| Users | `view_users`, `show_user`, `create_user`, `update_user`, `delete_user` |
| Roles | `view_roles`, `show_role`, `create_role`, `update_role`, `delete_role` |
| Permissions | `view_permissions`, `show_permission`, `create_permission`, `update_permission`, `delete_permission` |
| Organizations | `view_organizations`, `show_organization`, `create_organization`, `update_organization`, `delete_organization` |
| Facilities | `view_facilities`, `show_facility`, `create_facility`, `update_facility`, `delete_facility` |
| Departments | `view_departments`, `show_department`, `create_department`, `update_department`, `delete_department` |
| Staff | `view_staff`, `show_staff`, `create_staff`, `update_staff`, `delete_staff` |
| Patients | `view_patients`, `show_patient`, `create_patient`, `update_patient`, `delete_patient` |
| Appointments | `view_appointments`, `show_appointment`, `create_appointment`, `update_appointment`, `cancel_appointment`, `delete_appointment` |
| Prescriptions | `view_prescriptions`, `show_prescription`, `create_prescription`, `update_prescription`, `delete_prescription` |
| Medication Requests | `view_medication_requests`, `show_medication_request`, `create_medication_request`, `update_medication_request`, `approve_medication_request`, `reject_medication_request`, `delete_medication_request` |
| Medicines | `view_medicines`, `show_medicine`, `create_medicine`, `update_medicine`, `delete_medicine` |
| Stories | `view_stories`, `show_story`, `create_story`, `update_story`, `delete_story` |
| Articles | `view_articles`, `show_article`, `create_article`, `update_article`, `delete_article` |
| Staff Schedules | `view_staff_schedules`, `create_staff_schedule`, `update_staff_schedule`, `delete_staff_schedule` |
| Staff Unavailabilities | `view_staff_unavailabilities`, `create_staff_unavailability`, `update_staff_unavailability`, `delete_staff_unavailability` |
| Reviews | `view_reviews`, `approve_review`, `reject_review`, `delete_review` |
| Facility Documents | `view_facility_documents`, `upload_facility_document`, `approve_facility_document`, `reject_facility_document`, `delete_facility_document` |
| Facility Images | `view_facility_images`, `upload_facility_image`, `delete_facility_image` |
| Reports | `view_reports`, `export_reports` |
| Notifications | `view_notifications`, `send_notification`, `delete_notification` |
| Contact Messages | `view_contact_messages`, `show_contact_message`, `delete_contact_message` |
| Activity Logs | `view_activity_logs` |
| Profile | `view_profile`, `update_profile` |
| Settings | `view_settings`, `update_settings` |
| Medical Records | `view_medical_records`, `show_medical_record`, `create_medical_record`, `update_medical_record`, `delete_medical_record` |

### Permission Management

Permissions are defined in `config/permissions.php` and seeded via `PermissionSeeder` and `RolePermissionSeeder`. Each permission has bilingual names (`en`, `ar`) and is assignable to roles through the `role_permission` pivot table.

---

## API

All API endpoints are prefixed with `/api` and return JSON responses. Authentication is via Sanctum bearer tokens.

### Public Routes (No Authentication Required)

| Module | Endpoints |
|---|---|
| **Contact** | `POST /contact-us` |
| **Cities** | `GET /cities/list` |
| **Categories** | `GET /categories/{type}` |
| **Tags** | `GET /tags` |
| **Doctors** | `GET /doctors`, `GET /doctors/{facility}/{staff}/available-days`, `GET /doctors/{facility}/{staff}/available-slots`, `GET /doctors/{staff}/facilities`, `GET /doctors/{staff}` |
| **Facilities** | `GET /facilities`, `GET /facilities/{facility}` |
| **Facility Reviews** | `GET /facilities/{facility}/reviews` |
| **Articles** | `GET /articles`, `GET /articles/{article}`, `GET /articles/{article}/comments` |
| **Stories** | `GET /stories`, `GET /stories/{story}` |
| **Job Posts** | `GET /job-posts`, `GET /job-posts/{slug}` |
| **Pages** | `GET /pages/{slug}` |
| **Home** | `GET /home` |
| **Appointments** | `GET /appointments`, `POST /appointments` |
| **Platform Reviews** | `GET /public/platform-reviews` |
| **Donations** | `GET /donations`, `GET /donations/status` |
| **Payments** | `POST /payments/stripe/checkout` |
| **Webhooks** | `POST /webhooks/stripe` |
| **Subscriptions** | `POST /public/subscriptions`, `GET /public/subscriptions/verify/{token}`, `PATCH /public/subscriptions/{token}` |

### Authenticated Shared Routes

| Module | Endpoints |
|---|---|
| **Profile** | `GET /profile`, `PUT /profile` |
| **Email Verification** | `GET /email/verify/{id}/{hash}`, `POST /email/verification-notification` |
| **Favorites** | `GET /favorites`, `POST /favorites` (toggle), `DELETE /favorites/{favorite}` |
| **Search History** | `GET /search-histories`, `POST /search-histories`, `DELETE /search-histories` |
| **Platform Reviews** | `GET /platform-review`, `POST /platform-review`, `PUT /platform-review`, `DELETE /platform-review` |
| **Conversations** | `GET /conversations`, `POST /conversations`, `POST /conversations/find-or-create`, `GET /conversations/{conversation}`, `POST /conversations/{conversation}/messages`, `POST /conversations/{conversation}/read` |
| **Notifications** | `GET /notifications`, `GET /notifications/unread`, `GET /notifications/count`, `PATCH /notifications/{notification}/read`, `PATCH /notifications/read-all`, `DELETE /notifications/{notification}`, `DELETE /notifications` |
| **Medicines** | `GET /medicines`, `GET /medicines/lookup`, `GET /medicines/{medicine}` |
| **Workspace** | `POST /set-active-workspace/{facility}` |

### Staff Routes

| Module | Endpoints |
|---|---|
| **Articles** | Full CRUD at `/staff/articles` |
| **Facilities** | `GET /staff/facilities` |
| **Symptoms** | `GET /staff/symptoms` |
| **Schedules** | CRUD at `/facilities/{facility}/schedules` |
| **Unavailabilities** | CRUD at `/facilities/{facility}/unavailabilities` |

### Patient Routes

| Module | Endpoints |
|---|---|
| **Stories** | `GET /patient/stories`, `POST /patient/stories`, `PUT /patient/stories/{story}` |
| **Story Donations** | `GET /story/{story}/donations`, `POST /story/{story}/donations`, `GET /story/{story}/donations/stats` |
| **AI Consultation** | `GET /patient/ai/conversations`, `POST /patient/ai/conversations`, `GET /patient/ai/conversations/{uuid}`, `POST /patient/ai/conversations/{uuid}/messages`, `POST /patient/ai/conversations/{uuid}/recommend-doctor` |
| **Prescriptions** | `GET /patient/prescriptions`, `GET /patient/prescriptions/{prescription}`, `POST /patient/prescriptions/{prescription}/select-pharmacy`, `GET /patient/prescriptions/{prescription}/pharmacies` |
| **Medication Requests** | `GET /patient/medication-requests`, `GET /patient/medication-requests/{uuid}`, `PATCH /patient/medication-requests/{uuid}/cancel` |

### Admin Dashboard Routes (`/dashboard`)

| Module | Endpoints |
|---|---|
| **Overview** | `GET /dashboard/dashboard` |
| **Reports** | `GET /dashboard/reports`, `GET /dashboard/reports/export/excel`, `GET /dashboard/reports/export/pdf` |
| **CRUD Resources** | Full API resources for organizations, facilities, departments, users, staff, patients, roles, permissions, categories, tags, articles, schedules, platform-reviews, positions, medicines |
| **Appointments** | Full CRUD + stats, calendar, analytics |
| **Stories** | Index, trash, stats, show, status update, delete, restore, force delete |
| **Symptoms** | Full CRUD + stats |
| **Specializations** | Full CRUD + symptom sync |
| **Conversations** | Index, stats, show, archive, lock |
| **Search Histories** | Index, trending |
| **Donations** | Index, show |
| **Payments** | Index |
| **Invoices** | Index |
| **AI Assistant** | `POST /dashboard/ai/ask`, conversations CRUD |
| **Audit Logs** | `GET /dashboard/audit-logs` |
| **User Management** | Trashed users, toggle status, restore |
| **Contact Messages** | Admin index, show, status update |

### Facility Dashboard Routes (`/facility/{facility}/dashboard`)

| Module | Endpoints |
|---|---|
| **Overview** | `GET /facility/{facility}/dashboard/` |
| **Alerts** | `GET /facility/{facility}/dashboard/alerts` |
| **Analytics** | `GET /facility/{facility}/dashboard/analytics` |
| **Live Appointments** | `GET /facility/{facility}/dashboard/appointments/live` |
| **Doctor Performance** | `GET /facility/{facility}/dashboard/doctors-performance` |
| **Patients** | `GET /facility/{facility}/dashboard/patients` |
| **Schedules** | `GET /facility/{facility}/dashboard/schedules` |
| **Reports** | `GET /facility/reports`, export Excel/PDF |

### Facility Owner Routes (`/facility`)

| Module | Endpoints |
|---|---|
| **Patients** | `GET /facility/patients`, `GET /facility/patients/{patient}` |
| **Reviews** | `GET /facility/reviews`, approve, reject |
| **Symptoms** | `GET /facility/symptoms` |
| **Staff** | CRUD at `/facility.staff` |
| **Departments** | CRUD at `/facility.departments` |
| **Staff Schedules** | CRUD at `/facility/{facility}/staff-schedules` |
| **Staff Unavailabilities** | Index, show, approve, reject |
| **Appointments** | Full CRUD + stats, lookup, medication requests |
| **Medicine** | CRUD + lookup, stats |
| **Prescriptions** | Index, store, show |
| **Users** | `GET /facility/{facility}/users` |
| **Notifications** | `GET /facility/notifications` |
| **Job Posts** | Scoped job post management |

---

## Frontend

The frontend is a **single-page application (SPA)** built as a separate Vue.js application that communicates with the Laravel API backend.

### Layouts

- Blade email layouts (`resources/views/components/email/`) for transactional emails
- Welcome page (`resources/views/welcome.blade.php`)

### Pages

The SPA frontend is not included in this repository (served externally). The API supports the following dashboard views:

- **Super Admin Dashboard** — Overview, analytics, reports (Excel/PDF export)
- **Facility Dashboard** — Overview, alerts, analytics, live appointments, doctor performance, patients, schedules
- **Staff Portal** — Articles, schedules, symptoms, facilities, workspace switching
- **Patient Portal** — Stories, AI consultation, prescriptions, medication requests

### Email Components

Blade components for email templates (`resources/views/components/email/`):
- `layout` — Base email layout
- `header` — Email header
- `card` — Content card
- `heading` / `subheading` — Typography
- `accent-line` — Decorative divider
- `divider` — Section divider
- `cta-button` — Call-to-action button
- `message` — Body text

### Real-Time Client

- `resources/js/echo.js` — Laravel Echo configuration with Pusher for real-time broadcasting

### State Management

The frontend SPA handles its own state management. The backend provides:
- Sanctum token-based authentication
- JSON API responses via API Resources
- Real-time events via broadcasting

---

## AI Features

### Providers

| Provider | Model | Purpose |
|---|---|---|
| OpenAI | GPT-4o | Default AI provider |
| Anthropic | Claude 3.5 Sonnet | Alternative AI provider |

Configured in `config/ai.php`. Switch via the `AI_PROVIDER` environment variable.

### AI Agents

| Agent | Purpose |
|---|---|
| `PatientHealthAssistant` | Guides patients through structured triage conversations; asks progressive questions; never diagnoses or prescribes |
| `MedicalTriageAgent` | Analyzes completed conversations and returns specialty match, urgency level, confidence score, and extracted symptoms |
| `HESAssistant` | Admin dashboard assistant for querying platform data (users, facilities, donations, reports) |
| `SeoAgent` | Generates SEO metadata (title, description, keywords, summary) for articles |
| `WriterAgent` | AI content writing assistance for blog posts |

### AI Tool System

Function-calling tools are registered in `composer.json` autoload files and available to agents:

| Tool | Description |
|---|---|
| `get_doctors_by_specialty` | Query doctors filtered by specialty, profession, facility, or experience |
| `search_specialties` | Search specializations by symptoms or name |
| `get_nearby_facilities` | Find facilities by geographic coordinates with distance calculation |
| `get_users` | Query users by role, status, or search term |
| `get_facilities` | Query facilities by type, city, or name |
| `get_donations` | Query donation records by story, donor, or date range |
| `get_reports` | Generate platform analytics (summary, users, facilities, appointments, revenue) |

### Medical Triage Flow

1. Patient starts an AI conversation via `POST /patient/ai/conversations`
2. `PatientHealthAssistant` guides the conversation, asking progressive questions
3. `MedicalTriageService` manages context, token limits, and conversation summaries
4. When enough messages are collected (configurable), patient can request doctor recommendation
5. `MedicalTriageAgent` analyzes the conversation and returns triage results
6. `DoctorRecommendationService` and `SpecialtyMatcherService` find matching doctors
7. `DoctorRecommendationResource` returns the recommendation to the patient

### Conversation Management

- Configurable max messages per conversation (default: 40)
- Context compression via AI summary when token limits are reached
- Configurable max context tokens (default: 4000) and max total tokens (default: 32000)
- Recent messages preserved during context compression (default: 15)
- Triage confidence threshold for recommendation (default: 0.5)

### Safety Considerations

The AI agents enforce strict safety rules:
- **Never diagnose** — Agents never make diagnostic claims
- **Never prescribe** — No medication recommendations
- **Never treat** — No treatment plans suggested
- **No external referrals** — Only recommends doctors within the platform database
- **Confidence-gated recommendations** — Doctor recommendations require minimum confidence scores

---

## Notifications

### Notification Matrix

The platform uses a configurable **notification matrix** (`config/notification-matrix.php`) that defines which channels to use for each event type and recipient role:

| Channel | Implementation |
|---|---|
| `database` | Laravel database notifications |
| `mail` | Email via Mailable classes |
| `broadcast` | Real-time via Pusher |
| `sms` | Twilio SMS via custom `TwilioSmsChannel` |

### Notification Events

43 listeners handle notification dispatch across these event categories:

- **Authentication** — Registration, login, email verification, password reset
- **Appointments** — Created, confirmed, cancelled, completed, rescheduled, reminders
- **Doctors/Staff** — Approved, rejected, reviewed, assigned, unassigned, unavailability
- **Patients** — Review replies, prescriptions, medication requests
- **Facilities** — Registered, approved, rejected, suspended, reviewed
- **Content** — Article created/approved/rejected, comments, stories
- **Donations & Payments** — Created, made, completed, processed, failed, invoices
- **Jobs** — Posted, approved, rejected
- **AI** — Prompted, conversation completed, recommendation available
- **System** — Subscriptions, maintenance, broadcasts

### Email Mailables

| Mailable | Purpose |
|---|---|
| `ContactMessageConfirmation` | Contact form confirmation |
| `ContentPublishedMail` | Content publication notification |
| `ReviewAdminReplyMail` | Admin reply to review notification |
| `ReviewSubmittedMail` | Review submission notification |
| `SubscriptionVerificationMail` | Email subscription verification |

### User Preferences

Users can configure notification preferences per channel:
- `email_notifications` — Boolean
- `push_notifications` — Boolean
- `sms_notifications` — Boolean

---

## File Storage

- **Default** — Local filesystem (`storage/app/public`)
- **AWS S3** — Configured via `AWS_*` environment variables
- **Public access** — Via `storage:link` artisan command

### Stored Files

| Type | Location |
|---|---|
| Facility images | Uploaded via `FacilityImage` model |
| Facility documents | Uploaded via `FacilityDocument` model |
| Appointment files | Uploaded via `AppointmentFile` model |
| Story cover images | `cover_image` field on stories |
| Job post cover images | `cover_image` field on job posts |
| Facility cover images | `cover_image` field on facilities |

---

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run with config clear
composer test

# Run specific test file
php artisan test --filter=MedicineTest

# Run feature tests only
php artisan test --testsuite=Feature
```

### Test Coverage

Tests are located in `tests/Feature/` and `tests/Unit/`:

- `AdminDashboardAnalyticsTest.php`
- `DashboardRedirectionTest.php`
- `FacilityPortalTest.php`
- `MedicineTest.php`
- `NotificationTest.php`
- `SubscriptionTest.php`
- `Dashboard/` — Dashboard-specific tests
- `Patient/` — Patient-specific tests
- `Staff/` — Staff-specific tests

### Test Framework

- **PHPUnit** 12.5+ (configured in `phpunit.xml`)
- **Mockery** for mocking
- **Faker** for test data generation
- **33 model factories** available for testing

---

## Development

### Artisan Commands

```bash
# Development server
php artisan serve

# Queue worker
php artisan queue:listen --tries=1 --timeout=0

# Log viewer
php artisan pail

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

# Generate key
php artisan key:generate

# Storage link
php artisan storage:link

# Tinker (REPL)
php artisan tinker

# List all routes
php artisan route:list
```

### NPM Commands

```bash
# Development server (Vite)
npm run dev

# Production build
npm run build
```

### Queue Commands

```bash
# Listen to default queue
php artisan queue:listen

# Process failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush

# Get queue info
php artisan queue:info
```

### Concurrent Development

```bash
# Run all services at once (server, queue, logs, vite)
composer dev
```

This uses `concurrently` to run:
- `php artisan serve` (HTTP server)
- `php artisan queue:listen` (Queue worker)
- `php artisan pail` (Log viewer)
- `npm run dev` (Vite dev server)

---

## Deployment

### Production Optimization

```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize
php artisan optimize

# Run migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Build frontend assets
npm ci
npm run build
```

### Queue & Scheduler

```bash
# Start queue worker (supervisor recommended)
php artisan queue:work --sleep=3 --tries=3

# Set up scheduler (add to crontab)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Environment Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated
- [ ] Database configured and migrated
- [ ] Queue driver configured
- [ ] Cache driver configured
- [ ] Mail driver configured
- [ ] Pusher credentials set (if using broadcasting)
- [ ] Stripe keys set (if using payments)
- [ ] Storage link created
- [ ] Config cached
- [ ] Routes cached
- [ ] Views cached

---

## Security

### Measures Implemented

| Measure | Implementation |
|---|---|
| Environment Variables | Secrets stored in `.env`, never committed |
| API Authentication | Laravel Sanctum token-based authentication |
| Input Validation | Form Request classes for all API endpoints |
| Authorization | Policies (4) + Permission middleware (80+ permissions) |
| CSRF Protection | Laravel's built-in CSRF token validation |
| Rate Limiting | Throttle middleware on email verification (6/minute) |
| Password Hashing | Bcrypt with configurable rounds (default: 12) |
| Email Verification | Required for all users (`MustVerifyEmail`) |
| Two-Factor Auth | Optional 2FA via Fortify |
| Audit Logging | Business events and model changes logged |
| SQL Injection Prevention | Eloquent ORM with parameterized queries |
| XSS Prevention | Blade template escaping + API-only responses |
| Soft Deletes | User and story soft deletes for data recovery |
| UUID Identifiers | Public-facing resources use UUIDs instead of sequential IDs |

### API Security

- Sanctum bearer token authentication on all protected routes
- CORS configured via `config/cors.php`
- Stateful domains configured for SPA authentication
- Token prefix support for GitHub secret scanning

---

## Performance

### Implemented Optimizations

- **Eager Loading** — Relationships loaded efficiently in services and controllers
- **API Resources** — Consistent response transformation avoiding over-fetching
- **Database Caching** — Configurable cache driver (database, Redis)
- **Queue System** — Background job processing for notifications and heavy operations
- **Lazy Collection** — Used where appropriate for large dataset processing
- **Index Optimization** — Database indexes on frequently queried columns
- **Vite Code Splitting** — Frontend assets optimized via Vite build
- **Query Scope Optimization** — Scopes on models for common query patterns
- **Pagination** — All list endpoints paginated to prevent large result sets
- **Config/Route Caching** — Production caching commands available

---

## Contributing

### Guidelines

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow PSR-12 coding standards
- Use `declare(strict_types=1)` in all PHP files
- Write tests for new features
- Update documentation for API changes

### Development Setup

```bash
composer setup
composer dev
```

---

## License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## Author

**HES Connect** — Built by the HES development team.

---

## Screenshots

> Screenshots will be added as the UI is finalized.

| Page | Preview |
|---|---|
| Home | `docs/screenshots/home.png` |
| Dashboard | `docs/screenshots/dashboard.png` |
| Login | `docs/screenshots/login.png` |
| AI Consultation | `docs/screenshots/ai-consultation.png` |
| Facility Management | `docs/screenshots/facility.png` |
| Appointment Calendar | `docs/screenshots/calendar.png` |

---

## Future Improvements

Based on the current implementation, logical enhancements include:

### Short-term
- [ ] Dedicated Vue.js SPA frontend (currently API-only)
- [ ] WebSocket-based real-time chat interface
- [ ] Push notification support (Firebase Cloud Messaging)
- [ ] Advanced search with Meilisearch integration
- [ ] Multi-language support expansion (additional locales beyond en/ar)
- [ ] Rate limiting on more API endpoints

### Medium-term
- [ ] Telehealth video consultation integration
- [ ] Electronic health records (EHR) module
- [ ] Lab results management
- [ ] Insurance integration
- [ ] Mobile application (React Native / Flutter)
- [ ] Advanced analytics dashboard with charts

### Long-term
- [ ] Multi-tenant architecture for white-label deployments
- [ ] Healthcare compliance (HIPAA, GDPR) audit tools
- [ ] Interoperability with HL7 FHIR standards
- [ ] AI-powered diagnostic assistance (with appropriate medical disclaimers)
- [ ] Automated appointment scheduling optimization
- [ ] Patient health tracking and wearable device integration
