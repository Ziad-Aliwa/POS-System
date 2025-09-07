## POS System (Laravel 10)

A simple Point of Sale web application built with Laravel 10. It supports user roles (admin, cashier), product and customer management, a shopping cart with stock checks, checkout to orders, and PDF invoices.

### Features
- **Authentication and Roles**: Register/Login users with roles `admin` and `cashier`.
- **Products**: Admin can create, edit, delete, and list products; image upload to `public/uploads`.
- **Customers**: Admin can create, edit, delete, and list customers.
- **Cart & Checkout**:
  - Add/update/remove items in cart per user.
  - Stock validation on add and update.
  - Checkout creates an order and decrements product stock.
- **Orders**: List orders, view order details, and print invoice as PDF.
- **Search**: Quick product search for POS flow.

### Tech Stack
- **Backend**: Laravel 10 (PHP ^8.1)
- **Auth**: Laravel auth, role checks via custom `CheckRole` middleware
- **PDF**: `barryvdh/laravel-dompdf`
- **API**: Sanctum installed (used for `/api/user` when authenticated)
- **Frontend tooling**: Vite, Axios
- **Database**: MySQL/MariaDB (via XAMPP or native)

### Prerequisites
- PHP ^8.1
- Composer
- Node.js 16+ and npm
- MySQL/MariaDB (e.g., XAMPP on Windows)

### Getting Started
1. Clone the repository into your web root (e.g., `C:/xampp/htdocs/POS_system`).
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Set up environment:
   - Copy `.env.example` to `.env` (Composer script may do this automatically).
   - Update DB settings in `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD).
4. Generate app key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations (and optional seeders):
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   Seeders include products, customers, and orders for demo data.
6. Build frontend assets (optional for vanilla Blade views but recommended):
   ```bash
   npm install
   npm run dev
   ```
7. Serve the app:
   - Using PHP built-in server:
     ```bash
     php artisan serve
     ```
   - Or configure an Apache vhost in XAMPP pointing to the `public` directory.

### User Accounts
There is no default user seeder for credentials. Create an account via the registration page and choose a role:
- Visit `/register` and create an `admin` to manage products/customers.
- Create `cashier` users for sales operations.

### Key Routes
- Auth: `/register`, `/login`, `/logout`
- Products (admin): `/products/create`, `/products/{id}/edit`, delete via resource route
- Products (browse/search): `/products`, `/products/search`
- Customers (admin): `/customers`
- Cart: `/cart`, add `/cart/add`, update `/cart/item/{item}`, remove `/cart/item/{item}`
- Checkout: `/checkout`
- Orders: `/orders`, `/orders/{order}`, print `/orders/{order}/print`

Most routes are protected by the `checkrole` middleware:
- Admin only: product CRUD, customer management, delete orders, dashboard
- Admin + Cashier: product listing/search, cart, checkout, orders view/print

### Database Schema (Overview)
- `products`: name, category, price, stock_quantity, min_order_quantity, image
- `customers`: name, phone, email, address
- `orders`: customer_id, user_id, total_price
- `order_items`: order_id, product_id, quantity, price_per_unit
- `carts` / `cart_items`: per-user carts and items

### File Uploads
Product images are stored under `public/uploads`. Ensure the directory is writable on your system.

### PDF Invoices
Invoices are generated with `barryvdh/laravel-dompdf` at `/orders/{order}/print`.

### Running Tests
PHPUnit is configured (Laravel default). No custom tests are included.

### Troubleshooting
- 403 Unauthorized: Ensure you are logged in with a role permitted for the route.
- Migrations/Seeds: Confirm DB credentials and that the database exists.
- Assets: If UI styles/scripts are missing, run `npm install` and `npm run dev`.

### License
This project is open-sourced software licensed under the MIT license.
