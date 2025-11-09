# HabitRact Supply Management

A PHP + MySQL web application for managing food, milk, and newspaper deliveries for multiple suppliers. The system supports four user roles (super admin, supplier admin, supplier staff, and supplier driver) with a Tailwind CSS responsive UI.

## Features

- Super admin portal to manage suppliers and supplier admins.
- Supplier admin tools to manage staff, drivers, customers, and delivery schedules.
- Delivery scheduling for food, milk, and newspapers across customer routes.
- Role-based access control with a horizontal navigation bar and responsive layout.

## Tech Stack

- PHP 8+
- MySQL 5.7+
- Tailwind CSS 3

## Getting Started

1. Create the database and import the schema:

   ```sql
   CREATE DATABASE habitract_supply CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE habitract_supply;
   SOURCE sql/schema.sql;
   ```

2. Update `config/config.php` with your MySQL credentials and the `base_url` that matches the folder where the app is hosted (e.g. `/app`).

3. Serve the application with PHP's built-in server:

   ```bash
   php -S localhost:8000 -t public
   ```

4. Sign in with the seeded credentials:

   - Super Admin: `superadmin` / `superadmin123`
   - Supplier Admin: `supplieradmin` / `admin123`

## Folder Structure

- `public/` – Web root containing entry points and static assets.
- `includes/` – Shared PHP includes (layout, helpers, database).
- `config/` – Application configuration.
- `sql/` – Database schema and seed data.
- `assets/` – Logos and placeholder avatars.

## Security Notes

- Change the default passwords immediately in production.
- Configure HTTPS when deploying publicly.
- Harden session settings and consider using a mature framework for production systems.
