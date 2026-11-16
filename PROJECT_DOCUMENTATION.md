# Apex Logistics ERP - Commercial Courier & Supply Chain Management System

## System Overview
Apex Logistics ERP is a production-grade, enterprise-ready Courier and Logistics Management System built on Laravel 12, PHP 8.4, and MySQL 8. The application is completely functional with database integrity, normalized schemas, business logic services, role-based workflows, and automated test coverage.

- **Local Server URL**: [http://127.0.0.1:8001](http://127.0.0.1:8001)
- **Database**: `courier_erp` (MySQL 8 on `127.0.0.1:3306`)

---

## 1. Quick Access & Pre-Configured Demo Accounts
The system features instant 1-click login buttons on `/login` or you can use the credentials below:

| Role | Email | Password | Primary Interface |
|---|---|---|---|
| **System Administrator** | `admin@courierx.com` | `password` | Master Operations Dashboard |
| **Operations Hub Manager** | `operations@courierx.com` | `password` | Hub Inbound & Sorting Station |
| **Delivery Rider (Field Agent)** | `rider1@courierx.com` | `password` | Mobile Rider App (`/agent/deliveries`) |
| **Finance & Accounts** | `finance@courierx.com` | `password` | Invoicing, COD & General Ledger |
| **Merchant (FashionNova)** | `merchant@fashionnova.demo` | `password` | E-Commerce Merchant Portal (`/portal`) |

---

## 2. Key Modules & Operational Features

### A. Consignment Booking & AWB Generation
- **Automatic AWB Sequences**: Atomic allocation with prefix and sequence locking (`AwbGeneratorService`).
- **Dynamic Weight Calculation**: Volumetric calculation `(L × W × H) / 5000` with `chargeable_weight = max(actual, volumetric)` (`WeightCalculationService`).
- **Progressive Tariff Engine**: Zone determination (LOCAL, REGIONAL, NATIONAL), weight slabs, fuel surcharge, handling fees, and GST tax calculation (`RateCalculationService`).
- **Thermal Shipping Labels**: Standard 4×6 inch format with Code-128 barcode, routing codes, sender/receiver particulars, and COD highlight (`/shipments/{id}/label`).
- **Tax Invoices & Consignment Waybills**: Printable multi-copy consignment note (`/shipments/{id}/invoice`).

### B. Hub, Sorting & Linehaul Operations
- **Inbound Scanner Terminal**: Barcode scanning terminal for high-speed parcel arrivals (`/hubs/inbound`).
- **Sorting & Bagging Terminal**: Packaging parcels into destination bags with tamper-evident security seal tags (`/hubs/sorting`).
- **Linehaul Manifest & Dispatch**: Inter-hub linehaul manifests with vehicle, driver, and weight balancing (`/manifests`).
- **Destination Receiving**: Seamless manifest intake and status transition to destination hubs.

### C. Last-Mile Delivery & Mobile Rider Portal
- **Delivery Run Sheets (DRS)**: Batching consignments into sequenced delivery runs for riders (`/delivery`).
- **Mobile Rider App**: Responsive mobile portal (`/agent/deliveries`) equipped with one-touch phone dialer, navigation links, and delivery actions.
- **Electronic Proof of Delivery (e-POD)**: Recipient signature capture, relation, and 6-digit verification PIN/OTP.
- **Exception & Failure Management**: Reason-coded failure logging (customer unavailable, wrong address, door closed).

### D. Reverse Logistics & RTO
- **Automated RTO Trigger**: Automatically initiates Return to Origin when max delivery attempts (3) are reached or upon doorstep customer refusal.
- **Return Tracking**: Generates reverse AWB (`RTO-CX...`) and tracks return transit to the merchant warehouse (`/rto`).

### E. Cash on Delivery (COD) & Escrow Accounting
- **Collection Ledger**: Tracks cash/UPI collected by riders on field delivery (`/cod/collections`).
- **Branch Cash Deposit**: Cashier verification and deposit into branch accounts.
- **Merchant COD Settlements**: Calculates gross COD collected, deducts shipping freight, and generates remittance statements (`/cod/settlements`).

### F. Billing, Invoicing & Double-Entry Ledger
- **GST Invoicing**: Itemized corporate invoicing with tax breakdowns (`/billing/invoices`).
- **Operational Expense Logs**: Fuel, vehicle repairs, and warehouse rent recording (`/billing/expenses`).
- **Chart of Accounts (COA)**: Double-entry ledger (Assets, Liabilities, Freight Revenue, Escrow Payable, Expenses) with trial balance (`/billing/ledger`).

### G. Public Tracking & Merchant REST API
- **Public Tracking Widget**: Live tracking stepper without login (`/track/{awb}`).
- **REST API Endpoints**:
  - `POST /api/v1/shipments/book`: Book consignments programmatically.
  - `GET /api/v1/shipments/track/{awb}`: Real-time JSON milestone events.
  - `POST /api/v1/rates/calculate`: Instant quotation engine.

---

## 3. Automated Test Suite
All 10 unit and feature tests pass with 44 assertions:
```bash
php artisan test --testdox
```
- Volumetric weight calculation
- AWB generator sequence locking
- Rate calculation engine with slabs
- Complete booking workflow
- Hub inbound and manifest dispatch
- Last mile delivery and POD
- Delivery failure and RTO initiation
- REST API shipment booking and tracking
