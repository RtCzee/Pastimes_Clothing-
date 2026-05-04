-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 04, 2026 at 06:57 PM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clothingstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

DROP TABLE IF EXISTS `tbladmin`;
CREATE TABLE IF NOT EXISTS `tbladmin` (
  `adminID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`adminID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`adminID`, `username`, `email`, `password`) VALUES
(1, 'admin', 'admin@store.com', '$2y$10$eFTSmayoiy57G2pCdR8xLuFPCSA/Yt.XSeY4afb1eBaJR4gQ6Gj56');

-- --------------------------------------------------------

--
-- Table structure for table `tblaorder`
--

DROP TABLE IF EXISTS `tblaorder`;
CREATE TABLE IF NOT EXISTS `tblaorder` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `itemID` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `orderDate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`orderID`),
  KEY `userID` (`userID`),
  KEY `itemID` (`itemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tblclothes`
--

DROP TABLE IF EXISTS `tblclothes`;
CREATE TABLE IF NOT EXISTS `tblclothes` (
  `itemID` int(11) NOT NULL AUTO_INCREMENT,
  `itemName` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `sellerID` int(11) DEFAULT NULL,
  PRIMARY KEY (`itemID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tblclothes`
--

INSERT INTO `tblclothes` (`itemID`, `itemName`, `description`, `price`, `image`, `sellerID`) VALUES
(3, 'Vintage Leather jacket', 'made out of leather', '1200.00', 'assets/images/clothes/item_1777913851_69f8cffbd4de7.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

DROP TABLE IF EXISTS `tbluser`;
CREATE TABLE IF NOT EXISTS `tbluser` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','declined') DEFAULT 'pending',
  `role` enum('buyer','seller') DEFAULT 'buyer',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`userID`, `username`, `fullName`, `email`, `password`, `status`, `role`) VALUES
(1, 'johnnD', 'John Doe', 'john@gmail.com', '$2y$10$tzm9kbbjHDW2yL9/0sgwjOQG4gwIzGFmyaE03bu0deRTWyoPLuQoq', 'pending', 'buyer'),
(2, 'siHLE_OVU', 'Sihle Ndlovu', 'sihle@gmail.com', '$2y$10$XOreEzGN96xv9/6TFm1ZY.LXov3BTSRsm9VmlWEJlnGyL4pHq/FxO', 'pending', 'buyer'),
(3, 'MR_khumalo', 'Mike Khumalo', 'mike@gmail.com', '$2y$10$eM/LeXYFoSgtuRIDcNskUuL3wRw94tUcZQDGVeCDmvqBGV/QklAJ6', 'pending', 'buyer'),
(4, 'yourgirl_aya', 'Ayanda Mandla', 'mandla@gmail.com', '$2y$10$frGo0F.OpRhfet43vWlqM.zMgVBi.BbJy5ic5MpASCxlyB7fjIewa', 'pending', 'buyer'),
(5, 'ama_baloyi', 'Amanda Baloyi', 'david@gmail.com', '$2y$10$vRuqR4plV5ihFIFKaaDOu.B3EncPUlViXB7NXHw6JyOnuScufN5KK', 'pending', 'buyer'),
(6, 'admin', 'Admin@1', 'admin@store', '$2y$10$h2YN.cfQjIpQvgTZxeSuVOVkoOfzLkAKNHgghLsWm.yvjqq36rVfy', 'pending', 'buyer'),
(10, 'Styzey._', 'didi', 'didintlekutlwano@gmail.com', '$2y$10$lBaNfVXnZPOyTtL85L2e9.ZMO56B4/5xhr7nc1lBubMTjBD3kXu3G', 'active', 'buyer'),
(8, 'Pentacost_08', 'pentacost', 'pentacost8@gmail.com', '$2y$10$.hopsXGNs70ra8NTJqs8Wuj2P8ne/EvOwl71Gy4vfBa65ALn0TaOi', 'pending', 'buyer'),
(9, 'testseller', 'seller', 'testseller@gmail.com', '$2y$10$OyOkUHvLRH0J4Z.SLTmqde1B45XVujy3jDJv.HNYvq7B4tEwKalxi', 'active', 'seller');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
