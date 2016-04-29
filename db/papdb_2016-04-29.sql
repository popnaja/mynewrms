-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2016 at 06:02 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `papdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `pap_comp_process`
--

CREATE TABLE IF NOT EXISTS `pap_comp_process` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comp_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `volume` int(10) unsigned NOT NULL,
  `est_time_hour` decimal(5,2) unsigned DEFAULT NULL,
  `machine_id` int(10) unsigned DEFAULT NULL,
  `result` smallint(5) unsigned DEFAULT NULL,
  `plan_start` datetime DEFAULT NULL,
  `plan_end` datetime DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_comp_process_pap_order_comp1_idx` (`comp_id`),
  KEY `fk_pap_comp_process_pap_process1_idx` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `pap_comp_process`
--

INSERT INTO `pap_comp_process` (`id`, `comp_id`, `process_id`, `name`, `volume`, `est_time_hour`, `machine_id`, `result`, `plan_start`, `plan_end`, `start`, `end`, `remark`) VALUES
(14, 4, 13, 'ตัดเจียน', 1000, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 4, 10, '0.25,กลับใน,4/4,675', 1350, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 4, 4, 'PVC เงา', 1000, '24.00', NULL, 1000, NULL, NULL, '2016-04-29 21:18:55', '2016-04-29 21:19:53', NULL),
(17, 4, 24, 'ตัดแบ่ง', 3300, '0.00', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pap_contact`
--

CREATE TABLE IF NOT EXISTS `pap_contact` (
  `contact_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `contact_cat` tinyint(4) DEFAULT NULL,
  `contact_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_tel` varchar(45) NOT NULL,
  `contact_remark` text,
  PRIMARY KEY (`contact_id`),
  KEY `fk_pap_contact_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `pap_contact`
--

INSERT INTO `pap_contact` (`contact_id`, `customer_id`, `contact_cat`, `contact_name`, `contact_email`, `contact_tel`, `contact_remark`) VALUES
(1, 9, 1, 'นายไก่ กา', 'ch@gmail.com', '024442424', ''),
(2, 9, 1, 'นายหล่อ', 'hand@gmail.com', '02-555-7777', ''),
(3, 8, 1, 'นายสาม สหาย', 'three@gmail.com', '03-333-3333', ''),
(4, 1, 1, 'นายข้าวสวย หุงดี', 'rice@gmail.com', '02-111-1111', ''),
(5, 2, 1, 'นางสาว สวย', 'pretty@gmail.com', '03-333-2222', ''),
(6, 10, 1, 'นายใหญ่', 'big@gmail.com', '09-999-9999', 'test'),
(7, 10, 1, 'นายรอง', 'rong@gmail.com', '01-555-5555', '00'),
(8, 11, 1, 'คุณสวย ', 'beauty@gmail.com', '061-888-7777', ''),
(11, 11, 1, 'คุณสวยมาก', 'beauty@gmail.com', '024564568', ''),
(12, 12, 1, 'คุณ เขียว', 'kGreen@gmail.com', '081-777-7535', ''),
(13, 12, 1, 'คุณ รัก', 'lov@gmail.com', '081-777-5324', ''),
(14, 13, 1, 'นายวัน', 'one@gmail.com', '01', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_crm`
--

CREATE TABLE IF NOT EXISTS `pap_crm` (
  `crm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `crm_detail` text,
  `crm_date` date NOT NULL,
  PRIMARY KEY (`crm_id`),
  KEY `fk_pap_crm_pap_user1_idx` (`user_id`),
  KEY `fk_pap_crm_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `pap_crm`
--

INSERT INTO `pap_crm` (`crm_id`, `user_id`, `customer_id`, `crm_detail`, `crm_date`) VALUES
(1, 1, 9, 'โทรหานัดพบ1', '2016-02-07'),
(2, 1, 9, 'เข้าพบ', '2016-02-08'),
(3, 5, 9, 'Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.', '2016-02-09'),
(4, 1, 9, 'test', '2016-02-24'),
(5, 1, 9, 'นัดพบผู้บริหาร', '2016-03-07');

-- --------------------------------------------------------

--
-- Table structure for table `pap_customer`
--

CREATE TABLE IF NOT EXISTS `pap_customer` (
  `customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_taxid` varchar(17) DEFAULT NULL,
  `customer_address` text NOT NULL,
  `customer_url` varchar(255) DEFAULT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_tel` varchar(20) NOT NULL,
  `customer_fax` varchar(20) DEFAULT NULL,
  `customer_pay` tinyint(4) DEFAULT NULL,
  `customer_credit_day` tinyint(4) DEFAULT NULL,
  `customer_credit_amount` int(10) unsigned DEFAULT NULL,
  `customer_place_bill` varchar(50) DEFAULT NULL,
  `customer_collect_cheque` varchar(50) DEFAULT NULL,
  `customer_added` datetime NOT NULL,
  `customer_status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `pap_customer`
--

INSERT INTO `pap_customer` (`customer_id`, `customer_code`, `customer_name`, `customer_taxid`, `customer_address`, `customer_url`, `customer_email`, `customer_tel`, `customer_fax`, `customer_pay`, `customer_credit_day`, `customer_credit_amount`, `customer_place_bill`, `customer_collect_cheque`, `customer_added`, `customer_status`) VALUES
(1, 'A00001', 'บริษัท ใหม่', '1-2312-31231-23-1', '1234 บ้านใหม่', '', 'test@gmail.com', '02-222-2222', '02-333-3333', 1, 30, 300000, 'day', 'day', '2016-01-26 20:44:33', 1),
(2, 'A00002', 'บริษัท สอง', '1-1111-11111-11-1', '2/22 หมู่บ้าน สอง เขต สองสาม 12121', 'www.twocoltd.com', 'two@gmail.com', '02-222-2222', '', 1, 30, 300000, 'dofw', 'dofw', '2016-02-03 17:31:58', 1),
(8, 'A00003', 'บจก. สามสหาย', '3-3333-33333-33-3', '33 หมู่บ้านสาม', '', 'threeg@gmail.com', '02-333-3333', '', 0, 0, 0, '', '', '2016-02-06 12:18:44', 1),
(9, 'D00001', 'บจก สี่', '', '44/4', '', 'four@gmail.com', '444-4444', '', 1, 30, 300000, 'eofm', 'eofm', '2016-02-06 12:22:31', 1),
(10, 'D00002', 'บจก. พยาไท', '1-2312-31231-23-1', '12/55 กทม', 'ีpt.com', 'pt@gmail.com', '01-222-4444', '01-223-3333', 0, 0, 0, '', '', '2016-02-20 12:08:05', 2),
(11, 'A00004', 'บริษัท นิตยสารสวย จำกัด', '1-2312-31231-23-1', '12/23 กรุงเทพฯ', '', 'bbook@gmail.com', '02-444-2525', '', 1, 30, 100000, 'eofm', 'eofm', '2016-03-08 11:54:40', 2),
(12, 'A00005', 'บจก เขียวรักโลก จำกัด', '1-2312-31231-23-1', '12/34 หมู่บ้านเขียว กรุงเทพฯ 10110', 'www.green.com', 'green@gmail.com', '01-223-3332', '01-223-3333', 1, 30, 200000, 'day', 'day', '2016-04-18 08:42:10', 2),
(13, 'C00001', 'บจก. วันวางบิล', '1-2345-67894-56-2', '35', '', '35@gmail.com', '02', '02', 1, 30, 100000, 'day', 'dofw', '2016-04-27 20:04:05', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pap_customer_cat`
--

CREATE TABLE IF NOT EXISTS `pap_customer_cat` (
  `tax_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tax_id`,`customer_id`),
  KEY `fk_pap_customer_has_pap_term_tax_pap_term_tax1_idx` (`tax_id`),
  KEY `fk_pap_customer_has_pap_term_tax_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_customer_cat`
--

INSERT INTO `pap_customer_cat` (`tax_id`, `customer_id`) VALUES
(5, 1),
(5, 12),
(7, 8),
(8, 9),
(8, 10),
(12, 2),
(12, 11),
(12, 13);

-- --------------------------------------------------------

--
-- Table structure for table `pap_customer_meta`
--

CREATE TABLE IF NOT EXISTS `pap_customer_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_customer_meta_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- Dumping data for table `pap_customer_meta`
--

INSERT INTO `pap_customer_meta` (`id`, `customer_id`, `meta_key`, `meta_value`) VALUES
(1, 10, 'tax_exclude', 'no'),
(2, 9, 'tax_exclude', 'no'),
(3, 8, 'tax_exclude', 'no'),
(4, 2, 'tax_exclude', 'no'),
(5, 1, 'tax_exclude', 'no'),
(6, 11, 'tax_exclude', 'yes'),
(17, 1, 'picture', '/p-pap/image/customer/A00001-FGC85HDP3QBVEY5_s.jpg,/p-pap/image/customer/A00001-colbus_s.jpg,/p-pap/image/customer/A00001-room41_s.jpg'),
(18, 10, 'picture', NULL),
(19, 2, 'picture', ''),
(24, 11, 'picture', ''),
(25, 12, 'picture', '/p-pap/image/customer/A00005-12608_1054791597900964_1477366911948896117_n_s.jpg'),
(26, 12, 'tax_exclude', 'no'),
(27, 13, 'picture', ''),
(28, 13, 'tax_exclude', 'no'),
(29, 13, 'bill_day', '5'),
(30, 13, 'cheque_week', '2'),
(31, 13, 'cheque_weekday', '1'),
(32, 12, 'bill_day', '5'),
(33, 12, 'cheque_day', '10'),
(34, 9, 'picture', ''),
(35, 2, 'bill_week', '1'),
(36, 2, 'bill_weekday', '1'),
(37, 2, 'cheque_week', '2'),
(38, 2, 'cheque_weekday', '1'),
(39, 1, 'bill_day', '7'),
(40, 1, 'cheque_day', '14');

-- --------------------------------------------------------

--
-- Table structure for table `pap_cus_ad`
--

CREATE TABLE IF NOT EXISTS `pap_cus_ad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `map` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_cus_ad_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `pap_cus_ad`
--

INSERT INTO `pap_cus_ad` (`id`, `customer_id`, `name`, `address`, `map`) VALUES
(1, 1, 'คลังสินค้า บริษัทใหม่ จำกัด (สำนักงานใหญ่)', '32/12 แขวงหนองค้างพลู เขตหนองแขม\r\nกทม. 10160', NULL),
(3, 1, 'ศูนย์กระจายสินค้า', '20/15 แขวงพระนคร เขตพระนคร กทม. 10000', NULL),
(4, 2, 'คลังสินค้า บริษัท สอง จำกัด (สำนักงานใหญ่)', '22/22 แขวงป้อมปราม เขตป้อมปราบ กทม. 12345', NULL),
(5, 2, 'ศูนย์กระจายสินค้า', 'กทม', '/p-pap/image/customer/map/FGC85HDP3QBVEY5_s.jpg'),
(6, 2, 'ศูนย์กระจาย 2', 'กทม 2', '/p-pap/image/customer/map/php5550_s.png');

-- --------------------------------------------------------

--
-- Table structure for table `pap_delivery`
--

CREATE TABLE IF NOT EXISTS `pap_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no` varchar(45) DEFAULT NULL,
  `contact` int(10) unsigned NOT NULL,
  `address` int(10) unsigned NOT NULL,
  `remark` text,
  `date` date NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `total` decimal(10,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_delivery_pap_contact1_idx` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_delivery`
--

INSERT INTO `pap_delivery` (`id`, `no`, `contact`, `address`, `remark`, `date`, `status`, `total`) VALUES
(1, 'DN16040001', 4, 0, '', '2016-04-27', 80, '86353.00'),
(2, 'DN16040002', 5, 4, '', '2016-04-30', 98, '15000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pap_delivery_dt`
--

CREATE TABLE IF NOT EXISTS `pap_delivery_dt` (
  `deli_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `job_name` varchar(255) DEFAULT NULL,
  `credit` tinyint(4) DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `type` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`deli_id`,`order_id`),
  KEY `fk_pap_delivery_has_pap_order_pap_order1_idx` (`order_id`),
  KEY `fk_pap_delivery_has_pap_order_pap_delivery1_idx` (`deli_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_delivery_dt`
--

INSERT INTO `pap_delivery_dt` (`deli_id`, `order_id`, `qty`, `price`, `discount`, `job_name`, `credit`, `customer_id`, `type`) VALUES
(2, 2, 3000, '15000.00', '0.00', 'QT160400001:แผ่นพับสอง', 30, 2, 11);

-- --------------------------------------------------------

--
-- Table structure for table `pap_invoice`
--

CREATE TABLE IF NOT EXISTS `pap_invoice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no` varchar(45) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `remark` text,
  `discount` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_invoice_pap_customer1_idx` (`customer_id`),
  KEY `fk_pap_invoice_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pap_invoice`
--

INSERT INTO `pap_invoice` (`id`, `no`, `customer_id`, `user_id`, `date`, `remark`, `discount`, `total`) VALUES
(2, 'IV16040001', 2, 1, '2016-04-29', '', '3000.00', '7000.00'),
(3, 'IV16040002', 2, 1, '2016-04-29', '', '1000.00', '4000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pap_invoice_dt`
--

CREATE TABLE IF NOT EXISTS `pap_invoice_dt` (
  `invoice_id` int(10) unsigned NOT NULL,
  `deli_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`invoice_id`,`deli_id`),
  KEY `fk_pap_invoice_has_pap_order_pap_invoice1_idx` (`invoice_id`),
  KEY `fk_pap_invoice_dt_pap_delivery1_idx` (`deli_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_invoice_dt`
--

INSERT INTO `pap_invoice_dt` (`invoice_id`, `deli_id`, `amount`) VALUES
(2, 2, '10000.00'),
(3, 2, '5000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pap_log`
--

CREATE TABLE IF NOT EXISTS `pap_log` (
  `id` bigint(19) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `function` varchar(255) NOT NULL,
  `info` text,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_log_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `pap_log`
--

INSERT INTO `pap_log` (`id`, `user_id`, `function`, `info`, `date`) VALUES
(1, 1, 'delete_quote', '{"request":"delete_quote","qid":"2","redirect":"http:\\/\\/localhost\\/resolutems\\/p-pap\\/quotation.php"}', '2016-04-28 23:34:02'),
(2, 1, 'delete_quote', '{"request":"delete_quote","qid":"1","redirect":"http:\\/\\/localhost\\/resolutems\\/p-pap\\/quotation.php"}', '2016-04-28 23:34:49'),
(3, 1, 'delete_process_po', '{"request":"delete_process_po","poid":"1","redirect":"http:\\/\\/localhost\\/resolutems\\/p-pap\\/outsource.php"}', '2016-04-29 21:20:17'),
(4, 1, 'delete_pbill', '{"request":"delete_pbill","bid":"1","redirect":"http:\\/\\/localhost\\/resolutems\\/p-pap\\/ac_bill.php"}', '2016-04-29 21:51:08'),
(5, 1, 'delete_invoice', '{"request":"delete_invoice","ivid":"1","redirect":"http:\\/\\/localhost\\/resolutems\\/p-pap\\/ac_bill.php"}', '2016-04-29 22:09:23');

-- --------------------------------------------------------

--
-- Table structure for table `pap_machine`
--

CREATE TABLE IF NOT EXISTS `pap_machine` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `cap` float unsigned NOT NULL,
  `setup_min` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_machine_pap_process1_idx` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `pap_machine`
--

INSERT INTO `pap_machine` (`id`, `process_id`, `name`, `cap`, `setup_min`) VALUES
(1, 13, 'ตัดเจียน 1', 15000, 5),
(2, 10, 'เครื่องพิมพ์ 4 สี 1', 7000, 30),
(3, 29, 'เครื่องพิมพ์ 1 สี 1', 8000, 20),
(4, 29, 'เครื่องพิมพ์ 1 สี 2', 8000, 20),
(5, 10, 'เครื่องพิมพ์ 4 สี 2', 7000, 30),
(6, 24, 'ตัด 1', 50000, 10),
(7, 8, 'พับ 1', 4000, 15),
(8, 8, 'พับ 2', 4000, 15),
(9, 9, 'หน่วยเก็บเล่ม(6คน)', 24000, 0),
(10, 2, 'เย็บลวด 1', 2000, 20),
(11, 2, 'เย็บลวด 2', 2000, 20),
(12, 1, 'ไสกาว', 3000, 20),
(13, 22, 'ตัดสามด้าน 1', 2000, 20),
(14, 22, 'ตัดสามด้าน 2', 2000, 20);

-- --------------------------------------------------------

--
-- Table structure for table `pap_mach_user`
--

CREATE TABLE IF NOT EXISTS `pap_mach_user` (
  `user_id` int(10) unsigned NOT NULL,
  `mach_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`mach_id`),
  KEY `fk_pap_user_has_pap_machine_pap_machine1_idx` (`mach_id`),
  KEY `fk_pap_user_has_pap_machine_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_mach_user`
--

INSERT INTO `pap_mach_user` (`user_id`, `mach_id`) VALUES
(6, 1),
(8, 2),
(9, 3),
(9, 4),
(8, 5),
(6, 6),
(10, 7),
(11, 8);

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat`
--

CREATE TABLE IF NOT EXISTS `pap_mat` (
  `mat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mat_code` varchar(100) DEFAULT NULL,
  `mat_name` varchar(255) DEFAULT NULL,
  `mat_unit` varchar(45) NOT NULL,
  `mat_cat_id` int(10) unsigned NOT NULL,
  `mat_type` int(10) unsigned DEFAULT NULL,
  `mat_size` int(10) unsigned DEFAULT NULL,
  `mat_weight` float unsigned DEFAULT NULL,
  `mat_order_lot_size` smallint(5) unsigned NOT NULL,
  `mat_std_cost` float unsigned NOT NULL,
  `mat_std_leadtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mat_id`),
  KEY `fk_pap_mat_pap_option1_idx` (`mat_cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `pap_mat`
--

INSERT INTO `pap_mat` (`mat_id`, `mat_code`, `mat_name`, `mat_unit`, `mat_cat_id`, `mat_type`, `mat_size`, `mat_weight`, `mat_order_lot_size`, `mat_std_cost`, `mat_std_leadtime`) VALUES
(1, NULL, 'กระดาษปอนด์ 24x35 70g', 'แผ่น', 8, 43, 18, 22, 500, 1.10013, 3),
(3, NULL, 'กระดาษอาร์ทด้าน 25x36 160g', 'แผ่น', 8, 45, 19, 35, 500, 3.01935, 5),
(4, NULL, 'กระดาษปอนด์ 24x35 74g', 'แผ่น', 8, 43, 18, 25, 500, 1.16299, 5),
(5, NULL, 'กระดาษอาร์ทด้าน 25x36 300g', 'แผ่น', 8, 45, 19, 46, 125, 5.66129, 5),
(6, NULL, 'กระดาษอาร์ทด้าน 24x35 120g', 'แผ่น', 8, 45, 18, 33, 500, 1.88594, 5),
(7, NULL, 'กระดาษปอนด์ 31x43 70g', 'แผ่น', 8, 43, 21, 22, 500, 1.7759, 5),
(8, NULL, 'กระดาษอาร์ทมัน 25x36 230g', 'แผ่น', 8, 44, 19, 48, 125, 3.8729, 5),
(9, NULL, 'กระดาษอาร์ทมัน 25x36 260g', 'แผ่น', 8, 44, 19, 47, 125, 4.98194, 5),
(10, NULL, 'กระดาษปอนด์ 24x35 75g', 'แผ่น', 8, 43, 18, 52, 500, 1.17871, 5),
(11, NULL, 'กระดาษอาร์ทการ์ด 25x36 180g', 'แผ่น', 8, 57, 19, 36, 100, 3.44903, 5),
(12, NULL, 'กระดาษอาร์ทการ์ด 25x36 190g', 'แผ่น', 8, 57, 19, 37, 100, 5, 5),
(13, NULL, 'กาวทาเคลือบเพลต', 'ลิตร', 16, 0, 0, 0, 10, 2000, 10),
(14, NULL, 'แอลกอฮอล์', 'ลิตร', 16, 0, 0, 0, 10, 200, 10),
(15, NULL, 'สีทอง', 'ลิตร', 15, 0, 0, 0, 5, 300, 3),
(16, NULL, 'น้ำยาฟาวเท่น', 'ลิตร', 15, 0, 0, 0, 15, 200, 10),
(17, NULL, 'ใบมีดเครื่องตัด', 'ใบ', 16, 0, 0, 0, 1, 10000, 30),
(18, NULL, 'กระดาษอาร์ทการ์ด 31x43 190g', 'แผ่น', 8, 57, 21, 37, 125, 5.3105, 2),
(19, NULL, 'กระดาษปอนด์ 31x43 74g', 'แผ่น', 8, 43, 21, 25, 500, 1.84556, 3),
(20, '', 'กระดาษปอนด์ 24x35 62.9g', 'แผ่น', 8, 43, 18, 23, 100, 0.98854, 2);

-- --------------------------------------------------------

--
-- Table structure for table `pap_matmeta`
--

CREATE TABLE IF NOT EXISTS `pap_matmeta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mat_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_matmeta_pap_mat1_idx` (`mat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- Dumping data for table `pap_matmeta`
--

INSERT INTO `pap_matmeta` (`id`, `mat_id`, `meta_key`, `meta_value`) VALUES
(1, 20, 'paper_cost_base', 'baht/kg'),
(2, 20, 'paper_cost', '29'),
(3, 15, 'paper_cost_base', 'baht/kg'),
(4, 15, 'paper_cost', ''),
(5, 1, 'paper_cost_base', 'baht/kg'),
(6, 1, 'paper_cost', '29'),
(7, 7, 'paper_cost_base', 'baht/kg'),
(8, 7, 'paper_cost', '29.5'),
(9, 5, 'paper_cost_base', 'baht/kg'),
(10, 5, 'paper_cost', '32.5'),
(11, 12, 'paper_cost_base', 'baht/lot'),
(12, 12, 'paper_cost', '500'),
(13, 3, 'paper_cost_base', 'baht/kg'),
(14, 3, 'paper_cost', '32.5'),
(15, 19, 'paper_cost_base', 'baht/kg'),
(16, 19, 'paper_cost', '29'),
(17, 10, 'paper_cost_base', 'baht/kg'),
(18, 10, 'paper_cost', '29'),
(19, 8, 'paper_cost_base', 'baht/kg'),
(20, 8, 'paper_cost', '29'),
(21, 6, 'paper_cost_base', 'baht/kg'),
(22, 6, 'paper_cost', '29'),
(23, 4, 'paper_cost_base', 'baht/kg'),
(24, 4, 'paper_cost', '29'),
(25, 11, 'paper_cost_base', 'baht/kg'),
(26, 11, 'paper_cost', '33'),
(27, 18, 'paper_cost_base', 'baht/kg'),
(28, 18, 'paper_cost', '32.5'),
(29, 9, 'paper_cost_base', 'baht/kg'),
(30, 9, 'paper_cost', '33');

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_cost`
--

CREATE TABLE IF NOT EXISTS `pap_mat_cost` (
  `mat_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `mat_costperunit` float unsigned NOT NULL,
  PRIMARY KEY (`mat_id`,`supplier_id`),
  KEY `fk_mat_has_pap_supplier_pap_supplier1_idx` (`supplier_id`),
  KEY `fk_mat_has_pap_supplier_mat1_idx` (`mat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_mat_cost`
--

INSERT INTO `pap_mat_cost` (`mat_id`, `supplier_id`, `mat_costperunit`) VALUES
(1, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_delivery`
--

CREATE TABLE IF NOT EXISTS `pap_mat_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ref` varchar(45) NOT NULL,
  `deliveried` datetime NOT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_psp_mat_delivery_pap_mat_po1_idx` (`po_id`),
  KEY `fk_pap_mat_delivery_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pap_mat_delivery`
--

INSERT INTO `pap_mat_delivery` (`id`, `po_id`, `user_id`, `ref`, `deliveried`, `remark`) VALUES
(1, 1, 1, '008', '2016-04-29 18:59:07', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_delivery_dt`
--

CREATE TABLE IF NOT EXISTS `pap_mat_delivery_dt` (
  `delivery_id` int(10) unsigned NOT NULL,
  `dt_id` int(10) unsigned NOT NULL,
  `qty` decimal(10,2) unsigned NOT NULL,
  `stk_location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`delivery_id`,`dt_id`),
  KEY `fk_pap_mat_delivery_dt_pap_mat_delivery1_idx` (`delivery_id`),
  KEY `fk_pap_mat_delivery_dt_pap_mat_po_detail1_idx` (`dt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_mat_delivery_dt`
--

INSERT INTO `pap_mat_delivery_dt` (`delivery_id`, `dt_id`, `qty`, `stk_location`) VALUES
(1, 2, '2.00', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_po`
--

CREATE TABLE IF NOT EXISTS `pap_mat_po` (
  `po_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_code` varchar(45) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `ct_id` int(10) unsigned NOT NULL,
  `po_cost` decimal(10,2) DEFAULT NULL,
  `po_delivery_plan` date DEFAULT NULL,
  `po_status` tinyint(3) unsigned NOT NULL,
  `po_payment` tinyint(3) unsigned DEFAULT NULL,
  `po_remark` text,
  `po_created` datetime NOT NULL,
  `po_deliveried` datetime DEFAULT NULL,
  `po_paid` date DEFAULT NULL,
  `po_paid_ref` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`po_id`),
  KEY `fk_pap_mat_po_pap_user1_idx` (`user_id`),
  KEY `fk_pap_mat_po_pap_supplier1_idx` (`supplier_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pap_mat_po`
--

INSERT INTO `pap_mat_po` (`po_id`, `po_code`, `user_id`, `supplier_id`, `ct_id`, `po_cost`, `po_delivery_plan`, `po_status`, `po_payment`, `po_remark`, `po_created`, `po_deliveried`, `po_paid`, `po_paid_ref`) VALUES
(1, 'PO1604001', 1, 1, 1, '1886.00', '2016-04-30', 5, 30, '', '2016-04-29 18:58:36', '2016-04-29 18:59:07', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_po_detail`
--

CREATE TABLE IF NOT EXISTS `pap_mat_po_detail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(10) unsigned NOT NULL,
  `mat_id` int(10) unsigned NOT NULL,
  `mat_cost` decimal(7,2) NOT NULL,
  `mat_qty` decimal(10,2) NOT NULL,
  `order_ref` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_mat_po_has_pap_mat_cost_pap_mat_po1_idx` (`po_id`),
  KEY `fk_pap_po_detail_pap_mat1_idx` (`mat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_mat_po_detail`
--

INSERT INTO `pap_mat_po_detail` (`id`, `po_id`, `mat_id`, `mat_cost`, `mat_qty`, `order_ref`) VALUES
(2, 1, 6, '943.00', '2.00', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pap_mat_stk`
--

CREATE TABLE IF NOT EXISTS `pap_mat_stk` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mat_id` int(10) unsigned NOT NULL,
  `delivery_id` int(10) unsigned DEFAULT NULL,
  `req_id` int(10) unsigned DEFAULT NULL,
  `in` int(10) unsigned DEFAULT NULL,
  `out` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_mat_stk_pap_mat1_idx` (`mat_id`),
  KEY `fk_pap_mat_stk_pap_mat_delivery1_idx` (`delivery_id`),
  KEY `fk_pap_mat_stk_pap_requisition1_idx` (`req_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pap_option`
--

CREATE TABLE IF NOT EXISTS `pap_option` (
  `op_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `op_type` varchar(45) NOT NULL,
  `op_name` varchar(255) DEFAULT NULL,
  `op_value` text,
  PRIMARY KEY (`op_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=78 ;

--
-- Dumping data for table `pap_option`
--

INSERT INTO `pap_option` (`op_id`, `op_type`, `op_name`, `op_value`) VALUES
(1, 'role_auth', 'Admin', '{"customer.php":"4","term.php?tax=customer":"4","shipping_address.php":"4","quotation.php":"4","lay.php":"4","ac_schedule.php":"4","ac_bill.php":"4","ac_buy.php":"4","ac_credit.php":"4","order.php?d=ga":"4","order.php":"4","production.php":"4","status.php":"4","outsource.php":"4","outsource.php?action=viewpo":"4","index.php":"4","delivery.php":"4","process.php":"4","machine.php":"4","paper.php":"4","paper.php?action=viewpo":"4","mat.php":"4","supplier.php":"4","term.php?tax=supplier":"4","mat_received.php":"4","outsource_rc.php":"4","userinfo.php":"4","user.php":"3","role.php":"3","pap_option.php?type=product_cat":"3","pap_option.php?type=mat_cat":"3","process_cat.php":"3","pap_option.php?type=paper_allo":"3","pap_option.php?type=paper_type":"3","pap_option.php?type=paper_size":"3","pap_option.php?type=paper_weight":"3","setting.php":"3","upload.php":"4"}'),
(2, 'role_auth', 'Observer', '{"customer.php":"3","term.php?tax=customer":"3","shipping_address.php":"3","quotation.php":"3","lay.php":"3","ac_schedule.php":"3","ac_bill.php":"3","ac_buy.php":"3","ac_credit.php":"3","order.php?d=ga":"3","order.php":"3","production.php":"3","status.php":"3","outsource.php":"3","outsource.php?action=viewpo":"3","index.php":"3","delivery.php":"3","process.php":"3","machine.php":"3","paper.php":"3","paper.php?action=viewpo":"3","mat.php":"3","supplier.php":"3","term.php?tax=supplier":"3","mat_received.php":"3","outsource_rc.php":"3","userinfo.php":"3","user.php":"0","role.php":"0","pap_option.php?type=product_cat":"0","pap_option.php?type=mat_cat":"0","process_cat.php":"0","pap_option.php?type=paper_allo":"0","pap_option.php?type=paper_type":"0","pap_option.php?type=paper_size":"0","pap_option.php?type=paper_weight":"0","setting.php":"0","upload.php":"0"}'),
(3, 'cinfo', 'name', 'Tempo Co.,Ltd.'),
(4, 'cinfo', 'address', 'เลขที่ 7 ถนนเพชรเกษม 77 หนองแขม กทม 10160 '),
(5, 'cinfo', 'email', 'tempo7@gmail.com'),
(6, 'cinfo', 'tel', '02-777-7777'),
(7, 'cinfo', 'tax_id', '7-7777-77777-77-7'),
(8, 'mat_cat', 'กระดาษ', ''),
(9, 'mat_cat', 'เพลท', ''),
(10, 'product_cat', 'หนังสือ/นิตยสาร', '1,2,3,4,5,6,7,8,9,10,11,12'),
(11, 'product_cat', 'แผ่นพับ', '1,2,3,4,5,6,11,12'),
(12, 'product_cat', 'ใบปลิว', '1,2,3,4,5,6,11,12'),
(13, 'product_cat', 'โปสเตอร์', '1,2,3,4,5,6,11,12'),
(14, 'product_cat', 'อื่นๆ', '1,2,3,4,5,6,7,8,9,10,11,12'),
(15, 'mat_cat', 'สี', ''),
(16, 'mat_cat', 'อื่นๆ', ''),
(17, 'role_auth', 'Sale', '{"customer.php":"2","term.php?tax=customer":"2","shipping_address.php":"2","quotation.php":"2","lay.php":"2","ac_schedule.php":"0","ac_bill.php":"0","ac_buy.php":"0","ac_credit.php":"2","order.php?d=ga":"1","order.php":"1","production.php":"0","status.php":"0","outsource.php":"0","outsource.php?action=viewpo":"0","index.php":"0","delivery.php":"0","process.php":"0","machine.php":"0","paper.php":"0","paper.php?action=viewpo":"0","mat.php":"0","supplier.php":"0","term.php?tax=supplier":"0","mat_received.php":"0","outsource_rc.php":"0","userinfo.php":"2","user.php":"0","role.php":"0","pap_option.php?type=product_cat":"0","pap_option.php?type=mat_cat":"0","process_cat.php":"0","pap_option.php?type=paper_allo":"0","pap_option.php?type=paper_type":"0","pap_option.php?type=paper_size":"0","pap_option.php?type=paper_weight":"0","setting.php":"0","upload.php":"0"}'),
(18, 'paper_size', '24x35', '{"width":"24","length":"35"}'),
(19, 'paper_size', '25x36', '{"width":"25","length":"36"}'),
(20, 'paper_size', '28x40', '{"width":"28","length":"40"}'),
(21, 'paper_size', '31x43', '{"width":"31","length":"43"}'),
(22, 'paper_weight', '70', ''),
(23, 'paper_weight', '62.9', ''),
(24, 'paper_weight', '66', ''),
(25, 'paper_weight', '74', ''),
(26, 'paper_weight', '81.4', ''),
(27, 'paper_weight', '80', ''),
(28, 'paper_weight', '85', ''),
(29, 'paper_weight', '90', ''),
(30, 'paper_weight', '100', ''),
(31, 'paper_weight', '105', ''),
(32, 'paper_weight', '115', ''),
(33, 'paper_weight', '120', ''),
(34, 'paper_weight', '130', ''),
(35, 'paper_weight', '160', ''),
(36, 'paper_weight', '180', ''),
(37, 'paper_weight', '190', ''),
(38, 'quote_status', '1_ฉบับร่าง', 'สร้างใบเสนอราคาฉบับร่าง'),
(39, 'quote_status', '2_ตรวจแล้ว', 'ตรวจโดย Manager แล้ว'),
(40, 'quote_status', '3_น้ำเสนอลูกค้าแล้ว', 'ส่งใบเสนอราคาให้ลูกค้าแล้ว'),
(41, 'quote_status', '4_ลูกค้าตอบ', ''),
(42, 'quote_status', '5_ลูกค้าปฏิเสธ', ''),
(43, 'paper_type', 'กระดาษปอนด์', ''),
(44, 'paper_type', 'กระดาษอาร์ทมัน', ''),
(45, 'paper_type', 'กระดาษอาร์ทด้าน', ''),
(46, 'paper_weight', '300', ''),
(47, 'paper_weight', '260', ''),
(48, 'paper_weight', '230', ''),
(51, 'role_auth', 'Manager', '{"customer.php":"4","term.php?tax=customer":"4","shipping_address.php":"4","quotation.php":"4","lay.php":"4","ac_schedule.php":"4","ac_bill.php":"4","ac_buy.php":"4","ac_credit.php":"4","order.php?d=ga":"4","order.php":"4","production.php":"4","status.php":"4","outsource.php":"4","outsource.php?action=viewpo":"4","index.php":"4","delivery.php":"4","process.php":"4","machine.php":"4","paper.php":"4","paper.php?action=viewpo":"4","mat.php":"4","supplier.php":"4","term.php?tax=supplier":"4","mat_received.php":"4","outsource_rc.php":"4","userinfo.php":"4","user.php":"4","role.php":"4","pap_option.php?type=product_cat":"4","pap_option.php?type=mat_cat":"4","process_cat.php":"4","pap_option.php?type=paper_allo":"4","pap_option.php?type=paper_type":"4","pap_option.php?type=paper_size":"4","pap_option.php?type=paper_weight":"4","setting.php":"4","upload.php":"0"}'),
(52, 'paper_weight', '75', ''),
(53, 'paper_allo', '300', '0,5000'),
(54, 'paper_allo', '500', '5001,10000'),
(56, 'cinfo', 'c_digit', '5'),
(57, 'paper_type', 'กระดาษอาร์ทการ์ด', ''),
(58, 'cinfo', 'grip_size', '1'),
(59, 'cinfo', 'bleed_size', '0.2'),
(60, 'cinfo', 'rno_quote', 'QT,%y%m,5,'),
(61, 'cinfo', 'margin', '18'),
(64, 'cinfo', 'rno_order', NULL),
(65, 'cinfo', 'rno_matpo', 'PO,%y%m,3,'),
(66, 'cinfo', 'rno_prodpo', 'OS,%y%m,3,'),
(67, 'cinfo', 's_digit', '3'),
(68, 'role_auth', 'ฝ่ายผลิต', '{"customer.php":"0","term.php?tax=customer":"0","shipping_address.php":"2","quotation.php":"0","lay.php":"0","ac_schedule.php":"0","ac_bill.php":"0","ac_buy.php":"0","ac_credit.php":"0","order.php?d=ga":"2","order.php":"2","production.php":"2","status.php":"2","outsource.php":"2","outsource.php?action=viewpo":"2","index.php":"2","delivery.php":"2","process.php":"2","machine.php":"2","paper.php":"0","paper.php?action=viewpo":"0","mat.php":"0","supplier.php":"0","term.php?tax=supplier":"0","mat_received.php":"0","outsource_rc.php":"2","userinfo.php":"2","user.php":"0","role.php":"0","pap_option.php?type=product_cat":"0","pap_option.php?type=mat_cat":"0","process_cat.php":"0","pap_option.php?type=paper_allo":"0","pap_option.php?type=paper_type":"0","pap_option.php?type=paper_size":"0","pap_option.php?type=paper_weight":"0","setting.php":"0","upload.php":"0"}'),
(69, 'product_cat', 'สมุด', '1,2,3,4,5,6,7,8,9,10,11,12'),
(70, 'cinfo', 'c_logo', '/p-pap/image/company_logo.png'),
(71, 'cinfo', 'rno_deli', 'DN,%y%m,4,'),
(72, 'cinfo', 'rno_bill', 'B,%y%m,4,'),
(73, 'cinfo', 'rno_invoice', 'IV,%y%m,4,'),
(74, 'cinfo', 'rno_rc', 'RC,%y%m,4,'),
(75, 'role_auth', 'ฝ่ายจัดซื้อ', '{"customer.php":"0","term.php?tax=customer":"0","shipping_address.php":"2","quotation.php":"0","lay.php":"0","ac_schedule.php":"0","ac_bill.php":"0","ac_buy.php":"0","ac_credit.php":"0","order.php?d=ga":"0","order.php":"0","production.php":"0","status.php":"0","outsource.php":"0","outsource.php?action=viewpo":"0","index.php":"0","delivery.php":"0","process.php":"0","machine.php":"0","paper.php":"2","paper.php?action=viewpo":"2","mat.php":"2","supplier.php":"2","term.php?tax=supplier":"2","mat_received.php":"2","outsource_rc.php":"0","userinfo.php":"2","user.php":"0","role.php":"0","pap_option.php?type=product_cat":"0","pap_option.php?type=mat_cat":"0","process_cat.php":"0","pap_option.php?type=paper_allo":"0","pap_option.php?type=paper_type":"0","pap_option.php?type=paper_size":"0","pap_option.php?type=paper_weight":"0","setting.php":"0","upload.php":"0"}'),
(76, 'role_auth', 'ฝ่ายบัญชี', '{"customer.php":"0","term.php?tax=customer":"0","shipping_address.php":"0","quotation.php":"0","lay.php":"0","ac_schedule.php":"2","ac_bill.php":"2","ac_buy.php":"2","ac_credit.php":"2","order.php?d=ga":"0","order.php":"0","production.php":"0","status.php":"0","outsource.php":"0","outsource.php?action=viewpo":"0","index.php":"0","delivery.php":"0","process.php":"0","machine.php":"0","paper.php":"0","paper.php?action=viewpo":"0","mat.php":"0","supplier.php":"0","term.php?tax=supplier":"0","mat_received.php":"0","outsource_rc.php":"0","userinfo.php":"2","user.php":"0","role.php":"0","pap_option.php?type=product_cat":"0","pap_option.php?type=mat_cat":"0","process_cat.php":"0","pap_option.php?type=paper_allo":"0","pap_option.php?type=paper_type":"0","pap_option.php?type=paper_size":"0","pap_option.php?type=paper_weight":"0","setting.php":"0","upload.php":"0"}'),
(77, 'cinfo', 'fax', '02-777-7778');

-- --------------------------------------------------------

--
-- Table structure for table `pap_order`
--

CREATE TABLE IF NOT EXISTS `pap_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(100) DEFAULT NULL,
  `quote_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `plate_plan` date DEFAULT NULL,
  `plate_received` date DEFAULT NULL,
  `paper_plan` date DEFAULT NULL,
  `paper_received` date DEFAULT NULL,
  `prod_plan` datetime DEFAULT NULL,
  `prod_start` datetime DEFAULT NULL,
  `prod_finished` datetime DEFAULT NULL,
  `delivery` int(10) unsigned DEFAULT NULL,
  `billed` int(10) unsigned DEFAULT NULL,
  `paid` decimal(10,2) unsigned DEFAULT NULL,
  `invoiced` date DEFAULT NULL,
  `picture` varchar(150) DEFAULT NULL,
  `remark` text,
  PRIMARY KEY (`order_id`),
  KEY `fk_pap_order_pap_quotation1_idx` (`quote_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_order`
--

INSERT INTO `pap_order` (`order_id`, `order_no`, `quote_id`, `status`, `created`, `plate_plan`, `plate_received`, `paper_plan`, `paper_received`, `prod_plan`, `prod_start`, `prod_finished`, `delivery`, `billed`, `paid`, `invoiced`, `picture`, `remark`) VALUES
(2, 'QT160400001', 3, 79, '2016-04-29 18:57:10', '2016-04-30', '2016-04-30', '2016-04-30', '2016-04-29', NULL, NULL, NULL, 2, 2, '10576.92', '2016-04-29', '/p-pap/image/job/QT160400001.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_order_comp`
--

CREATE TABLE IF NOT EXISTS `pap_order_comp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `paper_id` int(10) unsigned DEFAULT NULL,
  `paper_use` decimal(10,2) NOT NULL,
  `paper_lay` smallint(6) DEFAULT NULL,
  `paper_cut` tinyint(4) NOT NULL,
  `print_size` varchar(255) DEFAULT NULL,
  `page` smallint(5) unsigned NOT NULL,
  `allowance` smallint(6) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_order_comp_pap_order1_idx` (`order_id`),
  KEY `fk_pap_order_comp_pap_mat1_idx` (`paper_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pap_order_comp`
--

INSERT INTO `pap_order_comp` (`id`, `type`, `name`, `order_id`, `paper_id`, `paper_use`, `paper_lay`, `paper_cut`, `print_size`, `page`, `allowance`, `status`) VALUES
(4, 9, 'ชิ้นงาน', 2, 6, '2.00', 8, 1, '24x35', 2, 300, 4);

-- --------------------------------------------------------

--
-- Table structure for table `pap_pbill`
--

CREATE TABLE IF NOT EXISTS `pap_pbill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no` varchar(45) NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `contact` int(10) unsigned NOT NULL,
  `payment` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `pay_date` date NOT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_pbill_pap_contact1_idx` (`contact`),
  KEY `fk_pap_pbill_pap_customer1_idx` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_pbill`
--

INSERT INTO `pap_pbill` (`id`, `no`, `customer_id`, `contact`, `payment`, `date`, `pay_date`, `remark`) VALUES
(2, 'B16050001', 2, 5, 'รับชำระเป็นเช็ค', '2016-05-04', '2016-05-11', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_pbill_dt`
--

CREATE TABLE IF NOT EXISTS `pap_pbill_dt` (
  `pbill_id` int(10) unsigned NOT NULL,
  `deli_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`pbill_id`,`deli_id`),
  KEY `fk_pap_pbill_has_pap_delivery_pap_delivery1_idx` (`deli_id`),
  KEY `fk_pap_pbill_has_pap_delivery_pap_pbill1_idx` (`pbill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_pbill_dt`
--

INSERT INTO `pap_pbill_dt` (`pbill_id`, `deli_id`, `amount`) VALUES
(2, 2, '16050.00');

-- --------------------------------------------------------

--
-- Table structure for table `pap_process`
--

CREATE TABLE IF NOT EXISTS `pap_process` (
  `process_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process_name` varchar(100) NOT NULL,
  `process_cat_id` int(10) unsigned NOT NULL,
  `process_unit` varchar(45) NOT NULL,
  `process_source` tinyint(3) unsigned NOT NULL,
  `process_setup_min` smallint(5) unsigned DEFAULT NULL,
  `process_cap` int(10) unsigned DEFAULT NULL,
  `process_std_leadtime_hour` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`process_id`),
  UNIQUE KEY `process_name_UNIQUE` (`process_name`),
  KEY `fk_pap_process_pap_process_cat1_idx` (`process_cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=43 ;

--
-- Dumping data for table `pap_process`
--

INSERT INTO `pap_process` (`process_id`, `process_name`, `process_cat_id`, `process_unit`, `process_source`, `process_setup_min`, `process_cap`, `process_std_leadtime_hour`) VALUES
(1, 'ไสกาว', 9, 'piece', 1, 20, 3000, 0),
(2, 'เย็บลวดมุงหลังคา', 9, 'piece', 1, 20, 2000, 0),
(3, 'เย็บกี่', 9, 'piece', 2, 0, 0, 24),
(4, 'PVC เงา', 4, 'sheet', 2, 0, 0, 24),
(5, 'PVC ด้าน', 4, 'sheet', 2, 0, 0, 24),
(6, 'เคลือบ UV', 4, 'sheet', 2, 0, 0, 24),
(7, 'Spot UV', 4, 'sheet', 2, 0, 0, 24),
(8, 'พับ', 7, 'set', 1, 15, 10000, 0),
(9, 'เก็บเล่ม', 8, 'set', 1, 0, 24000, 0),
(10, 'พิมพ์ 4 สี', 3, 'round', 1, 30, 8000, 0),
(11, 'ปั้มนูน', 5, 'sheet', 2, 0, 0, 24),
(12, 'PVC ด้าน + Spot UV', 4, 'sheet', 2, 0, 0, 48),
(13, 'ตัดเจียน', 2, 'sheet', 1, 5, 15000, 0),
(14, 'เย็บลวดหุ้มสัน', 9, 'piece', 2, 0, 0, 24),
(15, 'เย็บเชือก', 9, 'piece', 2, 0, 0, 24),
(16, 'ริมห่วง', 9, 'piece', 2, 0, 0, 24),
(17, 'ปั้มทอง', 5, 'sheet', 2, 0, 0, 24),
(18, 'ปั้มเงิน', 5, 'sheet', 2, 0, 0, 24),
(19, 'ปั้มแบบ', 5, 'sheet', 2, 0, 0, 24),
(20, 'เจาะหน้าต่าง', 5, 'sheet', 2, 0, 0, 24),
(21, 'ปั้มฟอยด์', 5, 'sheet', 2, 0, 0, 24),
(22, 'ตัดสันสามด้าน', 10, 'piece', 1, 20, 2000, 0),
(23, 'ติด Label', 11, 'piece', 1, 0, 1200, 0),
(24, 'ตัดแบ่ง', 6, 'set', 1, 5, 15000, 0),
(25, 'ออกแบบ Artwork ใหม่', 1, 'allpage', 1, 0, 1, 0),
(26, 'ทำเพลต 4 สี', 1, 'frame', 2, 0, 0, 120),
(27, 'Digital Proof', 1, 'allpage', 1, 0, 0, 0),
(28, 'Plate Proof', 1, 'frame', 1, 0, 0, 0),
(29, 'พิมพ์ 1 สี', 3, 'round', 1, 15, 7000, 0),
(30, 'พิมพ์ 2 สี', 3, 'round', 1, 20, 8000, 0),
(31, 'พิมพ์ 5 สี', 3, 'round', 1, 30, 8000, 0),
(32, 'ขนส่งด้วยรถกระบะ', 12, 'km', 1, 20, 60, 0),
(33, 'ขนส่งด้วยรถบรรทุก 6 ล้อ', 12, 'km', 1, 20, 50, 0),
(34, 'แทรกแผ่น', 11, 'piece', 1, 0, 5000, 0),
(35, 'ห่อกระดาษ', 11, 'piece', 1, 0, 1000, 0),
(36, 'shrink film อ่อน', 11, 'piece', 1, 0, 0, 0),
(37, 'ทำเพลต 1 สี', 1, 'frame', 2, 0, 0, 120),
(38, 'ทำเพลต 2 สี', 1, 'frame', 2, 0, 0, 120),
(39, 'ทำเพลต 5 สี', 1, 'frame', 2, 0, 0, 120),
(40, 'พับ 2', 7, 'set', 1, 20, 3000, 0),
(41, 'พับ 3', 7, 'set', 1, 20, 3000, 0),
(42, 'พับ zigzag', 7, 'set', 1, 25, 3000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pap_process_cat`
--

CREATE TABLE IF NOT EXISTS `pap_process_cat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `pap_process_cat`
--

INSERT INTO `pap_process_cat` (`id`, `name`) VALUES
(1, 'ก่อนพิมพ์'),
(2, 'เจียน'),
(3, 'พิมพ์'),
(4, 'เคลือบ'),
(5, 'ไดคัท-ปั้มนูน'),
(6, 'ตัด'),
(7, 'พับ'),
(8, 'เก็บเล่ม'),
(9, 'เข้าเล่ม'),
(10, 'ตัดสัน'),
(11, 'แพ็ค'),
(12, 'ขนส่ง'),
(13, 'อื่นๆ');

-- --------------------------------------------------------

--
-- Table structure for table `pap_process_meta`
--

CREATE TABLE IF NOT EXISTS `pap_process_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_process_meta_pap_process1_idx` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=211 ;

--
-- Dumping data for table `pap_process_meta`
--

INSERT INTO `pap_process_meta` (`id`, `process_id`, `meta_key`, `meta_value`) VALUES
(1, 5, 'cost', '[{"cost":"4","min":"0","cond":"0","btw":"","to":""}]'),
(2, 1, 'cost', '[{"cost":"1","min":"1000","cond":"page","btw":"0","to":"199"},{"cost":"1.5","min":"1000","cond":"page","btw":"200","to":"0"}]'),
(3, 2, 'cost', '[{"cost":"1","min":"1000","cond":"page","btw":"0","to":"199"},{"cost":"1.5","min":"1000","cond":"page","btw":"200","to":"0"}]'),
(4, 8, 'cost', '[{"cost":"0.03","min":"500","cond":"0","btw":"","to":""}]'),
(5, 9, 'cost', '[{"cost":"0.03","min":"500","cond":"0","btw":"","to":""}]'),
(6, 4, 'cost', '[{"cost":"3","min":"0","cond":"0","btw":"","to":""}]'),
(7, 7, 'cost', '[{"cost":"3","min":"0","cond":"0","btw":"","to":""}]'),
(8, 6, 'cost', '[{"cost":"1","min":"0","cond":"0","btw":"","to":""}]'),
(9, 10, 'cost', '[{"cost":"0.25","min":"2500","cond":"0","btw":"","to":""}]'),
(10, 11, 'cost', '[{"cost":"1","min":"0","cond":"0","btw":"","to":""}]'),
(11, 12, 'cost', '[{"cost":"7","min":"0","cond":"0","btw":"","to":""}]'),
(12, 13, 'cost', '[]'),
(13, 13, 'detail_cost', '1'),
(14, 13, 'detail_labor', '1'),
(15, 13, 'detail_mat', '[["17","0.000001"]]'),
(16, 14, 'cost', '[{"cost":"2","min":"0","cond":"0","btw":"","to":""}]'),
(17, 14, 'detail_cost', '0'),
(18, 14, 'detail_labor', ''),
(19, 14, 'detail_mat', '[]'),
(20, 15, 'cost', '[{"cost":"3","min":"0","cond":"0","btw":"","to":""}]'),
(21, 15, 'detail_cost', '0'),
(22, 15, 'detail_labor', ''),
(23, 15, 'detail_mat', '[]'),
(24, 16, 'cost', '[{"cost":"3","min":"","cond":"0","btw":"","to":""}]'),
(25, 16, 'detail_cost', '0'),
(26, 16, 'detail_labor', ''),
(27, 16, 'detail_mat', '[]'),
(28, 17, 'cost', '[{"cost":"2","min":"","cond":"0","btw":"","to":""}]'),
(29, 17, 'detail_cost', '0'),
(30, 17, 'detail_labor', ''),
(31, 17, 'detail_mat', '[]'),
(32, 18, 'cost', '[{"cost":"2","min":"","cond":"0","btw":"","to":""}]'),
(33, 18, 'detail_cost', '0'),
(34, 18, 'detail_labor', ''),
(35, 18, 'detail_mat', '[]'),
(36, 19, 'cost', '[{"cost":"2","min":"","cond":"0","btw":"","to":""}]'),
(37, 19, 'detail_cost', '0'),
(38, 19, 'detail_labor', ''),
(39, 19, 'detail_mat', '[]'),
(40, 20, 'cost', '[{"cost":"5","min":"","cond":"0","btw":"","to":""}]'),
(41, 20, 'detail_cost', '0'),
(42, 20, 'detail_labor', ''),
(43, 20, 'detail_mat', '[]'),
(44, 21, 'cost', '[{"cost":"3","min":"","cond":"0","btw":"","to":""}]'),
(45, 21, 'detail_cost', '0'),
(46, 21, 'detail_labor', ''),
(47, 21, 'detail_mat', '[]'),
(48, 8, 'detail_cost', '0'),
(49, 8, 'detail_labor', ''),
(50, 8, 'detail_mat', '[]'),
(51, 9, 'detail_cost', '0'),
(52, 9, 'detail_labor', ''),
(53, 9, 'detail_mat', '[]'),
(54, 3, 'cost', '[{"cost":"4","min":"","cond":"0","btw":"","to":""}]'),
(55, 3, 'detail_cost', '0'),
(56, 3, 'detail_labor', ''),
(57, 3, 'detail_mat', '[]'),
(58, 2, 'detail_cost', '0'),
(59, 2, 'detail_labor', ''),
(60, 2, 'detail_mat', '[]'),
(61, 1, 'detail_cost', '0'),
(62, 1, 'detail_labor', ''),
(63, 1, 'detail_mat', '[]'),
(64, 22, 'cost', '[{"cost":"1","min":"","cond":"0","btw":"","to":""}]'),
(65, 22, 'detail_cost', '0'),
(66, 22, 'detail_labor', ''),
(67, 22, 'detail_mat', '[]'),
(68, 23, 'cost', '[{"cost":"1","min":"","cond":"0","btw":"","to":""}]'),
(69, 23, 'detail_cost', '0'),
(70, 23, 'detail_labor', ''),
(71, 23, 'detail_mat', '[]'),
(72, 24, 'cost', '[{"cost":"0.01","min":"","cond":"0","btw":"","to":""}]'),
(73, 24, 'detail_cost', '0'),
(74, 24, 'detail_labor', ''),
(75, 24, 'detail_mat', '[]'),
(76, 25, 'cost', '[{"cost":"20","min":"","cond":"0","btw":"","to":""}]'),
(77, 25, 'detail_cost', '0'),
(78, 25, 'detail_labor', ''),
(79, 25, 'detail_mat', '[]'),
(80, 27, 'cost', '[{"cost":"2","min":"","cond":"0","btw":"","to":""}]'),
(81, 27, 'detail_cost', NULL),
(82, 27, 'detail_labor', NULL),
(83, 27, 'detail_mat', '[]'),
(84, 28, 'cost', '[{"cost":"300","min":"","cond":"0","btw":"","to":""}]'),
(85, 28, 'detail_cost', NULL),
(86, 28, 'detail_labor', NULL),
(87, 28, 'detail_mat', '[]'),
(88, 26, 'cost', '[{"cost":"2500","min":"","cond":"0","btw":"","to":""}]'),
(89, 26, 'detail_cost', '0'),
(90, 26, 'detail_labor', ''),
(91, 26, 'detail_mat', '[]'),
(92, 29, 'cost', '[{"cost":"0.25","min":"2500","cond":"0","btw":"","to":""}]'),
(93, 29, 'detail_cost', '0'),
(94, 29, 'detail_labor', ''),
(95, 29, 'detail_mat', '[]'),
(96, 30, 'cost', '[{"cost":"0.2","min":"2000","cond":"0","btw":"","to":""}]'),
(97, 30, 'detail_cost', '0'),
(98, 30, 'detail_labor', ''),
(99, 30, 'detail_mat', '[]'),
(100, 31, 'cost', '[{"cost":"0.3","min":"3000","cond":"0","btw":"","to":""}]'),
(101, 31, 'detail_cost', '0'),
(102, 31, 'detail_labor', ''),
(103, 31, 'detail_mat', '[]'),
(104, 32, 'cost', '[{"cost":"5","min":"300","cond":"0","btw":"","to":""}]'),
(105, 32, 'detail_cost', '0'),
(106, 32, 'detail_labor', ''),
(107, 32, 'detail_mat', '[]'),
(108, 33, 'cost', '[{"cost":"7","min":"500","cond":"0","btw":"","to":""}]'),
(109, 33, 'detail_cost', '0'),
(110, 33, 'detail_labor', ''),
(111, 33, 'detail_mat', '[]'),
(112, 34, 'cost', '[{"cost":"0.05","min":"","cond":"0","btw":"","to":""}]'),
(113, 34, 'detail_cost', '0'),
(114, 34, 'detail_labor', ''),
(115, 34, 'detail_mat', '[]'),
(116, 35, 'cost', '[{"cost":"0.5","min":"","cond":"0","btw":"","to":""}]'),
(117, 35, 'detail_cost', '0'),
(118, 35, 'detail_labor', ''),
(119, 35, 'detail_mat', '[]'),
(120, 36, 'cost', '[{"cost":"0.1","min":"","cond":"0","btw":"","to":""}]'),
(121, 36, 'detail_cost', '0'),
(122, 36, 'detail_labor', ''),
(123, 36, 'detail_mat', '[]'),
(124, 12, 'detail_cost', '0'),
(125, 12, 'detail_labor', ''),
(126, 12, 'detail_mat', '[]'),
(127, 11, 'detail_cost', '0'),
(128, 11, 'detail_labor', ''),
(129, 11, 'detail_mat', '[]'),
(130, 7, 'detail_cost', '0'),
(131, 7, 'detail_labor', ''),
(132, 7, 'detail_mat', '[]'),
(133, 6, 'detail_cost', '0'),
(134, 6, 'detail_labor', ''),
(135, 6, 'detail_mat', '[]'),
(136, 37, 'cost', '[{"cost":"1000","min":"","cond":"0","btw":"","to":""}]'),
(137, 37, 'detail_cost', '0'),
(138, 37, 'detail_labor', ''),
(139, 37, 'detail_mat', '[]'),
(140, 38, 'cost', '[{"cost":"2000","min":"","cond":"0","btw":"","to":""}]'),
(141, 38, 'detail_cost', '0'),
(142, 38, 'detail_labor', ''),
(143, 38, 'detail_mat', '[]'),
(144, 39, 'cost', '[{"cost":"3000","min":"","cond":"0","btw":"","to":""}]'),
(145, 39, 'detail_cost', '0'),
(146, 39, 'detail_labor', ''),
(147, 39, 'detail_mat', '[]'),
(148, 10, 'detail_cost', '0'),
(149, 10, 'detail_labor', ''),
(150, 10, 'detail_mat', '[]'),
(151, 5, 'detail_cost', '0'),
(152, 5, 'detail_labor', ''),
(153, 5, 'detail_mat', '[]'),
(154, 4, 'detail_cost', '0'),
(155, 4, 'detail_labor', ''),
(156, 4, 'detail_mat', '[]'),
(157, 27, 'pc_show', '2'),
(158, 25, 'pc_show', '1'),
(159, 26, 'pc_show', '1'),
(160, 28, 'pc_show', '1'),
(161, 37, 'pc_show', '1'),
(162, 38, 'pc_show', '1'),
(163, 39, 'pc_show', '1'),
(164, 13, 'pc_show', '1'),
(165, 10, 'pc_show', '1'),
(166, 29, 'pc_show', '1'),
(167, 30, 'pc_show', '1'),
(168, 31, 'pc_show', '1'),
(169, 4, 'pc_show', '1'),
(170, 5, 'pc_show', '1'),
(171, 6, 'pc_show', '1'),
(172, 7, 'pc_show', '1'),
(173, 12, 'pc_show', '1'),
(174, 11, 'pc_show', '1'),
(175, 17, 'pc_show', '1'),
(176, 18, 'pc_show', '1'),
(177, 19, 'pc_show', '1'),
(178, 20, 'pc_show', '1'),
(179, 21, 'pc_show', '1'),
(180, 24, 'pc_show', '1'),
(181, 8, 'pc_show', '1'),
(182, 9, 'pc_show', '1'),
(183, 1, 'pc_show', '1'),
(184, 2, 'pc_show', '1'),
(185, 3, 'pc_show', '1'),
(186, 14, 'pc_show', '1'),
(187, 15, 'pc_show', '1'),
(188, 16, 'pc_show', '1'),
(189, 22, 'pc_show', '1'),
(190, 23, 'pc_show', '1'),
(191, 34, 'pc_show', '1'),
(192, 35, 'pc_show', '1'),
(193, 36, 'pc_show', '1'),
(194, 32, 'pc_show', '1'),
(195, 33, 'pc_show', '1'),
(196, 40, 'cost', '[{"cost":"0.5","min":"1000","cond":"0","btw":"","to":""}]'),
(197, 40, 'detail_cost', NULL),
(198, 40, 'detail_labor', NULL),
(199, 40, 'detail_mat', '[]'),
(200, 40, 'pc_show', '1'),
(201, 41, 'cost', '[{"cost":"0.6","min":"1200","cond":"0","btw":"","to":""}]'),
(202, 41, 'detail_cost', NULL),
(203, 41, 'detail_labor', NULL),
(204, 41, 'detail_mat', '[]'),
(205, 41, 'pc_show', '1'),
(206, 42, 'cost', '[{"cost":"0.7","min":"1300","cond":"0","btw":"","to":""}]'),
(207, 42, 'detail_cost', NULL),
(208, 42, 'detail_labor', NULL),
(209, 42, 'detail_mat', '[]'),
(210, 42, 'pc_show', '1');

-- --------------------------------------------------------

--
-- Table structure for table `pap_process_po`
--

CREATE TABLE IF NOT EXISTS `pap_process_po` (
  `po_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_code` varchar(45) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `ct_id` int(10) unsigned NOT NULL,
  `po_cost` decimal(10,2) DEFAULT NULL,
  `po_delivery_plan` date NOT NULL,
  `po_status` tinyint(3) unsigned NOT NULL,
  `po_payment` tinyint(3) unsigned DEFAULT NULL,
  `po_remark` text,
  `po_created` datetime NOT NULL,
  `po_deliveried` datetime DEFAULT NULL,
  `po_paid` date DEFAULT NULL,
  `po_paid_ref` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`po_id`),
  KEY `fk_pap_mat_po_pap_user1_idx` (`user_id`),
  KEY `fk_pap_process_po_pap_supplier1_idx` (`supplier_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_process_po`
--

INSERT INTO `pap_process_po` (`po_id`, `po_code`, `user_id`, `supplier_id`, `ct_id`, `po_cost`, `po_delivery_plan`, `po_status`, `po_payment`, `po_remark`, `po_created`, `po_deliveried`, `po_paid`, `po_paid_ref`) VALUES
(2, 'OS1604002', 1, 2, 3, '500.00', '2016-04-30', 5, 60, '', '2016-04-29 21:18:55', '2016-04-29 21:19:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pap_pro_po_dt`
--

CREATE TABLE IF NOT EXISTS `pap_pro_po_dt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(10) unsigned NOT NULL,
  `process_id` int(10) unsigned NOT NULL,
  `unit` varchar(45) DEFAULT NULL,
  `cost_per_u` decimal(7,2) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT NULL,
  `cpro_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_pro_po_dt_pap_process_po1_idx` (`po_id`),
  KEY `fk_pap_pro_po_dt_pap_process1_idx` (`process_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `pap_pro_po_dt`
--

INSERT INTO `pap_pro_po_dt` (`id`, `po_id`, `process_id`, `unit`, `cost_per_u`, `qty`, `cpro_id`) VALUES
(4, 2, 4, 'แผ่น', '0.50', '1000.00', 16);

-- --------------------------------------------------------

--
-- Table structure for table `pap_quotation`
--

CREATE TABLE IF NOT EXISTS `pap_quotation` (
  `quote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_no` varchar(45) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `cat_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `job_size_id` int(10) unsigned NOT NULL,
  `prepress` varchar(45) DEFAULT NULL,
  `binding_id` int(10) unsigned NOT NULL,
  `amount` smallint(5) unsigned NOT NULL,
  `q_price` float unsigned DEFAULT NULL,
  `credit` tinyint(3) unsigned NOT NULL,
  `plan_delivery` date DEFAULT NULL,
  `status` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `approved` datetime DEFAULT NULL,
  `finished` datetime DEFAULT NULL,
  PRIMARY KEY (`quote_id`),
  KEY `fk_pap_quotation_pap_user1_idx` (`user_id`),
  KEY `fk_pap_quotation_pap_customer1_idx` (`customer_id`),
  KEY `fk_pap_quotation_pap_size1_idx` (`job_size_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pap_quotation`
--

INSERT INTO `pap_quotation` (`quote_id`, `quote_no`, `user_id`, `customer_id`, `cat_id`, `name`, `job_size_id`, `prepress`, `binding_id`, `amount`, `q_price`, `credit`, `plan_delivery`, `status`, `created`, `approved`, `finished`) VALUES
(3, 'QT160400001', 1, 2, 11, 'แผ่นพับสอง', 1, '', 0, 3000, 15000, 30, '2016-04-30', 9, '2016-04-28 23:52:09', '2016-04-29 18:56:00', '2016-04-29 18:57:09');

-- --------------------------------------------------------

--
-- Table structure for table `pap_quote_comp`
--

CREATE TABLE IF NOT EXISTS `pap_quote_comp` (
  `quote_id` int(10) unsigned NOT NULL,
  `comp_type` tinyint(4) NOT NULL,
  `comp_page` smallint(5) unsigned NOT NULL,
  `comp_paper_type` int(10) unsigned NOT NULL,
  `comp_paper_weight` int(10) unsigned NOT NULL,
  `comp_paper_allowance` smallint(6) DEFAULT NULL,
  `comp_coating` int(10) unsigned NOT NULL,
  `comp_print_id` int(10) unsigned DEFAULT NULL,
  `comp_print2` int(11) DEFAULT NULL,
  `comp_postpress` varchar(100) DEFAULT NULL,
  KEY `fk_pap_quote_comp_pap_quotation1_idx` (`quote_id`),
  KEY `fk_pap_quote_comp_pap_process1_idx` (`comp_print_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_quote_comp`
--

INSERT INTO `pap_quote_comp` (`quote_id`, `comp_type`, `comp_page`, `comp_paper_type`, `comp_paper_weight`, `comp_paper_allowance`, `comp_coating`, `comp_print_id`, `comp_print2`, `comp_postpress`) VALUES
(3, 1, 2, 45, 33, 300, 4, 10, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_quote_meta`
--

CREATE TABLE IF NOT EXISTS `pap_quote_meta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_quote_meta_pap_quotation1_idx` (`quote_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=49 ;

--
-- Dumping data for table `pap_quote_meta`
--

INSERT INTO `pap_quote_meta` (`id`, `quote_id`, `meta_key`, `meta_value`) VALUES
(32, 3, 'remark', ''),
(33, 3, 'exclude', ''),
(34, 3, 'packing', ''),
(35, 3, 'shipping', ''),
(36, 3, 'distance', ''),
(37, 3, 'discount', '0'),
(38, 3, 'adj_margin', '18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18,18'),
(39, 3, 'contact_id', '5'),
(40, 3, 'page_cover', '0'),
(41, 3, 'page_inside', '2'),
(42, 3, 'cal_amount', '5000,10000'),
(43, 3, 'cwing', '0'),
(44, 3, 'folding', '41'),
(45, 3, 'multi_quote_info', '[{"show":"1","amount":"5000","remark":"\\u0e22\\u0e2d\\u0e14 5000","price":"15418"},{"show":"1","amount":"10000","remark":"\\u0e22\\u0e2d\\u0e14 10000","price":"24723"}]'),
(46, 3, 'detail_price', '[["0","Plate \\u0e0a\\u0e34\\u0e49\\u0e19\\u0e07\\u0e32\\u0e19","1","2500","2500"],["0","\\u0e01\\u0e23\\u0e30\\u0e14\\u0e32\\u0e29 \\u0e0a\\u0e34\\u0e49\\u0e19\\u0e07\\u0e32\\u0e19","2","942.97","1885.94"]]'),
(47, 3, 'print_cost', '11865.94'),
(48, 3, 'quote_sign', '/p-pap/image/quote_sign/QT160400001.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pap_rc`
--

CREATE TABLE IF NOT EXISTS `pap_rc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no` varchar(45) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `payment` varchar(100) NOT NULL,
  `check_bank` varchar(100) DEFAULT NULL,
  `check_bank_branch` varchar(100) DEFAULT NULL,
  `check_no` varchar(45) DEFAULT NULL,
  `check_date` date DEFAULT NULL,
  `cash_remark` varchar(255) DEFAULT NULL,
  `transfer_bank` varchar(100) DEFAULT NULL,
  `transfer_ref` varchar(45) DEFAULT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_rc_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pap_rc`
--

INSERT INTO `pap_rc` (`id`, `no`, `user_id`, `date`, `payment`, `check_bank`, `check_bank_branch`, `check_no`, `check_date`, `cash_remark`, `transfer_bank`, `transfer_ref`, `remark`) VALUES
(1, 'RC16040001', 1, '2016-04-29', 'รับชำระเป็นเช็ค', '', '', '', '0000-00-00', '', '', '', ''),
(2, 'RC16040002', 1, '2016-04-29', 'รับชำระเป็นเงินสด', '', '', '', '0000-00-00', '', '', '', ''),
(3, 'RC16040003', 1, '2016-04-29', 'รับชำระเป็นเงินสด', '', '', '', '0000-00-00', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_rc_dt`
--

CREATE TABLE IF NOT EXISTS `pap_rc_dt` (
  `rc_id` int(10) unsigned NOT NULL,
  `invoice_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`rc_id`,`invoice_id`),
  KEY `fk_pap_invoice_has_pap_rc_pap_rc1_idx` (`rc_id`),
  KEY `fk_pap_invoice_has_pap_rc_pap_invoice1_idx` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_rc_dt`
--

INSERT INTO `pap_rc_dt` (`rc_id`, `invoice_id`, `amount`) VALUES
(1, 2, '7000.00'),
(2, 3, '3000.00'),
(3, 3, '1000.00');

-- --------------------------------------------------------

--
-- Table structure for table `pap_requisition`
--

CREATE TABLE IF NOT EXISTS `pap_requisition` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) DEFAULT NULL,
  `order_ref` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_requisition_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pap_req_dt`
--

CREATE TABLE IF NOT EXISTS `pap_req_dt` (
  `req_id` int(10) unsigned NOT NULL,
  `mat_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  PRIMARY KEY (`req_id`,`qty`),
  KEY `fk_pap_req_dt_pap_requisition1_idx` (`req_id`),
  KEY `fk_pap_req_dt_pap_mat1_idx` (`mat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pap_sale_cus`
--

CREATE TABLE IF NOT EXISTS `pap_sale_cus` (
  `user_id` int(10) unsigned NOT NULL,
  `cus_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`cus_id`),
  KEY `fk_pap_user_has_pap_customer_pap_customer1_idx` (`cus_id`),
  KEY `fk_pap_user_has_pap_customer_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_sale_cus`
--

INSERT INTO `pap_sale_cus` (`user_id`, `cus_id`) VALUES
(3, 1),
(5, 2),
(3, 8),
(5, 9),
(5, 10),
(3, 11),
(3, 12),
(5, 13);

-- --------------------------------------------------------

--
-- Table structure for table `pap_size`
--

CREATE TABLE IF NOT EXISTS `pap_size` (
  `size_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `size_name` varchar(100) NOT NULL,
  `size_height` float unsigned NOT NULL,
  `size_width` float unsigned NOT NULL,
  `cover_paper` int(10) unsigned NOT NULL,
  `cover_lay` tinyint(3) unsigned NOT NULL,
  `inside_paper` int(10) unsigned NOT NULL,
  `inside_lay` tinyint(3) unsigned NOT NULL,
  `cover_thick` float DEFAULT NULL,
  `cover_div` tinyint(4) DEFAULT NULL,
  `inside_div` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`size_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `pap_size`
--

INSERT INTO `pap_size` (`size_id`, `size_name`, `size_height`, `size_width`, `cover_paper`, `cover_lay`, `inside_paper`, `inside_lay`, `cover_thick`, `cover_div`, `inside_div`) VALUES
(1, 'A4', 29.5, 21, 19, 4, 18, 8, 1, 1, 1),
(2, 'A5', 14.5, 21, 19, 8, 18, 16, 1, 1, 1),
(3, '12.5x18cm', 12.5, 18, 21, 8, 21, 16, 1, 2, 2),
(4, '15x19cm', 15, 19, 19, 8, 19, 16, 1, 1, 1),
(5, '14.5x21cm', 14.5, 21, 18, 8, 18, 16, 1, 1, 1),
(6, '11.5x21', 11.5, 21, 18, 10, 20, 24, 1, 1, 1),
(7, '18x15cm', 18, 15, 19, 4, 19, 8, 1, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `pap_supplier`
--

CREATE TABLE IF NOT EXISTS `pap_supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `taxid` varchar(17) DEFAULT NULL,
  `address` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `tel` varchar(45) NOT NULL,
  `fax` varchar(45) DEFAULT NULL,
  `payment` tinyint(4) DEFAULT NULL,
  `credit_day` tinyint(3) unsigned DEFAULT NULL,
  `credit_amount` int(10) unsigned DEFAULT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `pap_supplier`
--

INSERT INTO `pap_supplier` (`id`, `code`, `name`, `taxid`, `address`, `url`, `email`, `tel`, `fax`, `payment`, `credit_day`, `credit_amount`, `added`) VALUES
(1, 'M001', 'กระดาษรักษ์โลก', '1-2345-67891-11-1', '123/2 บ้านต้นไม้ กทม', '', 'gpaper@gmail.com', '02-123-5544', '02-123-5544', 1, 30, 100000, '2016-02-19 22:47:20'),
(2, 'P001', 'บริษัท เคลือบสวย จำกัด', '', '12/22 กทม', '', 'coat@gmail.com', '02-123-5555', '', 1, 60, 300000, '2016-02-25 12:49:01'),
(3, 'M002', 'บจก. สีส้วยสวย จำกัด', '1-2312-31231-23-1', '1/2 หมู่บ้านสีวลี กทม', '', 'color@gmail.com', '02-222-2222', '', 1, 30, 300000, '2016-04-07 18:16:56');

-- --------------------------------------------------------

--
-- Table structure for table `pap_supplier_cat`
--

CREATE TABLE IF NOT EXISTS `pap_supplier_cat` (
  `tax_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tax_id`,`supplier_id`),
  KEY `fk_pap_term_tax_has_pap_supplier_pap_supplier1_idx` (`supplier_id`),
  KEY `fk_pap_term_tax_has_pap_supplier_pap_term_tax1_idx` (`tax_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_supplier_cat`
--

INSERT INTO `pap_supplier_cat` (`tax_id`, `supplier_id`) VALUES
(2, 1),
(10, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pap_supplier_ct`
--

CREATE TABLE IF NOT EXISTS `pap_supplier_ct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tel` varchar(45) NOT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_supplier_ct_pap_supplier1_idx` (`supplier_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `pap_supplier_ct`
--

INSERT INTO `pap_supplier_ct` (`id`, `supplier_id`, `name`, `email`, `tel`, `remark`) VALUES
(1, 1, 'นายสมหมาย ใจดี', 'sommai@jd.com', '02-444-2222', '11'),
(2, 1, 'นายสมนึก มุ่งหมาย', 'sm@gmail.com', '02-222-2222', ''),
(3, 2, 'คุณ เก่ง งานดี', 'keng@gmail.com', '081-888-8888', ''),
(4, 2, 'คุณ เร็ว ทำไว', 'fast@gmail.com', '082-777-7777', ''),
(5, 1, 'นายสมชัย มั่นหมาย', 'mm@gmail.com', '081-555-7895', ''),
(6, 3, 'คุณ เก่ง งานดี', 'keng@gmail.com', '02-222-2222', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_temp_deli`
--

CREATE TABLE IF NOT EXISTS `pap_temp_deli` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `deli_id` int(10) unsigned NOT NULL,
  `no` varchar(45) NOT NULL,
  `contact` int(10) unsigned NOT NULL,
  `address` int(10) unsigned NOT NULL,
  `remark` text,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_temp_deli_pap_delivery1_idx` (`deli_id`),
  KEY `fk_pap_temp_deli_pap_contact1_idx` (`contact`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_temp_deli`
--

INSERT INTO `pap_temp_deli` (`id`, `deli_id`, `no`, `contact`, `address`, `remark`, `date`) VALUES
(1, 1, 'DN16040001-1', 4, 0, '', '2016-04-27'),
(2, 2, 'DN16040002', 5, 4, '', '2016-04-30');

-- --------------------------------------------------------

--
-- Table structure for table `pap_temp_dt`
--

CREATE TABLE IF NOT EXISTS `pap_temp_dt` (
  `temp_deli_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  KEY `fk_pap_temp_dt_pap_temp_deli1_idx` (`temp_deli_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_temp_dt`
--

INSERT INTO `pap_temp_dt` (`temp_deli_id`, `order_id`, `qty`) VALUES
(1, 1, 1000),
(2, 2, 3000);

-- --------------------------------------------------------

--
-- Table structure for table `pap_term`
--

CREATE TABLE IF NOT EXISTS `pap_term` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `des` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `pap_term`
--

INSERT INTO `pap_term` (`id`, `name`, `slug`, `des`) VALUES
(1, 'ผลิตวัตถุดิบ', 'M', 'กลุ่มผู้ผลิตวัตถุดิบ'),
(2, 'กระดาษ', 'M', 'กลุ่มผู้ผลิตวัตถุดิบกระดาษ'),
(3, 'รับจ้างผลิต', 'P', 'กลุ่มผู้รับจ้างทำกระบวนการหลังพิมพ์'),
(4, 'รับจ้างพิมพ์', 'P', 'กลุ่มผู้รับจ้างพิมพ์'),
(5, 'Agency', 'A', ''),
(6, 'Sub Agency', 'A', ''),
(7, 'Sub Agency 2', 'A', ''),
(8, 'Department Store', 'D', ''),
(9, 'ทำเพลต', 'P', 'กลุ่มผู้รับจ้าทำเพลต'),
(10, 'งานเคลือบ', 'P', 'กลุ่มผู้รับจ้างเคลือบงาน'),
(11, 'ผลิตสี', 'M', 'กลุ่มผู้ผลิตสี'),
(12, 'Direct Contact', 'C', 'ลูกค้ากลุ่มบริษัทโดยตรง');

-- --------------------------------------------------------

--
-- Table structure for table `pap_term_tax`
--

CREATE TABLE IF NOT EXISTS `pap_term_tax` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` int(10) unsigned NOT NULL,
  `tax` varchar(100) NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `lineage` varchar(255) NOT NULL,
  `deep` tinyint(3) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pap_term_tax_pap_term1_idx` (`term_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `pap_term_tax`
--

INSERT INTO `pap_term_tax` (`id`, `term_id`, `tax`, `parent`, `lineage`, `deep`, `count`) VALUES
(1, 1, 'supplier', 0, '1', 0, 0),
(2, 2, 'supplier', 1, '1-2', 1, 0),
(3, 3, 'supplier', 0, '3', 0, 0),
(4, 4, 'supplier', 3, '3-4', 1, 0),
(5, 5, 'customer', 0, '5', 0, 0),
(6, 6, 'customer', 5, '5-6', 1, 0),
(7, 7, 'customer', 5, '5-7', 1, 0),
(8, 8, 'customer', 0, '8', 0, 0),
(9, 9, 'supplier', 3, '3-9', 1, 0),
(10, 10, 'supplier', 3, '3-10', 1, 0),
(11, 11, 'supplier', 1, '1-11', 1, 0),
(12, 12, 'customer', 0, '12', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pap_user`
--

CREATE TABLE IF NOT EXISTS `pap_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(30) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_pass` varchar(32) NOT NULL,
  `user_added` datetime NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_login_UNIQUE` (`user_login`),
  UNIQUE KEY `user_email_UNIQUE` (`user_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `pap_user`
--

INSERT INTO `pap_user` (`user_id`, `user_login`, `user_email`, `user_pass`, `user_added`) VALUES
(1, 'resolutems', 'resolute.ms@gmail.com', '93c6e444c9b2b24528b638149ba4b283', '2016-01-07 18:03:16'),
(2, 'testuser', 'test@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-01-11 11:21:55'),
(3, 'sale', 'sale@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-01-26 12:11:33'),
(4, 'manager', 'manager@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-03 20:03:09'),
(5, 'sale2', 'sale2@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-06 12:32:37'),
(6, 'cut_operator1', 'operator@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 11:54:33'),
(7, 'cut_operator2', 'operator2@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 11:54:52'),
(8, 'print_operator1', 'po@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 12:26:56'),
(9, 'print_operator2', 'po2@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 12:27:14'),
(10, 'fold_operator1', 'fo1@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 15:31:25'),
(11, 'fold_operator2', 'fo2@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-02-22 15:31:50'),
(12, 'pop', 'real_pop444@hotmail.com', 'a0c34625aba9db026f726dfa02f58ed8', '2016-03-07 15:42:41'),
(13, 'chanon', 'chanon.rms@gmail.com', '93c6e444c9b2b24528b638149ba4b283', '2016-03-08 10:18:12'),
(18, 'testu2', 'testu2@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-03-08 23:37:07'),
(19, 'manager2', 'm2@gmail.com', '93c6e444c9b2b24528b638149ba4b283', '2016-03-15 20:05:51'),
(20, 'production', 'pro@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-04-07 18:34:16'),
(21, 'account', 'acc@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-04-07 18:34:38'),
(22, 'buyer', 'buyer@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2016-04-07 18:34:59'),
(23, 'pornchai', 'pornchai.s@bu.ac.th', '81dc9bdb52d04dc20036dbd8313ed055', '2016-04-29 22:52:49');

-- --------------------------------------------------------

--
-- Table structure for table `pap_usermeta`
--

CREATE TABLE IF NOT EXISTS `pap_usermeta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_usermeta_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=55 ;

--
-- Dumping data for table `pap_usermeta`
--

INSERT INTO `pap_usermeta` (`id`, `user_id`, `meta_key`, `meta_value`) VALUES
(2, 2, 'user_auth', '2'),
(3, 1, 'user_auth', '1'),
(6, 1, 'l_status', ''),
(7, 3, 'user_auth', '17'),
(8, 3, 'l_status', ''),
(9, 4, 'user_auth', '51'),
(10, 5, 'user_auth', '17'),
(11, 5, 'l_status', ''),
(12, 4, 'signature', '/p-pap/image/user/sig_4.png'),
(13, 4, 'l_status', '2016-02-13 22:50:10'),
(14, 6, 'user_auth', '68'),
(15, 6, 'signature', ''),
(16, 7, 'user_auth', '68'),
(17, 7, 'signature', ''),
(18, 8, 'user_auth', '68'),
(19, 8, 'signature', ''),
(20, 9, 'user_auth', '68'),
(21, 9, 'signature', ''),
(22, 10, 'user_auth', '68'),
(23, 10, 'signature', ''),
(24, 11, 'user_auth', '68'),
(25, 11, 'signature', ''),
(26, 1, 'signature', '/p-pap/image/user/sig_1.jpg'),
(27, 12, 'user_auth', '1'),
(28, 12, 'signature', ''),
(29, 13, 'user_auth', '1'),
(30, 13, 'signature', ''),
(39, 18, 'user_auth', '2'),
(40, 18, 'signature', 'http://localhost/resolutems/p-pap/image/user/sig_18.png'),
(41, 19, 'user_auth', '51'),
(42, 19, 'signature', '/p-pap/image/user/sig_19.jpg'),
(43, 13, 'l_status', ''),
(44, 12, 'l_status', ''),
(45, 20, 'user_auth', '68'),
(46, 20, 'signature', ''),
(47, 21, 'user_auth', '76'),
(48, 21, 'signature', ''),
(49, 22, 'user_auth', '75'),
(50, 22, 'signature', ''),
(51, 20, 'l_status', ''),
(52, 22, 'l_status', ''),
(53, 23, 'user_auth', '2'),
(54, 23, 'signature', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_wip_delivery`
--

CREATE TABLE IF NOT EXISTS `pap_wip_delivery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `po_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ref` varchar(45) NOT NULL,
  `deliveried` datetime NOT NULL,
  `remark` text,
  PRIMARY KEY (`id`),
  KEY `fk_pap_wip_delivery_pap_process_po1_idx` (`po_id`),
  KEY `fk_pap_wip_delivery_pap_user1_idx` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `pap_wip_delivery`
--

INSERT INTO `pap_wip_delivery` (`id`, `po_id`, `user_id`, `ref`, `deliveried`, `remark`) VALUES
(2, 2, 1, '008', '2016-04-29 21:19:53', '');

-- --------------------------------------------------------

--
-- Table structure for table `pap_wip_delivery_dt`
--

CREATE TABLE IF NOT EXISTS `pap_wip_delivery_dt` (
  `delivery_id` int(10) unsigned NOT NULL,
  `dt_id` int(10) unsigned NOT NULL,
  `qty` decimal(10,2) unsigned NOT NULL,
  `stk_location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`delivery_id`,`dt_id`),
  KEY `fk_pap_wip_delivery_dt_pap_wip_delivery1_idx` (`delivery_id`),
  KEY `fk_pap_wip_delivery_dt_pap_pro_po_dt1_idx` (`dt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pap_wip_delivery_dt`
--

INSERT INTO `pap_wip_delivery_dt` (`delivery_id`, `dt_id`, `qty`, `stk_location`) VALUES
(2, 4, '1000.00', 'w2');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pap_comp_process`
--
ALTER TABLE `pap_comp_process`
  ADD CONSTRAINT `fk_pap_comp_process_pap_order_comp1` FOREIGN KEY (`comp_id`) REFERENCES `pap_order_comp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_comp_process_pap_process1` FOREIGN KEY (`process_id`) REFERENCES `pap_process` (`process_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_contact`
--
ALTER TABLE `pap_contact`
  ADD CONSTRAINT `fk_pap_contact_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_crm`
--
ALTER TABLE `pap_crm`
  ADD CONSTRAINT `fk_pap_crm_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_crm_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_customer_cat`
--
ALTER TABLE `pap_customer_cat`
  ADD CONSTRAINT `fk_pap_customer_has_pap_term_tax_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_customer_has_pap_term_tax_pap_term_tax1` FOREIGN KEY (`tax_id`) REFERENCES `pap_term_tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_customer_meta`
--
ALTER TABLE `pap_customer_meta`
  ADD CONSTRAINT `fk_pap_customer_meta_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_cus_ad`
--
ALTER TABLE `pap_cus_ad`
  ADD CONSTRAINT `fk_pap_cus_ad_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_delivery`
--
ALTER TABLE `pap_delivery`
  ADD CONSTRAINT `fk_pap_delivery_pap_contact1` FOREIGN KEY (`contact`) REFERENCES `pap_contact` (`contact_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_delivery_dt`
--
ALTER TABLE `pap_delivery_dt`
  ADD CONSTRAINT `fk_pap_delivery_has_pap_order_pap_delivery1` FOREIGN KEY (`deli_id`) REFERENCES `pap_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_delivery_has_pap_order_pap_order1` FOREIGN KEY (`order_id`) REFERENCES `pap_order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_invoice`
--
ALTER TABLE `pap_invoice`
  ADD CONSTRAINT `fk_pap_invoice_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pap_invoice_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_invoice_dt`
--
ALTER TABLE `pap_invoice_dt`
  ADD CONSTRAINT `fk_pap_invoice_dt_pap_delivery1` FOREIGN KEY (`deli_id`) REFERENCES `pap_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_invoice_has_pap_order_pap_invoice1` FOREIGN KEY (`invoice_id`) REFERENCES `pap_invoice` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_log`
--
ALTER TABLE `pap_log`
  ADD CONSTRAINT `fk_pap_log_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_machine`
--
ALTER TABLE `pap_machine`
  ADD CONSTRAINT `fk_pap_machine_pap_process1` FOREIGN KEY (`process_id`) REFERENCES `pap_process` (`process_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_mach_user`
--
ALTER TABLE `pap_mach_user`
  ADD CONSTRAINT `fk_pap_user_has_pap_machine_pap_machine1` FOREIGN KEY (`mach_id`) REFERENCES `pap_machine` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_user_has_pap_machine_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat`
--
ALTER TABLE `pap_mat`
  ADD CONSTRAINT `fk_pap_mat_pap_option1` FOREIGN KEY (`mat_cat_id`) REFERENCES `pap_option` (`op_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_matmeta`
--
ALTER TABLE `pap_matmeta`
  ADD CONSTRAINT `fk_pap_matmeta_pap_mat1` FOREIGN KEY (`mat_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat_cost`
--
ALTER TABLE `pap_mat_cost`
  ADD CONSTRAINT `fk_mat_has_pap_supplier_mat1` FOREIGN KEY (`mat_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_mat_has_pap_supplier_pap_supplier1` FOREIGN KEY (`supplier_id`) REFERENCES `pap_supplier` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_mat_delivery`
--
ALTER TABLE `pap_mat_delivery`
  ADD CONSTRAINT `fk_pap_mat_delivery_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_psp_mat_delivery_pap_mat_po1` FOREIGN KEY (`po_id`) REFERENCES `pap_mat_po` (`po_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat_delivery_dt`
--
ALTER TABLE `pap_mat_delivery_dt`
  ADD CONSTRAINT `fk_pap_mat_delivery_dt_pap_mat_delivery1` FOREIGN KEY (`delivery_id`) REFERENCES `pap_mat_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_mat_delivery_dt_pap_mat_po_detail1` FOREIGN KEY (`dt_id`) REFERENCES `pap_mat_po_detail` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat_po`
--
ALTER TABLE `pap_mat_po`
  ADD CONSTRAINT `fk_pap_mat_po_pap_supplier1` FOREIGN KEY (`supplier_id`) REFERENCES `pap_supplier` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pap_mat_po_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat_po_detail`
--
ALTER TABLE `pap_mat_po_detail`
  ADD CONSTRAINT `fk_pap_mat_po_has_pap_mat_cost_pap_mat_po1` FOREIGN KEY (`po_id`) REFERENCES `pap_mat_po` (`po_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_po_detail_pap_mat1` FOREIGN KEY (`mat_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_mat_stk`
--
ALTER TABLE `pap_mat_stk`
  ADD CONSTRAINT `fk_pap_mat_stk_pap_mat1` FOREIGN KEY (`mat_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_mat_stk_pap_mat_delivery1` FOREIGN KEY (`delivery_id`) REFERENCES `pap_mat_delivery` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_mat_stk_pap_requisition1` FOREIGN KEY (`req_id`) REFERENCES `pap_requisition` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_order`
--
ALTER TABLE `pap_order`
  ADD CONSTRAINT `fk_pap_order_pap_quotation1` FOREIGN KEY (`quote_id`) REFERENCES `pap_quotation` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_order_comp`
--
ALTER TABLE `pap_order_comp`
  ADD CONSTRAINT `fk_pap_order_comp_pap_mat1` FOREIGN KEY (`paper_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pap_order_comp_pap_order1` FOREIGN KEY (`order_id`) REFERENCES `pap_order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_pbill`
--
ALTER TABLE `pap_pbill`
  ADD CONSTRAINT `fk_pap_pbill_pap_contact1` FOREIGN KEY (`contact`) REFERENCES `pap_contact` (`contact_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_pbill_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_pbill_dt`
--
ALTER TABLE `pap_pbill_dt`
  ADD CONSTRAINT `fk_pap_pbill_has_pap_delivery_pap_delivery1` FOREIGN KEY (`deli_id`) REFERENCES `pap_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_pbill_has_pap_delivery_pap_pbill1` FOREIGN KEY (`pbill_id`) REFERENCES `pap_pbill` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_process`
--
ALTER TABLE `pap_process`
  ADD CONSTRAINT `fk_pap_process_pap_process_cat1` FOREIGN KEY (`process_cat_id`) REFERENCES `pap_process_cat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_process_meta`
--
ALTER TABLE `pap_process_meta`
  ADD CONSTRAINT `fk_pap_process_meta_pap_process1` FOREIGN KEY (`process_id`) REFERENCES `pap_process` (`process_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_process_po`
--
ALTER TABLE `pap_process_po`
  ADD CONSTRAINT `fk_pap_mat_po_pap_user10` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_process_po_pap_supplier1` FOREIGN KEY (`supplier_id`) REFERENCES `pap_supplier` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_pro_po_dt`
--
ALTER TABLE `pap_pro_po_dt`
  ADD CONSTRAINT `fk_pap_pro_po_dt_pap_process1` FOREIGN KEY (`process_id`) REFERENCES `pap_process` (`process_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_pro_po_dt_pap_process_po1` FOREIGN KEY (`po_id`) REFERENCES `pap_process_po` (`po_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_quotation`
--
ALTER TABLE `pap_quotation`
  ADD CONSTRAINT `fk_pap_quotation_pap_customer1` FOREIGN KEY (`customer_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_quotation_pap_size1` FOREIGN KEY (`job_size_id`) REFERENCES `pap_size` (`size_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_quotation_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_quote_comp`
--
ALTER TABLE `pap_quote_comp`
  ADD CONSTRAINT `fk_pap_quote_comp_pap_process1` FOREIGN KEY (`comp_print_id`) REFERENCES `pap_process` (`process_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_quote_comp_pap_quotation1` FOREIGN KEY (`quote_id`) REFERENCES `pap_quotation` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_quote_meta`
--
ALTER TABLE `pap_quote_meta`
  ADD CONSTRAINT `fk_pap_quote_meta_pap_quotation1` FOREIGN KEY (`quote_id`) REFERENCES `pap_quotation` (`quote_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_rc`
--
ALTER TABLE `pap_rc`
  ADD CONSTRAINT `fk_pap_rc_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_rc_dt`
--
ALTER TABLE `pap_rc_dt`
  ADD CONSTRAINT `fk_pap_invoice_has_pap_rc_pap_invoice1` FOREIGN KEY (`invoice_id`) REFERENCES `pap_invoice` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_invoice_has_pap_rc_pap_rc1` FOREIGN KEY (`rc_id`) REFERENCES `pap_rc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_requisition`
--
ALTER TABLE `pap_requisition`
  ADD CONSTRAINT `fk_pap_requisition_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_req_dt`
--
ALTER TABLE `pap_req_dt`
  ADD CONSTRAINT `fk_pap_req_dt_pap_mat1` FOREIGN KEY (`mat_id`) REFERENCES `pap_mat` (`mat_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_req_dt_pap_requisition1` FOREIGN KEY (`req_id`) REFERENCES `pap_requisition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_sale_cus`
--
ALTER TABLE `pap_sale_cus`
  ADD CONSTRAINT `fk_pap_user_has_pap_customer_pap_customer1` FOREIGN KEY (`cus_id`) REFERENCES `pap_customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_user_has_pap_customer_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_supplier_cat`
--
ALTER TABLE `pap_supplier_cat`
  ADD CONSTRAINT `fk_pap_term_tax_has_pap_supplier_pap_supplier1` FOREIGN KEY (`supplier_id`) REFERENCES `pap_supplier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_term_tax_has_pap_supplier_pap_term_tax1` FOREIGN KEY (`tax_id`) REFERENCES `pap_term_tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_supplier_ct`
--
ALTER TABLE `pap_supplier_ct`
  ADD CONSTRAINT `fk_pap_supplier_ct_pap_supplier1` FOREIGN KEY (`supplier_id`) REFERENCES `pap_supplier` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pap_temp_deli`
--
ALTER TABLE `pap_temp_deli`
  ADD CONSTRAINT `fk_pap_temp_deli_pap_contact1` FOREIGN KEY (`contact`) REFERENCES `pap_contact` (`contact_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_temp_deli_pap_delivery1` FOREIGN KEY (`deli_id`) REFERENCES `pap_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_temp_dt`
--
ALTER TABLE `pap_temp_dt`
  ADD CONSTRAINT `fk_pap_temp_dt_pap_temp_deli1` FOREIGN KEY (`temp_deli_id`) REFERENCES `pap_temp_deli` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_term_tax`
--
ALTER TABLE `pap_term_tax`
  ADD CONSTRAINT `fk_pap_term_tax_pap_term1` FOREIGN KEY (`term_id`) REFERENCES `pap_term` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_usermeta`
--
ALTER TABLE `pap_usermeta`
  ADD CONSTRAINT `fk_pap_usermeta_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pap_wip_delivery`
--
ALTER TABLE `pap_wip_delivery`
  ADD CONSTRAINT `fk_pap_wip_delivery_pap_process_po1` FOREIGN KEY (`po_id`) REFERENCES `pap_process_po` (`po_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_wip_delivery_pap_user1` FOREIGN KEY (`user_id`) REFERENCES `pap_user` (`user_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `pap_wip_delivery_dt`
--
ALTER TABLE `pap_wip_delivery_dt`
  ADD CONSTRAINT `fk_pap_wip_delivery_dt_pap_pro_po_dt1` FOREIGN KEY (`dt_id`) REFERENCES `pap_pro_po_dt` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pap_wip_delivery_dt_pap_wip_delivery1` FOREIGN KEY (`delivery_id`) REFERENCES `pap_wip_delivery` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
