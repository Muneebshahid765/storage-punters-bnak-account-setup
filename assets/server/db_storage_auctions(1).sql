-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 02:24 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_storage_auctions`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auctions`
--

CREATE TABLE `tbl_auctions` (
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `unit_number` text NOT NULL,
  `disclose_unit_number_on_website` text NOT NULL,
  `outstanding_dept` decimal(15,2) NOT NULL,
  `starting_price` decimal(15,2) NOT NULL,
  `start_date` date NOT NULL,
  `start_time` text NOT NULL,
  `end_date` date NOT NULL,
  `end_time` text NOT NULL,
  `youtube_video_url` text NOT NULL,
  `unit_size` text NOT NULL,
  `tags` text NOT NULL,
  `additional_features` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auctions_release_payment_schedule`
--

CREATE TABLE `tbl_auctions_release_payment_schedule` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `invoice_amount` decimal(15,2) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_at` datetime NOT NULL DEFAULT current_timestamp(),
  `schedule_status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_categories`
--

CREATE TABLE `tbl_auction_categories` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_auction_categories`
--

INSERT INTO `tbl_auction_categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'General', '2025-02-25 06:35:21', '2025-02-25 06:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_category_relationship`
--

CREATE TABLE `tbl_auction_category_relationship` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `auction_category_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_confirmations`
--

CREATE TABLE `tbl_auction_confirmations` (
  `id` int(11) NOT NULL,
  `winner_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `confirmation_type_id` int(11) NOT NULL,
  `confirmed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_confirmation_types`
--

CREATE TABLE `tbl_auction_confirmation_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_auction_confirmation_types`
--

INSERT INTO `tbl_auction_confirmation_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Confirmed', '2025-04-17 06:08:02', '2025-04-17 06:08:02'),
(2, 'Declined', '2025-04-17 06:08:02', '2025-04-17 06:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_favorites`
--

CREATE TABLE `tbl_auction_favorites` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_release_payment_schedule`
--

CREATE TABLE `tbl_auction_release_payment_schedule` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auction_schedule_type_id` int(11) NOT NULL,
  `release_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_a` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_schedule_types`
--

CREATE TABLE `tbl_auction_schedule_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_auction_schedule_types`
--

INSERT INTO `tbl_auction_schedule_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Scheduled', '2025-04-17 08:20:11', '2025-04-17 08:20:11'),
(2, 'Released', '2025-04-17 08:20:11', '2025-04-17 08:20:11'),
(3, 'Failed', '2025-04-17 08:20:24', '2025-04-17 08:20:24'),
(4, 'Cancelled', '2025-04-17 08:20:24', '2025-04-17 08:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_status_relationship`
--

CREATE TABLE `tbl_auction_status_relationship` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `auction_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_storage_facility_relationship`
--

CREATE TABLE `tbl_auction_storage_facility_relationship` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_types`
--

CREATE TABLE `tbl_auction_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_auction_types`
--

INSERT INTO `tbl_auction_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Live', '2025-02-09 22:25:43', '2025-02-09 22:25:43'),
(2, 'Paused', '2025-02-09 22:25:43', '2025-02-09 22:25:43'),
(3, 'Scheduled', '2025-02-09 22:25:52', '2025-02-09 22:25:52'),
(4, 'Closed', '2025-02-09 22:25:52', '2025-02-09 22:25:52'),
(5, 'Cancelled', '2025-02-09 22:25:58', '2025-02-09 22:25:58'),
(6, 'Draft', '2025-02-09 22:26:12', '2025-02-09 22:26:12'),
(7, 'Under Review', '2025-02-09 22:26:12', '2025-02-09 22:26:12'),
(8, 'Sold', '2025-02-09 22:26:17', '2025-02-09 22:26:17'),
(9, 'Won', '2025-04-28 06:49:58', '2025-04-28 06:49:58'),
(10, 'Expired', '2025-05-01 08:13:53', '2025-05-01 08:13:53');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_upload_relationship`
--

CREATE TABLE `tbl_auction_upload_relationship` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `upload_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_views`
--

CREATE TABLE `tbl_auction_views` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_auction_won`
--

CREATE TABLE `tbl_auction_won` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bank`
--

CREATE TABLE `tbl_bank` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_bank_account_id` text NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `is_default` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bids`
--

CREATE TABLE `tbl_bids` (
  `id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bid_amount` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_companies`
--

CREATE TABLE `tbl_companies` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `website` text NOT NULL,
  `email` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_location_relationship`
--

CREATE TABLE `tbl_company_location_relationship` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_status_relationship`
--

CREATE TABLE `tbl_company_status_relationship` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_company_verfication_relationship`
--

CREATE TABLE `tbl_company_verfication_relationship` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `verfication_type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_email_queue`
--

CREATE TABLE `tbl_email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `to_email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `auction_item` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `auction_amount` decimal(15,2) NOT NULL,
  `auction_total` decimal(15,2) NOT NULL,
  `auction_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','processing','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoices`
--

CREATE TABLE `tbl_invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` text NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` text NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_auction_relationship`
--

CREATE TABLE `tbl_invoice_auction_relationship` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `invoice_type_id` int(11) NOT NULL,
  `invoice_payment_method_type_id` int(11) NOT NULL,
  `invoice_payment_status_type_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_payment_method_types`
--

CREATE TABLE `tbl_invoice_payment_method_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_invoice_payment_method_types`
--

INSERT INTO `tbl_invoice_payment_method_types` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'Cash', 'Payment made using physical currency.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(2, 'Credit Card', 'Payment made using a credit card.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(3, 'Debit Card', 'Payment made using a debit card.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(4, 'Bank Transfer', 'Direct payment via bank transfer.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(5, 'PayPal', 'Online payment through PayPal.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(6, 'Cryptocurrency', 'Payment made using Bitcoin, Ethereum, or other cryptocurrencies.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(7, 'Google Pay', 'Digital payment via Google Pay.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(8, 'Apple Pay', 'Digital payment via Apple Pay.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(9, 'Cheque', 'Payment made using a bank cheque.', '2025-03-18 22:24:51', '2025-03-18 22:24:51'),
(10, 'Wire Transfer', 'Electronic funds transfer between banks.', '2025-03-18 22:24:51', '2025-03-18 22:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_stripe_logs_relationship`
--

CREATE TABLE `tbl_invoice_stripe_logs_relationship` (
  `id` int(11) NOT NULL,
  `stripe_event_id` text NOT NULL,
  `stripe_payment_intent_id` text NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_charge_id` text NOT NULL,
  `amount_received` text NOT NULL,
  `currency` text NOT NULL,
  `payment_status` text NOT NULL,
  `event_type` text NOT NULL,
  `payment_at` datetime NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_stripe_relationship`
--

CREATE TABLE `tbl_invoice_stripe_relationship` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `stripe_invoice_id` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_invoice_types`
--

CREATE TABLE `tbl_invoice_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_invoice_types`
--

INSERT INTO `tbl_invoice_types` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'Draft', 'Invoice is created but not yet submitted.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(2, 'Submitted', 'Invoice has been submitted for review.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(3, 'Approved', 'Invoice has been reviewed and approved.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(4, 'Pending', 'Invoice is awaiting payment.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(5, 'Cancelled', 'Invoice has been canceled.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(6, 'Voided', 'Invoice has been invalidated and holds no financial impact.', '2025-03-18 22:31:30', '2025-03-18 22:31:30'),
(7, 'Disputed', 'Invoice is under dispute and being reviewed.', '2025-03-18 22:31:30', '2025-03-18 22:31:30');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_locations`
--

CREATE TABLE `tbl_locations` (
  `id` int(11) NOT NULL,
  `street_address` text NOT NULL,
  `apt_suite` text NOT NULL,
  `city` text NOT NULL,
  `postal_code` text NOT NULL,
  `state` text NOT NULL,
  `country` text NOT NULL,
  `phone` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notification_types`
--

CREATE TABLE `tbl_notification_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_notification_types`
--

INSERT INTO `tbl_notification_types` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'New Auction', 'A new auction has been listed. Check out the details and place your bids before the auction ends.', '2025-02-09 22:47:22', '2025-02-09 22:47:22'),
(2, 'Favorite Auction', 'A reminder for an auction you favorited. Stay updated as it approaches the closing time!', '2025-02-09 22:47:22', '2025-02-09 22:47:22'),
(3, 'Outbid Auction', 'You have been outbid on an auction. Click the link to place a higher bid and stay in the race.', '2025-02-09 22:47:36', '2025-02-09 22:47:36'),
(4, 'Winning Auction', 'Congratulations! You have won the auction. Here’s how to contact the seller and proceed with payment and collection.', '2025-02-09 22:47:36', '2025-02-09 22:47:36'),
(5, 'Auction Closed', 'The auction you participated in has ended. View the final results and explore other ongoing auctions.', '2025-02-09 22:47:56', '2025-02-09 22:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payment_types`
--

CREATE TABLE `tbl_payment_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_payment_types`
--

INSERT INTO `tbl_payment_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Unpaid', '2025-02-09 22:31:00', '2025-02-09 22:31:00'),
(2, 'Paid', '2025-02-09 22:31:00', '2025-02-09 22:31:00'),
(3, 'Pending', '2025-02-09 22:31:10', '2025-02-09 22:31:10'),
(4, 'Failed', '2025-02-09 22:31:10', '2025-02-09 22:31:10'),
(5, 'Refunded', '2025-02-09 22:31:25', '2025-02-09 22:31:25'),
(6, 'Partially Paid', '2025-02-09 22:31:25', '2025-02-09 22:31:25'),
(7, 'Overpaid', '2025-02-09 22:31:35', '2025-02-09 22:31:35'),
(8, 'Cancelled', '2025-02-09 22:31:35', '2025-02-09 22:31:35');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_preference_types`
--

CREATE TABLE `tbl_preference_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_preference_types`
--

INSERT INTO `tbl_preference_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Email', '2025-03-18 20:14:23', '2025-03-18 20:14:23'),
(2, 'Text', '2025-03-18 20:14:23', '2025-03-18 20:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_role_types`
--

CREATE TABLE `tbl_role_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_role_types`
--

INSERT INTO `tbl_role_types` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'Storage Facility', 'Manages and lists storage units for auctions.', '2025-02-08 15:26:26', '2025-02-08 15:26:26'),
(2, 'Punter', 'Participates in auctions by placing bids.', '2025-02-08 15:26:26', '2025-02-08 15:26:26'),
(3, 'Staff', 'Assists in managing storage facilities or platform operations.', '2025-02-09 22:54:22', '2025-02-09 22:54:22');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_company_access_relationship`
--

CREATE TABLE `tbl_staff_company_access_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_staff_permissions`
--

CREATE TABLE `tbl_staff_permissions` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_status_types`
--

CREATE TABLE `tbl_status_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `descriptions` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_status_types`
--

INSERT INTO `tbl_status_types` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'Active', 'Currently enabled and operational.', '2025-02-09 21:15:23', '2025-02-09 21:15:23'),
(2, 'Inactive', 'Currently disabled and not operational.', '2025-02-09 21:15:23', '2025-02-09 21:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_storage_facilities`
--

CREATE TABLE `tbl_storage_facilities` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `abn` text NOT NULL,
  `website` text NOT NULL,
  `description` text NOT NULL,
  `timezone` text NOT NULL,
  `contact_email` text NOT NULL,
  `additional_email` text NOT NULL,
  `office_hours` text NOT NULL,
  `payout_details` text NOT NULL,
  `youtube_video_url` text NOT NULL,
  `branding_logo` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_storage_facility_company_relationship`
--

CREATE TABLE `tbl_storage_facility_company_relationship` (
  `id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_storage_facility_location_relationship`
--

CREATE TABLE `tbl_storage_facility_location_relationship` (
  `id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_storage_facility_staff_relationship`
--

CREATE TABLE `tbl_storage_facility_staff_relationship` (
  `id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_storage_facility_status_relationship`
--

CREATE TABLE `tbl_storage_facility_status_relationship` (
  `id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_token_activation_types`
--

CREATE TABLE `tbl_token_activation_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_token_activation_types`
--

INSERT INTO `tbl_token_activation_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Email Activation', '2025-05-04 07:34:22', '2025-05-04 07:34:22'),
(2, 'Forgot Password', '2025-05-04 07:34:22', '2025-05-04 07:34:22'),
(3, 'Invitation Staff', '2025-05-05 05:06:55', '2025-05-05 05:06:55');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_uploads`
--

CREATE TABLE `tbl_uploads` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `path` text NOT NULL,
  `type` text NOT NULL,
  `size` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_activation_token`
--

CREATE TABLE `tbl_user_activation_token` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `token` text NOT NULL,
  `expires_at` datetime NOT NULL,
  `token_activation_type_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_activity`
--

CREATE TABLE `tbl_user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_status` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_alert_settings`
--

CREATE TABLE `tbl_user_alert_settings` (
  `id` int(11) NOT NULL,
  `preference_type_id` int(11) NOT NULL,
  `notification_type_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_bank_informations_relationship`
--

CREATE TABLE `tbl_user_bank_informations_relationship` (
  `id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_company_relationship`
--

CREATE TABLE `tbl_user_company_relationship` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_credentials`
--

CREATE TABLE `tbl_user_credentials` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_location_relationship`
--

CREATE TABLE `tbl_user_location_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_payment_methods_relationship`
--

CREATE TABLE `tbl_user_payment_methods_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `stripe_payment_method_id` text NOT NULL,
  `is_default` text NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `card_holder_first_name` text NOT NULL,
  `card_holder_last_name` text NOT NULL,
  `card_holder_email` text NOT NULL,
  `card_holder_phone` text NOT NULL,
  `card_billing_address` text NOT NULL,
  `card_billing_zip` text NOT NULL,
  `card_type` text NOT NULL,
  `card_holder_card_number` text NOT NULL,
  `card_expiration_month` text NOT NULL,
  `card_expiration_year` int(11) NOT NULL,
  `card_cvv` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_profile_relationship`
--

CREATE TABLE `tbl_user_profile_relationship` (
  `id` int(11) NOT NULL,
  `date_of_birth` date NOT NULL,
  `time_zone` text NOT NULL,
  `hear_us` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_role_relationship`
--

CREATE TABLE `tbl_user_role_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_status_relationship`
--

CREATE TABLE `tbl_user_status_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_type_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_stripe_relationship`
--

CREATE TABLE `tbl_user_stripe_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_customer_id` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_verfication_relationship`
--

CREATE TABLE `tbl_user_verfication_relationship` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verfication_type_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_verfication_types`
--

CREATE TABLE `tbl_verfication_types` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tbl_verfication_types`
--

INSERT INTO `tbl_verfication_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Email', '2025-02-09 22:20:16', '2025-02-09 22:20:16'),
(2, 'Phone', '2025-02-09 22:20:16', '2025-02-09 22:20:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_auctions`
--
ALTER TABLE `tbl_auctions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auctions_release_payment_schedule`
--
ALTER TABLE `tbl_auctions_release_payment_schedule`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auction_categories`
--
ALTER TABLE `tbl_auction_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auction_category_relationship`
--
ALTER TABLE `tbl_auction_category_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `auction_category_id` (`auction_category_id`);

--
-- Indexes for table `tbl_auction_confirmations`
--
ALTER TABLE `tbl_auction_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `winner_id` (`winner_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tbl_auction_confirmations_ibfk_3` (`confirmation_type_id`);

--
-- Indexes for table `tbl_auction_confirmation_types`
--
ALTER TABLE `tbl_auction_confirmation_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auction_favorites`
--
ALTER TABLE `tbl_auction_favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_auction_release_payment_schedule`
--
ALTER TABLE `tbl_auction_release_payment_schedule`
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `auction_schedule_type_id` (`auction_schedule_type_id`);

--
-- Indexes for table `tbl_auction_schedule_types`
--
ALTER TABLE `tbl_auction_schedule_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auction_status_relationship`
--
ALTER TABLE `tbl_auction_status_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `tbl_auction_status_relationship_ibfk_2` (`auction_type_id`);

--
-- Indexes for table `tbl_auction_storage_facility_relationship`
--
ALTER TABLE `tbl_auction_storage_facility_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`);

--
-- Indexes for table `tbl_auction_types`
--
ALTER TABLE `tbl_auction_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_auction_upload_relationship`
--
ALTER TABLE `tbl_auction_upload_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `upload_id` (`upload_id`);

--
-- Indexes for table `tbl_auction_views`
--
ALTER TABLE `tbl_auction_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_auction_won`
--
ALTER TABLE `tbl_auction_won`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_bank`
--
ALTER TABLE `tbl_bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_bids`
--
ALTER TABLE `tbl_bids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_company_location_relationship`
--
ALTER TABLE `tbl_company_location_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `tbl_company_status_relationship`
--
ALTER TABLE `tbl_company_status_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `status_id` (`status_type_id`);

--
-- Indexes for table `tbl_company_verfication_relationship`
--
ALTER TABLE `tbl_company_verfication_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `verfication_type_id` (`verfication_type_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `tbl_email_queue`
--
ALTER TABLE `tbl_email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_scheduled_at` (`status`,`scheduled_at`);

--
-- Indexes for table `tbl_invoices`
--
ALTER TABLE `tbl_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_invoice_auction_relationship`
--
ALTER TABLE `tbl_invoice_auction_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `invoice_payment_method_type_id` (`invoice_payment_method_type_id`),
  ADD KEY `invoice_type_id` (`invoice_type_id`),
  ADD KEY `invoice_payment_status_type_id` (`invoice_payment_status_type_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_invoice_payment_method_types`
--
ALTER TABLE `tbl_invoice_payment_method_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_invoice_stripe_logs_relationship`
--
ALTER TABLE `tbl_invoice_stripe_logs_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `tbl_invoice_stripe_relationship`
--
ALTER TABLE `tbl_invoice_stripe_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `tbl_invoice_types`
--
ALTER TABLE `tbl_invoice_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_locations`
--
ALTER TABLE `tbl_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_notification_types`
--
ALTER TABLE `tbl_notification_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_payment_types`
--
ALTER TABLE `tbl_payment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_preference_types`
--
ALTER TABLE `tbl_preference_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_role_types`
--
ALTER TABLE `tbl_role_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_staff_company_access_relationship`
--
ALTER TABLE `tbl_staff_company_access_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_type_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `tbl_staff_permissions`
--
ALTER TABLE `tbl_staff_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_status_types`
--
ALTER TABLE `tbl_status_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_storage_facilities`
--
ALTER TABLE `tbl_storage_facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_storage_facility_company_relationship`
--
ALTER TABLE `tbl_storage_facility_company_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `tbl_storage_facility_location_relationship`
--
ALTER TABLE `tbl_storage_facility_location_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `tbl_storage_facility_staff_relationship`
--
ALTER TABLE `tbl_storage_facility_staff_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_storage_facility_status_relationship`
--
ALTER TABLE `tbl_storage_facility_status_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storage_facility_id` (`storage_facility_id`),
  ADD KEY `status_type_id` (`status_type_id`);

--
-- Indexes for table `tbl_token_activation_types`
--
ALTER TABLE `tbl_token_activation_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_uploads`
--
ALTER TABLE `tbl_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user_activation_token`
--
ALTER TABLE `tbl_user_activation_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_activation_type_id` (`token_activation_type_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_user_activity`
--
ALTER TABLE `tbl_user_activity`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user_alert_settings`
--
ALTER TABLE `tbl_user_alert_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_type_id` (`notification_type_id`),
  ADD KEY `preference_type_id` (`preference_type_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_type_id` (`status_type_id`);

--
-- Indexes for table `tbl_user_bank_informations_relationship`
--
ALTER TABLE `tbl_user_bank_informations_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `tbl_user_company_relationship`
--
ALTER TABLE `tbl_user_company_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_type_id` (`status_type_id`);

--
-- Indexes for table `tbl_user_credentials`
--
ALTER TABLE `tbl_user_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl_user_credentials_ibfk_1` (`user_id`);

--
-- Indexes for table `tbl_user_location_relationship`
--
ALTER TABLE `tbl_user_location_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `tbl_user_payment_methods_relationship`
--
ALTER TABLE `tbl_user_payment_methods_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_type_id` (`status_type_id`);

--
-- Indexes for table `tbl_user_profile_relationship`
--
ALTER TABLE `tbl_user_profile_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_user_role_relationship`
--
ALTER TABLE `tbl_user_role_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `tbl_user_status_relationship`
--
ALTER TABLE `tbl_user_status_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_type_id`);

--
-- Indexes for table `tbl_user_stripe_relationship`
--
ALTER TABLE `tbl_user_stripe_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_user_verfication_relationship`
--
ALTER TABLE `tbl_user_verfication_relationship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `verfication_type_id` (`verfication_type_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `tbl_verfication_types`
--
ALTER TABLE `tbl_verfication_types`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_auctions`
--
ALTER TABLE `tbl_auctions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auctions_release_payment_schedule`
--
ALTER TABLE `tbl_auctions_release_payment_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_categories`
--
ALTER TABLE `tbl_auction_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_auction_category_relationship`
--
ALTER TABLE `tbl_auction_category_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_confirmations`
--
ALTER TABLE `tbl_auction_confirmations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_confirmation_types`
--
ALTER TABLE `tbl_auction_confirmation_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_auction_favorites`
--
ALTER TABLE `tbl_auction_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_schedule_types`
--
ALTER TABLE `tbl_auction_schedule_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_auction_status_relationship`
--
ALTER TABLE `tbl_auction_status_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_storage_facility_relationship`
--
ALTER TABLE `tbl_auction_storage_facility_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_types`
--
ALTER TABLE `tbl_auction_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_auction_upload_relationship`
--
ALTER TABLE `tbl_auction_upload_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_views`
--
ALTER TABLE `tbl_auction_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_auction_won`
--
ALTER TABLE `tbl_auction_won`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_bank`
--
ALTER TABLE `tbl_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_bids`
--
ALTER TABLE `tbl_bids`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_companies`
--
ALTER TABLE `tbl_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_company_location_relationship`
--
ALTER TABLE `tbl_company_location_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_company_status_relationship`
--
ALTER TABLE `tbl_company_status_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_company_verfication_relationship`
--
ALTER TABLE `tbl_company_verfication_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_email_queue`
--
ALTER TABLE `tbl_email_queue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoices`
--
ALTER TABLE `tbl_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoice_auction_relationship`
--
ALTER TABLE `tbl_invoice_auction_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoice_payment_method_types`
--
ALTER TABLE `tbl_invoice_payment_method_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_invoice_stripe_logs_relationship`
--
ALTER TABLE `tbl_invoice_stripe_logs_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoice_stripe_relationship`
--
ALTER TABLE `tbl_invoice_stripe_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_invoice_types`
--
ALTER TABLE `tbl_invoice_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_locations`
--
ALTER TABLE `tbl_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_notification_types`
--
ALTER TABLE `tbl_notification_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_payment_types`
--
ALTER TABLE `tbl_payment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_preference_types`
--
ALTER TABLE `tbl_preference_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_role_types`
--
ALTER TABLE `tbl_role_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_staff_company_access_relationship`
--
ALTER TABLE `tbl_staff_company_access_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_staff_permissions`
--
ALTER TABLE `tbl_staff_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_status_types`
--
ALTER TABLE `tbl_status_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_storage_facilities`
--
ALTER TABLE `tbl_storage_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_storage_facility_company_relationship`
--
ALTER TABLE `tbl_storage_facility_company_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_storage_facility_location_relationship`
--
ALTER TABLE `tbl_storage_facility_location_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_storage_facility_staff_relationship`
--
ALTER TABLE `tbl_storage_facility_staff_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_storage_facility_status_relationship`
--
ALTER TABLE `tbl_storage_facility_status_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_token_activation_types`
--
ALTER TABLE `tbl_token_activation_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_uploads`
--
ALTER TABLE `tbl_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_activation_token`
--
ALTER TABLE `tbl_user_activation_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_activity`
--
ALTER TABLE `tbl_user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_alert_settings`
--
ALTER TABLE `tbl_user_alert_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_bank_informations_relationship`
--
ALTER TABLE `tbl_user_bank_informations_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_company_relationship`
--
ALTER TABLE `tbl_user_company_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_credentials`
--
ALTER TABLE `tbl_user_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_location_relationship`
--
ALTER TABLE `tbl_user_location_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_payment_methods_relationship`
--
ALTER TABLE `tbl_user_payment_methods_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_profile_relationship`
--
ALTER TABLE `tbl_user_profile_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_role_relationship`
--
ALTER TABLE `tbl_user_role_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_status_relationship`
--
ALTER TABLE `tbl_user_status_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_stripe_relationship`
--
ALTER TABLE `tbl_user_stripe_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user_verfication_relationship`
--
ALTER TABLE `tbl_user_verfication_relationship`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_verfication_types`
--
ALTER TABLE `tbl_verfication_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_auction_category_relationship`
--
ALTER TABLE `tbl_auction_category_relationship`
  ADD CONSTRAINT `tbl_auction_category_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_category_relationship_ibfk_2` FOREIGN KEY (`auction_category_id`) REFERENCES `tbl_auction_categories` (`id`);

--
-- Constraints for table `tbl_auction_confirmations`
--
ALTER TABLE `tbl_auction_confirmations`
  ADD CONSTRAINT `tbl_auction_confirmations_ibfk_1` FOREIGN KEY (`winner_id`) REFERENCES `tbl_auction_won` (`id`),
  ADD CONSTRAINT `tbl_auction_confirmations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_auction_confirmations_ibfk_3` FOREIGN KEY (`confirmation_type_id`) REFERENCES `tbl_auction_confirmation_types` (`id`);

--
-- Constraints for table `tbl_auction_favorites`
--
ALTER TABLE `tbl_auction_favorites`
  ADD CONSTRAINT `tbl_auction_favorites_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_favorites_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_auction_release_payment_schedule`
--
ALTER TABLE `tbl_auction_release_payment_schedule`
  ADD CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_auction_release_payment_schedule_ibfk_3` FOREIGN KEY (`auction_schedule_type_id`) REFERENCES `tbl_auction_schedule_types` (`id`);

--
-- Constraints for table `tbl_auction_status_relationship`
--
ALTER TABLE `tbl_auction_status_relationship`
  ADD CONSTRAINT `tbl_auction_status_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_status_relationship_ibfk_2` FOREIGN KEY (`auction_type_id`) REFERENCES `tbl_auction_types` (`id`);

--
-- Constraints for table `tbl_auction_storage_facility_relationship`
--
ALTER TABLE `tbl_auction_storage_facility_relationship`
  ADD CONSTRAINT `tbl_auction_storage_facility_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_storage_facility_relationship_ibfk_2` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`);

--
-- Constraints for table `tbl_auction_upload_relationship`
--
ALTER TABLE `tbl_auction_upload_relationship`
  ADD CONSTRAINT `tbl_auction_upload_relationship_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_upload_relationship_ibfk_2` FOREIGN KEY (`upload_id`) REFERENCES `tbl_uploads` (`id`);

--
-- Constraints for table `tbl_auction_views`
--
ALTER TABLE `tbl_auction_views`
  ADD CONSTRAINT `tbl_auction_views_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_auction_won`
--
ALTER TABLE `tbl_auction_won`
  ADD CONSTRAINT `tbl_auction_won_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_auction_won_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_bids`
--
ALTER TABLE `tbl_bids`
  ADD CONSTRAINT `tbl_bids_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_bids_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_company_location_relationship`
--
ALTER TABLE `tbl_company_location_relationship`
  ADD CONSTRAINT `tbl_company_location_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  ADD CONSTRAINT `tbl_company_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`);

--
-- Constraints for table `tbl_company_status_relationship`
--
ALTER TABLE `tbl_company_status_relationship`
  ADD CONSTRAINT `tbl_company_status_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  ADD CONSTRAINT `tbl_company_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_company_verfication_relationship`
--
ALTER TABLE `tbl_company_verfication_relationship`
  ADD CONSTRAINT `tbl_company_verfication_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  ADD CONSTRAINT `tbl_company_verfication_relationship_ibfk_2` FOREIGN KEY (`verfication_type_id`) REFERENCES `tbl_verfication_types` (`id`),
  ADD CONSTRAINT `tbl_company_verfication_relationship_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_invoice_auction_relationship`
--
ALTER TABLE `tbl_invoice_auction_relationship`
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`),
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `tbl_auctions` (`id`),
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_3` FOREIGN KEY (`invoice_payment_method_type_id`) REFERENCES `tbl_invoice_payment_method_types` (`id`),
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_4` FOREIGN KEY (`invoice_type_id`) REFERENCES `tbl_invoice_types` (`id`),
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_5` FOREIGN KEY (`invoice_payment_status_type_id`) REFERENCES `tbl_payment_types` (`id`),
  ADD CONSTRAINT `tbl_invoice_auction_relationship_ibfk_6` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_invoice_stripe_logs_relationship`
--
ALTER TABLE `tbl_invoice_stripe_logs_relationship`
  ADD CONSTRAINT `tbl_invoice_stripe_logs_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`);

--
-- Constraints for table `tbl_invoice_stripe_relationship`
--
ALTER TABLE `tbl_invoice_stripe_relationship`
  ADD CONSTRAINT `tbl_invoice_stripe_relationship_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`id`);

--
-- Constraints for table `tbl_staff_company_access_relationship`
--
ALTER TABLE `tbl_staff_company_access_relationship`
  ADD CONSTRAINT `tbl_staff_company_access_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_staff_company_access_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`),
  ADD CONSTRAINT `tbl_staff_company_access_relationship_ibfk_3` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`);

--
-- Constraints for table `tbl_staff_permissions`
--
ALTER TABLE `tbl_staff_permissions`
  ADD CONSTRAINT `tbl_staff_permissions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  ADD CONSTRAINT `tbl_staff_permissions_ibfk_2` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  ADD CONSTRAINT `tbl_staff_permissions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_storage_facility_company_relationship`
--
ALTER TABLE `tbl_storage_facility_company_relationship`
  ADD CONSTRAINT `tbl_storage_facility_company_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  ADD CONSTRAINT `tbl_storage_facility_company_relationship_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`);

--
-- Constraints for table `tbl_storage_facility_location_relationship`
--
ALTER TABLE `tbl_storage_facility_location_relationship`
  ADD CONSTRAINT `tbl_storage_facility_location_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  ADD CONSTRAINT `tbl_storage_facility_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`);

--
-- Constraints for table `tbl_storage_facility_staff_relationship`
--
ALTER TABLE `tbl_storage_facility_staff_relationship`
  ADD CONSTRAINT `tbl_storage_facility_staff_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  ADD CONSTRAINT `tbl_storage_facility_staff_relationship_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_storage_facility_status_relationship`
--
ALTER TABLE `tbl_storage_facility_status_relationship`
  ADD CONSTRAINT `tbl_storage_facility_status_relationship_ibfk_1` FOREIGN KEY (`storage_facility_id`) REFERENCES `tbl_storage_facilities` (`id`),
  ADD CONSTRAINT `tbl_storage_facility_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_user_activation_token`
--
ALTER TABLE `tbl_user_activation_token`
  ADD CONSTRAINT `tbl_user_activation_token_ibfk_1` FOREIGN KEY (`token_activation_type_id`) REFERENCES `tbl_token_activation_types` (`id`),
  ADD CONSTRAINT `tbl_user_activation_token_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_user_activity`
--
ALTER TABLE `tbl_user_activity`
  ADD CONSTRAINT `tbl_user_activity_ibfk_1` FOREIGN KEY (`id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_user_alert_settings`
--
ALTER TABLE `tbl_user_alert_settings`
  ADD CONSTRAINT `tbl_user_alert_settings_ibfk_1` FOREIGN KEY (`notification_type_id`) REFERENCES `tbl_notification_types` (`id`),
  ADD CONSTRAINT `tbl_user_alert_settings_ibfk_2` FOREIGN KEY (`preference_type_id`) REFERENCES `tbl_preference_types` (`id`),
  ADD CONSTRAINT `tbl_user_alert_settings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_alert_settings_ibfk_4` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_user_bank_informations_relationship`
--
ALTER TABLE `tbl_user_bank_informations_relationship`
  ADD CONSTRAINT `tbl_user_bank_informations_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_bank_informations_relationship_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `tbl_user_bank_informations_relationship` (`id`);

--
-- Constraints for table `tbl_user_company_relationship`
--
ALTER TABLE `tbl_user_company_relationship`
  ADD CONSTRAINT `tbl_user_company_relationship_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `tbl_companies` (`id`),
  ADD CONSTRAINT `tbl_user_company_relationship_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_company_relationship_ibfk_3` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_user_credentials`
--
ALTER TABLE `tbl_user_credentials`
  ADD CONSTRAINT `tbl_user_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_user_location_relationship`
--
ALTER TABLE `tbl_user_location_relationship`
  ADD CONSTRAINT `tbl_user_location_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_location_relationship_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `tbl_locations` (`id`);

--
-- Constraints for table `tbl_user_payment_methods_relationship`
--
ALTER TABLE `tbl_user_payment_methods_relationship`
  ADD CONSTRAINT `tbl_user_payment_methods_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_payment_methods_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_user_profile_relationship`
--
ALTER TABLE `tbl_user_profile_relationship`
  ADD CONSTRAINT `tbl_user_profile_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_user_role_relationship`
--
ALTER TABLE `tbl_user_role_relationship`
  ADD CONSTRAINT `tbl_user_role_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_role_relationship_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `tbl_role_types` (`id`);

--
-- Constraints for table `tbl_user_status_relationship`
--
ALTER TABLE `tbl_user_status_relationship`
  ADD CONSTRAINT `tbl_user_status_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_status_relationship_ibfk_2` FOREIGN KEY (`status_type_id`) REFERENCES `tbl_status_types` (`id`);

--
-- Constraints for table `tbl_user_stripe_relationship`
--
ALTER TABLE `tbl_user_stripe_relationship`
  ADD CONSTRAINT `tbl_user_stripe_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`);

--
-- Constraints for table `tbl_user_verfication_relationship`
--
ALTER TABLE `tbl_user_verfication_relationship`
  ADD CONSTRAINT `tbl_user_verfication_relationship_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`),
  ADD CONSTRAINT `tbl_user_verfication_relationship_ibfk_2` FOREIGN KEY (`verfication_type_id`) REFERENCES `tbl_verfication_types` (`id`),
  ADD CONSTRAINT `tbl_user_verfication_relationship_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `tbl_status_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
