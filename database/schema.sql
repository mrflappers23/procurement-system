-- =====================================================================
-- Ledgerway Procurement Management System
-- MySQL schema for XAMPP / phpMyAdmin
-- Import this file first (creates + seeds everything except user
-- passwords), then run database/seed_users.php once in your browser.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS procurement_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE procurement_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Departments
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS departments;
CREATE TABLE departments (
  department_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Users (employees, managers, procurement officers, admins)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('employee','manager','procurement_officer','admin') NOT NULL DEFAULT 'employee',
  department_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Suppliers
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS suppliers;
CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL,
  contact_name VARCHAR(100),
  email VARCHAR(150),
  phone VARCHAR(50),
  address VARCHAR(255),
  payment_terms VARCHAR(50) DEFAULT 'Net 30',
  contract_start DATE NULL,
  contract_end DATE NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Supplier Catalogue
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS supplier_catalogue;

CREATE TABLE supplier_catalogue (
    catalogue_id INT AUTO_INCREMENT PRIMARY KEY,

    supplier_id INT NOT NULL,

    product_name VARCHAR(150) NOT NULL,

    description TEXT NULL,

    unit VARCHAR(50) NOT NULL,

    unit_price DECIMAL(10,2) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_supplier_catalogue_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES suppliers(supplier_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Supplier performance ratings (delivery / quality / cost)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS supplier_ratings;
CREATE TABLE supplier_ratings (
  rating_id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  delivery_score DECIMAL(5,2) NOT NULL,   -- 0 - 100
  quality_score DECIMAL(5,2) NOT NULL,    -- 0 - 100
  cost_score DECIMAL(5,2) NOT NULL,       -- 0 - 100
  notes VARCHAR(255),
  rated_by INT NULL,
  rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE,
  FOREIGN KEY (rated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Purchase requisitions
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS requisitions;
CREATE TABLE requisitions (
  requisition_id INT AUTO_INCREMENT PRIMARY KEY,
  req_code VARCHAR(20) NOT NULL UNIQUE,        -- e.g. REQ-1042
  department_id INT NOT NULL,
  requested_by INT NOT NULL,
  item_description VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  estimated_amount DECIMAL(12,2) NOT NULL,
  preferred_supplier_id INT NULL,
  justification TEXT,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  decided_by INT NULL,
  decided_at TIMESTAMP NULL,
  decision_notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(department_id),
  FOREIGN KEY (requested_by) REFERENCES users(user_id),
  FOREIGN KEY (preferred_supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
  FOREIGN KEY (decided_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Purchase orders (created only from approved requisitions, or manually
-- by a procurement officer / admin)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS purchase_orders;
CREATE TABLE purchase_orders (
  po_id INT AUTO_INCREMENT PRIMARY KEY,
  po_code VARCHAR(20) NOT NULL UNIQUE,          -- e.g. PO-3312
  requisition_id INT NULL UNIQUE,
  supplier_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  issue_date DATE NOT NULL,
  expected_delivery_date DATE NULL,
  status ENUM('sent','confirmed','delivered','cancelled') NOT NULL DEFAULT 'sent',
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (requisition_id) REFERENCES requisitions(requisition_id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
  FOREIGN KEY (created_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Goods receipts (what actually arrived)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS goods_receipts;
CREATE TABLE goods_receipts (
  receipt_id INT AUTO_INCREMENT PRIMARY KEY,
  po_id INT NOT NULL,
  received_by INT NOT NULL,
  received_date DATE NOT NULL,
  quantity_received INT NOT NULL,
  condition_notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
  FOREIGN KEY (received_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Supplier invoices (what the supplier billed)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
  invoice_id INT AUTO_INCREMENT PRIMARY KEY,
  po_id INT NOT NULL,
  supplier_id INT NOT NULL,
  invoice_number VARCHAR(50) NOT NULL,
  invoice_date DATE NOT NULL,
  quantity_billed INT NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3-way match result per purchase order (PO vs receipt vs invoice)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS invoice_matching;
CREATE TABLE invoice_matching (
  matching_id INT AUTO_INCREMENT PRIMARY KEY,
  po_id INT NOT NULL UNIQUE,
  receipt_id INT NULL,
  invoice_id INT NULL,
  match_status ENUM('pending','matched','mismatch') NOT NULL DEFAULT 'pending',
  discrepancy_notes VARCHAR(500),
  matched_by INT NULL,
  matched_at TIMESTAMP NULL,
  payment_approved TINYINT(1) NOT NULL DEFAULT 0,
  payment_approved_by INT NULL,
  payment_approved_at TIMESTAMP NULL,
  FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
  FOREIGN KEY (receipt_id) REFERENCES goods_receipts(receipt_id) ON DELETE SET NULL,
  FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id) ON DELETE SET NULL,
  FOREIGN KEY (matched_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (payment_approved_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Activity log (drives the dashboard activity feed)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS activity_log;
CREATE TABLE activity_log (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- SEED DATA
-- =====================================================================

INSERT INTO departments (name) VALUES
('IT Services'), ('Facilities'), ('Marketing'), ('Operations'), ('Warehouse'), ('Admin');

-- Users are inserted WITHOUT a real password hash here — run
-- database/seed_users.php once in your browser to set working
-- passwords (all seeded users use the password: password123).
INSERT INTO users (name, email, password_hash, role, department_id) VALUES
('Mara Cortez',  'mara@ledgerway.test',  'PENDING', 'admin',               6),
('D. Reyes',     'reyes@ledgerway.test', 'PENDING', 'manager',             5),
('J. Aquino',    'aquino@ledgerway.test','PENDING', 'employee',            1),
('P. Ong',       'ong@ledgerway.test',   'PENDING', 'procurement_officer', 4);

INSERT INTO suppliers (name, category, contact_name, email, phone, address, payment_terms, contract_start, contract_end, status) VALUES
('Solari Office Supplies',        'Office & Facilities',   'R. Salonga',  'sales@solari.test',    '+63 2 8555 1010', 'Makati City',  'Net 30', '2021-03-01', '2027-03-01', 'active'),
('Northbridge Industrial',        'Industrial & Warehouse','T. Herrera',  'sales@northbridge.test','+63 2 8555 2020', 'Cavite',       'Net 45', '2022-06-15', '2026-06-15', 'active'),
('Kaizen Print Co.',              'Print & Marketing',     'A. Lim',      'hello@kaizenprint.test','+63 2 8555 3030', 'Quezon City',  'Net 15', '2020-01-10', '2027-01-10', 'active'),
('Circuit & Tech Distributors',   'IT & Electronics',      'B. Uy',       'sales@circuittech.test','+63 2 8555 4040', 'Pasig City',   'Net 30', '2021-09-01', '2026-09-01', 'active'),
('Fleetline Rentals',             'Industrial & Warehouse','G. Domingo',  'ops@fleetline.test',   '+63 2 8555 5050', 'Laguna',       'Net 15', '2023-02-01', '2025-02-01', 'inactive'),
('Greenhouse Janitorial',         'Facilities',            'N. Cruz',     'contact@greenhouse.test','+63 2 8555 6060', 'Taguig City', 'Net 30', '2022-01-01', '2027-01-01', 'active');

INSERT INTO supplier_ratings (supplier_id, delivery_score, quality_score, cost_score, notes) VALUES
(1, 96, 92, 84, 'Consistently reliable for office consumables'),
(2, 78, 85, 70, 'Delivery delays on the last two rentals'),
(3, 99, 95, 90, 'Excellent turnaround on print jobs'),
(4, 88, 90, 80, 'Solid IT hardware partner'),
(5, 61, 70, 65, 'Contract lapsed, under review'),
(6, 100, 94, 88, 'Zero missed cleaning cycles this year');

INSERT INTO requisitions (req_code, department_id, requested_by, item_description, quantity, estimated_amount, preferred_supplier_id, justification, status, decided_by, decided_at) VALUES
('REQ-1042', 1, 3, 'Laptops — Dell Latitude x6', 6, 312000.00, 4, 'Needed to equip six incoming hires starting July 20. Current pool of loaner laptops is fully allocated.', 'pending', NULL, NULL),
('REQ-1041', 2, 2, 'HVAC maintenance contract', 1, 84500.00, NULL, 'Annual preventive maintenance renewal.', 'pending', NULL, NULL),
('REQ-1039', 3, 3, 'Trade-show booth materials', 1, 56200.00, 3, 'Q3 industry expo participation.', 'pending', NULL, NULL),
('REQ-1037', 4, 4, 'Forklift rental — 3 months', 1, 145000.00, 5, 'Temporary capacity for peak season.', 'pending', NULL, NULL),
('REQ-1036', 6, 3, 'Office pantry supplies — Q3', 42, 18900.00, 1, 'Quarterly pantry restock.', 'pending', NULL, NULL),
('REQ-1038', 5, 2, 'Pallet racking — Aisle 4', 1, 221000.00, 2, 'Additional storage capacity for Q3 inventory.', 'approved', 1, '2026-06-26 10:00:00'),
('REQ-1035', 3, 3, 'Print collateral — Kaizen Print', 1, 32400.00, 3, 'Trade-show print collateral.', 'approved', 2, '2026-06-22 09:00:00'),
('REQ-1033', 1, 3, 'Firewall appliance renewal', 1, 198750.00, 4, 'End-of-life hardware replacement.', 'approved', 1, '2026-06-20 14:00:00'),
('REQ-1031', 2, 2, 'Janitorial contract renewal', 1, 64000.00, 6, 'Annual services renewal.', 'approved', 1, '2026-06-18 11:00:00'),
('REQ-1029', 1, 3, 'Monitor arms x20', 20, 41000.00, 4, 'Ergonomics rollout.', 'approved', 1, '2026-06-15 15:00:00'),
('REQ-1027', 6, 3, 'Office pantry supplies — Q2', 42, 18900.00, 1, 'Quarterly pantry restock.', 'approved', 1, '2026-06-19 10:00:00'),
('REQ-1018', 4, 4, 'Forklift rental — prior cycle', 1, 145000.00, 2, 'Temporary capacity.', 'approved', 1, '2026-06-11 09:00:00'),
('REQ-1030', 3, 3, 'Influencer event sponsorship', 1, 250000.00, NULL, 'Proposed brand sponsorship.', 'rejected', 1, '2026-06-16 16:00:00'),
('REQ-1024', 4, 4, 'Second forklift — outright purchase', 1, 890000.00, NULL, 'Requested outright purchase instead of rental.', 'rejected', 1, '2026-06-10 13:00:00');

UPDATE requisitions SET decision_notes = 'Budget re-allocated to leasing instead of outright purchase.' WHERE req_code = 'REQ-1030';
UPDATE requisitions SET decision_notes = 'Rental remains more cost-effective than outright purchase this cycle.' WHERE req_code = 'REQ-1024';

INSERT INTO purchase_orders (po_code, requisition_id, supplier_id, quantity, unit_price, total_amount, issue_date, expected_delivery_date, status, created_by) VALUES
('PO-3312', 7,  3, 1,  32400.00,  32400.00,  '2026-06-29', '2026-07-08', 'sent', 1),
('PO-3309', 10, 4, 20, 2050.00,   41000.00,  '2026-06-27', '2026-07-05', 'sent', 1),
('PO-3307', 9,  6, 1,  64000.00,  64000.00,  '2026-06-26', '2026-07-03', 'sent', 1),
('PO-3304', 8,  4, 1,  198750.00, 198750.00, '2026-06-24', '2026-07-02', 'confirmed', 1),
('PO-3301', 6,  2, 1,  221000.00, 221000.00, '2026-06-21', '2026-06-30', 'confirmed', 1),
('PO-3305', 11, 1, 42, 450.00,    18900.00,  '2026-06-20', '2026-06-21', 'delivered', 1),
('PO-3298', 12, 2, 1,  145000.00, 145000.00, '2026-06-12', '2026-06-18', 'delivered', 1),
('PO-3290', NULL, 3, 60, 460.00,  27600.00,  '2026-06-05', '2026-06-10', 'delivered', 1),
('PO-3284', NULL, 5, 1,  96000.00, 96000.00, '2026-05-28', '2026-06-02', 'cancelled', 1);

-- Goods receipts + invoices for the delivered POs (drives the matching demo)
INSERT INTO goods_receipts (po_id, received_by, received_date, quantity_received, condition_notes) VALUES
(6, 4, '2026-06-21', 42, 'Good'),
(7, 4, '2026-06-18', 1,  'Good'),
(8, 4, '2026-06-10', 60, 'Good');

INSERT INTO invoices (po_id, supplier_id, invoice_number, invoice_date, quantity_billed, unit_price, total_amount) VALUES
(6, 1, 'SO-INV-5528', '2026-06-22', 42, 450.00,  18900.00),
(7, 2, 'NB-INV-7741', '2026-06-19', 1,  158500.00, 158500.00),
(8, 3, 'KP-INV-3319', '2026-06-11', 60, 460.00,  27600.00);

INSERT INTO invoice_matching (po_id, receipt_id, invoice_id, match_status, discrepancy_notes, matched_by, matched_at, payment_approved) VALUES
(6, 1, 1, 'matched',  NULL, 1, '2026-06-22 09:00:00', 1),
(7, 2, 2, 'mismatch', 'Invoice total is 13,500.00 higher than the purchase order (158,500.00 vs 145,000.00) at the same quantity.', 1, '2026-06-19 09:30:00', 0),
(8, 3, 3, 'matched',  NULL, 1, '2026-06-11 10:00:00', 1);

INSERT INTO activity_log (user_id, action, description) VALUES
(1, 'match',   'PO-3305 3-way matched & cleared for payment — Solari Office Supplies'),
(1, 'flag',    'PO-3298 invoice amount mismatch flagged — Northbridge Industrial'),
(1, 'po_sent', 'PO-3312 sent to Kaizen Print Co. (auto-generated from REQ-1035)'),
(1, 'approve', 'REQ-1038 approved — Pallet racking, Aisle 4');
