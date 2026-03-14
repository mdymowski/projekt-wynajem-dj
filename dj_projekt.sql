-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 09:02 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dj_projekt`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `faq`
--

CREATE TABLE `faq` (
  `id_faq` int(11) NOT NULL,
  `pytanie` varchar(255) DEFAULT NULL,
  `odpowiedz` varchar(255) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL COMMENT 'Użytkownik, który zadał pytanie',
  `data_pytania` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `oferta_uslug`
--

CREATE TABLE `oferta_uslug` (
  `id_uslugi` int(11) NOT NULL,
  `nazwa` varchar(255) DEFAULT NULL,
  `cena` decimal(10,2) DEFAULT NULL COMMENT 'Cena w PLN',
  `opis` varchar(255) DEFAULT NULL COMMENT 'Szczegółowy opis usługi',
  `czas_trwania` int(11) DEFAULT NULL COMMENT 'Czas trwania usługi w minutach',
  `id_promocji` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oferta_uslug`
--

INSERT INTO `oferta_uslug` (`id_uslugi`, `nazwa`, `cena`, `opis`, `czas_trwania`, `id_promocji`) VALUES
(1, 'Oprawa muzyczna wesela', 2000.00, 'Profesjonalna oprawa muzyczna na wesele, w tym światła i nagłośnienie.', 300, NULL),
(2, 'Impreza firmowa', 1500.00, 'DJ na imprezę firmową z różnorodnym repertuarem.', 240, NULL),
(3, 'Urodziny', 1000.00, 'Muzyka na imprezę urodzinową, światła i mikrofon.', 180, NULL),
(4, 'Studniówka', 2500.00, 'Kompleksowa oprawa muzyczna i świetlna na studniówkę.', 360, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `opinie_klientow`
--

CREATE TABLE `opinie_klientow` (
  `id_opinii` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `tresc` varchar(255) DEFAULT NULL,
  `data_opinii` date DEFAULT NULL,
  `ocena` int(11) DEFAULT NULL COMMENT 'Ocena w skali 1-5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `playlisty`
--

CREATE TABLE `playlisty` (
  `id_playlista` int(11) NOT NULL,
  `id_rezerwacji` int(11) DEFAULT NULL,
  `link_playlista` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `promocje`
--

CREATE TABLE `promocje` (
  `id_promocji` int(11) NOT NULL,
  `nazwa` varchar(255) DEFAULT NULL COMMENT 'Nazwa promocji, np. Zniżka na konserwację',
  `opis` varchar(255) DEFAULT NULL COMMENT 'Opis promocji',
  `rabat` float DEFAULT NULL COMMENT 'Wartość rabatu w procentach, np. 10 dla 10%',
  `data_rozpoczecia` date DEFAULT NULL COMMENT 'Data rozpoczęcia promocji',
  `data_zakonczenia` date DEFAULT NULL COMMENT 'Data zakończenia promocji'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promocje`
--

INSERT INTO `promocje` (`id_promocji`, `nazwa`, `opis`, `rabat`, `data_rozpoczecia`, `data_zakonczenia`) VALUES
(1, 'Promocja weselna', 'Zniżka 15% na oprawę muzyczną wesel.', 15, '2024-05-01', '2024-06-30'),
(2, 'Lato z DJ-em', 'Zniżka 10% na wszystkie usługi w lipcu.', 10, '2024-07-01', '2024-07-31');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rezerwacja`
--

CREATE TABLE `rezerwacja` (
  `id_rezerwacji` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `termin` date DEFAULT NULL,
  `id_uslugi` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uprawnienia`
--

CREATE TABLE `uprawnienia` (
  `id_uprawnienia` int(11) NOT NULL,
  `nazwa_uprawnienia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uprawnienia`
--

INSERT INTO `uprawnienia` (`id_uprawnienia`, `nazwa_uprawnienia`) VALUES
(1, 'Administrator'),
(2, 'Moderator'),
(3, 'Użytkownik');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id_user` int(11) NOT NULL,
  `login` varchar(255) DEFAULT NULL,
  `haslo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `imie` varchar(255) DEFAULT NULL,
  `nazwisko` varchar(255) DEFAULT NULL,
  `miasto` varchar(255) DEFAULT NULL,
  `ulica` varchar(255) DEFAULT NULL,
  `nr_domu` int(11) DEFAULT NULL,
  `nr_mieszkania` int(11) DEFAULT NULL,
  `nr_telefonu` varchar(255) DEFAULT NULL,
  `id_uprawnienia` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id_user`, `login`, `haslo`, `email`, `imie`, `nazwisko`, `miasto`, `ulica`, `nr_domu`, `nr_mieszkania`, `nr_telefonu`, `id_uprawnienia`) VALUES
(1, 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'admin@example.com', 'Admin', 'Admin', 'Warszawa', 'Adminowa', 1, 0, '123456789', 1),
(3, 'example', '50d858e0985ecc7f60418aaf0cc5ab587f42c2570a884095a9e8ccacd0f6545c', 'example@example.com', 'example', 'example', 'example', 'example', 1, 0, '123456798', 3);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id_faq`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeksy dla tabeli `oferta_uslug`
--
ALTER TABLE `oferta_uslug`
  ADD PRIMARY KEY (`id_uslugi`),
  ADD KEY `id_promocji` (`id_promocji`);

--
-- Indeksy dla tabeli `opinie_klientow`
--
ALTER TABLE `opinie_klientow`
  ADD PRIMARY KEY (`id_opinii`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeksy dla tabeli `playlisty`
--
ALTER TABLE `playlisty`
  ADD PRIMARY KEY (`id_playlista`),
  ADD KEY `id_rezerwacji` (`id_rezerwacji`);

--
-- Indeksy dla tabeli `promocje`
--
ALTER TABLE `promocje`
  ADD PRIMARY KEY (`id_promocji`);

--
-- Indeksy dla tabeli `rezerwacja`
--
ALTER TABLE `rezerwacja`
  ADD PRIMARY KEY (`id_rezerwacji`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_uslugi` (`id_uslugi`);

--
-- Indeksy dla tabeli `uprawnienia`
--
ALTER TABLE `uprawnienia`
  ADD PRIMARY KEY (`id_uprawnienia`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_uprawnienia` (`id_uprawnienia`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faq`
--
ALTER TABLE `faq`
  ADD CONSTRAINT `faq_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `uzytkownicy` (`id_user`);

--
-- Constraints for table `oferta_uslug`
--
ALTER TABLE `oferta_uslug`
  ADD CONSTRAINT `oferta_uslug_ibfk_1` FOREIGN KEY (`id_promocji`) REFERENCES `promocje` (`id_promocji`);

--
-- Constraints for table `opinie_klientow`
--
ALTER TABLE `opinie_klientow`
  ADD CONSTRAINT `opinie_klientow_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `uzytkownicy` (`id_user`);

--
-- Constraints for table `playlisty`
--
ALTER TABLE `playlisty`
  ADD CONSTRAINT `playlisty_ibfk_1` FOREIGN KEY (`id_rezerwacji`) REFERENCES `rezerwacja` (`id_rezerwacji`);

--
-- Constraints for table `rezerwacja`
--
ALTER TABLE `rezerwacja`
  ADD CONSTRAINT `rezerwacja_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `uzytkownicy` (`id_user`),
  ADD CONSTRAINT `rezerwacja_ibfk_2` FOREIGN KEY (`id_uslugi`) REFERENCES `oferta_uslug` (`id_uslugi`);

--
-- Constraints for table `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD CONSTRAINT `uzytkownicy_ibfk_1` FOREIGN KEY (`id_uprawnienia`) REFERENCES `uprawnienia` (`id_uprawnienia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
