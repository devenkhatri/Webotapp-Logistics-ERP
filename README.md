# Webotapp Logistics ERP

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

> **Webotapp Logistics ERP** is a modern, enterprise-grade, multi-tenant Courier and Logistics Management System built on Laravel 12. Designed for 3PL providers, domestic & international courier services, linehaul networks, and last-mile delivery fleets.

---

## 🌟 Key Features

### 1. Role-Segregated Dashboards & Portals
- **Super Admin & Executive Dashboard**: Multi-branch analytics, real-time revenue, shipment lifecycle overview, vehicle fleet health, and global system settings.
- **Operations & Hub Manager Portal**: Inbound/outbound manifest scanning, barcode verification, shipment bag consolidation, transit status updates, and dispatch runs.
- **Delivery Agent (Rider) Mobile Portal**: Dedicated touch-friendly interface for assigned deliveries, 1-click status transitions, digital Proof of Delivery (signature & photo POD), and COD collection logging.
- **Finance & Accounting Desk**: Automated billing, double-entry ledger accounts, journal entries, merchant COD settlements, and fleet operational expense logging.
- **Merchant / Customer Self-Service Portal**: Single & bulk shipment booking, 4x6" thermal shipping label generation, address book management, and real-time package tracking.

### 2. Operational Core
- **Automated AWB Engine**: Formatted Air Waybill numbers with prefix codes and sequential check digits.
- **Volumetric & Rate Calculator**: Automatic chargeable weight calculation ($L \times W \times H / 5000$) versus dead weight, with dynamic multi-slab regional pricing matrices.
- **Hub & Transit Management**: Linehaul manifests, bag seals, inbound/outbound scans with exception logging.
- **Cash on Delivery (COD) Management**: Strict audit trail for rider cash collections, remittance reconciliation, and merchant bank payout batches.
- **RTO (Return to Origin)**: Multi-attempt delivery management with automatic RTO triggers, return fees, and reverse logistics tracking.
- **Thermal Label Printing**: Standard 4x6" shipping label generation with Code128 barcodes, routing codes, and consignee details.
- **Public Tracking Portal**: Customer-facing live tracking page with detailed status timelines.
- **RESTful API**: Token-authenticated REST endpoints for external e-commerce integrations (Shopify, WooCommerce, custom ERPs).

---

## 🛠️ Technology Stack

- **Framework**: [Laravel 12](https://laravel.com)
- **Language**: PHP 8.2+
- **Database**: MySQL 8.x / MariaDB
- **Front-end**: Blade Templates with Tailwind CSS & Alpine.js
- **Icons**: Heroicons
- **Testing**: PHPUnit / Pest with feature & unit test suites

---

## 🚀 Quick Start Guide

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.x / MariaDB
- Node.js & NPM (optional, for asset compilation)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/webotapp-academy/Webotapp-Logistics-ERP.git
   cd Webotapp-Logistics-ERP
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up database credentials in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=courier_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and database seeders:**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server:**
   ```bash
   php artisan serve --port=8001
   ```
   Visit `http://127.0.0.1:8001` in your browser.

---

## 👥 Demo User Credentials

The database seeder provisions pre-configured accounts for testing each operational role:

| Role | Name | Email | Password | Access Area |
|---|---|---|---|---|
| **Super Admin** | Vikramaditya Singhania | `admin@courierx.com` | `password` | Master Operations Dashboard (`/dashboard`) |
| **Hub Manager** | Priya Sharma | `operations@courierx.com` | `password` | Hub Inbound & Operations (`/dashboard`) |
| **Delivery Rider** | Rajesh Kumar | `rider1@courierx.com` | `password` | Rider Mobile Portal (`/agent/deliveries`) |
| **Accountant** | Anita Desai | `finance@courierx.com` | `password` | Invoicing, COD & Ledger (`/billing/invoices`) |
| **Merchant** | Rohan Mehra (FabIndia) | `merchant@fashionnova.demo` | `password` | Merchant Portal (`/portal`) |

*(Quick-login buttons are also available on the `/login` page for fast role switching during testing.)*

---

## 🧪 Testing

Run the automated test suite:
```bash
php artisan test
```

---

## 📄 License

The Webotapp Logistics ERP is open-source software licensed under the [MIT license](LICENSE).
