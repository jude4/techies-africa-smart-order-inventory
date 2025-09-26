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

```bash
curl -X POST http://127.0.0.1:8000/api/v1/user/login \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@example.com","password":"password"}'
```

# 3) Create product (multipart/form-data)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/admin/products \
  -H "Authorization: Bearer <token>" \
  -F "name=New Product" \
  -F "sku=NP-001" \
  -F "price=49.99" \
  -F "quantity=10" \
  -F "image=@/path/to/image.jpg"
```

# 4) Bulk import (CSV or XLSX)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/admin/products/upload-excel \
  -H "Authorization: Bearer <token>" \
  -F "file=@/path/to/products.csv"
```

# 5) Create an order

```bash
curl -X POST http://127.0.0.1:8000/api/v1/admin/orders \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":"<product-uuid>","quantity":2}]}'
```

## Notes & next steps

-   Role-based access (admin vs user) is possible and `spatie/laravel-permission` is included in composer.json; we can wire roles and permission middleware if you want the bonus.
-   If you prefer orders to remain `pending` without decrementing stock, we should implement a reservation flow instead. Currently orders can be created as `pending` but the implementation as shipped decrements stock at creation time (reserving). Tell me which behavior you'd like and I will adjust.

## Contact / support

If you want me to continue, tell me whether you want:

1. Orders created as `pending` but stock NOT decremented (needs reservation implementation).
2. Orders `pending` but still decrement stock to reserve it (simple change to status only).
3. I should install `maatwebsite/excel` to fully support .xlsx imports (I can add composer suggestions and a patch).

I'll proceed on your confirmation.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Redberry](https://redberry.international/laravel-development)**
-   **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
