# Eventilize

## Project Overview

Eventilize is a centralized UTM event gathering system designed to solve the problem of fragmented event information across multiple communication channels such as Telegram and WhatsApp.

The platform provides a single location for students to discover, search, bookmark and stay updated with campus events while enabling event organizers to publish events and monitor engagement through analytics.

Eventilize aims to improve event accessibility, visibility and participation within the Universiti Teknologi Malaysia (UTM) community.

---

## Project Objectives

### 1. Centralize Campus Event Information

Provide a unified platform where students can easily discover and access all campus events without relying on multiple messaging groups.

### 2. Improve Event Discovery

Allow users to search, filter and browse events efficiently based on category, date, location and organizer.

### 3. Enhance User Engagement

Enable personalized experiences through event bookmarking, notifications and recommendation features.

### 4. Support Event Organizers

Provide organizers with tools to create, manage and promote events while monitoring event performance through analytics.

### 5. Maintain Platform Quality

Allow administrators to review, approve and manage event submissions to ensure platform reliability and content quality.

---

## Key Features

### Student Features
- User Registration and Login
- Event Discovery
- Search and Filtering
- Event Bookmarking
- Event Registration
- Event Notifications
- Event Recommendations
- Event Calendar
- User Profile

### Organizer Features
- Event Creation
- Event Management
- Event Update and Delete
- Event Analytics
- Event Poster Upload
- Organizer Profile

### Administrator Features
- Event Approval Workflow
- Event Management
- Category Management
- Notification Management
- Admin Profile

---

## Current Development Progress

### Completed
- Database schema for users, categories, events, bookmarks, registrations, notifications, notification settings and analytics.
- Backend setup script with database connection status, schema compatibility checks and repeatable sample data seeding.
- REST API foundation using PHP Slim controllers, models, routes and middleware.
- Event CRUD API:
  - `GET /api/events`
  - `GET /api/events/{id}`
  - `POST /api/events`
  - `PUT /api/events/{id}`
  - `DELETE /api/events/{id}`
  - `PATCH /api/events/{id}/status`
- Frontend event data is retrieved from the backend instead of static hardcoded event lists.
- Organizer event creation/editing/deletion is connected to backend APIs.
- Admin approval/rejection flow is connected to backend APIs.
- Student bookmark and registration flows are connected to backend APIs.
- Seeded demo accounts and sample data are available for testing.

### In Progress / Next
- JWT protected access and stricter role-based API authorization.
- Additional production hardening for file uploads and deployment configuration.

---

## Technology Stack

### Frontend
- Vue.js
- Vue Router
- Axios
- HTML5
- CSS3
- JavaScript (ES6)

### Backend
- PHP Slim Framework
- RESTful API
- JSON Web Token (JWT)
- PDO

### Database
- MySQL
- `schema.sql`
- `seed.sql`

### Development Tools
- Git
- GitHub
- Visual Studio Code
- Composer
- npm

---

## Project Structure

```text
eventilize/
│
├── frontend/
│   ├── src/
│   ├── public/
│   └── package.json
│
├── backend/
│   ├── public/
│   ├── src/
│   ├── database/
│   ├── composer.json
│   └── .env
│
├── docs/
│
└── README.md
```

---

## Project Setup

### Clone Repository

```bash
git clone <repository-url>
cd eventilize
```

---

### Frontend Setup

```bash
cd frontend

npm install

npm run dev
```

Frontend will run on:

```text
http://localhost:5173
```

---

### Backend Setup

```bash
cd backend

composer install
```

Create environment file:

```bash
cp .env.example .env
```

Configure database credentials inside `.env`.

Create/update the database and execute `database/seed.sql` before starting the backend:

```bash
php scripts/setup-database.php
```

This step creates the schema if needed, syncs sample data, and prints database/seed status in the terminal.

Start PHP server after the seed step succeeds:

```bash
php -S localhost:8000 -t public
```

Backend API will run on:

```text
http://localhost:8000
```

When the backend starts and receives its first request, it also runs the startup database setup once for that backend process and prints setup status to the terminal/error log.

---

### Demo Accounts

The seed step creates these demo accounts:

| Role | Email | Password |
|--------|--------|--------|
| Student | student@utm.my | student123 |
| Organizer | organizer@utm.my | organizer123 |
| Admin | admin@utm.my | admin1234 |

---

### Main REST API Endpoints

#### Auth
```text
POST /api/auth/register
POST /api/auth/login
```

#### Events
```text
GET    /api/events
GET    /api/events/{id}
POST   /api/events
PUT    /api/events/{id}
DELETE /api/events/{id}
PATCH  /api/events/{id}/status
POST   /api/events/{id}/views
POST   /api/events/{id}/bookmark
```

#### Registrations
```text
GET  /api/users/{userId}/registrations
GET  /api/users/{userId}/registrations/{eventId}
POST /api/events/{eventId}/registrations
```

#### Notifications
```text
GET   /api/users/{userId}/notifications
PATCH /api/notifications/{id}/read
PATCH /api/users/{userId}/notifications/read-all
GET   /api/users/{userId}/notification-settings
PUT   /api/users/{userId}/notification-settings
```

#### Dashboard / Reference Data
```text
GET /api/admin/dashboard
GET /api/categories
GET /api/health
GET /api/test-db
```

---

## Demo Flow

### Student
1. Login with `student@utm.my / student123`.
2. Browse events from `/student/events`.
3. Bookmark an event and confirm it appears in `/student/bookmarks`.
4. Register for an approved event and confirm the button changes to `Registered`.

### Organizer
1. Login with `organizer@utm.my / organizer123`.
2. Create an event from `/organizer/events/create`.
3. View, edit or delete organizer-owned events from `/organizer/events`.

### Admin
1. Login with `admin@utm.my / admin1234`.
2. Review pending events from `/admin/approvals`.
3. Approve or reject events with a rejection reason.
4. Manage all events from `/admin/events`.

---

## Team Members

| Name | Role |
|--------|--------|
| Tahnussri A/P Morthy | Database & Security |
| Sathishwar Rao A/L Mahendra Rao | Project Manager / QA |
| Chew Chuan Kai | Backend Developer |
| Julker Nayeen | Frontend Developer |

---

## Course Information

**Course:** SECJ3483 Web Technology  
**Session:** Semester II 2025/2026  
**Institution:** Universiti Teknologi Malaysia (UTM)

---

## Team Motto

**Discover. Connect. Experience.**
