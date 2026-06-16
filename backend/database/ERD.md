# Eventilize Database ERD

## 1. Purpose of Tables

Users:
Stores students, organizers and admin.

Categories:
Stores event types such as Technology and Sports.

Events:
Stores all event information.

Bookmarks:
Stores saved events by users.

Notifications:
Stores system notifications.

Event Analytics:
Stores event engagement data.

---

## 2. Relationships

- One user (organizer) → many events
- One category → many events
- One user → many bookmarks
- One event → many bookmarks
- One user → many notifications
- One event → many notifications
- One event → one analytics record

---

## 3. Normalization

The database is in 3NF:
- No duplicate data
- Each table has a single purpose
- Foreign keys used properly

---

## 4. System Support

Supports:
- Event CRUD (create/read/update/delete)
- Bookmark system
- Notification system
- Event analytics tracking
- JWT authentication (via users table)
- REST API integration (PHP Slim)