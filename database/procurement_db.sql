-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 06:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `procurement_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 1, 'match', 'PO-3305 3-way matched & cleared for payment — Solari Office Supplies', '2026-07-14 12:23:10'),
(2, 1, 'flag', 'PO-3298 invoice amount mismatch flagged — Northbridge Industrial', '2026-07-14 12:23:10'),
(3, 1, 'po_sent', 'PO-3312 sent to Kaizen Print Co. (auto-generated from REQ-1035)', '2026-07-14 12:23:10'),
(4, 1, 'approve', 'REQ-1038 approved — Pallet racking, Aisle 4', '2026-07-14 12:23:10'),
(5, 1, 'flag', 'PO-3298 invoice amount mismatch flagged', '2026-07-14 12:40:10'),
(6, 1, 'supplier_rate', 'New performance rating submitted for supplier #5', '2026-07-14 12:40:56'),
(7, 1, 'flag', 'PO-3298 invoice amount mismatch flagged', '2026-07-14 12:47:11'),
(8, 1, 'approve', 'REQ-1042 approved — Laptops — Dell Latitude x6', '2026-07-14 17:35:26'),
(9, 1, 'po_status', 'PO-3307 status changed to confirmed', '2026-07-14 17:35:40'),
(10, 1, 'po_status', 'PO-3307 status changed to delivered', '2026-07-14 17:35:44'),
(11, 1, 'submit', 'REQ-1043 submitted for approval — Metal', '2026-07-14 17:57:56'),
(12, 1, 'po_sent', 'PO-3313 sent to Circuit & Tech Distributors (auto-generated from requisition)', '2026-07-14 18:58:47'),
(13, 1, 'approve', 'REQ-1043 approved — Metal', '2026-07-14 18:59:05'),
(14, 1, 'po_status', 'PO-3313 status changed to confirmed', '2026-07-14 18:59:13'),
(15, 1, 'po_status', 'PO-3313 status changed to delivered', '2026-07-14 18:59:14'),
(16, 1, 'flag', 'PO-3298 invoice amount mismatch flagged', '2026-07-14 19:31:24'),
(17, 1, 'po_sent', 'PO-3314 sent to Circuit & Tech Distributors (manual PO)', '2026-07-14 19:45:04'),
(18, 1, 'po_status', 'PO-3314 status changed to confirmed', '2026-07-14 19:45:47'),
(19, 1, 'po_status', 'PO-3314 status changed to cancelled', '2026-07-14 19:46:10'),
(20, 1, 'receipt', 'Goods receipt logged for PO-3313 (6 received)', '2026-07-14 19:46:58'),
(21, 1, 'invoice', 'Invoice 67 logged for PO-3313', '2026-07-14 19:47:14'),
(22, 1, 'match', 'PO-3313 3-way matched & cleared for payment', '2026-07-14 19:47:16'),
(23, 1, 'payment', 'Payment approved for PO-3313', '2026-07-14 19:47:20'),
(24, 1, 'receipt', 'Goods receipt logged for PO-3307 (2 received)', '2026-07-14 19:48:41'),
(25, 1, 'invoice', 'Invoice 6 logged for PO-3307', '2026-07-14 19:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `name`) VALUES
(6, 'Admin'),
(2, 'Facilities'),
(1, 'IT Services'),
(3, 'Marketing'),
(4, 'Operations'),
(5, 'Warehouse');

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `receipt_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `received_by` int(11) NOT NULL,
  `received_date` date NOT NULL,
  `quantity_received` int(11) NOT NULL,
  `condition_notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goods_receipts`
--

INSERT INTO `goods_receipts` (`receipt_id`, `po_id`, `received_by`, `received_date`, `quantity_received`, `condition_notes`, `created_at`) VALUES
(1, 6, 4, '2026-06-21', 42, 'Good', '2026-07-14 12:23:10'),
(2, 7, 4, '2026-06-18', 1, 'Good', '2026-07-14 12:23:10'),
(3, 8, 4, '2026-06-10', 60, 'Good', '2026-07-14 12:23:10'),
(4, 10, 1, '2026-07-14', 6, NULL, '2026-07-14 19:46:58'),
(5, 3, 1, '2026-07-14', 2, NULL, '2026-07-14 19:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `quantity_billed` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `po_id`, `supplier_id`, `invoice_number`, `invoice_date`, `quantity_billed`, `unit_price`, `total_amount`, `created_at`) VALUES
(1, 6, 1, 'SO-INV-5528', '2026-06-22', 42, 450.00, 18900.00, '2026-07-14 12:23:10'),
(2, 7, 2, 'NB-INV-7741', '2026-06-19', 1, 158500.00, 158500.00, '2026-07-14 12:23:10'),
(3, 8, 3, 'KP-INV-3319', '2026-06-11', 60, 460.00, 27600.00, '2026-07-14 12:23:10'),
(4, 10, 4, '67', '2026-07-14', 6, 1.00, 6.00, '2026-07-14 19:47:14'),
(5, 3, 6, '6', '2026-07-14', 1, 64000.00, 64000.00, '2026-07-14 19:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_matching`
--

CREATE TABLE `invoice_matching` (
  `matching_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `receipt_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `match_status` enum('pending','matched','mismatch') NOT NULL DEFAULT 'pending',
  `discrepancy_notes` varchar(500) DEFAULT NULL,
  `matched_by` int(11) DEFAULT NULL,
  `matched_at` timestamp NULL DEFAULT NULL,
  `payment_approved` tinyint(1) NOT NULL DEFAULT 0,
  `payment_approved_by` int(11) DEFAULT NULL,
  `payment_approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_matching`
--

INSERT INTO `invoice_matching` (`matching_id`, `po_id`, `receipt_id`, `invoice_id`, `match_status`, `discrepancy_notes`, `matched_by`, `matched_at`, `payment_approved`, `payment_approved_by`, `payment_approved_at`) VALUES
(1, 6, 1, 1, 'matched', NULL, 1, '2026-06-22 01:00:00', 1, NULL, NULL),
(2, 7, 2, 2, 'mismatch', 'Invoice total is ₱13,500.00 higher than the purchase order (₱158,500.00 vs ₱145,000.00 at the same quantity).', 1, '2026-07-14 19:31:24', 0, NULL, NULL),
(3, 8, 3, 3, 'matched', NULL, 1, '2026-06-11 02:00:00', 1, NULL, NULL),
(4, 3, 5, 5, 'pending', NULL, NULL, NULL, 0, NULL, NULL),
(5, 10, 4, 4, 'matched', NULL, 1, '2026-07-14 19:47:16', 1, 1, '2026-07-14 19:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `po_code` varchar(20) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `catalogue_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `issue_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` enum('sent','confirmed','delivered','cancelled') NOT NULL DEFAULT 'sent',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_id`, `po_code`, `requisition_id`, `supplier_id`, `catalogue_id`, `quantity`, `unit_price`, `total_amount`, `issue_date`, `expected_delivery_date`, `status`, `created_by`, `created_at`) VALUES
(1, 'PO-3312', 7, 3, NULL, 1, 32400.00, 32400.00, '2026-06-29', '2026-07-08', 'sent', 1, '2026-07-14 12:23:10'),
(2, 'PO-3309', 10, 4, NULL, 20, 2050.00, 41000.00, '2026-06-27', '2026-07-05', 'sent', 1, '2026-07-14 12:23:10'),
(3, 'PO-3307', 9, 6, NULL, 1, 64000.00, 64000.00, '2026-06-26', '2026-07-03', 'delivered', 1, '2026-07-14 12:23:10'),
(4, 'PO-3304', 8, 4, NULL, 1, 198750.00, 198750.00, '2026-06-24', '2026-07-02', 'confirmed', 1, '2026-07-14 12:23:10'),
(5, 'PO-3301', 6, 2, NULL, 1, 221000.00, 221000.00, '2026-06-21', '2026-06-30', 'confirmed', 1, '2026-07-14 12:23:10'),
(6, 'PO-3305', 11, 1, NULL, 42, 450.00, 18900.00, '2026-06-20', '2026-06-21', 'delivered', 1, '2026-07-14 12:23:10'),
(7, 'PO-3298', 12, 2, NULL, 1, 145000.00, 145000.00, '2026-06-12', '2026-06-18', 'delivered', 1, '2026-07-14 12:23:10'),
(8, 'PO-3290', NULL, 3, NULL, 60, 460.00, 27600.00, '2026-06-05', '2026-06-10', 'delivered', 1, '2026-07-14 12:23:10'),
(9, 'PO-3284', NULL, 5, NULL, 1, 96000.00, 96000.00, '2026-05-28', '2026-06-02', 'cancelled', 1, '2026-07-14 12:23:10'),
(10, 'PO-3313', 1, 4, 1, 6, 1.00, 6.00, '2026-07-14', NULL, 'delivered', 1, '2026-07-14 18:58:47'),
(11, 'PO-3314', NULL, 4, 1, 7, 1.00, 7.00, '2026-07-14', '2026-07-22', 'cancelled', 1, '2026-07-14 19:45:04');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `requisition_id` int(11) NOT NULL,
  `req_code` varchar(20) NOT NULL,
  `department_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `estimated_amount` decimal(12,2) NOT NULL,
  `preferred_supplier_id` int(11) DEFAULT NULL,
  `catalogue_id` int(11) DEFAULT NULL,
  `justification` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `decision_notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`requisition_id`, `req_code`, `department_id`, `requested_by`, `item_description`, `quantity`, `estimated_amount`, `preferred_supplier_id`, `catalogue_id`, `justification`, `status`, `decided_by`, `decided_at`, `decision_notes`, `created_at`) VALUES
(1, 'REQ-1042', 1, 3, 'Laptops — Dell Latitude x6', 6, 312000.00, 4, NULL, 'Needed to equip six incoming hires starting July 20. Current pool of loaner laptops is fully allocated.', 'approved', 1, '2026-07-14 17:35:26', '', '2026-07-14 12:23:10'),
(2, 'REQ-1041', 2, 2, 'HVAC maintenance contract', 1, 84500.00, NULL, NULL, 'Annual preventive maintenance renewal.', 'pending', NULL, NULL, NULL, '2026-07-14 12:23:10'),
(3, 'REQ-1039', 3, 3, 'Trade-show booth materials', 1, 56200.00, 3, NULL, 'Q3 industry expo participation.', 'pending', NULL, NULL, NULL, '2026-07-14 12:23:10'),
(4, 'REQ-1037', 4, 4, 'Forklift rental — 3 months', 1, 145000.00, 5, NULL, 'Temporary capacity for peak season.', 'pending', NULL, NULL, NULL, '2026-07-14 12:23:10'),
(5, 'REQ-1036', 6, 3, 'Office pantry supplies — Q3', 42, 18900.00, 1, NULL, 'Quarterly pantry restock.', 'pending', NULL, NULL, NULL, '2026-07-14 12:23:10'),
(6, 'REQ-1038', 5, 2, 'Pallet racking — Aisle 4', 1, 221000.00, 2, NULL, 'Additional storage capacity for Q3 inventory.', 'approved', 1, '2026-06-26 02:00:00', NULL, '2026-07-14 12:23:10'),
(7, 'REQ-1035', 3, 3, 'Print collateral — Kaizen Print', 1, 32400.00, 3, NULL, 'Trade-show print collateral.', 'approved', 2, '2026-06-22 01:00:00', NULL, '2026-07-14 12:23:10'),
(8, 'REQ-1033', 1, 3, 'Firewall appliance renewal', 1, 198750.00, 4, NULL, 'End-of-life hardware replacement.', 'approved', 1, '2026-06-20 06:00:00', NULL, '2026-07-14 12:23:10'),
(9, 'REQ-1031', 2, 2, 'Janitorial contract renewal', 1, 64000.00, 6, NULL, 'Annual services renewal.', 'approved', 1, '2026-06-18 03:00:00', NULL, '2026-07-14 12:23:10'),
(10, 'REQ-1029', 1, 3, 'Monitor arms x20', 20, 41000.00, 4, NULL, 'Ergonomics rollout.', 'approved', 1, '2026-06-15 07:00:00', NULL, '2026-07-14 12:23:10'),
(11, 'REQ-1027', 6, 3, 'Office pantry supplies — Q2', 42, 18900.00, 1, NULL, 'Quarterly pantry restock.', 'approved', 1, '2026-06-19 02:00:00', NULL, '2026-07-14 12:23:10'),
(12, 'REQ-1018', 4, 4, 'Forklift rental — prior cycle', 1, 145000.00, 2, NULL, 'Temporary capacity.', 'approved', 1, '2026-06-11 01:00:00', NULL, '2026-07-14 12:23:10'),
(13, 'REQ-1030', 3, 3, 'Influencer event sponsorship', 1, 250000.00, NULL, NULL, 'Proposed brand sponsorship.', 'rejected', 1, '2026-06-16 08:00:00', 'Budget re-allocated to leasing instead of outright purchase.', '2026-07-14 12:23:10'),
(14, 'REQ-1024', 4, 4, 'Second forklift — outright purchase', 1, 890000.00, NULL, NULL, 'Requested outright purchase instead of rental.', 'rejected', 1, '2026-06-10 05:00:00', 'Rental remains more cost-effective than outright purchase this cycle.', '2026-07-14 12:23:10'),
(15, 'REQ-1043', 6, 1, 'Metal', 67, 67.00, 4, NULL, '67', 'approved', 1, '2026-07-14 18:59:05', '', '2026-07-14 17:57:56');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT 'Net 30',
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `category`, `contact_name`, `email`, `phone`, `address`, `payment_terms`, `contract_start`, `contract_end`, `status`, `created_at`) VALUES
(1, 'Solari Office Supplies', 'Office & Facilities', 'R. Salonga', 'sales@solari.test', '+63 2 8555 1010', 'Makati City', 'Net 30', '2021-03-01', '2027-03-01', 'active', '2026-07-14 12:23:10'),
(2, 'Northbridge Industrial', 'Industrial & Warehouse', 'T. Herrera', 'sales@northbridge.test', '+63 2 8555 2020', 'Cavite', 'Net 45', '2022-06-15', '2026-06-15', 'active', '2026-07-14 12:23:10'),
(3, 'Kaizen Print Co.', 'Print & Marketing', 'A. Lim', 'hello@kaizenprint.test', '+63 2 8555 3030', 'Quezon City', 'Net 15', '2020-01-10', '2027-01-10', 'active', '2026-07-14 12:23:10'),
(4, 'Circuit & Tech Distributors', 'IT & Electronics', 'B. Uy', 'sales@circuittech.test', '+63 2 8555 4040', 'Pasig City', 'Net 30', '2021-09-01', '2026-09-01', 'active', '2026-07-14 12:23:10'),
(5, 'Fleetline Rentals', 'Industrial & Warehouse', 'G. Domingo', 'ops@fleetline.test', '+63 2 8555 5050', 'Laguna', 'Net 15', '2023-02-01', '2025-02-01', 'inactive', '2026-07-14 12:23:10'),
(6, 'Greenhouse Janitorial', 'Facilities', 'N. Cruz', 'contact@greenhouse.test', '+63 2 8555 6060', 'Taguig City', 'Net 30', '2022-01-01', '2027-01-01', 'active', '2026-07-14 12:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_catalogue`
--

CREATE TABLE `supplier_catalogue` (
  `catalogue_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_catalogue`
--

INSERT INTO `supplier_catalogue` (`catalogue_id`, `supplier_id`, `product_name`, `description`, `unit`, `unit_price`, `created_at`) VALUES
(1, 4, 'Metal', 'metal', '1', 1.00, '2026-07-14 17:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_ratings`
--

CREATE TABLE `supplier_ratings` (
  `rating_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `delivery_score` decimal(5,2) NOT NULL,
  `quality_score` decimal(5,2) NOT NULL,
  `cost_score` decimal(5,2) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `rated_by` int(11) DEFAULT NULL,
  `rated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_ratings`
--

INSERT INTO `supplier_ratings` (`rating_id`, `supplier_id`, `delivery_score`, `quality_score`, `cost_score`, `notes`, `rated_by`, `rated_at`) VALUES
(1, 1, 96.00, 92.00, 84.00, 'Consistently reliable for office consumables', NULL, '2026-07-14 12:23:10'),
(2, 2, 78.00, 85.00, 70.00, 'Delivery delays on the last two rentals', NULL, '2026-07-14 12:23:10'),
(3, 3, 99.00, 95.00, 90.00, 'Excellent turnaround on print jobs', NULL, '2026-07-14 12:23:10'),
(4, 4, 88.00, 90.00, 80.00, 'Solid IT hardware partner', NULL, '2026-07-14 12:23:10'),
(5, 5, 61.00, 70.00, 65.00, 'Contract lapsed, under review', NULL, '2026-07-14 12:23:10'),
(6, 6, 100.00, 94.00, 88.00, 'Zero missed cleaning cycles this year', NULL, '2026-07-14 12:23:10'),
(7, 5, 20.00, 3.00, 24.00, NULL, 1, '2026-07-14 12:40:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('employee','manager','procurement_officer','admin') NOT NULL DEFAULT 'employee',
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `department_id`, `created_at`) VALUES
(1, 'Mara Cortez', 'mara@ledgerway.test', '$2y$10$raowvZsNJTW2c2qXJhcacuzSAKYR6auJu2iQmRQJVCxFXxWHDpZAy', 'admin', 6, '2026-07-14 12:23:10'),
(2, 'D. Reyes', 'reyes@ledgerway.test', '$2y$10$raowvZsNJTW2c2qXJhcacuzSAKYR6auJu2iQmRQJVCxFXxWHDpZAy', 'manager', 5, '2026-07-14 12:23:10'),
(3, 'J. Aquino', 'aquino@ledgerway.test', '$2y$10$raowvZsNJTW2c2qXJhcacuzSAKYR6auJu2iQmRQJVCxFXxWHDpZAy', 'employee', 1, '2026-07-14 12:23:10'),
(4, 'P. Ong', 'ong@ledgerway.test', '$2y$10$raowvZsNJTW2c2qXJhcacuzSAKYR6auJu2iQmRQJVCxFXxWHDpZAy', 'procurement_officer', 4, '2026-07-14 12:23:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `invoice_matching`
--
ALTER TABLE `invoice_matching`
  ADD PRIMARY KEY (`matching_id`),
  ADD UNIQUE KEY `po_id` (`po_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `matched_by` (`matched_by`),
  ADD KEY `payment_approved_by` (`payment_approved_by`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD UNIQUE KEY `po_code` (`po_code`),
  ADD UNIQUE KEY `requisition_id` (`requisition_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_po_catalogue` (`catalogue_id`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`requisition_id`),
  ADD UNIQUE KEY `req_code` (`req_code`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `preferred_supplier_id` (`preferred_supplier_id`),
  ADD KEY `decided_by` (`decided_by`),
  ADD KEY `fk_req_catalogue` (`catalogue_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `supplier_catalogue`
--
ALTER TABLE `supplier_catalogue`
  ADD PRIMARY KEY (`catalogue_id`),
  ADD KEY `fk_supplier_catalogue_supplier` (`supplier_id`);

--
-- Indexes for table `supplier_ratings`
--
ALTER TABLE `supplier_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `rated_by` (`rated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoice_matching`
--
ALTER TABLE `invoice_matching`
  MODIFY `matching_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `requisition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `supplier_catalogue`
--
ALTER TABLE `supplier_catalogue`
  MODIFY `catalogue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_ratings`
--
ALTER TABLE `supplier_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD CONSTRAINT `goods_receipts_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goods_receipts_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `invoice_matching`
--
ALTER TABLE `invoice_matching`
  ADD CONSTRAINT `invoice_matching_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_matching_ibfk_2` FOREIGN KEY (`receipt_id`) REFERENCES `goods_receipts` (`receipt_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_matching_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_matching_ibfk_4` FOREIGN KEY (`matched_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_matching_ibfk_5` FOREIGN KEY (`payment_approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_catalogue` FOREIGN KEY (`catalogue_id`) REFERENCES `supplier_catalogue` (`catalogue_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`requisition_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `fk_req_catalogue` FOREIGN KEY (`catalogue_id`) REFERENCES `supplier_catalogue` (`catalogue_id`),
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  ADD CONSTRAINT `requisitions_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `requisitions_ibfk_3` FOREIGN KEY (`preferred_supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requisitions_ibfk_4` FOREIGN KEY (`decided_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_catalogue`
--
ALTER TABLE `supplier_catalogue`
  ADD CONSTRAINT `fk_supplier_catalogue_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_ratings`
--
ALTER TABLE `supplier_ratings`
  ADD CONSTRAINT `supplier_ratings_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_ratings_ibfk_2` FOREIGN KEY (`rated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
