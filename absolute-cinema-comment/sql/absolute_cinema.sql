-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- create time: 25-05-01 00:40
-- server version: 10.4.28-MariaDB
-- PHP version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- create table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `showtime_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
);

--
-- insert value into `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `showtime_id`, `seat_id`, `booking_date`) VALUES
(1, 1, 1, 1, '2023-06-14 08:30:00'),
(2, 1, 1, 2, '2023-06-14 08:30:00'),
(3, 2, 3, 3, '2023-06-14 09:45:00'),
(4, 2, 3, 3, '2023-06-14 09:45:00'),
(5, 2, 3, 4, '2023-06-14 09:45:00'),
(6, 3, 5, 5, '2023-06-14 11:20:00'),
(7, 4, 7, 6, '2023-06-14 12:10:00'),
(8, 4, 7, 7, '2023-06-14 12:10:00'),
(9, 6, 9, 8, '2023-06-14 13:30:00'),
(10, 6, 9, 9, '2023-06-14 13:30:00'),
(11, 6, 9, 1, '2023-06-14 13:30:00'),
(12, 6, 9, 4, '2023-06-14 13:30:00'),
(13, 1, 7, 2, '2025-04-30 21:18:58'),
(14, 1, 1, 2, '2025-04-30 21:19:07');

-- --------------------------------------------------------

--
-- create table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `synopsis` text DEFAULT NULL,
  `status` enum('now_showing','coming_soon') DEFAULT 'now_showing',
  `release_date` date DEFAULT NULL
);

--
-- insert value into `movies`
--

INSERT INTO `movies` (`id`, `title`, `poster`, `duration`, `synopsis`, `status`, `release_date`) VALUES
(1, 'Avatar: The Way of Water', 'avatar.jpg', 192, 'Jake Sully lives with his newfound family formed on the planet of Pandora. Once a familiar threat returns to finish what was previously started, Jake must work with Neytiri and the army of the Na\'vi race to protect their planet.', 'now_showing', '2022-12-16'),
(2, 'John Wick: Chapter 4', 'johnwick4.jpg', 169, 'John Wick uncovers a path to defeating The High Table. But before he can earn his freedom, Wick must face off against a new enemy with powerful alliances across the globe and forces that turn old friends into foes.', 'now_showing', '2023-03-24'),
(3, 'The Super Mario Bros. Movie', 'mario.jpg', 92, 'A Brooklyn plumber named Mario travels through the Mushroom Kingdom with a princess named Peach and an anthropomorphic mushroom named Toad to find Mario\'s brother, Luigi, and to save the world from a ruthless fire-breathing Koopa named Bowser.', 'now_showing', '2023-04-05'),
(4, 'Guardians of the Galaxy Vol. 3', 'gotg3.jpg', 150, 'Still reeling from the loss of Gamora, Peter Quill must rally his team to defend the universe and protect one of their own. If the mission is not completely successful, it could possibly lead to the end of the Guardians as we know them.', 'now_showing', '2023-05-05'),
(5, 'Fast X', 'fastx.jpg', 141, 'Dom Toretto and his family are targeted by the vengeful son of drug kingpin Hernan Reyes.', 'now_showing', '2023-05-19'),
(6, 'Spider-Man: Across the Spider-Verse', 'spiderverse.jpg', 140, 'Miles Morales catapults across the multiverse, where he encounters a team of Spider-People charged with protecting its very existence. When the heroes clash on how to handle a new threat, Miles must redefine what it means to be a hero.', 'now_showing', '2023-06-02'),
(7, 'Oppenheimer', 'oppenheimer.jpg', 180, 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.', 'coming_soon', '2023-07-21'),
(8, 'Barbie', 'barbie.jpg', 114, 'Barbie suffers a crisis that leads her to question her world and her existence.', 'coming_soon', '2023-07-21'),
(9, 'Mission: Impossible - Dead Reckoning Part One', 'missionimpossible.jpg', 163, 'Ethan Hunt and his IMF team must track down a dangerous weapon before it falls into the wrong hands.', 'coming_soon', '2023-07-14');

-- --------------------------------------------------------
--
-- create table `genres`
--
CREATE TABLE `genres` (
    `id` INT(11) NOT NULL,
    name VARCHAR(50) NOT NULL
);

--
-- insert value into `genres`
--
INSERT INTO `genres` (`id`, `name`) VALUES
(1, 'Drama'),
(2, 'Action'),
(3, 'Animation'),
(4, 'Family'),
(5, 'Comedy'),
(6, 'Fantasy'),
(7, 'Sci-Fi'),
(8, 'Thriller'),
(9, 'Horror'),
(10, 'Documentary'),
(11, 'History');

-- --------------------------------------------------------
-- create table `movie_genres`
--
-- Junction table: movie_genres
CREATE TABLE `movie_genres` (
    `movie_id` INT(11) NOT NULL,
    `genre_id` INT(11) NOT NULL
);

--
-- insert value into `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(1, 2),
(1, 6),
(2, 2),
(3, 3),
(3, 4),
(4, 2),
(4, 6),
(5, 2),
(6, 2),
(6, 3),
(7, 1),
(7, 11),
(8, 6),
(9, 2);
-- -------------------------------------------------------
--
-- create table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Credit Card', 'Debit Card', 'Cash') NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed') DEFAULT 'pending'
);

-- --------------------------------------------------------

--
-- create table `seats`
--

CREATE TABLE `seats` (
  `id` int(11) NOT NULL,
  `seat_number` varchar(10) NOT NULL
);

-- insert value into table seats
--

INSERT INTO `seats` (`id`, `seat_number`) VALUES
(1, 'A1'),
(2, 'A2'),
(3, 'A3'),
(4, 'A4'),
(5, 'A5'),
(6, 'B1'),
(7, 'B2'),
(8, 'B3'),
(9, 'B4'),
(10, 'B5'),
(11, 'C1'),
(12, 'C2'),
(13, 'C3'),
(14, 'C4'),
(15, 'C5'),
(16, 'D1'),
(17, 'D2'),
(18, 'D3'),
(19, 'D4'),
(20, 'D5'),
(21, 'E1'),
(22, 'E2'),
(23, 'E3'),
(24, 'E4'),
(25, 'E5');


-- --------------------------------------------------------

--
-- create table `showtimes`
--

CREATE TABLE `showtimes` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) DEFAULT NULL,
  `show_date` date NOT NULL,
  `show_time` time NOT NULL,
  `theater_number` int(11) DEFAULT NULL
);

--
-- insert value into `showtimes`
--

INSERT INTO `showtimes` (`id`, `movie_id`, `show_date`, `show_time`, `theater_number`) VALUES
(1, 1, '2023-06-15', '10:30:00', 1),
(2, 1, '2023-06-15', '18:30:00', 1),
(3, 3, '2023-06-16', '10:30:00', 1),
(4, 3, '2023-06-16', '18:30:00', 1),
(5, 2, '2023-06-17', '10:30:00', 1),
(6, 2, '2023-06-17', '18:30:00', 1),
(7, 2, '2023-06-15', '10:30:00', 2),
(8, 2, '2023-06-15', '18:30:00', 2),
(9, 1, '2023-06-16', '10:30:00', 2),
(10, 1, '2023-06-16', '18:30:00', 2),
(11, 3, '2023-06-17', '10:30:00', 2),
(12, 3, '2023-06-17', '18:30:00', 2),
(13, 3, '2023-06-15', '18:30:00', 3),
(14, 2, '2023-06-16', '10:30:00', 3),
(15, 2, '2023-06-16', '18:30:00', 3),
(16, 1, '2023-06-17', '10:30:00', 3),
(17, 1, '2023-06-17', '18:30:00', 3),
(18, 4, '2023-06-15', '10:30:00', 4),
(19, 5, '2023-06-16', '18:30:00', 4),
(20, 4, '2023-06-17', '10:30:00', 4),
(21, 4, '2023-06-17', '18:30:00', 4),
(22, 5, '2023-06-15', '18:30:00', 5),
(23, 4, '2023-06-16', '10:30:00', 5),
(24, 5, '2023-06-17', '18:30:00', 5),
(25, 6, '2023-06-15', '10:30:00', 6),
(26, 6, '2023-06-16', '18:30:00', 6),
(27, 6, '2023-06-17', '10:30:00', 6);

-- --------------------------------------------------------

--
-- create table `seat_status`
--

CREATE TABLE `seat_status`(
  `seat_id` int(11) NOT NULL,
  `showtime_id` int(11) NOT NULL,
  `status` enum('available','reserved','selected') DEFAULT 'available'
);

-- -------------------------------------------------------

--
-- insert value to `seat_status`
--
INSERT INTO seat_status (`seat_id`, `showtime_id`, `status`) VALUES
(1, 1, 'selected'),
(1, 2, 'selected'),
(1, 3, 'selected'),
(1, 4, 'reserved'),
(1, 5, 'available'),
(1, 6, 'reserved'),
(1, 7, 'selected'),
(1, 8, 'reserved'),
(1, 9, 'available'),
(1, 10, 'reserved'),
(1, 11, 'selected'),
(1, 12, 'reserved'),
(1, 13, 'selected'),
(1, 14, 'reserved'),
(1, 15, 'available'),
(1, 16, 'reserved'),
(1, 17, 'reserved'),
(1, 18, 'reserved'),
(1, 19, 'selected'),
(1, 20, 'reserved'),
(1, 21, 'selected'),
(1, 22, 'reserved'),
(1, 23, 'available'),
(1, 24, 'selected'),
(1, 25, 'selected'),
(1, 26, 'available'),
(1, 27, 'reserved'),
(2, 1, 'reserved'),
(2, 2, 'selected'),
(2, 3, 'selected'),
(2, 4, 'available'),
(2, 5, 'selected'),
(2, 6, 'selected'),
(2, 7, 'reserved'),
(2, 8, 'reserved'),
(2, 9, 'reserved'),
(2, 10, 'available'),
(2, 11, 'selected'),
(2, 12, 'selected'),
(2, 13, 'reserved'),
(2, 14, 'reserved'),
(2, 15, 'selected'),
(2, 16, 'selected'),
(2, 17, 'selected'),
(2, 18, 'selected'),
(2, 19, 'available'),
(2, 20, 'available'),
(2, 21, 'reserved'),
(2, 22, 'selected'),
(2, 23, 'available'),
(2, 24, 'reserved'),
(2, 25, 'selected'),
(2, 26, 'selected'),
(2, 27, 'selected'),
(3, 1, 'selected'),
(3, 2, 'selected'),
(3, 3, 'available'),
(3, 4, 'available'),
(3, 5, 'selected'),
(3, 6, 'available'),
(3, 7, 'reserved'),
(3, 8, 'available'),
(3, 9, 'available'),
(3, 10, 'available'),
(3, 11, 'reserved'),
(3, 12, 'available'),
(3, 13, 'selected'),
(3, 14, 'selected'),
(3, 15, 'available'),
(3, 16, 'reserved'),
(3, 17, 'reserved'),
(3, 18, 'reserved'),
(3, 19, 'reserved'),
(3, 20, 'available'),
(3, 21, 'reserved'),
(3, 22, 'selected'),
(3, 23, 'reserved'),
(3, 24, 'available'),
(3, 25, 'selected'),
(3, 26, 'reserved'),
(3, 27, 'selected'),
(4, 1, 'reserved'),
(4, 2, 'available'),
(4, 3, 'available'),
(4, 4, 'selected'),
(4, 5, 'reserved'),
(4, 6, 'selected'),
(4, 7, 'available'),
(4, 8, 'reserved'),
(4, 9, 'selected'),
(4, 10, 'reserved'),
(4, 11, 'available'),
(4, 12, 'available'),
(4, 13, 'available'),
(4, 14, 'available'),
(4, 15, 'reserved'),
(4, 16, 'selected'),
(4, 17, 'selected'),
(4, 18, 'selected'),
(4, 19, 'reserved'),
(4, 20, 'reserved'),
(4, 21, 'available'),
(4, 22, 'reserved'),
(4, 23, 'selected'),
(4, 24, 'available'),
(4, 25, 'reserved'),
(4, 26, 'reserved'),
(4, 27, 'selected'),
(5, 1, 'selected'),
(5, 2, 'selected'),
(5, 3, 'reserved'),
(5, 4, 'selected'),
(5, 5, 'reserved'),
(5, 6, 'reserved'),
(5, 7, 'reserved'),
(5, 8, 'reserved'),
(5, 9, 'selected'),
(5, 10, 'reserved'),
(5, 11, 'selected'),
(5, 12, 'reserved'),
(5, 13, 'available'),
(5, 14, 'available'),
(5, 15, 'reserved'),
(5, 16, 'selected'),
(5, 17, 'available'),
(5, 18, 'selected'),
(5, 19, 'available'),
(5, 20, 'available'),
(5, 21, 'available'),
(5, 22, 'reserved'),
(5, 23, 'available'),
(5, 24, 'selected'),
(5, 25, 'selected'),
(5, 26, 'available'),
(5, 27, 'reserved'),
(6, 1, 'reserved'),
(6, 2, 'selected'),
(6, 3, 'available'),
(6, 4, 'reserved'),
(6, 5, 'available'),
(6, 6, 'reserved'),
(6, 7, 'available'),
(6, 8, 'available'),
(6, 9, 'reserved'),
(6, 10, 'available'),
(6, 11, 'selected'),
(6, 12, 'reserved'),
(6, 13, 'selected'),
(6, 14, 'selected'),
(6, 15, 'selected'),
(6, 16, 'reserved'),
(6, 17, 'reserved'),
(6, 18, 'reserved'),
(6, 19, 'available'),
(6, 20, 'selected'),
(6, 21, 'selected'),
(6, 22, 'available'),
(6, 23, 'selected'),
(6, 24, 'available'),
(6, 25, 'selected'),
(6, 26, 'selected'),
(6, 27, 'reserved'),
(7, 1, 'available'),
(7, 2, 'available'),
(7, 3, 'available'),
(7, 4, 'selected'),
(7, 5, 'selected'),
(7, 6, 'selected'),
(7, 7, 'selected'),
(7, 8, 'selected'),
(7, 9, 'available'),
(7, 10, 'reserved'),
(7, 11, 'available'),
(7, 12, 'available'),
(7, 13, 'selected'),
(7, 14, 'selected'),
(7, 15, 'selected'),
(7, 16, 'reserved'),
(7, 17, 'reserved'),
(7, 18, 'available'),
(7, 19, 'selected'),
(7, 20, 'selected'),
(7, 21, 'selected'),
(7, 22, 'selected'),
(7, 23, 'available'),
(7, 24, 'selected'),
(7, 25, 'reserved'),
(7, 26, 'available'),
(7, 27, 'available'),
(8, 1, 'selected'),
(8, 2, 'selected'),
(8, 3, 'selected'),
(8, 4, 'reserved'),
(8, 5, 'reserved'),
(8, 6, 'selected'),
(8, 7, 'available'),
(8, 8, 'available'),
(8, 9, 'selected'),
(8, 10, 'selected'),
(8, 11, 'selected'),
(8, 12, 'available'),
(8, 13, 'selected'),
(8, 14, 'reserved'),
(8, 15, 'available'),
(8, 16, 'available'),
(8, 17, 'reserved'),
(8, 18, 'reserved'),
(8, 19, 'selected'),
(8, 20, 'reserved'),
(8, 21, 'selected'),
(8, 22, 'available'),
(8, 23, 'selected'),
(8, 24, 'selected'),
(8, 25, 'reserved'),
(8, 26, 'reserved'),
(8, 27, 'available'),
(9, 1, 'selected'),
(9, 2, 'reserved'),
(9, 3, 'available'),
(9, 4, 'available'),
(9, 5, 'available'),
(9, 6, 'selected'),
(9, 7, 'reserved'),
(9, 8, 'reserved'),
(9, 9, 'selected'),
(9, 10, 'reserved'),
(9, 11, 'reserved'),
(9, 12, 'reserved'),
(9, 13, 'available'),
(9, 14, 'available'),
(9, 15, 'selected'),
(9, 16, 'selected'),
(9, 17, 'selected'),
(9, 18, 'reserved'),
(9, 19, 'available'),
(9, 20, 'selected'),
(9, 21, 'reserved'),
(9, 22, 'reserved'),
(9, 23, 'available'),
(9, 24, 'selected'),
(9, 25, 'reserved'),
(9, 26, 'selected'),
(9, 27, 'selected'),
(10, 1, 'available'),
(10, 2, 'selected'),
(10, 3, 'reserved'),
(10, 4, 'available'),
(10, 5, 'selected'),
(10, 6, 'available'),
(10, 7, 'selected'),
(10, 8, 'available'),
(10, 9, 'reserved'),
(10, 10, 'reserved'),
(10, 11, 'reserved'),
(10, 12, 'selected'),
(10, 13, 'reserved'),
(10, 14, 'reserved'),
(10, 15, 'reserved'),
(10, 16, 'reserved'),
(10, 17, 'reserved'),
(10, 18, 'reserved'),
(10, 19, 'selected'),
(10, 20, 'available'),
(10, 21, 'selected'),
(10, 22, 'reserved'),
(10, 23, 'selected'),
(10, 24, 'available'),
(10, 25, 'reserved'),
(10, 26, 'selected'),
(10, 27, 'selected'),
(11, 1, 'available'),
(11, 2, 'reserved'),
(11, 3, 'available'),
(11, 4, 'available'),
(11, 5, 'selected'),
(11, 6, 'available'),
(11, 7, 'reserved'),
(11, 8, 'available'),
(11, 9, 'reserved'),
(11, 10, 'available'),
(11, 11, 'reserved'),
(11, 12, 'available'),
(11, 13, 'selected'),
(11, 14, 'available'),
(11, 15, 'reserved'),
(11, 16, 'reserved'),
(11, 17, 'available'),
(11, 18, 'reserved'),
(11, 19, 'selected'),
(11, 20, 'available'),
(11, 21, 'selected'),
(11, 22, 'reserved'),
(11, 23, 'reserved'),
(11, 24, 'selected'),
(11, 25, 'reserved'),
(11, 26, 'available'),
(11, 27, 'selected'),
(12, 1, 'selected'),
(12, 2, 'reserved'),
(12, 3, 'available'),
(12, 4, 'selected'),
(12, 5, 'selected'),
(12, 6, 'reserved'),
(12, 7, 'selected'),
(12, 8, 'reserved'),
(12, 9, 'available'),
(12, 10, 'available'),
(12, 11, 'reserved'),
(12, 12, 'available'),
(12, 13, 'selected'),
(12, 14, 'reserved'),
(12, 15, 'selected'),
(12, 16, 'available'),
(12, 17, 'available'),
(12, 18, 'reserved'),
(12, 19, 'reserved'),
(12, 20, 'available'),
(12, 21, 'reserved'),
(12, 22, 'reserved'),
(12, 23, 'available'),
(12, 24, 'reserved'),
(12, 25, 'selected'),
(12, 26, 'reserved'),
(12, 27, 'reserved'),
(13, 1, 'available'),
(13, 2, 'reserved'),
(13, 3, 'selected'),
(13, 4, 'selected'),
(13, 5, 'available'),
(13, 6, 'selected'),
(13, 7, 'reserved'),
(13, 8, 'selected'),
(13, 9, 'selected'),
(13, 10, 'selected'),
(13, 11, 'reserved'),
(13, 12, 'selected'),
(13, 13, 'reserved'),
(13, 14, 'reserved'),
(13, 15, 'available'),
(13, 16, 'reserved'),
(13, 17, 'available'),
(13, 18, 'available'),
(13, 19, 'available'),
(13, 20, 'reserved'),
(13, 21, 'selected'),
(13, 22, 'reserved'),
(13, 23, 'available'),
(13, 24, 'reserved'),
(13, 25, 'available'),
(13, 26, 'selected'),
(13, 27, 'reserved'),
(14, 1, 'reserved'),
(14, 2, 'reserved'),
(14, 3, 'reserved'),
(14, 4, 'reserved'),
(14, 5, 'available'),
(14, 6, 'selected'),
(14, 7, 'available'),
(14, 8, 'reserved'),
(14, 9, 'selected'),
(14, 10, 'available'),
(14, 11, 'reserved'),
(14, 12, 'reserved'),
(14, 13, 'reserved'),
(14, 14, 'available'),
(14, 15, 'reserved'),
(14, 16, 'selected'),
(14, 17, 'reserved'),
(14, 18, 'reserved'),
(14, 19, 'available'),
(14, 20, 'available'),
(14, 21, 'reserved'),
(14, 22, 'reserved'),
(14, 23, 'selected'),
(14, 24, 'reserved'),
(14, 25, 'selected'),
(14, 26, 'selected'),
(14, 27, 'reserved'),
(15, 1, 'selected'),
(15, 2, 'available'),
(15, 3, 'selected'),
(15, 4, 'selected'),
(15, 5, 'available'),
(15, 6, 'reserved'),
(15, 7, 'selected'),
(15, 8, 'available'),
(15, 9, 'selected'),
(15, 10, 'reserved'),
(15, 11, 'selected'),
(15, 12, 'reserved'),
(15, 13, 'selected'),
(15, 14, 'available'),
(15, 15, 'available'),
(15, 16, 'selected'),
(15, 17, 'reserved'),
(15, 18, 'available'),
(15, 19, 'reserved'),
(15, 20, 'reserved'),
(15, 21, 'selected'),
(15, 22, 'available'),
(15, 23, 'available'),
(15, 24, 'selected'),
(15, 25, 'available'),
(15, 26, 'available'),
(15, 27, 'available'),
(16, 1, 'reserved'),
(16, 2, 'selected'),
(16, 3, 'selected'),
(16, 4, 'reserved'),
(16, 5, 'available'),
(16, 6, 'available'),
(16, 7, 'available'),
(16, 8, 'reserved'),
(16, 9, 'selected'),
(16, 10, 'reserved'),
(16, 11, 'available'),
(16, 12, 'available'),
(16, 13, 'selected'),
(16, 14, 'selected'),
(16, 15, 'available'),
(16, 16, 'available'),
(16, 17, 'available'),
(16, 18, 'available'),
(16, 19, 'available'),
(16, 20, 'reserved'),
(16, 21, 'reserved'),
(16, 22, 'selected'),
(16, 23, 'selected'),
(16, 24, 'reserved'),
(16, 25, 'reserved'),
(16, 26, 'selected'),
(16, 27, 'selected'),
(17, 1, 'available'),
(17, 2, 'selected'),
(17, 3, 'reserved'),
(17, 4, 'available'),
(17, 5, 'selected'),
(17, 6, 'available'),
(17, 7, 'reserved'),
(17, 8, 'available'),
(17, 9, 'reserved'),
(17, 10, 'available'),
(17, 11, 'selected'),
(17, 12, 'reserved'),
(17, 13, 'reserved'),
(17, 14, 'selected'),
(17, 15, 'selected'),
(17, 16, 'selected'),
(17, 17, 'selected'),
(17, 18, 'available'),
(17, 19, 'available'),
(17, 20, 'available'),
(17, 21, 'selected'),
(17, 22, 'selected'),
(17, 23, 'reserved'),
(17, 24, 'selected'),
(17, 25, 'selected'),
(17, 26, 'reserved'),
(17, 27, 'reserved'),
(18, 1, 'reserved'),
(18, 2, 'selected'),
(18, 3, 'selected'),
(18, 4, 'reserved'),
(18, 5, 'available'),
(18, 6, 'reserved'),
(18, 7, 'reserved'),
(18, 8, 'selected'),
(18, 9, 'selected'),
(18, 10, 'reserved'),
(18, 11, 'available'),
(18, 12, 'selected'),
(18, 13, 'reserved'),
(18, 14, 'available'),
(18, 15, 'selected'),
(18, 16, 'available'),
(18, 17, 'reserved'),
(18, 18, 'reserved'),
(18, 19, 'selected'),
(18, 20, 'reserved'),
(18, 21, 'selected'),
(18, 22, 'available'),
(18, 23, 'available'),
(18, 24, 'available'),
(18, 25, 'available'),
(18, 26, 'available'),
(18, 27, 'reserved'),
(19, 1, 'selected'),
(19, 2, 'selected'),
(19, 3, 'available'),
(19, 4, 'selected'),
(19, 5, 'selected'),
(19, 6, 'reserved'),
(19, 7, 'reserved'),
(19, 8, 'selected'),
(19, 9, 'available'),
(19, 10, 'available'),
(19, 11, 'available'),
(19, 12, 'reserved'),
(19, 13, 'available'),
(19, 14, 'selected'),
(19, 15, 'reserved'),
(19, 16, 'reserved'),
(19, 17, 'selected'),
(19, 18, 'available'),
(19, 19, 'selected'),
(19, 20, 'selected'),
(19, 21, 'available'),
(19, 22, 'reserved'),
(19, 23, 'reserved'),
(19, 24, 'available'),
(19, 25, 'selected'),
(19, 26, 'reserved'),
(19, 27, 'reserved'),
(20, 1, 'available'),
(20, 2, 'reserved'),
(20, 3, 'available'),
(20, 4, 'reserved'),
(20, 5, 'reserved'),
(20, 6, 'reserved'),
(20, 7, 'reserved'),
(20, 8, 'selected'),
(20, 9, 'selected'),
(20, 10, 'selected'),
(20, 11, 'reserved'),
(20, 12, 'selected'),
(20, 13, 'available'),
(20, 14, 'available'),
(20, 15, 'reserved'),
(20, 16, 'selected'),
(20, 17, 'selected'),
(20, 18, 'reserved'),
(20, 19, 'reserved'),
(20, 20, 'selected'),
(20, 21, 'available'),
(20, 22, 'selected'),
(20, 23, 'available'),
(20, 24, 'selected'),
(20, 25, 'reserved'),
(20, 26, 'reserved'),
(20, 27, 'available'),
(21, 1, 'reserved'),
(21, 2, 'selected'),
(21, 3, 'reserved'),
(21, 4, 'reserved'),
(21, 5, 'reserved'),
(21, 6, 'available'),
(21, 7, 'selected'),
(21, 8, 'selected'),
(21, 9, 'selected'),
(21, 10, 'reserved'),
(21, 11, 'reserved'),
(21, 12, 'available'),
(21, 13, 'selected'),
(21, 14, 'selected'),
(21, 15, 'reserved'),
(21, 16, 'selected'),
(21, 17, 'available'),
(21, 18, 'reserved'),
(21, 19, 'reserved'),
(21, 20, 'reserved'),
(21, 21, 'reserved'),
(21, 22, 'selected'),
(21, 23, 'selected'),
(21, 24, 'available'),
(21, 25, 'available'),
(21, 26, 'selected'),
(21, 27, 'reserved'),
(22, 1, 'available'),
(22, 2, 'available'),
(22, 3, 'selected'),
(22, 4, 'reserved'),
(22, 5, 'available'),
(22, 6, 'available'),
(22, 7, 'available'),
(22, 8, 'selected'),
(22, 9, 'selected'),
(22, 10, 'available'),
(22, 11, 'selected'),
(22, 12, 'selected'),
(22, 13, 'available'),
(22, 14, 'available'),
(22, 15, 'reserved'),
(22, 16, 'selected'),
(22, 17, 'reserved'),
(22, 18, 'available'),
(22, 19, 'available'),
(22, 20, 'available'),
(22, 21, 'available'),
(22, 22, 'reserved'),
(22, 23, 'reserved'),
(22, 24, 'reserved'),
(22, 25, 'reserved'),
(22, 26, 'selected'),
(22, 27, 'available'),
(23, 1, 'reserved'),
(23, 2, 'selected'),
(23, 3, 'selected'),
(23, 4, 'available'),
(23, 5, 'reserved'),
(23, 6, 'available'),
(23, 7, 'available'),
(23, 8, 'reserved'),
(23, 9, 'available'),
(23, 10, 'available'),
(23, 11, 'reserved'),
(23, 12, 'selected'),
(23, 13, 'reserved'),
(23, 14, 'reserved'),
(23, 15, 'reserved'),
(23, 16, 'available'),
(23, 17, 'available'),
(23, 18, 'available'),
(23, 19, 'available'),
(23, 20, 'reserved'),
(23, 21, 'selected'),
(23, 22, 'selected'),
(23, 23, 'available'),
(23, 24, 'available'),
(23, 25, 'selected'),
(23, 26, 'reserved'),
(23, 27, 'reserved'),
(24, 1, 'selected'),
(24, 2, 'reserved'),
(24, 3, 'reserved'),
(24, 4, 'available'),
(24, 5, 'available'),
(24, 6, 'selected'),
(24, 7, 'reserved'),
(24, 8, 'selected'),
(24, 9, 'selected'),
(24, 10, 'selected'),
(24, 11, 'reserved'),
(24, 12, 'available'),
(24, 13, 'reserved'),
(24, 14, 'reserved'),
(24, 15, 'selected'),
(24, 16, 'available'),
(24, 17, 'available'),
(24, 18, 'reserved'),
(24, 19, 'selected'),
(24, 20, 'available'),
(24, 21, 'reserved'),
(24, 22, 'available'),
(24, 23, 'selected'),
(24, 24, 'selected'),
(24, 25, 'selected'),
(24, 26, 'selected'),
(24, 27, 'available'),
(25, 1, 'reserved'),
(25, 2, 'selected'),
(25, 3, 'reserved'),
(25, 4, 'selected'),
(25, 5, 'selected'),
(25, 6, 'available'),
(25, 7, 'reserved'),
(25, 8, 'reserved'),
(25, 9, 'reserved'),
(25, 10, 'reserved'),
(25, 11, 'reserved'),
(25, 12, 'selected'),
(25, 13, 'available'),
(25, 14, 'reserved'),
(25, 15, 'available'),
(25, 16, 'reserved'),
(25, 17, 'reserved'),
(25, 18, 'selected'),
(25, 19, 'reserved'),
(25, 20, 'selected'),
(25, 21, 'reserved'),
(25, 22, 'selected'),
(25, 23, 'selected'),
(25, 24, 'selected'),
(25, 25, 'available'),
(25, 26, 'selected'),
(25, 27, 'reserved');

-- --------------------------------------------------------

--
-- create table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','staff','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
);
--
-- insert value into `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `role`, `is_active`, `created_at`) VALUES
(1, 'Somchai Chaiboon', 'user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812345678', 'user', 1, '2023-01-01 03:00:00'),
(2, 'Suda Intanon', 'user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0823456789', 'user', 1, '2023-01-02 04:00:00'),
(3, 'Wichai Srisuk', 'user3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0834567890', 'user', 1, '2023-01-03 05:00:00'),
(4, 'Kanya Phromma', 'staff1@cinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0845678901', 'staff', 1, '2023-01-05 02:00:00'),
(5, 'Pimchanok Jindarat', 'staff2@cinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0856789012', 'staff', 1, '2023-01-06 03:00:00'),
(6, 'Chanida Thepparat', 'admin@cinema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0867890123', 'admin', 1, '2023-01-10 01:00:00');


--
-- create table `trash`
--
CREATE TABLE `trash` (
    trash_id INT(11),
    table_name VARCHAR(255),
    deleted_data JSON,
    deleted_at TIMESTAMP
);

--
-- indicate table's primary key and key
--

--
-- table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `showtime_id` (`showtime_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`);

--
-- table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- table `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`id`);

--
-- table `showtimes`
--
ALTER TABLE `showtimes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`);

--
-- table `seat_status`
--
ALTER TABLE `seat_status`
  ADD PRIMARY KEY (`seat_id`, `showtime_id`),
  ADD KEY `seat_id` (`seat_id`),
  ADD KEY `showtime_id` (`showtime_id`);

--
-- table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
--
-- table `trash`
--
ALTER TABLE `trash`
  ADD PRIMARY KEY (`trash_id`);

--
-- table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

--
-- table `movie_genres`
--
ALTER TABLE `movie_genres`
  ADD PRIMARY KEY (`movie_id`, `genre_id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `genre_id` (`genre_id`);


-- --------------------------------------------
--
-- add AUTO_INCREMENT
--

--
-- table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- table `seats`
--
ALTER TABLE `seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=901;

--
-- table `showtimes`
--
ALTER TABLE `showtimes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


--
-- table `trssh`
--
ALTER TABLE `trash`
  MODIFY `trash_id` int(11) NOT NULL AUTO_INCREMENT;


--
-- indicate keys as foreign keys
--

--
-- table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`);

--
-- table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- table `seat_status`
--
ALTER TABLE `seat_status`
  ADD CONSTRAINT `seats_status_ibfk_1` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`),
  ADD CONSTRAINT `seats_status_ibfk_2` FOREIGN KEY (`showtime_id`) REFERENCES `showtimes` (`id`);

--
-- table `showtimes`
--
ALTER TABLE `showtimes`
  ADD CONSTRAINT `showtimes_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`);
COMMIT;

ALTER TABLE `movie_genres`
  ADD CONSTRAINT `movie_genres_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`),
  ADD CONSTRAINT `movie_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
