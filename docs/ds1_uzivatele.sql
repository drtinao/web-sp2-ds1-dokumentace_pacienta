-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Počítač: localhost
-- Vygenerováno: Ned 21. říj 2018, 19:31
-- Verze serveru: 5.6.12-log
-- Verze PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `ds1_web_semestralka2`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `ds1_uzivatele`
--

CREATE TABLE IF NOT EXISTS `ds1_uzivatele` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `password_bcrypt` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `jmeno` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `prijmeni` varchar(45) COLLATE utf8_czech_ci DEFAULT NULL,
  `telefon` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  `pravo` int(11) DEFAULT NULL,
  `datum_vytvoreni` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `ds1_uzivatele`
--

INSERT INTO `ds1_uzivatele` (`id`, `login`, `password_bcrypt`, `jmeno`, `prijmeni`, `telefon`, `email`, `pravo`, `datum_vytvoreni`) VALUES
(1, 'admin', '$2y$10$ruzwJwD.xQOZZh1yvP48e.4Sj4Uvz3RCuobnfGqYwK7KNSw3MlOgG', 'Jan', 'Novák', '123 456 789', 'email@seznam.cz', 100, '2018-09-01 01:00:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
