# Ledgerway — Procurement Management System

A functional PHP + MySQL implementation of the four core procurement
workflows: Purchase Requisition & Approval, Supplier Management,
Purchase Order Management, and Goods Receipt & Invoice Matching.

## Tech stack
- PHP 8+ (plain PHP with PDO — no framework required)
- MySQL / MariaDB (via XAMPP)
- Vanilla HTML/CSS/JS front end (no build step)

## 1. Install into XAMPP

1. Copy the whole `procurement-system` folder into your XAMPP `htdocs` directory, e.g.:
   `C:\xampp\htdocs\procurement-system` (Windows) or `/Applications/XAMPP/htdocs/procurement-system` (macOS).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.

## 2. Create the database

1. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
2. Go to the **Import** tab.
3. Choose the file `database/schema.sql` from this project and click **Go**.
   This creates the `procurement_db` database, all tables, and seed data
   (departments, users, suppliers, requisitions, purchase orders, goods
   receipts, invoices, and a ready-made 3-way matching example).

## 3. Set working passwords for the seeded users

Seeded users are inserted with a placeholder password. Run this once in
your browser to set real, working bcrypt passwords:

```
http://localhost/procurement-system/database/seed_users.php
```

All seeded accounts share the password: **password123**

| Email                     | Role                 |
|---------------------------|----------------------|
| mara@ledgerway.test       | admin                |
| reyes@ledgerway.test      | manager              |
| aquino@ledgerway.test     | employee             |
| ong@ledgerway.test        | procurement_officer  |

You can delete `database/seed_users.php` afterwards (or leave it —
running it again is harmless, it only updates accounts still marked
`PENDING`).

## 4. Log in

Visit:

```
http://localhost/procurement-system/login.php
```

Log in with any of the accounts above.

## Roles & permissions

| Action                                   | employee | manager | procurement_officer | admin |
|-------------------------------------------|:--------:|:-------:|:--------------------:|:-----:|
| Submit a requisition                      | ✅       | ✅      | ✅                    | ✅    |
| Approve / reject a requisition            |          | ✅      |                       | ✅    |
| Add / edit suppliers, rate performance    |          |         | ✅                    | ✅    |
| Create purchase orders                    |          |         | ✅                    | ✅    |
| Update PO status (confirm/deliver/cancel) |          |         | ✅                    | ✅    |
| Log goods receipts / invoices             |          |         | ✅                    | ✅    |
| Run 3-way match / approve payment         |          |         | ✅                    | ✅    |

`admin` always has full access. Everyone can view the dashboard,
supplier directory, purchase orders, and matching workspace regardless
of role — only the write actions above are gated.

## How the core workflow ties together

1. **Purchase Requisition & Approval** (`requisitions.php`,
   `requisition_view.php`, `actions/create_requisition.php`,
   `actions/requisition_decision.php`) — anyone can submit a
   requisition; only a manager/admin can approve or reject it. Only
   `approved` requisitions are eligible to become a purchase order.
2. **Supplier Management** (`suppliers.php`, `supplier_view.php`,
   `actions/create_supplier.php`, `actions/rate_supplier.php`) —
   stores contact info, pricing terms, and contract dates, and lets a
   procurement officer log delivery/quality/cost ratings, which are
   averaged into the star rating and performance bars.
3. **Purchase Order Management** (`purchase_orders.php`, `po_view.php`,
   `actions/create_po.php`, `actions/update_po_status.php`) — a PO can
   be auto-generated from an approved requisition (auto-fills quantity
   and unit price) or created manually. Status moves through
   `sent → confirmed → delivered` (or `cancelled`). Marking a PO
   **delivered** automatically opens a pending 3-way match record.
4. **Goods Receipt & Invoice Matching** (`goods_receipt.php`,
   `actions/log_receipt.php`, `actions/log_invoice.php`,
   `actions/run_matching.php`, `actions/approve_payment.php`) — log
   what physically arrived and what the supplier billed, then run the
   3-way match. The engine compares quantity received, quantity
   billed, and invoice total against the PO and flags any mismatch
   with a specific reason. Payment can only be approved once the
   match status is `matched`.

## Database

See `database/schema.sql` for the full DDL + seed data, and
`database/ER_diagram.mmd` for the entity-relationship diagram
(Mermaid format — view it at https://mermaid.live or in any Mermaid-
compatible viewer).

Core tables: `departments`, `users`, `suppliers`, `supplier_ratings`,
`requisitions`, `purchase_orders`, `goods_receipts`, `invoices`,
`invoice_matching`, `activity_log`.

## Notes / next steps for a production build

- Passwords are bcrypt-hashed via PHP's `password_hash()`, but there's
  no "forgot password" flow, rate limiting, or CSRF token yet — add
  these before deploying anywhere public.
- File attachments (e.g. scanned invoices) aren't handled — you'd add
  an `uploads/` folder and a file column on `invoices`/`goods_receipts`.
- Multi-line items per requisition/PO are simplified to a single
  description + quantity + unit price for clarity; a real deployment
  would normalize these into `requisition_items` / `po_items` tables.
