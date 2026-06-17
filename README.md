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
- Event Notifications
- Event Recommendations
- Event Calendar
- User Profile

### Organizer Features
- Event Creation
- Event Management
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

### Database
- MySQL
- PDO

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

---

### Demo Accounts

The seed step creates these demo accounts:

| Role | Email | Password |
|--------|--------|--------|
| Student | student@utm.my | student123 |
| Organizer | organizer@utm.my | organizer123 |
| Admin | admin@utm.my | admin1234 |

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
