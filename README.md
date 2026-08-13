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

## Products

Product management (all routes require authentication). Products are
**deactivated** (`is_active = 0`), never physically deleted.

| Route                     | Description                                        |
| ------------------------- | -------------------------------------------------- |
| `/products`               | Paginated listing with search + category filter    |
| `/products/create`        | Add-product form                                   |
| `/products/store`         | Save a new product (POST, CSRF protected)          |
| `/products/edit/{id}`     | Edit-product form                                  |
| `/products/update/{id}`   | Save product changes (POST, CSRF protected)        |
| `/products/disable/{id}`  | Deactivate a product (POST only, CSRF protected)   |

- Search matches the product **name** or **code**; the category filter
  combines with search, and pagination preserves both parameters.
- Product codes must be unique; the price must be a valid non-negative
  decimal; the category must exist and be active.
- Pagination styling lives in `application/config/pagination.php`.

### Key files

- `application/controllers/Products.php` — list / create / store / edit / update / disable
- `application/models/Product_model.php` — all `products` and `categories` access
- `application/views/products/*` — list, create, edit and the shared `_form` partial
- `application/database/schema.sql` — `categories` and `products` tables (with seed categories)

## Customers

Customer management (all routes require authentication). Intentionally
minimal — only a name and an optional phone number; no addresses, emails,
groups, notes or other CRM-style fields.

| Route                     | Description                                        |
| ------------------------- | -------------------------------------------------- |
| `/customers`              | Customer listing                                    |
| `/customers/create`       | Add-customer form                                   |
| `/customers/store`        | Save a new customer (POST, CSRF protected)          |
| `/customers/edit/{id}`    | Edit-customer form                                  |
| `/customers/update/{id}`  | Save customer changes (POST, CSRF protected)        |

- The name is required (at most 150 characters); the phone is optional
  (at most 30 characters) and stored as NULL when left blank.
- Non-existent or invalid customer IDs redirect back to the list with an
  error flash message.
- Customers are listed alphabetically by name.

### Key files

- `application/controllers/Customers.php` — list / create / store / edit / update
- `application/models/Customer_model.php` — all `customers` table access
- `application/views/customers/*` — list, create, edit and the shared `_form` partial
- `application/database/schema.sql` — `customers` table

## Inventory

Warehouse inventory management (all routes require authentication). Stock is
stored per warehouse/product pair in `warehouse_stock`; this feature only
**reads** quantities — it never writes stock.

| Route                          | Description                                   |
| ------------------------------ | --------------------------------------------- |
| `/inventory`                   | Inventory listing, filterable by warehouse    |
| `/inventory/warehouses`        | Warehouse list                                |
| `/inventory/warehouses/create` | Add-warehouse form                            |
| `/inventory/warehouses/store`  | Save a new warehouse (POST, CSRF protected)   |
| `/inventory/product/{w}/{p}`   | Quantity of a product in a warehouse          |

- Warehouse names are required, trimmed, unique and at most 100 characters.
- The warehouse filter is GET-based (`/inventory?warehouse_id=1`) and the
  selected warehouse stays selected after filtering.
- A warehouse/product pair without a `warehouse_stock` record shows quantity
  `0` — viewing never creates a stock record.

### Key files

- `application/controllers/Inventory.php` — inventory, warehouse list, create/store
- `application/models/Warehouse_model.php` — all `warehouses` table access
- `application/models/Inventory_model.php` — read-only `warehouse_stock` queries
- `application/views/inventory/*` — inventory, warehouse list, add-warehouse form, product quantity
- `application/database/schema.sql` — `warehouses` and `warehouse_stock` tables (with seed data)

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
