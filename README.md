# Task Management System (Bootstrap + PHP + MySQL)

Simple task manager scaffold for learning purposes (HTML, CSS, JS, PHP, MySQL).

Quick setup
1. Ensure XAMPP (or similar) is running (Apache + MySQL).
2. Import database:

   - Open phpMyAdmin and run the SQL in `create_db.sql`, or run from CLI:

```sql
SOURCE create_db.sql;
```

3. (Optional) Seed sample data by visiting in your browser:

```
http://localhost/css-dashboard/api/seed.php
```

The seeder creates two accounts by default:
- admin: `admin@example.com` / `admin123` (role: admin)
- user: `user@example.com` / `user123` (role: user)

Seeded tasks will be linked to those users.

4. Open the app:

```
http://localhost/css-dashboard/
```

API endpoints (JSON)
- `api/tasks.php?action=list` — GET list of tasks
- `api/tasks.php?action=add` — POST JSON to add task
- `api/tasks.php?action=update` — POST JSON to update task
- `api/tasks.php?action=delete` — POST JSON to delete by id
- `api/tasks.php?action=complete` — POST JSON {id} to mark complete

Example curl (add):
```bash
curl -X POST "http://localhost/css-dashboard/api/tasks.php?action=add" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Test task","description":"desc","status":"pending","priority":"high","due_date":"2026-03-01"}'
```

Notes & next steps
- If DB credentials differ, update `api/db.php` (`$dbUser`, `$dbPass`, `$dbName`).
- Add authentication (login) for per-user tasks.
- Add server-side validation and CSRF protections for production.
- Improve UI: pagination, sort, column filters, inline edit.

If you want, I can:
- Add user authentication scaffold (PHP sessions)
- Add task ownership (user_id) and registration/login pages
- Add export/import CSV or PDF
# Image Upload PHP and MYSQL

Just a simple Image Upload PHP and MYSQL
version: 1.0.0

## Full Tutorial

[On YouTube](https://youtu.be/5v1DvTLzMrA)

## Authors

[Elias Abdurrahman](https://github.com/codingWithElias)