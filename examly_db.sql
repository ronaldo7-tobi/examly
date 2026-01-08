-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 06, 2026 at 02:18 PM
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
-- Database: `examly`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `content`, `is_correct`) VALUES
(1, 1, '<a>', 1),
(2, 1, '<link>', 0),
(3, 1, '<href>', 0),
(4, 1, '<url>', 0),
(5, 2, 'color: red;', 1),
(6, 2, 'background-color: red;', 0),
(7, 2, 'text-color: red;', 0),
(8, 2, 'font-color: red;', 0),
(9, 3, 'let', 1),
(10, 3, 'varr', 0),
(11, 3, 'define', 0),
(12, 3, 'declare', 0),
(13, 4, '<?php', 1),
(14, 4, '<php>', 0),
(15, 4, '<?=', 0),
(16, 4, '<script>', 0),
(17, 5, 'SELECT', 1),
(18, 5, 'INSERT', 0),
(19, 5, 'UPDATE', 0),
(20, 5, 'DELETE', 0),
(21, 6, 'INSERT INTO', 1),
(22, 6, 'SELECT *', 0),
(23, 6, 'CREATE TABLE', 0),
(24, 6, 'DROP TABLE', 0),
(25, 7, 'mail()', 1),
(26, 7, 'send_mail()', 0),
(27, 7, 'send()', 0),
(28, 7, 'mailSend()', 0),
(29, 8, '`onclick`', 1),
(30, 8, 'onhover', 0),
(31, 8, 'onload', 0),
(32, 8, 'onpress', 0),
(33, 9, 'background-color: blue;', 1),
(34, 9, 'color: blue;', 0),
(35, 9, 'bg: blue;', 0),
(36, 9, 'background: #00f;', 0),
(37, 10, 'To komputer, który obsługuje żądania `HTTP` i zwraca strony WWW.', 1),
(38, 10, 'To edytor kodu HTML.', 0),
(39, 10, 'To protokół zabezpieczający bazę danych.', 0),
(40, 10, 'To narzędzie do tworzenia grafik.', 0),
(41, 11, '<h1>', 1),
(42, 11, '<header>', 0),
(43, 11, '<title>', 0),
(44, 11, '<h6>', 0),
(45, 12, '<ul>', 1),
(46, 12, '<ol>', 0),
(47, 12, '<list>', 0),
(48, 12, '<nl>', 0),
(49, 13, 'src', 1),
(50, 13, 'href', 0),
(51, 13, 'link', 0),
(52, 13, 'alt', 0),
(53, 14, '', 1),
(54, 14, '// komentarz', 0),
(55, 14, '/* komentarz */', 0),
(56, 14, '# komentarz', 0),
(57, 15, 'font-size', 1),
(58, 15, 'text-size', 0),
(59, 15, 'font-style', 0),
(60, 15, 'size', 0),
(61, 16, 'text-align: center;', 1),
(62, 16, 'align: center;', 0),
(63, 16, 'margin: auto;', 0),
(64, 16, 'center-text: true;', 0),
(65, 17, 'p', 1),
(66, 17, '.p', 0),
(67, 17, '#p', 0),
(68, 17, 'all.p', 0),
(69, 18, 'Definiuje zewnętrzny odstęp (margines)', 1),
(70, 18, 'Definiuje wewnętrzny odstęp (padding)', 0),
(71, 18, 'Definiuje kolor obramowania', 0),
(72, 18, 'Definiuje pozycję elementu', 0),
(73, 19, 'alert()', 1),
(74, 19, 'prompt()', 0),
(75, 19, 'confirm()', 0),
(76, 19, 'console.log()', 0),
(77, 20, 'document.getElementById(\"demo\")', 1),
(78, 20, 'document.querySelector(\"#demo\")', 0),
(79, 20, 'document.getElementByName(\"demo\")', 0),
(80, 20, 'getElement(\"demo\")', 0),
(81, 21, '===', 1),
(82, 21, '==', 0),
(83, 21, '=', 0),
(84, 21, '!=', 0),
(85, 22, '//', 1),
(86, 22, '/*', 0),
(87, 22, '#', 0),
(89, 23, '$_GET', 1),
(90, 23, '$_POST', 0),
(91, 23, '$_SESSION', 0),
(92, 23, '$_REQUEST', 0),
(93, 24, '.', 1),
(94, 24, '+', 0),
(95, 24, '&', 0),
(96, 24, ',', 0),
(97, 25, 'session_start()', 1),
(98, 25, 'start_session()', 0),
(99, 25, 'session.begin()', 0),
(100, 25, 'init_session()', 0),
(101, 26, '10', 1),
(102, 26, '55 kotów', 0),
(103, 26, 'Błąd (Error)', 0),
(104, 26, '5', 0),
(105, 27, 'DROP TABLE', 1),
(106, 27, 'DELETE TABLE', 0),
(107, 27, 'REMOVE TABLE', 0),
(108, 27, 'TRUNCATE TABLE', 0),
(109, 28, 'ORDER BY', 1),
(110, 28, 'SORT BY', 0),
(111, 28, 'GROUP BY', 0),
(112, 28, 'ARRANGE BY', 0),
(113, 29, 'Do filtrowania rekordów', 1),
(114, 29, 'Do łączenia tabel', 0),
(115, 29, 'Do usuwania rekordów', 0),
(116, 29, 'Do aktualizacji rekordów', 0),
(117, 30, 'Modyfikuje istniejące dane', 1),
(118, 30, 'Dodaje nowe dane', 0),
(119, 30, 'Usuwa dane', 0),
(120, 30, 'Wybiera dane', 0),
(121, 31, 'Protokół transferu plików', 1),
(122, 31, 'System zarządzania bazą danych', 0),
(123, 31, 'Język programowania', 0),
(124, 31, 'Certyfikat bezpieczeństwa', 0),
(125, 32, 'Unikalny adres strony WWW', 1),
(126, 32, 'Adres IP serwera', 0),
(127, 32, 'Nazwa firmy hostingowej', 0),
(128, 32, 'Protokół internetowy', 0),
(129, 33, 'System Zarządzania Treścią', 1),
(130, 33, 'Centralny Moduł Serwera', 0),
(131, 33, 'Certyfikat Master Secure', 0),
(132, 33, 'Złożony System Marketingowy', 0),
(133, 34, 'Dopasowanie strony do rozmiaru ekranu', 1),
(134, 34, 'Szybkie odpowiadanie serwera', 0),
(135, 34, 'Technika animacji w CSS', 0),
(136, 34, 'System komentarzy na stronie', 0),
(137, 35, '<video>', 1),
(138, 35, '<movie>', 0),
(139, 35, '<embed>', 0),
(140, 35, '<media>', 0),
(141, 36, 'display: none;', 1),
(142, 36, 'visibility: hidden;', 0),
(143, 36, 'opacity: 0;', 0),
(144, 36, 'hidden: true;', 0),
(145, 37, 'const', 1),
(146, 37, 'let', 0),
(147, 37, 'var', 0),
(148, 37, 'static', 0),
(149, 38, 'isset()', 1),
(150, 38, 'exists()', 0),
(151, 38, 'has()', 0),
(152, 38, 'defined()', 0),
(153, 39, 'Wszystkie kolumny', 1),
(154, 39, 'Pierwszą kolumnę', 0),
(155, 39, 'Kolumnę `id`', 0),
(156, 39, 'Nic, to błąd składni', 0),
(157, 40, 'Sprawdzenie zgodności kodu ze standardami', 1),
(158, 40, 'Testowanie szybkości ładowania strony', 0),
(159, 40, 'Sprawdzanie, czy strona jest bezpieczna', 0),
(160, 40, 'Publikacja strony w internecie', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `course_user`
--

CREATE TABLE `course_user` (
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purchased_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `exam_types`
--

CREATE TABLE `exam_types` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `exam_types`
--

INSERT INTO `exam_types` (`id`, `code`, `full_name`) VALUES
(1, 'INF.03', 'Projektowanie i administrowanie stronami i systemami internetowymi');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `exam_type_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `explanation` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `topic_id`, `exam_type_id`, `content`, `explanation`, `image_path`) VALUES
(1, 1, 1, 'Który znacznik HTML służy do tworzenia odnośnika (linku)?', 'Znacznik <a> umożliwia tworzenie hiperłączy.', NULL),
(2, 2, 1, 'Który zapis CSS ustawi kolor tekstu na czerwony?', 'Właściwość color odpowiada za kolor tekstu.', NULL),
(3, 3, 1, 'Jakim słowem kluczowym zadeklarujesz zmienną w JavaScript?', 'Słowo kluczowe let tworzy zmienną o zasięgu blokowym.', 'js1.png'),
(4, 4, 1, 'Jak zaczyna się blok kodu PHP?', 'Każdy plik PHP zaczyna się od <?php.', 'php1.png'),
(5, 5, 1, 'Które polecenie SQL wybiera dane z tabeli?', 'SELECT służy do pobierania danych.', NULL),
(6, 5, 1, 'Które polecenie dodaje dane do tabeli?', 'INSERT INTO dodaje nowe rekordy do tabeli.', NULL),
(7, 4, 1, 'Która funkcja PHP służy do wysłania wiadomości e-mail?', 'mail() umożliwia wysyłanie wiadomości e-mail.', NULL),
(8, 3, 1, 'Jakie zdarzenie JavaScript uruchamia się po kliknięciu przycisku?', 'Zdarzenie onclick reaguje na kliknięcie.', NULL),
(9, 2, 1, 'Jak ustawić tło strony na kolor niebieski?', 'background-color: blue ustawia tło na niebieskie.', NULL),
(10, 6, 1, 'Czym jest serwer HTTP?', 'Serwer HTTP obsługuje żądania HTTP i zwraca odpowiedzi.', NULL),
(11, 1, 1, 'Który znacznik HTML definiuje nagłówek najwyższego poziomu?', 'Znacznik <h1> jest używany dla najważniejszego nagłówka na stronie.', NULL),
(12, 1, 1, 'Jakiego znacznika użyjesz do stworzenia listy   nienumerowanej?', 'Znacznik <ul> (unordered list) tworzy listę nienumerowaną, a <li> jej poszczególne elementy.', NULL),
(13, 1, 1, 'Który atrybut jest wymagany w znaczniku <img>?', 'Atrybut `src` (source) jest niezbędny, aby przeglądarka wiedziała, który obrazek wyświetlić.', NULL),
(14, 1, 1, 'Jak wstawić komentarz w kodzie HTML?', 'Komentarze w HTML umieszcza się pomiędzy ``.', NULL),
(15, 2, 1, 'Która właściwość CSS służy do zmiany rozmiaru czcionki?', '`font-size` pozwala na zdefiniowanie wielkości tekstu, np. w pikselach (px) lub punktach (pt).', NULL),
(16, 2, 1, 'Jak wyśrodkować tekst wewnątrz elementu za pomocą CSS?', 'Właściwość `text-align` z wartością `center` służy do centrowania tekstu.', NULL),
(17, 2, 1, 'Który selektor CSS odnosi się do wszystkich elementów <p>?', 'Selektor `p` odnosi się do wszystkich paragrafów na stronie.', NULL),
(18, 2, 1, 'Jaka jest rola właściwości `margin` w CSS?', '`margin` definiuje zewnętrzny odstęp (margines) wokół elementu.', NULL),
(19, 3, 1, 'Która metoda JavaScript służy do wyświetlenia komunikatu w oknie dialogowym?', '`alert()` wyświetla prosty komunikat z przyciskiem OK.', NULL),
(20, 3, 1, 'Jak w JavaScript odwołać się do elementu HTML o id=\"demo\"?', '`document.getElementById(\"demo\")` jest najczęstszym sposobem na znalezienie elementu po jego unikalnym ID.', NULL),
(21, 3, 1, 'Który operator w JavaScript służy do ścisłego porównania (wartość i typ)?', 'Operator `===` sprawdza zarówno wartość, jak i typ danych, w przeciwieństwie do `==`.', NULL),
(22, 3, 1, 'Jak zapisać komentarz jednoliniowy w JavaScript?', 'Komentarz jednoliniowy w JS rozpoczyna się od `//`.', NULL),
(23, 4, 1, 'Która zmienna superglobalna w PHP przechowuje dane wysłane metodą GET?', 'Tablica `$_GET` zawiera wszystkie parametry przekazane w adresie URL.', NULL),
(24, 4, 1, 'Jak połączyć dwa ciągi znaków (stringi) w PHP?', 'Operator kropki `.` służy do konkatenacji, czyli łączenia stringów.', NULL),
(25, 4, 1, 'Która funkcja w PHP służy do rozpoczęcia sesji?', '`session_start()` musi być wywołana na początku skryptu, aby móc korzystać ze zmiennych sesyjnych.', NULL),
(26, 4, 1, 'Co wyświetli instrukcja `echo 5 + \"5 kotów\";` w PHP?', 'PHP spróbuje przekonwertować string \"5 kotów\" na liczbę, co da w wyniku 5. Wynikiem działania będzie 10.', NULL),
(27, 5, 1, 'Jakie polecenie SQL służy do usunięcia tabeli z bazy danych?', '`DROP TABLE nazwa_tabeli` trwale usuwa tabelę wraz z jej strukturą i danymi.', NULL),
(28, 5, 1, 'Która klauzula SQL służy do sortowania wyników zapytania?', '`ORDER BY` pozwala sortować wyniki rosnąco (`ASC`) lub malejąco (`DESC`).', NULL),
(29, 5, 1, 'Jakie jest zastosowanie klauzuli `WHERE` w zapytaniu SQL?', '`WHERE` służy do filtrowania rekordów i zwracania tylko tych, które spełniają określony warunek.', NULL),
(30, 5, 1, 'Co robi polecenie `UPDATE` w SQL?', '`UPDATE` służy do modyfikacji istniejących rekordów w tabeli.', NULL),
(31, 6, 1, 'Co to jest FTP?', 'FTP (File Transfer Protocol) to protokół używany do przesyłania plików między komputerami w sieci.', NULL),
(32, 6, 1, 'Co to jest domena internetowa?', 'Domena to unikalny, łatwy do zapamiętania adres, który prowadzi do określonej strony internetowej (np. examly.pl).', NULL),
(33, 6, 1, 'Co oznacza skrót CMS?', 'CMS (Content Management System), czyli System Zarządzania Treścią, to oprogramowanie do łatwego tworzenia i zarządzania treścią na stronie WWW, np. WordPress.', NULL),
(34, 6, 1, 'Co to jest responsywność (RWD) w kontekście stron WWW?', 'RWD (Responsive Web Design) to technika projektowania stron, które automatycznie dostosowują swój wygląd do rozmiaru ekranu urządzenia.', NULL),
(35, 1, 1, 'Który znacznik HTML służy do osadzania filmów wideo?', 'Znacznik `<video>` jest standardem HTML5 do umieszczania wideo na stronie.', NULL),
(36, 2, 1, 'Która właściwość CSS sprawia, że element staje się niewidoczny?', '`display: none;` całkowicie usuwa element z układu strony, podczas gdy `visibility: hidden;` tylko go ukrywa, pozostawiając puste miejsce.', NULL),
(37, 3, 1, 'Jak zadeklarować stałą w JavaScript?', 'Słowo kluczowe `const` tworzy stałą, której wartości nie można później zmienić.', NULL),
(38, 4, 1, 'Która funkcja w PHP sprawdza, czy zmienna została ustawiona?', '`isset()` zwraca `true`, jeśli zmienna istnieje i nie ma wartości `null`.', NULL),
(39, 5, 1, 'Co oznacza `*` w zapytaniu `SELECT * FROM users`?', 'Gwiazdka `*` jest symbolem wieloznacznym oznaczającym \"wszystkie kolumny\".', NULL),
(40, 6, 1, 'Co to jest \"walidacja\" kodu HTML?', 'Walidacja to proces sprawdzania, czy kod strony jest napisany zgodnie ze standardami W3C, co zapewnia lepszą kompatybilność z przeglądarkami.', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `topics`
--

CREATE TABLE `topics` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `topics`
--

INSERT INTO `topics` (`id`, `name`) VALUES
(2, 'CSS'),
(1, 'HTML'),
(3, 'JS'),
(4, 'PHP'),
(5, 'SQL'),
(6, 'Teoria');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` char(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `google_id`, `first_name`, `last_name`, `email`, `password_hash`, `is_verified`, `created_at`, `updated_at`, `role`) VALUES
(5, NULL, 'Tobiasz', 'Szerszeń', 'tobiaszszerszen@gmail.com', '$2y$10$m7Um1SlcQ3hw2Q2L.2gNbu8jD0HqM1lmoRIb0dwnpGeweubXCyxg6', 1, '2025-09-01 20:13:45', '2025-09-01 20:13:53', 'user');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_exams`
--

CREATE TABLE `user_exams` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_taken` datetime NOT NULL DEFAULT current_timestamp(),
  `is_full_exam` tinyint(1) NOT NULL DEFAULT 0,
  `correct_answers` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL DEFAULT 40,
  `score_percent` decimal(5,2) NOT NULL,
  `duration_seconds` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_exam_topics`
--

CREATE TABLE `user_exam_topics` (
  `user_exam_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_progress`
--

CREATE TABLE `user_progress` (
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `correct_attempts` int(11) DEFAULT 0,
  `wrong_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_result` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` enum('email_verify','password_reset','email_change') NOT NULL,
  `token_data` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `user_tokens`
--

INSERT INTO `user_tokens` (`id`, `user_id`, `token`, `type`, `token_data`, `expires_at`, `created_at`) VALUES
(20, 5, '4fde4564765d8f2a896f2cd879748ccb50007731696c5d34cad14ef1cb65f184', 'email_verify', NULL, '2025-09-01 23:13:45', '2025-09-01 22:13:45');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indeksy dla tabeli `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `course_user`
--
ALTER TABLE `course_user`
  ADD PRIMARY KEY (`course_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `exam_types`
--
ALTER TABLE `exam_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeksy dla tabeli `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `exam_type_id` (`exam_type_id`);

--
-- Indeksy dla tabeli `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_google_id` (`google_id`);

--
-- Indeksy dla tabeli `user_exams`
--
ALTER TABLE `user_exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `user_exam_topics`
--
ALTER TABLE `user_exam_topics`
  ADD PRIMARY KEY (`user_exam_id`,`topic_id`),
  ADD KEY `topic_id` (`topic_id`);

--
-- Indeksy dla tabeli `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`user_id`,`question_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indeksy dla tabeli `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_types`
--
ALTER TABLE `exam_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `topics`
--
ALTER TABLE `topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_exams`
--
ALTER TABLE `user_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_user`
--
ALTER TABLE `course_user`
  ADD CONSTRAINT `course_user_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`exam_type_id`) REFERENCES `exam_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_exams`
--
ALTER TABLE `user_exams`
  ADD CONSTRAINT `user_exams_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_exam_topics`
--
ALTER TABLE `user_exam_topics`
  ADD CONSTRAINT `user_exam_topics_ibfk_1` FOREIGN KEY (`user_exam_id`) REFERENCES `user_exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_exam_topics_ibfk_2` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
