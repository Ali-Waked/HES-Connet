# Role-Based Dashboard Redirection

## Overview

When a user authenticates, the backend returns a `dashboard_route` field that tells the frontend which dashboard to navigate to. This is determined by the user's role and is the single source of truth — the frontend never hardcodes role-to-route mappings.

## Architecture

```
User::getDashboardRouteAttribute()  →  /admin/dashboard  (super_admin)
                                        /facility/dashboard  (facility_owner)
                                        /doctor/dashboard  (doctor)
                                        /patient/dashboard  (patient)
                                        /  (fallback)
        │
        ▼
  UserResource::toArray()  includes  'dashboard_route'
        │
        ├── LoginResponse  →  { success, user: { ..., dashboard_route } }
        └── GET /api/profile  →  { user: { ..., dashboard_route } }
```

## Files

| File | Purpose |
|------|---------|
| `app/Models/User.php` | `getDashboardRouteAttribute()` accessor, `hasRole()`, `hasPermission()` |
| `app/Http/Resources/UserResource.php` | Returns `uuid`, `email`, `role`, `profile`, `city`, `dashboard_route` |
| `app/Http/Responses/LoginResponse.php` | Fortify login response — returns `{ success, user }` |
| `app/Http/Responses/LogoutResponse.php` | Fortify logout response — returns `{ success, message }` |
| `app/Http/Controllers/Api/AuthController.php` | `profile()` endpoint |
| `app/Http/Middleware/DashboardAccessMiddleware.php` | Guards per-role route groups |
| `routes/api.php` | Route registration with middleware |
| `bootstrap/app.php` | Middleware alias registration |
| `app/Providers/FortifyServiceProvider.php` | Custom LoginResponse/LogoutResponse binding |

## Route-to-Dashboard Mapping

| Role | `dashboard_route` | Route Group | Middleware |
|------|-------------------|-------------|-----------|
| `super_admin` | `/admin/dashboard` | `/api/admin/*` | `dashboard.access:admin` |
| `facility_owner` | `/facility/dashboard` | `/api/facility/*` | `dashboard.access:facility` |
| `doctor` | `/doctor/dashboard` | `/api/doctor/*` | `dashboard.access:doctor` |
| `patient` | `/patient/dashboard` | `/api/patient/*` | `dashboard.access:patient` |
| *(unknown/null)* | `/` | — | — |

## Middleware Access Rules

`DashboardAccessMiddleware` takes a single parameter (e.g. `admin`, `facility`, `doctor`, `patient`) and maps it to allowed roles. `super_admin` is always allowed.

| Dashboard | Allowed Roles |
|-----------|---------------|
| `admin` | `super_admin` |
| `facility` | `super_admin`, `facility_owner` |
| `doctor` | `super_admin`, `doctor` |
| `patient` | `super_admin`, `patient` |

When a user lacks the required role, the middleware returns a **403 Forbidden** response.

## Auth Flow

### 1. Get CSRF Cookie

```http
GET /sanctum/csrf-cookie
```

### 2. Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "doctor@example.com",
  "password": "password"
}
```

Response:

```json
{
  "success": true,
  "user": {
    "uuid": "...",
    "email": "doctor@example.com",
    "name": {"en": "Dr. Smith", "ar": "د. سميث"},
    "role": {
      "uuid": "...",
      "name": {"en": "doctor", "ar": "طبيب"}
    },
    "profile": { ... },
    "city": { ... },
    "dashboard_route": "/doctor/dashboard"
  }
}
```

### 3. Profile

```http
GET /api/profile
Cookie: session=...
```

Response:

```json
{
  "user": {
    "uuid": "...",
    "email": "...",
    "role": { ... },
    "dashboard_route": "/doctor/dashboard",
    "profile": { ... },
    "city": { ... }
  }
}
```

### 4. Logout

```http
POST /api/logout
Cookie: session=...
```

Response:

```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

## Adding a New Role

1. Add the role in `database/seeders/RolePermissionSeeder.php`
2. Add the route mapping in `User::getDashboardRouteAttribute()`
3. Add the middleware rule in `DashboardAccessMiddleware`
4. Register route stubs in `routes/api.php`

No frontend changes required — the frontend reads `dashboard_route` from the API response.

## Running Tests

```bash
php artisan test tests/Unit/DashboardRedirectionTest.php
php artisan test tests/Unit/DashboardAccessMiddlewareTest.php
php artisan test tests/Feature/DashboardRedirectionTest.php
```
