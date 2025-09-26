## Smart Order & Inventory — API (Laravel)

This repository contains a mini API for managing products, orders and stock — built to satisfy the "Smart Order & Inventory Management" test requirements.

The API uses Laravel and Sanctum for authentication. It supports product CRUD, Excel (.xlsx and CSV) bulk import, order placement with stock validation and reservation, stock transaction logging, and reporting endpoints.

## Quick setup

1. Clone the repository and install PHP dependencies:

```bash
git clone <repo-url> smart-order-inventory
cd smart-order-inventory
composer install
```

2. Copy `.env` and adjust DB credentials:

```bash
cp .env.example .env
# edit .env to set DB_CONNECTION, DB_DATABASE, etc.
php artisan key:generate
```

3. Run migrations & seed sample data:

```bash
php artisan migrate --seed
```

4. (Local storage) create storage symlink so uploaded images are publicly accessible:

```bash
php artisan storage:link
```

5. Serve the app locally:

```bash
php artisan serve
```

Notes:

-   The project includes seeders which create sample users and products. Adjust `.env` DB settings before migrating.

## Authentication

This project uses Laravel Sanctum. Register and login endpoints are exposed under the `api/v1/user` prefix.

-   Register: POST /api/v1/user/register
-   Login: POST /api/v1/user/login

Both endpoints return an authentication token. Protect subsequent requests by sending the token as an Authorization header:

`Authorization: Bearer <token>`

## API endpoints (high level)

Base admin API prefix: `/api/v1/admin` (protected by `auth:sanctum`)

-   Products

    -   GET /api/v1/admin/products — list products (paginated)
    -   POST /api/v1/admin/products — create product (multipart form for image)
    -   GET /api/v1/admin/products/{id} — get product
    -   PUT /api/v1/admin/products/{id} — update product
    -   DELETE /api/v1/admin/products/{id} — delete product (blocked if linked to orders)
    -   POST /api/v1/admin/products/upload-excel — bulk import products (CSV or .xlsx)

-   Orders

    -   GET /api/v1/admin/orders — list orders (paginated)
    -   POST /api/v1/admin/orders — create order (payload: items array)
    -   GET /api/v1/admin/orders/{id} — get order
    -   DELETE /api/v1/admin/orders/{id} — cancel order (restores stock)

-   Reports
    -   GET /api/v1/admin/reports/low-stock — list products with quantity < 5
    -   GET /api/v1/admin/reports/sales-summary — returns total revenue (completed orders), number of completed orders, top 3 selling products

## Excel / CSV import details

The import expects a first row header with columns: `name, sku, price, quantity, image_url`.

Example CSV (first line is header):

```
name,sku,price,quantity,image_url
Product A,SKU-A,19.99,10,https://example.com/image-a.jpg
Product B,SKU-B,29.99,5,
```

Handling .xlsx files:

-   The code supports .xlsx if either `maatwebsite/excel` or `phpoffice/phpspreadsheet` packages are installed.
-   If you want to load .xlsx files, install the package locally (recommended):

```bash
composer require maatwebsite/excel
```

If those packages are not installed, the importer will accept CSV files (Excel can export to CSV). The importer will store images (fetched from `image_url`) into the `public` disk using `Storage::disk('public')` and set the product `image_path` accordingly.

Invalid rows will be skipped and reported in the response with row numbers and reason for failure.

## Discount rule

-   If an order's subtotal (sum of unit_price \* quantity) exceeds $500, a 10% discount is applied and `discount_amount` on the order is set accordingly.

## Transactions / Logs

-   Every stock change is recorded in `stock_transactions` with fields: `product_id`, `change_type` (in/out), `quantity_changed`, `user_id`, `reason`, `timestamps`.

## Running tests

The repository includes tests for placing an order with insufficient stock and for discount calculation.

Run tests with:

```bash
./vendor/bin/phpunit --color=always
```

You should see tests pass (the repository's test suite includes the two feature tests requested).

## Example cURL requests

# 1) Register

```bash
curl -X POST http://127.0.0.1:8000/api/v1/user/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com","password":"password"}'
```

# 2) Login

````bash
curl -X POST http://127.0.0.1:8000/api/v1/user/login \
  # Smart Order & Inventory — API (Laravel)

  This repository contains a small Laravel API for managing products, orders, and inventory for the Techies Africa developer test.

  Highlights
  - Product CRUD with image upload
  - Bulk import (CSV, optional .xlsx with package)
  - Order placement with stock validation and stock transaction logging
  - Simple admin & user API endpoints secured with Laravel Sanctum

  Quick start
  1. Install dependencies and copy environment:

  ```bash
  composer install
  cp .env.example .env
  php artisan key:generate
````

2. Configure your DB in `.env`, then run migrations and seeders:

```bash
php artisan migrate --seed
php artisan storage:link   # optional, for product images
```

3. Run tests (project includes feature tests):

```bash
./vendor/bin/phpunit --color=always
```

API notes

-   User (customer) endpoints:

    -   POST /api/v1/user/register — register
    -   POST /api/v1/user/login — login (returns Bearer token)
    -   POST /api/v1/user/orders — place an order (authenticated)
    -   GET /api/v1/user/orders — list user's orders (authenticated)

-   Admin endpoints (prefix `/api/v1/admin`):
    -   POST /api/v1/admin/login — admin login (returns Bearer token)
    -   GET/POST/PUT/DELETE /api/v1/admin/products — product CRUD (authenticated admin)
    -   POST /api/v1/admin/products/upload-excel — import products (CSV or .xlsx if package installed)
    -   GET /api/v1/admin/reports/low-stock
    -   GET /api/v1/admin/reports/sales-summary

Order behavior

-   Orders validate stock for each product and will return an error if stock is insufficient.
-   A discount of 10% is applied automatically when the order subtotal exceeds 500.

Excel / CSV import

-   The importer accepts CSV by default. If you need .xlsx support, install `maatwebsite/excel`:

```bash
composer require maatwebsite/excel
```

Example cURL (login & create order)

1. User login

```bash
curl -X POST http://127.0.0.1:8000/api/v1/user/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password"}'
```

2. Create an order (replace <token> and <product-id>)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/user/orders \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":"<product-id>","quantity":2}]}'
```
