-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 04:00 PM
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
-- Database: `votingsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `AccountID` int(11) NOT NULL,
  `GradeLevel` varchar(100) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Username` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `AccountStatus` varchar(255) DEFAULT NULL,
  `VoteStatus` varchar(100) DEFAULT NULL,
  `SecurityKey` varchar(20) DEFAULT NULL,
  `ModifiedBy` int(255) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `AdminModifiedBy` int(255) DEFAULT NULL,
  `AdminModifiedDate` datetime DEFAULT NULL,
  `CreatedBy` int(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`AccountID`, `GradeLevel`, `Name`, `Username`, `Password`, `AccountStatus`, `VoteStatus`, `SecurityKey`, `ModifiedBy`, `ModifiedDate`, `AdminModifiedBy`, `AdminModifiedDate`, `CreatedBy`, `CreatedDate`) VALUES
(1001, '7', 'Juan Dela Cruz', 'jdelacruz7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'A1B2C3D4E5', NULL, NULL, 3, '2025-08-22 16:10:45', 1, '2025-08-22 00:00:00'),
(1002, '8', 'Maria Santos', 'msantos8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'X9Y8Z7W6V5', NULL, NULL, 3, '2025-08-22 18:47:07', 1, '2025-08-22 00:00:00'),
(1003, '9', 'Jose Rizal', 'jrizal9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'K4L5M6N7O8', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1004, '10', 'Ana Reyes', 'areyes10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'P1Q2R3S4T5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1005, '11', 'Pedro Gomez', 'pgomez11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'U6V7W8X9Y0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1006, '12', 'Luisa Mendoza', 'lmendoza12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'M1N2B3V4C5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1007, '7', 'Ramon Villanueva', 'rvilla7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'Z1X2C3V4B5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1008, '8', 'Clarissa Bautista', 'cbautista8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'A2S3D4F5G6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1009, '9', 'Enrico Navarro', 'enavarro9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'Q1W2E3R4T5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1010, '10', 'Isabel Aquino', 'iaquino10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'L9K8J7H6G5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1011, '11', 'Carlo Hernandez', 'chernandez11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'N5M6B7V8C9', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1012, '12', 'Sophia Flores', 'sflores12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'T1Y2U3I4O5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1013, '7', 'Marco Cruz', 'mcruz7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'R6T7Y8U9I0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1014, '8', 'Jasmine Ramos', 'jramos8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'H1G2F3D4S5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1015, '9', 'Nico Torres', 'ntorres9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'K3J4H5G6F7', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1016, '10', 'Angelica Lim', 'alim10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'S8A7D6F5G4', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1017, '11', 'Renato Gutierrez', 'rgutierrez11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'W1Q2E3R4T6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1018, '12', 'Camille Pascual', 'cpascual12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'L1O2P3Q4R5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1019, '7', 'Francis Legaspi', 'flegaspi7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'V5B6N7M8L9', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1020, '8', 'Trisha Morales', 'tmorales8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'C9X8Z7A6S5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1021, '9', 'Jericho Dizon', 'jdizon9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'E1R2T3Y4U5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1022, '10', 'Bea Loyola', 'bloyola10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'Z9X8C7V6B5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1023, '11', 'Lorenzo Mercado', 'lmercado11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'H2J3K4L5M6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1024, '12', 'Patricia Yulo', 'pyulo12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'Q7W8E9R0T1', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1026, '8', 'Karina Salvador', 'ksalvador8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'S1D2F3G4H5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1027, '9', 'Miguel Ferrer', 'mferrer9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'A7S6D5F4G3', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1028, '10', 'Abigail Mendoza', 'amendoza10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'Q2W3E4R5T6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1029, '11', 'Rafael Cabral', 'rcabral11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'N9B8V7C6X5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1030, '12', 'Ivy Galvez', 'igalvez12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'Y6U7I8O9P0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1031, '7', 'Bryan Tuazon', 'btuazon7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'G1F2D3S4A5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1032, '8', 'Jolina Castillo', 'jcastillo8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'P9O8I7U6Y5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1033, '9', 'Alvin Gomez', 'agomez9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'R4E3W2Q1T0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1034, '10', 'Rica David', 'rdavid10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'H3J4K5L6Z7', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1035, '11', 'Celeste Marquez', 'cmarquez11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'F6G5H4J3K2', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1036, '12', 'Andre Ignacio', 'aignacio12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'M1N0B9V8C7', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1037, '7', 'Elena Aquino', 'eaquino7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'Z2X3C4V5B6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1038, '8', 'Joshua Alonzo', 'jalonzo8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'Q8W9E0R1T2', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1039, '9', 'Danica Uy', 'duy9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'O2P3L4K5J6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1040, '10', 'Vincent Ramos', 'vramos10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'G9H8J7K6L5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1041, '11', 'Trixie Soriano', 'tsoriano11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'A1S2D3F4G5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1042, '12', 'Leo Santos', 'lsantos12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'U0I9O8P7Q6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1043, '7', 'Maricar Beltran', 'mbeltran7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'T5R4E3W2Q1', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1044, '8', 'Kurt Sandoval', 'ksandoval8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'L3K2J1H0G9', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1045, '9', 'Dianne Tolentino', 'dtolentino9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'C4V3B2N1M0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1046, '10', 'Eugene Castro', 'ecastro10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'Z0A9S8D7F6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1047, '11', 'Clarence Abad', 'cabad11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'P4O3I2U1Y0', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1048, '12', 'Lara Fajardo', 'lfajardo12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'R9T8Y7U6I5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1049, '7', 'Jared Lopez', 'jlopez7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'K6L5M4N3B2', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1050, '8', 'Shaina Rivera', 'srivera8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'E3R2T1Y0U9', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1051, '9', 'Marvin Javier', 'mjavier9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'H5J6K7L8Z9', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1052, '10', 'Grace Valdez', 'gvaldez10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'S2D3F4G5H6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1053, '11', 'Nathaniel Cruz', 'ncruz11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'B1N2M3L4K5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1054, '12', 'Nicole Vergara', 'nvergara12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'W7Q8E9R0T1', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1055, '7', 'Gino Salvador', 'gsalvador7', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'A7B8C9D0E1', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1056, '8', 'Lianne Cruz', 'lcruz8', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'VOTED', 'F2G3H4J5K6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1057, '9', 'Oscar Ramos', 'oramos9', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'L1K2J3H4G5', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1058, '10', 'Mikaela Sy', 'msy10', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'BLOCKED', 'NOT VOTED', 'Z3X4C5V6B7', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1059, '11', 'Joaquin Luna', 'jluna11', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'VOTED', 'T2R3E4W5Q6', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00'),
(1060, '12', 'Bianca Robles', 'brobles12', '$2y$10$lBmXkd.BxcJdDCZeTUUNEu.NPgElqrF8kT/OIcJgFyrnsaTtWR.02', 'ACTIVE', 'NOT VOTED', 'M8N7B6V5C4', NULL, NULL, 1, '2025-08-22 00:00:00', 1, '2025-08-22 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(255) NOT NULL,
  `Username` varchar(100) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `SecurityKey` varchar(255) NOT NULL,
  `Status` varchar(255) NOT NULL,
  `ModifiedBy` int(255) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `CreatedBy` int(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Username`, `Password`, `SecurityKey`, `Status`, `ModifiedBy`, `ModifiedDate`, `CreatedBy`, `CreatedDate`) VALUES
(1, 'loralei', '$2y$10$26As8Kihcwb7UmqoWIdRYOFFovahmwKIjYkJANoSUC23oDWW8.U1e', 'ABC', 'ACTIVE', NULL, NULL, 1, '2025-08-22 00:00:00'),
(2, 'kuma', '$2y$10$26As8Kihcwb7UmqoWIdRYOFFovahmwKIjYkJANoSUC23oDWW8.U1e', 'ABC', 'ACTIVE', 3, '2025-08-22 12:31:20', 1, '2025-08-22 00:00:00'),
(3, 'afk', '$2y$10$26As8Kihcwb7UmqoWIdRYOFFovahmwKIjYkJANoSUC23oDWW8.U1e', 'ABC', 'ACTIVE', 3, '2025-08-22 18:46:55', 1, '2025-08-22 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `adminloggedrecord`
--

CREATE TABLE `adminloggedrecord` (
  `LoggedID` int(255) NOT NULL,
  `AdminID` int(255) NOT NULL,
  `LoggedIn` datetime DEFAULT NULL,
  `LoggedOut` datetime DEFAULT NULL,
  `ModifyBy` int(11) DEFAULT NULL,
  `ModifyDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminloggedrecord`
--

INSERT INTO `adminloggedrecord` (`LoggedID`, `AdminID`, `LoggedIn`, `LoggedOut`, `ModifyBy`, `ModifyDate`) VALUES
(1003, 3, '2025-08-22 21:54:06', '2025-08-22 21:54:19', 0, '0000-00-00 00:00:00'),
(1004, 3, '2025-08-22 21:54:35', '2025-08-22 21:55:02', 3, '0000-00-00 00:00:00'),
(1005, 3, '2025-08-22 21:55:46', NULL, 3, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `agenda`
--

CREATE TABLE `agenda` (
  `AgendaID` int(11) NOT NULL,
  `CandidateID` int(11) DEFAULT NULL,
  `Agenda` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agenda`
--

INSERT INTO `agenda` (`AgendaID`, `CandidateID`, `Agenda`) VALUES
(2, 1001, 'Promote mental health and peer support.'),
(4, 1002, 'Champion youth development through leadership programs.'),
(5, 1002, 'Enhance communication between students and administration.'),
(6, 1002, 'Promote academic and extracurricular balance.'),
(7, 1003, 'Foster unity and school spirit in all activities.'),
(8, 1003, 'Encourage student-led initiatives for community service.'),
(9, 1003, 'Support innovative ideas to improve campus life.'),
(10, 1005, 'Facilitate better student participation in events.'),
(11, 1005, 'Promote awareness on mental health and well-being.'),
(12, 1007, 'Support student welfare programs and activities.'),
(13, 1007, 'Encourage teamwork among student councils.'),
(14, 1008, 'Ensure accurate documentation of meetings and events.'),
(15, 1008, 'Improve communication through digital platforms.'),
(16, 1008, 'Maintain transparency in record keeping.'),
(17, 1009, 'Assist in organizing school events efficiently.'),
(18, 1009, 'Enhance communication within student organizations.'),
(19, 1009, 'Maintain proper documentation and correspondence.'),
(20, 1010, 'Support transparency in student council operations.'),
(21, 1010, 'Promote collaboration among different student groups.'),
(22, 1010, 'Document and share meeting outcomes promptly.'),
(23, 1012, 'Manage funds responsibly and transparently.'),
(24, 1012, 'Ensure timely reporting of all financial transactions.'),
(25, 1013, 'Assist in budgeting for school activities.'),
(26, 1013, 'Promote financial literacy among students.'),
(27, 1014, 'Conduct thorough audits to ensure financial accuracy.'),
(28, 1014, 'Promote accountability in fund usage.'),
(29, 1015, 'Support transparent financial practices.'),
(30, 1015, 'Assist in verifying proper use of resources.'),
(31, 1016, 'Provide timely updates on student council activities.'),
(32, 1016, 'Enhance social media presence for better outreach.'),
(33, 1016, 'Encourage student feedback through surveys and polls.'),
(34, 1017, 'Promote positive communication between students and officials.'),
(35, 1017, 'Create engaging content to highlight student achievements.'),
(36, 1017, 'Organize information campaigns on important issues.'),
(37, 1018, 'Ensure clear and accurate information dissemination.'),
(38, 1018, 'Promote inclusivity in all communications.'),
(39, 1018, 'Support collaboration between student organizations.'),
(40, 1019, 'Maintain order during school events and meetings.'),
(41, 1019, 'Promote respect and proper conduct among students.'),
(42, 1020, 'Coordinate protocols for official student activities.'),
(43, 1020, 'Ensure smooth execution of ceremonial functions.'),
(44, 1021, 'Promote academic excellence and participation.'),
(45, 1021, 'Support student well-being and inclusivity.'),
(46, 1022, 'Encourage teamwork and class spirit.'),
(47, 1022, 'Organize fun and educational activities.'),
(48, 1023, 'Represent student concerns effectively.'),
(49, 1023, 'Promote environmental awareness among peers.'),
(50, 1024, 'Support peer mentoring programs.'),
(51, 1024, 'Encourage participation in school activities.'),
(52, 1025, 'Promote leadership development among students.'),
(53, 1025, 'Encourage community involvement.'),
(54, 1026, 'Support student rights and welfare.'),
(55, 1026, 'Organize cultural and educational events.'),
(56, 1027, 'Advocate for a positive learning environment.'),
(57, 1027, 'Promote teamwork and respect.'),
(58, 1028, 'Encourage student engagement in school projects.'),
(60, 1029, 'Support academic support programs.'),
(61, 1029, 'Encourage participation in extracurricular activities.'),
(62, 1030, 'Promote environmental stewardship.'),
(63, 1030, 'Support student mental health initiatives.'),
(64, 1031, 'Advocate for student interests in school decisions.'),
(65, 1031, 'Promote unity among classmates.'),
(66, 1032, 'Encourage volunteerism and community service.'),
(67, 1032, 'Support student creativity and innovation.'),
(68, 1033, 'Promote academic achievement and excellence.'),
(69, 1033, 'Encourage active participation in school events.'),
(70, 1034, 'Support student-led initiatives.'),
(71, 1034, 'Promote peer support and cooperation.'),
(72, 1035, 'Advocate for a respectful and inclusive campus.'),
(73, 1035, 'Encourage cultural appreciation and understanding.'),
(74, 1036, 'Support student welfare programs.'),
(75, 1036, 'Promote responsible leadership.'),
(76, 1037, 'Prepare students for post-graduation success.'),
(77, 1037, 'Promote unity and school spirit.'),
(78, 1038, 'Support career guidance and counseling programs.'),
(79, 1038, 'Encourage academic perseverance and excellence.'),
(80, 1039, 'Promote mental health awareness and support.'),
(81, 1039, 'Advocate for student rights and welfare.'),
(82, 1040, 'Encourage active involvement in school governance.'),
(83, 1040, 'Support community service and outreach programs.');

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `CandidateID` int(11) NOT NULL,
  `ElectionID` varchar(255) DEFAULT NULL,
  `PartylistName` varchar(255) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Position` varchar(255) DEFAULT NULL,
  `GradeLevel` varchar(100) DEFAULT NULL,
  `ModifiedBy` int(255) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `CreatedBy` int(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`CandidateID`, `ElectionID`, `PartylistName`, `Name`, `Position`, `GradeLevel`, `ModifiedBy`, `ModifiedDate`, `CreatedBy`, `CreatedDate`) VALUES
(1001, 'E2025', 'Youth Vision Partylist', 'PERIA, JHANN EDRICK \"JHED\" S.', 'PRESIDENT', 'ALL', 3, '2025-08-22 19:07:23', NULL, NULL),
(1002, 'E2025', 'Alyansa Kabataan Partylist\r\n', 'ANTONIO, JAMES IAN \"IAN\" S.', 'PRESIDENT', 'ALL', NULL, NULL, NULL, NULL),
(1003, 'E2025', 'Independent', 'MORENO, CZAR SERAFIN \"SESAR\" O.', 'PRESIDENT', 'ALL', NULL, NULL, NULL, NULL),
(1005, 'E2025', 'Alyansa Kabataan Partylist', 'ESTRELLA, MARIA PATRISHA \"PAT\"', 'VICE PRESIDENT', 'ALL', NULL, NULL, NULL, NULL),
(1007, 'E2025', 'Youth Vision Partylist\r\n', 'MIRANDA, LHERIZA \"IZA\" A.', 'VICE PRESIDENT', 'ALL', NULL, NULL, NULL, NULL),
(1008, 'E2025', 'Youth Vision Partylist\r\n', 'PULLIDO, KEZIAH \"KISH\" B.', 'SECRETARY', 'ALL', NULL, NULL, NULL, NULL),
(1009, 'E2025', 'Alyansa Kabataan Partylist', 'DELOS SANTOS, CARINA M.', 'SECRETARY', 'ALL', NULL, NULL, NULL, NULL),
(1010, 'E2025', 'Independent', 'CRUZ, JACOB ELLORDI \"JAY\" T.', 'SECRETARY', 'ALL', NULL, NULL, NULL, NULL),
(1012, 'E2025', 'Youth Vision Partylist', 'SANTOS, JENNILYN \"JHEN\" B.', 'TREASURER', 'ALL', NULL, NULL, NULL, NULL),
(1013, 'E2025', 'Alyansa Kabataan Partylist', 'DELA PENA, MJ R.', 'TREASURER', 'ALL', NULL, NULL, NULL, NULL),
(1014, 'E2025', 'Youth Vision Partylist\r\n', 'CRUZ, RENALDO \"REN\" F.', 'AUDITOR', 'ALL', NULL, NULL, NULL, NULL),
(1015, 'E2025', 'Alyansa Kabataan Partylist\r\n', 'ALMARIO, LIZA MARIE \"LIZ\" G.', 'AUDITOR', 'ALL', NULL, NULL, NULL, NULL),
(1016, 'E2025', 'Independent', 'DELA CRUZ, ANTONIO \"TONY\" R.', 'PUBLIC INFORMATION OFFICER', 'ALL', NULL, NULL, NULL, NULL),
(1017, 'E2025', 'Youth Vision Partylist', 'RAMIREZ, SOFIA \"FIA\" N.', 'PUBLIC INFORMATION OFFICER', 'ALL', NULL, NULL, NULL, NULL),
(1018, 'E2025', 'Alyansa Kabataan Partylist\r\n', 'SANTOS, MIGUEL \"MIGZ\" S.', 'PUBLIC INFORMATION OFFICER', 'ALL', NULL, NULL, NULL, NULL),
(1019, 'E2025', 'Youth Vision Partylist', 'HERRERA, NICOLE \"NICKY\" E.', 'PROTOCOL OFFICER', 'ALL', NULL, NULL, NULL, NULL),
(1020, 'E2025', 'Alyansa Kabataan Partylist', 'MENDOZA, CARLO \"CARL\" P.', 'PROTOCOL OFFICER', 'ALL', NULL, NULL, NULL, NULL),
(1021, 'E2025', ' Youth Vision Partylist', 'MIGUEL, AISHA \"ASHANG\" C.', 'GRADE 8 REPRESENTATIVE', '8', NULL, NULL, NULL, NULL),
(1022, 'E2025', 'Youth Vision Partylist', 'AMAYO, KEVIN \"KEV\" R.', 'GRADE 8 REPRESENTATIVE', '8', NULL, NULL, NULL, NULL),
(1023, 'E2025', 'Alyansa Kabataan Partylist\r\n', 'VALDEZ, JESSA \"JESS\" L.', 'GRADE 8 REPRESENTATIVE', '8', NULL, NULL, NULL, NULL),
(1024, 'E2025', 'Independent\r\n', 'RAMOS, SHERLY GRACE \"SHERY\" T.', 'GRADE 8 REPRESENTATIVE', '8', NULL, NULL, NULL, NULL),
(1025, 'E2025', 'Youth Vision Partylist', 'DEL ROSARIO, HANNAH \"HAN\" K', 'GRADE 9 REPRESENTATIVE', '9', NULL, NULL, NULL, NULL),
(1026, 'E2025', 'Youth Vision Partylist\r\n', 'NAVARRO, JUSTIN \"TIN\" M.', 'GRADE 9 REPRESENTATIVE', '9', NULL, NULL, NULL, NULL),
(1027, 'E2025', 'Alyansa Kabataan Partylist\r\n', 'VILLANUEVA, CAMILLE \"CAM\" P.', 'GRADE 9 REPRESENTATIVE', '9', NULL, NULL, NULL, NULL),
(1028, 'E2025', 'Alyansa Kabataan Partylist', 'DIAZ, RYAN \"RY\" D.', 'GRADE 9 REPRESENTATIVE', '8', 3, '2025-08-22 18:03:02', NULL, NULL),
(1029, 'E2025', 'Youth Vision Partylist', 'HERNANDEZ, CARLO \"CJ\" L.', 'GRADE 10 REPRESENTATIVE', '10', NULL, NULL, NULL, NULL),
(1030, 'E2025', 'Youth Vision Partylist', 'SANTOS, ALYSSA \"LYSSA\" G.', 'GRADE 10 REPRESENTATIVE', '10', NULL, NULL, NULL, NULL),
(1031, 'E2025', 'Youth Vision Partylist\r\n', 'REYES, JERICHO \"RICH\" P.', 'GRADE 10 REPRESENTATIVE', '10', NULL, NULL, NULL, NULL),
(1032, 'E2025', 'Alyansa Kabataan Partylist', 'LOPEZ, MIA A.', 'GRADE 10 REPRESENTATIVE', '10', NULL, NULL, NULL, NULL),
(1033, 'E2025', 'Youth Vision Partylist\r\n', 'FLORES, BIANCA F.', 'GRADE 11 REPRESENTATIVE', '11', NULL, NULL, NULL, NULL),
(1034, 'E2025', 'Youth Vision Partylist', 'HERNANDEZ, ETHAN \"TAN\" H.', 'GRADE 11 REPRESENTATIVE', '11', NULL, NULL, NULL, NULL),
(1035, 'E2025', 'Alyansa Kabataan Partylist', 'DOMINGO, RACHEL D.', 'GRADE 11 REPRESENTATIVE', '11', NULL, NULL, NULL, NULL),
(1036, 'E2025', 'Alyansa Kabataan Partylist', 'RAMOS, LUIS \"LOU\" P.', 'GRADE 11 REPRESENTATIVE', '11', NULL, NULL, NULL, NULL),
(1037, 'E2025', 'Youth Vision Partylist', 'SISON, CARLA \"CARLIE\" S.', 'GRADE 12 REPRESENTATIVE', '12', NULL, NULL, NULL, NULL),
(1038, 'E2025', 'Youth Vision Partylist', 'LUCERO, DANIEL \"DAN\" R.', 'GRADE 12 REPRESENTATIVE', '12', NULL, NULL, NULL, NULL),
(1039, 'E2025', 'Youth Vision Partylist\r\n', 'GALLO, NICOLE \"COLE\" M.', 'GRADE 12 REPRESENTATIVE', '12', NULL, NULL, NULL, NULL),
(1040, 'E2025', 'Alyansa Kabataan Partylist', 'CORTEZ, ANGELICA \"ANGE\" T.', 'GRADE 12 REPRESENTATIVE', '12', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `election`
--

CREATE TABLE `election` (
  `ElectionID` varchar(255) NOT NULL,
  `ElectionName` varchar(255) NOT NULL,
  `StartDateTime` datetime NOT NULL,
  `EndDateTime` datetime NOT NULL,
  `G8RepNum` int(11) NOT NULL,
  `G9RepNum` int(11) NOT NULL,
  `G10RepNum` int(11) NOT NULL,
  `G11RepNum` int(11) NOT NULL,
  `G12RepNum` int(11) NOT NULL,
  `ModifiedBy` int(255) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL,
  `CreatedBy` int(255) DEFAULT NULL,
  `CreatedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election`
--

INSERT INTO `election` (`ElectionID`, `ElectionName`, `StartDateTime`, `EndDateTime`, `G8RepNum`, `G9RepNum`, `G10RepNum`, `G11RepNum`, `G12RepNum`, `ModifiedBy`, `ModifiedDate`, `CreatedBy`, `CreatedDate`) VALUES
('E2025', 'ELECTION 2025', '2025-08-22 21:58:00', '2025-08-22 21:59:00', 2, 2, 2, 2, 2, 3, '2025-08-22 18:12:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `userloggedrecord`
--

CREATE TABLE `userloggedrecord` (
  `LoggedID` int(255) NOT NULL,
  `AccountID` int(255) NOT NULL,
  `LoggedIn` datetime DEFAULT NULL,
  `LoggedOut` datetime DEFAULT NULL,
  `ModifiedBy` int(255) DEFAULT NULL,
  `ModifiedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vote`
--

CREATE TABLE `vote` (
  `VoteID` int(11) NOT NULL,
  `AccountID` int(11) DEFAULT NULL,
  `CandidateID` int(11) DEFAULT NULL,
  `ElectionID` varchar(255) DEFAULT NULL,
  `DateTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`AccountID`),
  ADD UNIQUE KEY `unique_username` (`Username`),
  ADD KEY `fk_acc_createdby` (`CreatedBy`),
  ADD KEY `fk_acc_adminmodifiedby` (`AdminModifiedBy`),
  ADD KEY `fk_acc_modifiedby` (`ModifiedBy`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `uniqueA_username` (`Username`),
  ADD KEY `fk_admin_createdby` (`CreatedBy`),
  ADD KEY `fk_admin_modifiedby` (`ModifiedBy`);

--
-- Indexes for table `adminloggedrecord`
--
ALTER TABLE `adminloggedrecord`
  ADD PRIMARY KEY (`LoggedID`),
  ADD KEY `fk_adminloggedrecords_admin` (`AdminID`);

--
-- Indexes for table `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`AgendaID`),
  ADD KEY `CandidateID` (`CandidateID`);

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`CandidateID`),
  ADD KEY `ElectionID` (`ElectionID`);

--
-- Indexes for table `election`
--
ALTER TABLE `election`
  ADD PRIMARY KEY (`ElectionID`),
  ADD UNIQUE KEY `unique_electionname` (`ElectionName`);

--
-- Indexes for table `userloggedrecord`
--
ALTER TABLE `userloggedrecord`
  ADD PRIMARY KEY (`LoggedID`),
  ADD KEY `fk_userloggedrecord_account` (`AccountID`);

--
-- Indexes for table `vote`
--
ALTER TABLE `vote`
  ADD PRIMARY KEY (`VoteID`),
  ADD KEY `AccountID` (`AccountID`),
  ADD KEY `CandidateID` (`CandidateID`),
  ADD KEY `ElectionID` (`ElectionID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `AccountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1061;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `adminloggedrecord`
--
ALTER TABLE `adminloggedrecord`
  MODIFY `LoggedID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1006;

--
-- AUTO_INCREMENT for table `agenda`
--
ALTER TABLE `agenda`
  MODIFY `AgendaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `CandidateID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1041;

--
-- AUTO_INCREMENT for table `userloggedrecord`
--
ALTER TABLE `userloggedrecord`
  MODIFY `LoggedID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1001;

--
-- AUTO_INCREMENT for table `vote`
--
ALTER TABLE `vote`
  MODIFY `VoteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1007;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `fk_acc_adminmodifiedby` FOREIGN KEY (`AdminModifiedBy`) REFERENCES `admin` (`AdminID`),
  ADD CONSTRAINT `fk_acc_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `admin` (`AdminID`),
  ADD CONSTRAINT `fk_acc_modifiedby` FOREIGN KEY (`ModifiedBy`) REFERENCES `account` (`AccountID`),
  ADD CONSTRAINT `fk_account_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `admin` (`AdminID`);

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_createdby` FOREIGN KEY (`CreatedBy`) REFERENCES `admin` (`AdminID`),
  ADD CONSTRAINT `fk_admin_modifiedby` FOREIGN KEY (`ModifiedBy`) REFERENCES `admin` (`AdminID`);

--
-- Constraints for table `adminloggedrecord`
--
ALTER TABLE `adminloggedrecord`
  ADD CONSTRAINT `fk_adminloggedrecords_admin` FOREIGN KEY (`AdminID`) REFERENCES `admin` (`AdminID`);

--
-- Constraints for table `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`CandidateID`) REFERENCES `candidate` (`CandidateID`);

--
-- Constraints for table `candidate`
--
ALTER TABLE `candidate`
  ADD CONSTRAINT `candidate_ibfk_1` FOREIGN KEY (`ElectionID`) REFERENCES `election` (`ElectionID`);

--
-- Constraints for table `userloggedrecord`
--
ALTER TABLE `userloggedrecord`
  ADD CONSTRAINT `fk_userloggedrecord_account` FOREIGN KEY (`AccountID`) REFERENCES `account` (`AccountID`);

--
-- Constraints for table `vote`
--
ALTER TABLE `vote`
  ADD CONSTRAINT `vote_ibfk_1` FOREIGN KEY (`AccountID`) REFERENCES `account` (`AccountID`),
  ADD CONSTRAINT `vote_ibfk_2` FOREIGN KEY (`CandidateID`) REFERENCES `candidate` (`CandidateID`),
  ADD CONSTRAINT `vote_ibfk_3` FOREIGN KEY (`ElectionID`) REFERENCES `election` (`ElectionID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
