# mini-sales-stock-erp

A lightweight Sales &amp; Stock ERP system for managing products, inventory, sales, purchases, customers, suppliers, and essential business operations. Built as a simple, practical, and extensible ERP solution for small businesses.

## Requirements

- PHP >= 7.4 (tested on PHP 8.x)
- MySQL / MariaDB
- Composer (optional, not required to run)
- Node.js >= 18 (only needed to rebuild the Tailwind CSS)

## Setup

1. **Create the database** (see [application/database/schema.sql](application/database/schema.sql)):

   ```bash
   mysql -u root -p < application/database/schema.sql
   ```

   The schema creates the `mini_sales_stock_erp` database and the `users` table used by authentication.

2. **Configure the database connection** in `application/config/database.php` (host, username, password, database name).

3. **Set your base URL** in `application/config/config.php` (`$config['base_url']`).

4. **Serve the application** — e.g. with PHP's built-in server:

   ```bash
   php -S localhost:8000
   ```

   or point Apache/nginx at the project root (`index.php`).

5. **Build the CSS** (only needed after changing view markup):

   ```bash
   npm install
   npm run build:css        # production build -> assets/css/app.css
   npm run watch:css        # watch mode while developing
   ```

   The compiled `assets/css/app.css` (Tailwind CSS v4) is committed, so the
   application runs without Node in production.

## Authentication

Username + password + session-based authentication (no email required).

| Route          | Description                                          |
| -------------- | ---------------------------------------------------- |
| `/auth/signup` | Create an account (auto signs you in)                |
| `/auth/signin` | Sign in                                              |
| `/auth/signout`| Sign out (POST only, CSRF protected)                 |
| `/dashboard`   | Example protected page (requires authentication)     |

- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored in plain text.
- CSRF protection is enabled for all forms.
- The session ID is regenerated on sign-in and sign-out (session-fixation protection).
- Inactive users (`is_active = 0`) cannot sign in.
- Guest access to protected pages redirects to `/auth/signin` and remembers the
  original destination so the user is sent back after signing in.

### Key files

- `application/controllers/Auth.php` — signup / signin / signout
- `application/controllers/Dashboard.php` — protected route example
- `application/models/User_model.php` — all `users` table access
- `application/libraries/Auth.php` — reusable auth layer (`is_logged_in()`, `current_user()`, `require_login()`, ...)
- `application/views/auth/*` — sign-in / sign-up views with a shared layout
- `application/views/partials/flash_messages.php` — reusable flash-message component

### Protecting a new page

```php
class Reports extends CI_Controller
{
    public function index()
    {
        $this->auth->require_login(); // redirects guests to /auth/signin
        // ...
    }
}
```

## Testing

Manual smoke tests:

1. **Signup** — visit `/auth/signup`, create an account. A duplicate username,
   a short password, and mismatched passwords must each show a validation error.
2. **Signin** — sign out, then sign in with the new credentials. Wrong password
   and unknown usernames must both show "Invalid username or password."
3. **Signout** — use the header button; you should land on `/auth/signin` with a
   success message and `/dashboard` should redirect back to `/auth/signin`.
4. **Inactive user** — set `is_active = 0` for a user in the database and confirm
   they cannot sign in.
5. **Redirect back** — while signed out, visit `/dashboard`, then sign in; you
   should be returned to `/dashboard`.
6. **CSRF** — submit either form without the CSRF token (e.g. via curl) and
   confirm the request is rejected.
