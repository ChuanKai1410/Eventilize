# Eventilize 🎓

**Discover. Connect. Experience.**

Eventilize is a centralized UTM campus event platform built for **SECJ3483 Web Technology**. It helps students discover campus events in one place, while organizers can publish events and administrators can review event submissions.

---

## 🚀 Project Overview

Campus event information is often scattered across Telegram, WhatsApp, posters, and informal announcements. Eventilize brings those events into one integrated web application where:

- 🎒 **Students** can discover, search, bookmark, register, and receive notifications for events.
- 🧑‍💼 **Organizers** can create, update, delete, and monitor their submitted events.
- 🛡️ **Admins** can approve, reject, and manage platform events.

The application is implemented as a full-stack SPA using **Vue.js**, **PHP Slim**, **MySQL**, **PDO**, and **JWT-protected API access**.

---

## ✨ Key Features

### 🎒 Student
- Register and login
- Browse approved campus events
- Search and filter events
- View event details
- Bookmark events
- Register for approved events
- View registered events and notifications
- Use an event calendar and recommendation section

### 🧑‍💼 Organizer
- Create events as draft or pending submission
- Upload event pictures and posters
- Edit and delete organizer-owned events
- View event status, views, and bookmarks
- Track event engagement from the organizer dashboard

### 🛡️ Admin
- View platform statistics
- Review pending event submissions
- Approve or reject events with rejection reasons
- Manage all platform events
- Access protected admin-only routes

---

## 🧱 Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Vue.js, Vue Router, Axios, HTML, CSS, JavaScript |
| Backend | PHP Slim Framework, REST API, PDO |
| Authentication | JWT |
| Database | MySQL |
| Tooling | Composer, npm, Vite, Git |

---

## 📁 Project Structure

```text
Eventilize/
├── backend/
│   ├── public/              # PHP Slim entry point
│   ├── src/
│   │   ├── Config/
│   │   ├── Controllers/
│   │   ├── Helpers/
│   │   ├── Middleware/
│   │   ├── Models/
│   │   └── Routes/
│   ├── database/            # schema.sql, seed.sql, ERD
│   ├── scripts/
│   └── composer.json
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── router/
│   │   ├── services/
│   │   └── views/
│   └── package.json
├── docs/
└── README.md
```

---

## ⚙️ Setup

### 1. Backend Setup 🧩

```bash
cd backend
composer install
```

Create your environment file:

```bash
cp .env.example .env
```

Configure database settings in `.env`:

```text
DB_HOST=localhost
DB_PORT=3306
DB_NAME=eventilize
DB_USER=root
DB_PASS=
JWT_SECRET=change_this_secret_key_for_eventilize_demo_2026
```

Create/update the database and seed demo data:

```bash
php scripts/setup-database.php
```

Start the backend server:

```bash
php -S localhost:8000 -t public
```

Backend API:

```text
http://localhost:8000
```

### 2. Frontend Setup 🎨

```bash
cd frontend
npm install
npm run dev
```

Frontend app:

```text
http://localhost:5173
```

---

## 🔐 Demo Accounts

| Role | Email | Password |
|---|---|---|
| Student | `student@utm.my` | `student123` |
| Organizer | `organizer@utm.my` | `organizer123` |
| Admin | `admin@utm.my` | `admin1234` |

---

## 🔌 Main REST API Endpoints

### Auth

```text
POST /api/auth/register
POST /api/auth/login
GET  /api/users/{id}
```

`login` and `register` return a JWT token. Protected endpoints require:

```text
Authorization: Bearer <token>
```

### Events

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

### Registrations

```text
GET    /api/users/{userId}/registrations
GET    /api/users/{userId}/registrations/{eventId}
POST   /api/events/{eventId}/registrations
DELETE /api/events/{eventId}/registrations
```

### Notifications

```text
GET   /api/users/{userId}/notifications
PATCH /api/notifications/{id}/read
PATCH /api/users/{userId}/notifications/read-all
GET   /api/users/{userId}/notification-settings
PUT   /api/users/{userId}/notification-settings
```

### Dashboard / Reference Data

```text
GET /api/admin/dashboard
GET /api/categories
GET /api/health
GET /api/test-db
```

---

## 🛡️ Protected Access

Eventilize uses JWT middleware to protect role-sensitive backend routes.

| Access | Protected Actions |
|---|---|
| Student | bookmark events, register for events, view notifications |
| Organizer | create, update, and delete owned events |
| Admin | approve/reject events, view admin dashboard, manage events |

The frontend also uses Vue Router guards to redirect users away from pages outside their role.

---

## 🗄️ Database Design

The MySQL schema includes related entities that support the implemented features:

- `users`
- `categories`
- `events`
- `bookmarks`
- `event_registrations`
- `notifications`
- `notification_settings`
- `event_analytics`

Schema and seed files:

```text
backend/database/schema.sql
backend/database/seed.sql
backend/database/ERD.md
```

---

## 🎤 Recommended Demo Flow

1. 🧑‍💼 Login as organizer: `organizer@utm.my / organizer123`
2. Create a new event from `/organizer/events/create`
3. Show the event listed as `Pending`
4. 🛡️ Login as admin: `admin@utm.my / admin1234`
5. Review the event from `/admin/approvals`
6. Approve or reject the event with a reason
7. 🎒 Login as student: `student@utm.my / student123`
8. Browse approved events from `/student/events`
9. Search/filter events
10. View event detail
11. Bookmark the event
12. Register for the approved event
13. Show bookmarked and registered events from the student dashboard

---

## ✅ Validation Commands

Frontend production build:

```bash
cd frontend
npm run build
```

Backend PHP syntax check:

```bash
Get-ChildItem backend\src -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Quick API health check:

```bash
curl http://localhost:8000/api/health
```

---

## 📌 Current Status

Completed:

- ✅ Vue SPA with role-based navigation
- ✅ REST API using PHP Slim
- ✅ MySQL database schema and seed data
- ✅ Event CRUD
- ✅ Admin approval/rejection flow
- ✅ Student bookmark and registration flow
- ✅ Notifications and notification settings
- ✅ JWT login/register token flow
- ✅ Protected API access by role
- ✅ Frontend production build verified

Possible future improvements:

- 📦 Move image storage from base64 database fields to file/object storage
- 🌍 Deploy to a live public server
- 📊 Replace placeholder analytics chart with real time-series data
- 🧪 Add automated backend and frontend tests

---

## 👥 Team Members

| Name | Role |
|---|---|
| Tahnussri A/P Morthy | Database & Security |
| Sathishwar Rao A/L Mahendra Rao | Project Manager / QA |
| Chew Chuan Kai | Backend Developer |
| Julker Nayeen | Frontend Developer |

---

## 📚 Course Information

**Course:** SECJ3483 Web Technology  
**Session:** Semester II 2025/2026  
**Institution:** Universiti Teknologi Malaysia (UTM)
