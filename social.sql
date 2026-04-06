-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 06, 2026 lúc 07:12 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `social`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `parent_comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'show'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `post_id`, `parent_comment_id`, `content`, `created_at`, `updated_at`, `media_path`, `status`) VALUES
(1, 7, 25, NULL, 'Velit nobis autem blanditiis illum voluptatum dolores.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(2, 12, 35, NULL, 'Exercitationem quas eaque dolores inventore labore.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(3, 10, 20, NULL, 'Ratione veritatis harum praesentium accusantium autem repellendus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(4, 18, 47, NULL, 'Minus cumque est consequatur accusamus dolorem.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(5, 20, 40, NULL, 'Consequatur qui quia sequi reiciendis saepe.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(6, 3, 28, NULL, 'Quasi commodi sed aspernatur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(7, 10, 46, NULL, 'Beatae sint vitae ea totam dolorem illo totam expedita.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(8, 17, 44, NULL, 'Sunt voluptas deleniti amet eveniet unde.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(10, 8, 44, NULL, 'Commodi enim eos dolorem fuga enim corrupti.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(11, 3, 40, NULL, 'Harum laboriosam consequuntur deleniti.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(12, 6, 38, NULL, 'Quae voluptatem doloremque qui.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(13, 6, 45, NULL, 'Sunt dolore soluta velit corporis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(14, 10, 40, NULL, 'Repellat tenetur sit voluptate quia.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(15, 20, 48, NULL, 'Qui voluptatibus aut rem labore eaque ad vero voluptas.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(16, 15, 49, NULL, 'Quod harum doloribus porro.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(17, 2, 18, NULL, 'Voluptates est qui fugiat excepturi soluta.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(18, 2, 3, NULL, 'Libero vel non temporibus sequi fugit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(19, 11, 24, NULL, 'Et quisquam accusamus quia nesciunt eveniet fugiat nihil.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(20, 9, 35, NULL, 'Animi atque repudiandae quis tempora eos.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(21, 8, 23, NULL, 'Aut provident deleniti dolores quis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(22, 20, 29, NULL, 'Minus sit id dicta nostrum.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(23, 4, 3, NULL, 'Dolor et dolore qui saepe quibusdam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(24, 4, 35, NULL, 'Harum quia reiciendis aut labore.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(25, 1, 2, NULL, 'Laborum ut praesentium qui quia ullam amet.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(26, 18, 41, NULL, 'Labore libero error veniam cumque amet.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(27, 13, 46, NULL, 'Suscipit velit quam cupiditate dicta et rerum enim.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(28, 9, 31, NULL, 'Pariatur molestiae sed esse reprehenderit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(30, 9, 34, NULL, 'Iusto autem assumenda in est.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(31, 14, 18, NULL, 'Non officiis earum nobis magni ullam est a.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(32, 6, 48, NULL, 'Suscipit labore excepturi at iste perferendis voluptatem libero.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(33, 15, 39, NULL, 'Atque odio hic incidunt blanditiis beatae nemo.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(34, 19, 40, NULL, 'Neque labore omnis quia rerum aspernatur id aliquid.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(35, 3, 18, NULL, 'Facilis veniam quis est eos vel aut odio.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(36, 10, 38, NULL, 'Pariatur rerum beatae esse rem est.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(37, 4, 40, NULL, 'Quas dignissimos vitae vel est.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(38, 20, 25, NULL, 'Minima autem similique veritatis sunt quisquam impedit voluptas eos.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(39, 4, 39, NULL, 'Hic rerum dolorum aut quasi iusto dolorem.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(40, 11, 20, NULL, 'Placeat id eaque aut pariatur explicabo.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(41, 7, 4, NULL, 'Iste est suscipit voluptatem sunt iste nam id.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(42, 11, 34, NULL, 'Et ea ea deleniti sed iste sequi quam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(43, 15, 5, NULL, 'Odio quod rem eos laboriosam tempora voluptatem autem.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(44, 12, 50, NULL, 'Distinctio accusamus non labore et voluptas quia.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(46, 6, 2, NULL, 'Nesciunt explicabo eaque praesentium iusto expedita qui.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(47, 14, 5, NULL, 'Voluptate commodi eum perferendis iure quia ipsam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(48, 2, 24, NULL, 'Sit incidunt dicta reprehenderit dolorem pariatur doloribus animi.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(49, 20, 38, NULL, 'In tempore quibusdam provident laborum officia molestiae sint labore.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(50, 13, 43, NULL, 'Dolor sed officia numquam ut in.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(51, 18, 32, NULL, 'Quis adipisci consectetur et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(52, 14, 36, NULL, 'Earum ipsa corrupti consectetur aperiam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(53, 16, 43, NULL, 'Eos enim sunt maxime et in.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(54, 2, 3, NULL, 'Facilis voluptatem ea perferendis itaque in delectus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(55, 5, 25, NULL, 'Blanditiis quasi ullam nemo amet cumque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(57, 10, 46, NULL, 'Quasi voluptate et odio quisquam ea quo quae.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(58, 9, 31, NULL, 'Est ut quisquam illo consequatur et optio soluta ut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(59, 13, 5, NULL, 'Facere ut voluptates esse ut rerum.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(60, 1, 3, NULL, 'Culpa quidem hic ut nobis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(61, 13, 46, NULL, 'Blanditiis veniam explicabo vero pariatur voluptas.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(62, 4, 38, NULL, 'Ad dolore sint et dolores architecto.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(63, 11, 30, NULL, 'Perferendis ut quia dolor optio laudantium aut sit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(64, 2, 2, NULL, 'Pariatur quo atque enim iusto aut voluptates corrupti ipsa.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(65, 4, 22, NULL, 'In fuga nemo ut ipsa omnis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(66, 16, 17, NULL, 'Distinctio aperiam blanditiis a quia et quod.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(68, 2, 26, NULL, 'Voluptatem nihil est omnis beatae consequatur nostrum.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(69, 9, 44, NULL, 'Rerum cupiditate et dolores blanditiis unde quisquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(70, 7, 18, NULL, 'Repellat autem voluptatem voluptatem eius.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(71, 19, 13, NULL, 'Sed error et et aut at.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(72, 7, 2, NULL, 'Delectus consequatur fugit et omnis consequatur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(74, 14, 44, NULL, 'In alias quidem qui dolores autem veniam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(75, 17, 32, NULL, 'Rerum dolores accusantium accusamus in ut magni.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(76, 16, 13, NULL, 'Adipisci hic dolores incidunt maxime quia.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(77, 3, 27, NULL, 'Sapiente atque dolore rerum facere at non est.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(78, 11, 28, NULL, 'Sed ut nisi hic atque quis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(79, 20, 31, NULL, 'Laboriosam quia nemo voluptas enim quisquam dolor commodi itaque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(80, 14, 16, NULL, 'Esse est est velit a voluptatem.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(81, 4, 17, NULL, 'Dolore consequuntur hic nihil et deleniti vel quisquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(82, 3, 48, NULL, 'Ipsum est quis placeat vitae quibusdam cumque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(83, 12, 32, NULL, 'Sint et nam doloribus sed.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(84, 11, 25, NULL, 'Nostrum occaecati itaque officiis eaque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(85, 9, 10, NULL, 'Consequatur sed vel possimus et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(86, 4, 41, NULL, 'Voluptatibus quia rerum qui culpa.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(87, 5, 36, NULL, 'Magnam minus culpa aut ipsum nemo quos voluptatem.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(88, 3, 27, NULL, 'Architecto molestias laudantium et minus enim repellendus quibusdam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(89, 3, 15, NULL, 'Cum odio minus enim nam odio corporis.', '2026-03-05 08:50:48', '2026-04-04 17:35:11', NULL, 'hidden'),
(90, 5, 6, NULL, 'Quas explicabo quia voluptas molestiae exercitationem magni sit praesentium.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(91, 15, 26, NULL, 'Cupiditate voluptatem aut modi velit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(92, 4, 20, NULL, 'Ut molestiae cum odio quia vel aut sed sapiente.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(93, 6, 12, NULL, 'Commodi molestias ut veniam illum eos.', '2026-03-05 08:50:48', '2026-04-04 07:35:13', NULL, 'hidden'),
(94, 3, 35, NULL, 'Tempora labore dolore sed quis tempore.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(95, 9, 49, NULL, 'Est veritatis reiciendis atque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(96, 5, 23, NULL, 'Quasi delectus beatae facere culpa ea provident excepturi.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(97, 2, 24, NULL, 'Esse voluptatem consequatur nam praesentium nemo ut aspernatur laborum.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(98, 10, 23, NULL, 'Non itaque aperiam tempora quo ut in numquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(99, 12, 30, NULL, 'Sequi iure laudantium dolorem facere nisi fugiat.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(100, 16, 37, NULL, 'Vero ducimus pariatur nesciunt voluptate qui ut ea.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(101, 8, 25, NULL, 'Aut autem ut alias eligendi.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(102, 3, 23, NULL, 'Consequuntur sed amet eveniet harum maxime.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(103, 12, 18, NULL, 'Magni inventore dignissimos consequatur eum provident dolor quia eos.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(104, 16, 5, NULL, 'Voluptates harum molestiae dignissimos.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(105, 17, 2, NULL, 'Enim ab voluptatem fuga non nihil et omnis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(106, 10, 45, NULL, 'Ut unde hic iusto voluptatem impedit repellendus in.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(107, 13, 35, NULL, 'Ipsam voluptatem harum molestiae ipsa.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(108, 14, 41, NULL, 'Omnis ut est fuga ab nihil.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(109, 2, 42, NULL, 'Voluptatum sit reprehenderit illo.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(110, 18, 6, NULL, 'Iure aperiam ad ullam quisquam maiores voluptatibus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(111, 8, 19, NULL, 'Sit accusantium excepturi est omnis asperiores.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(112, 20, 31, NULL, 'Quia dicta incidunt non sit provident.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(113, 18, 11, NULL, 'Eum et ut accusamus sit architecto reiciendis vel.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(114, 16, 16, NULL, 'Optio consectetur quia ea quam et totam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(115, 1, 43, NULL, 'Consequatur minus itaque quasi corporis rerum iusto aut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(117, 3, 4, NULL, 'Rerum est fugiat praesentium temporibus ea et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(118, 5, 50, NULL, 'Repellat amet aperiam quidem ab blanditiis qui esse.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(119, 12, 41, NULL, 'Veritatis vel provident eveniet.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(120, 4, 45, NULL, 'Libero ut iure non et perspiciatis voluptate iusto.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(121, 9, 21, NULL, 'Dignissimos rem omnis sed praesentium sed.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(123, 8, 49, NULL, 'Quis veritatis aliquam qui ex.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(124, 6, 12, NULL, 'Rem nisi id commodi cupiditate corrupti pariatur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(125, 9, 40, NULL, 'Id qui quod eum ut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(126, 8, 26, NULL, 'Ut minima ipsa maiores animi voluptatem fugit dolores facilis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(127, 15, 19, NULL, 'Nulla dolores in inventore officia occaecati nulla ut blanditiis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(128, 8, 48, NULL, 'Hic quidem id adipisci quam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(129, 5, 3, NULL, 'Eos qui at velit fugiat ut eligendi numquam deleniti.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(130, 6, 31, NULL, 'Nihil cumque perspiciatis quo ullam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(131, 11, 45, NULL, 'Rerum harum et esse.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(132, 1, 3, NULL, 'Et officia quia velit et sed.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(133, 8, 17, NULL, 'Omnis consequuntur omnis qui voluptatibus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(134, 5, 27, NULL, 'Facere nobis quia laudantium qui eligendi aliquam sunt.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(135, 20, 24, NULL, 'Consequuntur fugiat recusandae ut velit officia.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(136, 12, 3, NULL, 'Perspiciatis maxime delectus vel pariatur voluptates nostrum quaerat.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(137, 1, 39, NULL, 'Qui dicta culpa nihil doloremque architecto et quidem ipsam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(139, 4, 31, NULL, 'Nemo nobis blanditiis debitis totam ut veniam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(140, 3, 16, NULL, 'Aliquam ut repellendus quas fugit et itaque quo.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(141, 9, 6, NULL, 'Iure sapiente aut eligendi sint et perferendis nostrum dolor.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(142, 17, 5, NULL, 'Vero eos corporis cupiditate nihil et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(144, 8, 49, NULL, 'Maxime quod cumque assumenda pariatur voluptatibus officiis minima.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(145, 1, 50, NULL, 'Voluptatem minus non blanditiis rem dolore.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(146, 5, 15, NULL, 'Explicabo laudantium ipsam eaque fuga.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(147, 16, 43, NULL, 'Explicabo non ut nobis aut soluta.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(148, 12, 21, NULL, 'Natus sapiente sint autem nihil voluptas optio.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(149, 9, 27, NULL, 'Et maxime aut aperiam assumenda sed a.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(150, 12, 33, NULL, 'Qui dolore doloremque aut adipisci et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(151, 4, 26, NULL, 'Eum nobis debitis soluta ea blanditiis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(152, 1, 48, NULL, 'Qui qui quisquam quisquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(153, 11, 29, NULL, 'Nobis quod qui minima numquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(154, 10, 23, NULL, 'Facilis velit ea amet iste cum.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(155, 8, 19, NULL, 'Et pariatur non quod consectetur omnis hic.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(156, 2, 26, NULL, 'Non quibusdam odio quibusdam exercitationem placeat enim unde mollitia.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(157, 13, 29, NULL, 'Ut magni voluptatibus ipsam sit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(158, 7, 5, NULL, 'Fugit ratione molestias velit aut ut nihil neque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(159, 14, 24, NULL, 'Iure ea nihil exercitationem voluptate ratione est.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(160, 11, 4, NULL, 'Blanditiis nostrum fugit consectetur dolores.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(161, 14, 4, NULL, 'Eos dolorem sit sit eveniet nostrum eum magni provident.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(162, 19, 40, NULL, 'Facere numquam aut vel tenetur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(163, 1, 38, NULL, 'Aperiam aut provident adipisci quam doloribus tempore et ratione.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(164, 1, 4, NULL, 'Ut quibusdam sunt laborum fuga enim velit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(165, 14, 20, NULL, 'Sit ut sint quod iure aut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(167, 7, 17, NULL, 'Ipsa iste laudantium consequuntur praesentium aperiam quia quis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(168, 15, 45, NULL, 'Molestiae et occaecati amet non.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(169, 10, 40, NULL, 'Vero tenetur enim esse eum nam impedit autem aut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(170, 9, 49, NULL, 'Aliquid aut saepe saepe minus temporibus sed odio aspernatur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(171, 19, 40, NULL, 'Exercitationem est pariatur voluptate quasi at qui.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(172, 8, 25, NULL, 'Magni perferendis a optio architecto dolor odit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(173, 4, 40, NULL, 'Modi perspiciatis laborum esse velit aliquam.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(174, 9, 3, NULL, 'Nesciunt ea est exercitationem quia velit aliquid eaque.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(176, 6, 37, NULL, 'Culpa qui ab voluptas qui.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(177, 18, 3, NULL, 'Dicta ea id sequi.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(178, 4, 4, NULL, 'Modi voluptatibus quia laboriosam voluptas ea tenetur.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(179, 12, 47, NULL, 'Doloribus consequatur repudiandae deleniti doloribus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(180, 9, 46, NULL, 'Excepturi doloremque cumque animi.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(182, 4, 3, NULL, 'Repellendus voluptatibus voluptatem assumenda perspiciatis omnis.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(183, 9, 5, NULL, 'Possimus dolorem ea voluptates.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(184, 9, 46, NULL, 'Et animi dolores rerum optio.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(185, 8, 24, NULL, 'Quis possimus quasi est quam velit cupiditate.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(186, 7, 39, NULL, 'Consequuntur et eaque suscipit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(187, 10, 10, NULL, 'Accusamus quaerat ipsam eum iure.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(188, 13, 25, NULL, 'Minima ipsum eveniet fugiat officiis est facere in error.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(189, 9, 10, NULL, 'Ipsum id sint quia asperiores dolor alias vero non.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(191, 20, 36, NULL, 'Aliquid est a est ullam cupiditate odio.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(192, 3, 21, NULL, 'Cum sit perferendis iusto rerum maxime esse.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(193, 3, 42, NULL, 'Quaerat aliquam et voluptatum doloremque et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(194, 3, 17, NULL, 'Modi dolore non odio facilis quaerat reprehenderit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(195, 5, 35, NULL, 'Aut porro doloribus velit quaerat molestias ut.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(197, 14, 40, NULL, 'Debitis et qui nesciunt fugit.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(198, 4, 39, NULL, 'Id temporibus quas facilis quas deleniti rerum delectus.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(199, 2, 2, NULL, 'Fugiat qui rem minima corrupti nihil et.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(200, 3, 33, NULL, 'Amet recusandae id beatae reprehenderit at.', '2026-03-05 08:50:48', '2026-03-05 08:50:48', NULL, 'show'),
(202, 1, 3, NULL, 'dumamamamamamamamam', '2026-03-06 00:17:11', '2026-03-06 00:17:11', NULL, 'show'),
(203, 1, 3, NULL, 'dumamamamamamamamam', '2026-03-06 00:17:21', '2026-03-06 00:17:21', NULL, 'show'),
(204, 1, 3, NULL, 'dumamamamamamamamam', '2026-03-06 00:17:23', '2026-03-06 00:17:23', NULL, 'show'),
(205, 1, 3, NULL, 'ádasdasdasdasd', '2026-03-06 00:17:33', '2026-03-06 00:17:33', NULL, 'show'),
(206, 1, 56, NULL, 'clllll', '2026-03-06 00:18:16', '2026-03-06 00:18:16', NULL, 'show'),
(207, 1, 56, NULL, 'ấdđ', '2026-03-06 00:18:36', '2026-03-06 00:18:36', NULL, 'show'),
(208, 1, 3, NULL, 'harrrrrrrrrrr', '2026-03-06 00:18:48', '2026-03-06 00:18:48', NULL, 'show'),
(209, 1, 3, NULL, 'áddddddd', '2026-03-06 00:19:07', '2026-03-06 00:19:07', NULL, 'show'),
(210, 1, 3, NULL, 'áddddddđ', '2026-03-06 00:19:23', '2026-03-06 00:19:23', NULL, 'show'),
(211, 1, 56, NULL, 'ádasdas', '2026-03-06 00:19:46', '2026-03-06 00:19:46', NULL, 'show'),
(212, 1, 3, NULL, 'rádasdasdasd', '2026-03-06 00:19:53', '2026-03-06 00:19:53', NULL, 'show'),
(213, 1, 3, NULL, 'hú hú', '2026-03-06 00:20:08', '2026-03-06 00:20:08', NULL, 'show'),
(214, 1, 3, NULL, 'khẹt khẹt', '2026-03-06 00:21:25', '2026-03-06 00:21:25', NULL, 'show'),
(215, 1, 56, NULL, 'oke', '2026-03-06 00:23:13', '2026-03-06 00:23:13', NULL, 'show'),
(216, 1, 56, NULL, 'chhh', '2026-03-06 00:24:40', '2026-03-06 00:24:40', NULL, 'show'),
(217, 1, 56, NULL, 'ád', '2026-03-06 00:24:52', '2026-03-06 00:24:52', NULL, 'show'),
(218, 1, 56, NULL, 'ád', '2026-03-06 00:27:46', '2026-03-06 00:27:46', NULL, 'show'),
(219, 1, 56, NULL, 'cd', '2026-03-06 00:27:52', '2026-03-06 00:27:52', NULL, 'show'),
(222, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:43', '2026-03-06 00:36:43', NULL, 'show'),
(223, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:45', '2026-03-06 00:36:45', NULL, 'show'),
(224, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:46', '2026-03-06 00:36:46', NULL, 'show'),
(225, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:47', '2026-03-06 00:36:47', NULL, 'show'),
(226, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:47', '2026-03-06 00:36:47', NULL, 'show'),
(227, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:48', '2026-03-06 00:36:48', NULL, 'show'),
(228, 1, 3, NULL, 'asdasdasdasdas', '2026-03-06 00:36:48', '2026-03-06 00:36:48', NULL, 'show'),
(229, 1, 3, NULL, 'sadasdasdasdas', '2026-03-06 00:37:09', '2026-03-06 00:37:09', NULL, 'show'),
(230, 1, 3, NULL, 'sadasdasdasdas', '2026-03-06 00:37:10', '2026-03-06 00:37:10', NULL, 'show'),
(231, 1, 3, NULL, 'sadasdasdasdas', '2026-03-06 00:37:14', '2026-03-06 00:37:14', NULL, 'show'),
(232, 1, 3, NULL, 'adasdasd', '2026-03-06 00:37:17', '2026-03-06 00:37:17', NULL, 'show'),
(233, 1, 3, NULL, 'adasdasd', '2026-03-06 00:37:18', '2026-03-06 00:37:18', NULL, 'show'),
(234, 1, 3, NULL, 'adasdasd', '2026-03-06 00:37:18', '2026-03-06 00:37:18', NULL, 'show'),
(235, 1, 3, NULL, 'adasdasd', '2026-03-06 00:37:18', '2026-03-06 00:37:18', NULL, 'show'),
(236, 1, 3, NULL, 'dasdasdasdas', '2026-03-06 00:38:14', '2026-03-06 00:38:14', NULL, 'show'),
(237, 1, 3, NULL, 'dasdasdasdas', '2026-03-06 00:38:15', '2026-03-06 00:38:15', NULL, 'show'),
(238, 1, 3, NULL, 'dasdasdasdas', '2026-03-06 00:38:16', '2026-03-06 00:38:16', NULL, 'show'),
(239, 1, 3, NULL, 'asdasd', '2026-03-06 00:38:41', '2026-03-06 00:38:41', NULL, 'show'),
(240, 1, 3, NULL, 'vaix', '2026-03-06 00:38:59', '2026-03-06 00:38:59', NULL, 'show'),
(241, 1, 3, NULL, 'vaicl', '2026-03-06 08:19:10', '2026-03-06 08:19:10', NULL, 'show'),
(242, 1, 3, NULL, 'vãi', '2026-03-06 08:25:07', '2026-03-06 08:25:07', NULL, 'show'),
(243, 1, 3, NULL, 'vãi', '2026-03-06 08:25:08', '2026-03-06 08:25:08', NULL, 'show'),
(244, 1, 3, NULL, 'vãi', '2026-03-06 08:25:09', '2026-03-06 08:25:09', NULL, 'show'),
(245, 1, 3, NULL, 'vãi', '2026-03-06 08:25:10', '2026-03-06 08:25:10', NULL, 'show'),
(246, 1, 3, NULL, 'vãi', '2026-03-06 08:25:11', '2026-03-06 08:25:11', NULL, 'show'),
(247, 1, 3, NULL, 'hả', '2026-03-06 08:26:12', '2026-03-06 08:26:12', NULL, 'show'),
(248, 1, 3, NULL, 'hả', '2026-03-06 08:26:19', '2026-03-06 08:26:19', NULL, 'show'),
(249, 1, 3, NULL, 'hị', '2026-03-06 08:27:07', '2026-03-06 08:27:07', NULL, 'show'),
(250, 1, 3, NULL, 'hị', '2026-03-06 08:27:09', '2026-03-06 08:27:09', NULL, 'show'),
(251, 1, 3, NULL, 'là sao', '2026-03-06 08:27:42', '2026-03-06 08:27:42', NULL, 'show'),
(252, 1, 3, NULL, 'là sáo', '2026-03-06 08:29:18', '2026-03-06 08:29:18', NULL, 'show'),
(253, 1, 3, NULL, 'là sáo', '2026-03-06 08:29:20', '2026-03-06 08:29:20', NULL, 'show'),
(254, 1, 3, NULL, 'cayyyyyyyyy', '2026-03-06 08:29:57', '2026-03-06 08:29:57', NULL, 'show'),
(255, 1, 3, NULL, 'cayyyyyyyyy', '2026-03-06 08:29:58', '2026-03-06 08:29:58', NULL, 'show'),
(256, 1, 3, NULL, 'xád', '2026-03-06 08:30:08', '2026-03-06 08:30:08', NULL, 'show'),
(257, 1, 3, NULL, 'ádasda', '2026-03-06 08:31:46', '2026-03-06 08:31:46', NULL, 'show'),
(258, 1, 3, NULL, 'ádasdas', '2026-03-06 08:32:21', '2026-03-06 08:32:21', NULL, 'show'),
(259, 1, 3, NULL, 'hả', '2026-03-06 08:35:08', '2026-03-06 08:35:08', NULL, 'show'),
(260, 1, 3, NULL, 'hả', '2026-03-06 08:35:09', '2026-03-06 08:35:09', NULL, 'show'),
(261, 1, 3, NULL, 'adasdasdasdas', '2026-03-06 08:36:08', '2026-03-06 08:36:08', NULL, 'show'),
(262, 1, 3, NULL, 'asdasdasdasdasdasdasd g', '2026-03-06 08:36:22', '2026-03-06 08:36:22', NULL, 'show'),
(263, 1, 56, NULL, 'vcl', '2026-03-06 10:21:50', '2026-03-06 10:21:50', NULL, 'show'),
(265, 1, 54, NULL, 'v', '2026-03-11 08:36:05', '2026-03-11 08:36:05', NULL, 'show'),
(266, 1, 54, NULL, 'hả', '2026-03-11 08:38:58', '2026-03-11 08:38:58', NULL, 'show'),
(267, 1, 56, NULL, 'nắng', '2026-03-11 08:42:19', '2026-03-11 08:42:19', NULL, 'show'),
(269, 1, 56, NULL, 'ád', '2026-03-12 02:34:22', '2026-03-12 02:34:22', NULL, 'show'),
(270, 1, 56, NULL, 'gg', '2026-03-12 02:46:19', '2026-03-12 02:46:19', NULL, 'show'),
(282, 1, 54, NULL, 'ádasdasd', '2026-03-12 04:31:06', '2026-03-12 04:31:06', NULL, 'show'),
(283, 1, 54, NULL, 'vã', '2026-03-12 04:32:19', '2026-03-12 04:32:19', NULL, 'show'),
(465, 1, 54, 283, '@Dr. Garret Baumbach Jr. dádasd', '2026-03-15 09:26:51', '2026-03-15 09:26:51', NULL, 'show'),
(466, 1, 54, 283, '@Dr. Garret Baumbach Jr. ádasdas', '2026-03-15 09:26:54', '2026-03-15 09:26:54', NULL, 'show'),
(467, 1, 54, NULL, 'hahha', '2026-03-15 09:48:57', '2026-03-15 09:48:57', NULL, 'show'),
(468, 1, 54, 467, '@Dr. Garret Baumbach Jr. là sao ba', '2026-03-15 09:49:04', '2026-03-15 09:49:04', NULL, 'show'),
(469, 1, 54, 467, '@Dr. Garret Baumbach Jr. ný nsys', '2026-03-15 09:51:18', '2026-03-15 09:51:18', NULL, 'show'),
(470, 1, 54, 467, '@Dr. Garret Baumbach Jr. hả', '2026-03-15 09:52:08', '2026-03-15 09:52:08', NULL, 'show'),
(471, 1, 54, 467, '@Dr. Garret Baumbach Jr. hả', '2026-03-15 09:52:35', '2026-03-15 09:52:35', NULL, 'show'),
(472, 1, 54, NULL, 'hả', '2026-03-15 09:52:46', '2026-03-15 09:52:46', NULL, 'show'),
(473, 1, 54, 467, '@Dr. Garret Baumbach Jr. hả', '2026-03-15 09:52:52', '2026-03-15 09:52:52', NULL, 'show'),
(474, 1, 54, 467, '@Dr. Garret Baumbach Jr. hahaha', '2026-03-15 09:53:35', '2026-03-15 09:53:35', NULL, 'show'),
(479, 1, 54, NULL, 'hả', '2026-03-15 11:05:00', '2026-03-15 11:05:00', NULL, 'show'),
(480, 1, 54, 479, '@Dr. Garret Baumbach Jr. hả', '2026-03-15 11:05:04', '2026-03-15 11:05:04', NULL, 'show'),
(481, 1, 54, NULL, 'hả', '2026-03-15 11:05:47', '2026-03-15 11:05:47', NULL, 'show'),
(482, 1, 54, 481, '@Dr. Garret Baumbach Jr. hả', '2026-03-15 11:05:51', '2026-03-15 11:05:51', NULL, 'show'),
(483, 1, 54, NULL, 'ádsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsdsd', '2026-03-15 11:08:01', '2026-03-15 11:08:01', NULL, 'show'),
(484, 1, 54, NULL, 'dấdadasdasd', '2026-03-15 11:10:06', '2026-03-15 11:10:06', NULL, 'show'),
(485, 1, 54, NULL, 'áddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', '2026-03-15 11:10:13', '2026-03-15 11:10:13', NULL, 'show'),
(486, 1, 54, 485, '@Dr. Garret Baumbach Jr. kasdasdasdasdaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2026-03-15 11:14:02', '2026-03-15 11:14:02', NULL, 'show'),
(487, 1, 54, NULL, 'đâssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', '2026-03-15 11:14:18', '2026-03-15 11:14:18', NULL, 'show'),
(488, 1, 54, NULL, 'tafafoafoafaofoaof ấ,falfasofaf sfjajfasjfioasjao fbbfoabfoafbasifbafbasbfafobsaasdf', '2026-03-15 11:15:09', '2026-03-15 11:15:09', NULL, 'show'),
(526, 1, 54, NULL, 'helllo', '2026-03-17 07:02:36', '2026-03-17 07:02:36', 'comments/media/1773756156_bc73296dbf54630a3a45.jpg', 'show'),
(538, 1, 54, 488, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:42:41', '2026-03-17 07:42:41', 'comments/media/1773758561_fe47248b24f004e717f35c4279626c27.jpg', 'show'),
(539, 1, 54, 488, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:42:52', '2026-03-17 07:42:52', 'comments/media/1773758572_chainsaw-man-aki-hayakawa-hd-wallpaper-preview.jpg', 'show'),
(540, 1, 54, 526, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:43:00', '2026-03-17 07:43:00', 'comments/media/1773758580_bc73296dbf54630a3a45.jpg', 'show'),
(541, 1, 54, 526, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:43:09', '2026-03-17 07:43:09', 'comments/media/1773758589_manga-one-punch-man-saitama-wallpaper-preview (1).jpg', 'show'),
(542, 1, 54, NULL, 'ssss', '2026-03-17 07:45:45', '2026-03-17 07:45:45', 'comments/media/1773758745_fe47248b24f004e717f35c4279626c27.jpg', 'show'),
(543, 1, 54, 542, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:45:56', '2026-03-17 07:45:56', 'comments/media/1773758756_bc73296dbf54630a3a45.jpg', 'show'),
(544, 1, 54, 542, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:58:47', '2026-03-17 07:58:47', 'comments/media/1773759527_bc73296dbf54630a3a45.jpg', 'show'),
(545, 1, 54, NULL, 'kkk', '2026-03-17 07:59:03', '2026-03-17 07:59:03', 'comments/media/1773759543_bc73296dbf54630a3a45.jpg', 'show'),
(546, 1, 54, 545, '@Dr. Garret Baumbach Jr.', '2026-03-17 07:59:10', '2026-03-17 07:59:10', 'comments/media/1773759550_chainsaw-man-aki-hayakawa-hd-wallpaper-preview.jpg', 'show'),
(547, 1, 54, NULL, 'dddd', '2026-03-17 08:00:52', '2026-03-17 08:00:52', 'comments/media/1773759652_giphy.gif', 'show'),
(548, 1, 54, NULL, 'sss', '2026-03-17 08:01:32', '2026-03-17 08:01:32', 'comments/media/1773759692_yfVNONOE-JRyWcswbTrxIOKN38w_gzqe4tJhyXkPgKU.webp', 'show'),
(549, 1, 54, NULL, 'ddđ', '2026-03-17 08:02:03', '2026-03-17 08:02:03', 'comments/media/1773759723_giphy.gif', 'show'),
(550, 1, 54, NULL, 'lol', '2026-03-17 08:02:37', '2026-03-17 08:02:37', 'comments/media/1773759757_tumblr_e282adfeaf8dcd1cde9cf67e4499c280_a2e0e007_640.gif', 'show'),
(555, 1, 54, NULL, 'kkkkk', '2026-03-17 08:11:19', '2026-03-17 08:11:19', 'comments/media/1773760279_pixel-art-andlt-aestheticandgt-town-city-waneella-hd-wallpaper-preview.jpg', 'show'),
(556, 1, 54, NULL, 'ddd', '2026-03-17 08:12:26', '2026-03-17 08:12:26', 'comments/media/1773760346_pixel-art-wallpaper-gif-12.gif', 'show'),
(558, 1, 54, NULL, 'ssssaa', '2026-03-17 08:15:17', '2026-03-17 08:15:17', 'comments/media/1773760517_yfVNONOE-JRyWcswbTrxIOKN38w_gzqe4tJhyXkPgKU.webp', 'show'),
(560, 1, 54, NULL, 'ssss', '2026-03-17 08:16:39', '2026-03-17 08:16:39', 'comments/media/1773760599_fe47248b24f004e717f35c4279626c27.jpg', 'show'),
(561, 1, 54, NULL, 'sssss', '2026-03-17 08:17:32', '2026-03-17 08:17:32', 'comments/media/1773760652_giphy.gif', 'show'),
(562, 1, 54, NULL, 'sss', '2026-03-17 08:17:50', '2026-03-17 08:17:50', 'comments/media/1773760670_fe47248b24f004e717f35c4279626c27.jpg', 'show'),
(563, 1, 54, 562, '@Dr. Garret Baumbach Jr.', '2026-03-17 08:18:02', '2026-03-17 08:18:02', 'comments/media/1773760682_tumblr_e282adfeaf8dcd1cde9cf67e4499c280_a2e0e007_640.gif', 'show'),
(568, 1, 54, 562, '@Dr. Garret Baumbach Jr. lllll', '2026-03-19 20:59:06', '2026-03-19 20:59:06', NULL, 'show'),
(604, 1, 78, NULL, 'chào cậu', '2026-04-06 15:49:44', '2026-04-06 15:49:44', NULL, '1'),
(605, 1, 78, NULL, '😜', '2026-04-06 15:50:04', '2026-04-06 15:50:04', NULL, '1'),
(606, 1, 78, NULL, 'hay đấyy', '2026-04-06 16:13:46', '2026-04-06 16:13:46', NULL, '1'),
(607, 1, 32, NULL, 'chào nhé', '2026-04-06 16:22:38', '2026-04-06 16:22:38', NULL, '1'),
(608, 1, 11, NULL, 'hehehe', '2026-04-06 16:25:20', '2026-04-06 16:25:20', NULL, '1');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) DEFAULT 'show'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `conversations`
--

INSERT INTO `conversations` (`id`, `type`, `created_at`, `updated_at`, `status`) VALUES
(9, 'private', '2026-03-26 07:34:35', '2026-03-26 07:34:35', 'show'),
(10, 'private', '2026-03-26 07:34:35', '2026-03-26 07:34:35', 'show'),
(39, 'private', '2026-03-28 17:26:53', '2026-03-28 17:26:53', 'show'),
(42, 'private', '2026-03-29 09:09:10', '2026-03-29 09:09:10', 'show'),
(44, 'private', '2026-04-05 16:29:38', '2026-04-05 16:29:38', 'show');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `conversation_users`
--

CREATE TABLE `conversation_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `conversation_users`
--

INSERT INTO `conversation_users` (`id`, `conversation_id`, `user_id`) VALUES
(51, 39, 1),
(50, 39, 2),
(57, 42, 1),
(56, 42, 3),
(60, 44, 3),
(61, 44, 14);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `post_id`, `created_at`, `updated_at`) VALUES
(1, 1, 54, NULL, NULL),
(39, 1, 3, '2026-04-01 15:54:43', '2026-04-01 15:54:43'),
(40, 1, 78, '2026-04-06 15:49:52', '2026-04-06 15:49:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `follows`
--

CREATE TABLE `follows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `follower_id` bigint(20) UNSIGNED NOT NULL,
  `following_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(43, 1, 15, '2026-03-24 17:54:23'),
(44, 1, 20, '2026-03-24 17:54:23'),
(52, 2, 12, '2026-03-25 08:13:26'),
(55, 2, 14, '2026-03-25 08:13:28'),
(56, 2, 15, '2026-03-25 08:13:29'),
(57, 2, 20, '2026-03-25 08:13:29'),
(65, 2, 10, '2026-03-25 08:32:20'),
(66, 2, 11, '2026-03-25 08:32:21'),
(87, 1, 17, '2026-03-26 17:29:16'),
(97, 2, 1, '2026-03-30 17:20:38'),
(126, 1, 8, '2026-04-05 15:20:36'),
(128, 1, 10, '2026-04-05 15:20:41'),
(130, 1, 3, '2026-04-05 16:06:05'),
(131, 3, 1, '2026-04-05 16:09:05'),
(133, 1, 2, '2026-04-06 16:13:50'),
(134, 1, 4, '2026-04-06 16:13:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `like_comments`
--

CREATE TABLE `like_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `like_comments`
--

INSERT INTO `like_comments` (`id`, `user_id`, `comment_id`, `created_at`, `updated_at`) VALUES
(18, 1, 488, '2026-03-15 11:15:45', '2026-03-15 11:15:45'),
(86, 1, 270, '2026-04-05 14:52:18', '2026-04-05 14:52:18'),
(87, 1, 269, '2026-04-05 14:52:19', '2026-04-05 14:52:19'),
(88, 1, 267, '2026-04-05 14:52:20', '2026-04-05 14:52:20'),
(89, 3, 270, '2026-04-05 14:56:39', '2026-04-05 14:56:39'),
(90, 3, 262, '2026-04-05 16:11:31', '2026-04-05 16:11:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `like_posts`
--

CREATE TABLE `like_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `like_posts`
--

INSERT INTO `like_posts` (`id`, `user_id`, `post_id`, `created_at`, `updated_at`) VALUES
(107, 2, 56, '2026-03-09 08:29:28', '2026-03-09 08:29:28'),
(180, 1, 56, '2026-03-12 08:51:06', '2026-03-12 08:51:06'),
(182, 1, 54, '2026-03-15 00:18:41', '2026-03-15 00:18:41'),
(245, 1, 4, '2026-04-05 09:51:13', '2026-04-05 09:51:13'),
(246, 1, 40, '2026-04-05 16:07:31', '2026-04-05 16:07:31'),
(260, 1, 6, '2026-04-06 15:40:22', '2026-04-06 15:40:22'),
(262, 1, 23, '2026-04-06 16:13:14', '2026-04-06 16:13:14'),
(263, 1, 22, '2026-04-06 16:13:16', '2026-04-06 16:13:16'),
(265, 1, 32, '2026-04-06 16:22:58', '2026-04-06 16:22:58'),
(266, 1, 78, '2026-04-06 16:33:03', '2026-04-06 16:33:03'),
(267, 1, 10, '2026-04-06 16:57:27', '2026-04-06 16:57:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `media`
--

INSERT INTO `media` (`id`, `post_id`, `type`, `file_path`, `thumbnail`, `duration`, `created_at`, `updated_at`) VALUES
(6, 54, 'image', 'posts/media/1772729170_Ảnh chụp màn hình 2024-12-06 005007.png', NULL, NULL, '2026-03-05 09:46:10', '2026-03-05 09:46:10'),
(8, 56, 'video', 'posts/media/1772729423_Genshin Impact 2023-04-24 21-15-59.mp4', NULL, NULL, '2026-03-05 09:50:23', '2026-03-05 09:50:23'),
(9, 56, 'video', 'posts/media/1772729423_Genshin Impact 2023-04-29 22-31-56.mp4', NULL, NULL, '2026-03-05 09:50:23', '2026-03-05 09:50:23'),
(12, 56, 'image', 'posts/qXBVXnTdfGq87YUS8nnt1XChRN2rB4qR898Vfdi4.gif', NULL, NULL, '2026-03-06 09:00:41', '2026-03-06 09:00:41'),
(14, 56, 'image', 'posts/hUHde7uLLD0tmuSJlepoxx2ocEScXdLOqCRLwJQO.jpg', NULL, NULL, '2026-03-06 10:09:35', '2026-03-06 10:09:35'),
(15, 57, 'image', 'posts/media/1773246177_Ảnh chụp màn hình 2023-03-17 225019.png', NULL, NULL, '2026-03-11 09:22:58', '2026-03-11 09:22:58'),
(16, 57, 'image', 'posts/media/1773246178_Ảnh chụp màn hình 2024-12-06 005007.png', NULL, NULL, '2026-03-11 09:22:58', '2026-03-11 09:22:58'),
(17, 57, 'image', 'posts/media/1773246178_dcb46d6e58acfedf7aabaaa3af92225888.jpg', NULL, NULL, '2026-03-11 09:22:58', '2026-03-11 09:22:58'),
(21, 54, 'image', 'posts/b3cpN2hct6liHSSOfyVneYGm7SvbU92iiXzLeTSo.gif', NULL, NULL, '2026-03-11 09:32:14', '2026-03-11 09:32:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) DEFAULT 'show'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `content`, `created_at`, `updated_at`, `read_at`, `status`) VALUES
(158, 39, 2, 'chào', '2026-03-28 17:26:53', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(159, 39, 1, 'hello', '2026-03-28 17:27:00', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(160, 39, 2, 'chào', '2026-03-28 17:34:51', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(161, 39, 2, 'chào', '2026-03-28 17:35:53', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(162, 39, 1, 'hello friends', '2026-03-29 05:47:58', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(163, 39, 1, '', '2026-03-29 05:53:15', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(164, 39, 1, '😁', '2026-03-29 06:11:37', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(165, 39, 1, '😑🤭🫣', '2026-03-29 06:12:03', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(166, 39, 1, '😊🫢', '2026-03-29 06:23:59', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(167, 39, 1, 'chào friends', '2026-03-29 09:05:33', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(174, 42, 1, 'chào', '2026-03-29 09:09:18', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(175, 42, 1, 'chào', '2026-03-29 09:10:28', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(177, 39, 1, 'hello', '2026-03-29 14:31:52', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(178, 42, 1, 'kkk', '2026-03-29 14:34:37', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(179, 39, 2, 'chào bạn', '2026-03-29 14:34:41', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(180, 42, 1, 'dđ', '2026-03-29 14:44:34', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(181, 39, 2, 'chào', '2026-03-29 14:44:40', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(182, 42, 1, 'kkkk', '2026-03-29 14:47:42', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(183, 39, 2, 'chào bạn', '2026-03-29 14:47:45', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(184, 42, 1, 'gdfgsdg', '2026-03-29 14:50:01', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(185, 39, 2, 'chào ný', '2026-03-29 14:50:11', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(186, 42, 1, 'ádasd', '2026-03-29 14:59:15', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(187, 39, 2, 'chào friends', '2026-03-29 14:59:20', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(188, 42, 1, 'blalalal', '2026-03-29 15:08:09', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(189, 39, 2, 'lô friend', '2026-03-29 15:08:17', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(190, 39, 2, 'ổn k friend', '2026-03-29 15:08:29', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(191, 39, 2, 'alo vũ à vũ', '2026-03-29 15:08:41', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(192, 39, 2, 'em đừng có chối', '2026-03-29 15:08:50', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(193, 39, 1, 'có nhầm số k anh', '2026-03-29 15:09:05', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(194, 39, 2, '😙', '2026-03-29 15:11:08', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(195, 39, 2, '', '2026-03-29 15:11:25', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(196, 42, 1, 'chÀO', '2026-03-29 16:12:12', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(197, 39, 2, 'LO LO', '2026-03-29 16:12:17', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(198, 42, 1, 'HÈ', '2026-03-29 16:13:19', '2026-03-29 16:37:46', '2026-03-29 16:37:46', 'show'),
(199, 39, 2, 'HEHHEHH', '2026-03-29 16:13:24', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(200, 39, 2, 'KKKK', '2026-03-29 16:13:41', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(201, 39, 2, 'chào', '2026-03-29 16:18:47', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(202, 39, 2, 'chào', '2026-03-29 16:19:00', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(203, 39, 2, 'chào', '2026-03-29 16:33:50', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(204, 39, 2, 'chào', '2026-03-29 16:34:50', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(205, 39, 2, 'chào', '2026-03-29 16:36:39', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(206, 39, 1, 'chào', '2026-03-29 16:36:53', '2026-03-29 16:37:48', '2026-03-29 16:37:48', 'show'),
(207, 39, 2, 'chào', '2026-03-29 16:37:52', '2026-03-29 16:48:55', '2026-03-29 16:48:55', 'show'),
(208, 39, 2, 'chào', '2026-03-29 16:49:16', '2026-03-29 16:50:18', '2026-03-29 16:50:18', 'show'),
(209, 39, 1, 'chào', '2026-03-29 16:49:59', '2026-03-29 16:50:08', '2026-03-29 16:50:08', 'show'),
(210, 39, 1, 'chào', '2026-03-29 16:50:22', '2026-03-29 16:50:49', '2026-03-29 16:50:49', 'show'),
(211, 39, 2, 'chào', '2026-03-29 16:50:34', '2026-03-29 16:52:45', '2026-03-29 16:52:45', 'show'),
(212, 39, 2, 'chào', '2026-03-29 16:50:43', '2026-03-29 16:52:45', '2026-03-29 16:52:45', 'show'),
(213, 39, 2, 'chào', '2026-03-29 16:52:50', '2026-03-29 16:54:04', '2026-03-29 16:54:04', 'show'),
(214, 39, 1, 'chào', '2026-03-29 16:53:05', '2026-03-29 16:54:04', '2026-03-29 16:54:04', 'show'),
(215, 39, 1, 'chào nahsaa', '2026-03-29 16:54:45', '2026-03-29 16:55:08', '2026-03-29 16:55:08', 'show'),
(216, 39, 2, 'kkk xong', '2026-03-29 16:54:52', '2026-03-29 17:10:57', '2026-03-29 17:10:57', 'show'),
(217, 39, 2, 'ngon luôn', '2026-03-29 16:55:03', '2026-03-29 17:10:57', '2026-03-29 17:10:57', 'show'),
(218, 39, 1, 'hả', '2026-03-29 17:11:02', '2026-03-29 17:11:05', '2026-03-29 17:11:05', 'show'),
(219, 39, 2, 'chào', '2026-03-29 17:11:16', '2026-03-29 17:12:39', '2026-03-29 17:12:39', 'show'),
(220, 39, 1, 'hả', '2026-03-29 17:11:31', '2026-03-29 17:11:42', '2026-03-29 17:11:42', 'show'),
(221, 39, 2, 'chào', '2026-03-29 17:11:35', '2026-03-29 17:12:39', '2026-03-29 17:12:39', 'show'),
(222, 39, 2, 'chào', '2026-03-29 17:17:27', '2026-03-29 17:22:20', '2026-03-29 17:22:20', 'show'),
(223, 39, 1, 'vai', '2026-03-29 17:18:49', '2026-03-29 17:18:52', '2026-03-29 17:18:52', 'show'),
(224, 39, 2, 'chào', '2026-03-29 17:22:30', '2026-03-29 17:22:34', '2026-03-29 17:22:34', 'show'),
(225, 39, 2, 'chào', '2026-03-29 17:22:48', '2026-03-29 17:22:55', '2026-03-29 17:22:55', 'show'),
(226, 39, 2, 'chào', '2026-03-29 17:23:27', '2026-03-29 17:23:40', '2026-03-29 17:23:40', 'show'),
(227, 39, 2, 'chào', '2026-03-29 17:25:23', '2026-03-29 17:25:24', '2026-03-29 17:25:24', 'show'),
(228, 39, 2, 'chào', '2026-03-29 17:25:30', '2026-03-29 17:25:35', '2026-03-29 17:25:35', 'show'),
(229, 39, 2, 'chào', '2026-03-29 17:26:35', '2026-03-29 17:26:39', '2026-03-29 17:26:39', 'show'),
(230, 39, 2, 'chào', '2026-03-29 17:31:46', '2026-03-29 17:31:48', '2026-03-29 17:31:48', 'show'),
(231, 39, 2, 'chào', '2026-03-29 17:31:54', '2026-03-29 17:31:59', '2026-03-29 17:31:59', 'show'),
(232, 39, 2, 'ádadasd', '2026-03-29 17:34:48', '2026-03-29 17:34:51', '2026-03-29 17:34:51', 'show'),
(233, 39, 2, 'chào', '2026-03-30 14:52:40', '2026-03-30 14:52:56', '2026-03-30 14:52:56', 'show'),
(234, 39, 2, 'chàooo', '2026-03-30 14:53:04', '2026-03-30 14:55:57', '2026-03-30 14:55:57', 'show'),
(235, 39, 2, 'chfdo', '2026-03-30 14:56:45', '2026-03-30 14:56:50', '2026-03-30 14:56:50', 'show'),
(236, 39, 2, 'hello', '2026-03-30 14:57:08', '2026-03-30 14:57:10', '2026-03-30 14:57:10', 'show'),
(237, 39, 2, 'chào bạn', '2026-03-30 14:57:16', '2026-03-30 14:57:21', '2026-03-30 14:57:21', 'show'),
(241, 39, 2, 'chào', '2026-03-30 16:28:00', '2026-03-30 16:28:09', '2026-03-30 16:28:09', 'show'),
(242, 39, 2, 'chào', '2026-03-30 16:28:23', '2026-03-30 16:29:43', '2026-03-30 16:29:43', 'show'),
(243, 39, 2, 'chào', '2026-03-30 16:28:51', '2026-03-30 16:29:43', '2026-03-30 16:29:43', 'show'),
(244, 39, 2, 'chào', '2026-03-30 16:29:12', '2026-03-30 16:29:43', '2026-03-30 16:29:43', 'show'),
(245, 39, 2, 'chào\'', '2026-03-30 16:29:38', '2026-03-30 16:29:43', '2026-03-30 16:29:43', 'show'),
(246, 39, 2, 'chào', '2026-03-30 16:53:18', '2026-03-30 16:53:25', '2026-03-30 16:53:25', 'show'),
(247, 39, 2, 'vũ à', '2026-03-31 08:32:01', '2026-03-31 08:32:13', '2026-03-31 08:32:13', 'show'),
(248, 39, 2, 'vũ', '2026-03-31 08:32:20', '2026-03-31 08:32:33', '2026-03-31 08:32:33', 'show'),
(249, 39, 2, 'nà nần fanafnanfnaf', '2026-03-31 08:34:29', '2026-03-31 08:34:38', '2026-03-31 08:34:38', 'show'),
(251, 42, 3, 'hehehhe', '2026-04-05 16:09:38', '2026-04-05 16:09:40', '2026-04-05 16:09:40', 'show'),
(252, 42, 1, 'hhhehheh', '2026-04-05 16:09:58', '2026-04-05 16:09:59', '2026-04-05 16:09:59', 'show'),
(253, 42, 1, 'ê bạn', '2026-04-05 16:12:53', '2026-04-05 16:13:05', '2026-04-05 16:13:05', 'show'),
(254, 42, 3, 'hả', '2026-04-05 16:13:22', '2026-04-05 16:13:26', '2026-04-05 16:13:26', 'show'),
(255, 42, 3, 'gì bạn', '2026-04-05 16:13:32', '2026-04-05 16:13:34', '2026-04-05 16:13:34', 'show'),
(257, 44, 3, 'chag', '2026-04-05 16:29:38', '2026-04-05 16:29:38', NULL, 'show'),
(258, 42, 3, 'chào cậu', '2026-04-05 16:31:29', '2026-04-05 16:31:29', '2026-04-05 16:31:29', 'show'),
(259, 42, 1, 'chào', '2026-04-05 16:31:38', '2026-04-05 16:31:39', '2026-04-05 16:31:39', 'show'),
(260, 42, 3, 'r chào', '2026-04-05 16:33:43', '2026-04-05 16:33:43', '2026-04-05 16:33:43', 'show'),
(261, 42, 3, 'chào', '2026-04-05 16:34:02', '2026-04-05 16:35:57', '2026-04-05 16:35:57', 'show'),
(262, 42, 1, 'chào cái gì', '2026-04-05 16:36:10', '2026-04-05 16:36:10', '2026-04-05 16:36:10', 'show'),
(263, 42, 1, 'nói cái gì', '2026-04-05 16:36:50', '2026-04-05 16:36:51', '2026-04-05 16:36:51', 'show');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `message_media`
--

CREATE TABLE `message_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT 'image',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `message_media`
--

INSERT INTO `message_media` (`id`, `message_id`, `file_path`, `type`, `created_at`, `updated_at`) VALUES
(11, 163, 'message_media/X4tXy5TMuTdQC22X7Jk0TKDloIWmzqWTpjN1UClu.gif', 'image', '2026-03-29 05:53:16', '2026-03-29 05:53:16'),
(12, 194, 'message_media/aI0Kgh0WQ4Xx6rAztwq0m1gJr778vfAwWIoAyxkb.png', 'image', '2026-03-29 15:11:08', '2026-03-29 15:11:08'),
(13, 195, 'message_media/TY238N0EuC8HSaBRkNwbrbaETRBgTVwNQ5PyRagH.png', 'image', '2026-03-29 15:11:25', '2026-03-29 15:11:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_02_10_154343_create_topics_table', 1),
(5, '2026_02_10_154344_create_posts_table', 1),
(6, '2026_02_10_154345_create_media_table', 1),
(7, '2026_02_10_154346_create_comments_table', 1),
(8, '2026_02_10_154346_create_like_comments_table', 1),
(9, '2026_02_10_154346_create_like_posts_table', 1),
(10, '2026_02_10_154347_create_favorites_table', 1),
(12, '2026_02_10_154347_create_search_histories_table', 1),
(13, '2026_02_10_154348_create_notifications_table', 1),
(14, '2026_02_10_154348_create_video_views_table', 1),
(15, '2026_02_10_154349_create_conversations_table', 1),
(16, '2026_02_10_154349_create_follows_table', 1),
(17, '2026_02_10_154350_create_conversation_users_table', 1),
(18, '2026_02_10_154350_create_messages_table', 1),
(19, '2026_02_10_164945_create_profiles_table', 1),
(21, '2026_03_22_151639_create_post_topic_table', 2),
(22, '2026_03_26_215356_create_message_media_table', 3),
(23, '2026_02_10_154347_create_reports_table', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `content`, `type`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 'có lượt like từ ', 'like', 1, '2026-03-30 04:42:31', '2026-03-30 04:44:48'),
(2, 1, '<strong>Stephan Kemmer</strong> đã bắt đầu theo dõi bạn.', 'follow', 1, '2026-03-30 06:51:27', '2026-03-30 06:51:37'),
(3, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn.', 'like', 1, '2026-03-30 06:51:47', '2026-03-30 07:14:18'),
(4, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn.', 'comment', 1, '2026-03-30 06:54:04', '2026-03-30 06:54:12'),
(5, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 07:23:05', '2026-03-30 07:23:09'),
(6, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:67', 'like', 1, '2026-03-30 07:24:02', '2026-03-30 07:24:08'),
(7, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:65', 'like', 1, '2026-03-30 14:42:37', '2026-03-30 14:42:53'),
(8, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:65', 'like', 1, '2026-03-30 14:43:35', '2026-03-30 14:43:39'),
(9, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:65', 'like', 1, '2026-03-30 14:44:12', '2026-03-30 14:44:21'),
(10, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:65', 'like', 1, '2026-03-30 14:46:08', '2026-03-30 14:50:59'),
(11, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:02:39', '2026-03-30 15:02:48'),
(12, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:03:12', '2026-03-30 15:03:24'),
(13, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:03:53', '2026-03-30 15:04:04'),
(14, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:04:21', '2026-03-30 15:04:48'),
(15, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:04:55', '2026-03-30 15:05:05'),
(16, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:05:26', '2026-03-30 15:05:56'),
(17, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:06:18', '2026-03-30 15:06:32'),
(18, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:07:13', '2026-03-30 15:07:40'),
(19, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:08:43', '2026-03-30 15:08:50'),
(20, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-30 15:23:51', '2026-03-30 15:23:54'),
(21, 1, '<strong>Stephan Kemmer</strong> đã bắt đầu theo dõi bạn. follow: 1', 'follow', 1, '2026-03-30 15:59:38', '2026-03-30 15:59:43'),
(22, 1, '<strong>Stephan Kemmer</strong> đã bắt đầu theo dõi bạn. follow:2', 'follow', 1, '2026-03-30 16:12:44', '2026-03-30 16:12:50'),
(23, 1, '<strong>Stephan Kemmer</strong> đã bắt đầu theo dõi bạn. follow:2', 'follow', 1, '2026-03-30 16:20:30', '2026-03-30 16:20:33'),
(24, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 1, '2026-03-30 16:21:36', '2026-03-30 16:21:42'),
(25, 1, '<strong>Stephan Kemmer</strong> đã thích bình luận của bạn. likecomment:584', 'likecomment', 1, '2026-03-30 17:16:56', '2026-03-30 17:20:15'),
(26, 1, '<strong>Stephan Kemmer</strong> đã bắt đầu theo dõi bạn. follow:2', 'follow', 1, '2026-03-30 17:20:38', '2026-03-30 17:20:41'),
(27, 1, '<strong>Stephan Kemmer</strong> đã thích bình luận của bạn. likecomment:584', 'likecomment', 1, '2026-03-30 17:22:52', '2026-03-30 17:28:09'),
(28, 1, '<strong>Stephan Kemmer</strong> đã thích bình luận của bạn. likecomment:584', 'likecomment', 1, '2026-03-30 17:36:29', '2026-03-30 17:36:45'),
(29, 1, '<strong>Stephan Kemmer</strong> đã thích bình luận của bạn. likecomment:584', 'likecomment', 1, '2026-03-30 17:54:53', '2026-03-30 18:04:13'),
(30, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn.', 'comment', 1, '2026-03-30 18:09:19', '2026-03-30 18:09:23'),
(31, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn.', 'comment', 1, '2026-03-31 08:28:52', '2026-03-31 08:29:05'),
(32, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn. comment:589', 'comment', 1, '2026-03-31 08:30:20', '2026-03-31 08:31:07'),
(33, 1, '<strong>Stephan Kemmer</strong> đã thích bình luận của bạn. likecomment:584', 'likecomment', 1, '2026-03-31 08:31:20', '2026-03-31 14:29:09'),
(34, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-31 08:34:51', '2026-03-31 14:29:07'),
(35, 1, '<strong>Stephan Kemmer</strong> đã thích bài viết của bạn. post:69', 'like', 1, '2026-03-31 08:35:48', '2026-03-31 14:29:06'),
(36, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn. comment:590', 'comment', 1, '2026-03-31 14:33:01', '2026-03-31 14:33:09'),
(37, 1, '<strong>Stephan Kemmer</strong> đã phản hồi bình luận của bạn. comment:591', 'comment', 1, '2026-03-31 14:33:17', '2026-03-31 14:33:23'),
(38, 1, '<strong>Stephan Kemmer</strong> đã phản hồi bình luận trong bài viết của bạn. comment:598', 'comment', 1, '2026-03-31 14:46:44', '2026-03-31 14:47:13'),
(39, 1, '<strong>Stephan Kemmer</strong> đã bình luận bài viết của bạn. comment:599', 'comment', 1, '2026-03-31 14:47:05', '2026-03-31 14:47:10'),
(40, 2, '<strong>Stephan Kemmer</strong> đã phản hồi bình luận của bạn. comment:599', 'comment', 1, '2026-03-31 14:47:05', '2026-03-31 15:35:51'),
(41, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-01 15:54:44', '2026-04-05 15:55:37'),
(42, 12, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:4', 'like', 0, '2026-04-05 09:51:13', '2026-04-05 09:51:13'),
(43, 1, '<strong>Katrina Bogisich</strong> đã thích bình luận của bạn. likecomment:270', 'likecomment', 0, '2026-04-05 14:56:39', '2026-04-05 14:56:39'),
(44, 12, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:03:31', '2026-04-05 15:03:31'),
(45, 8, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:06:51', '2026-04-05 15:06:51'),
(46, 8, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:07:04', '2026-04-05 15:07:04'),
(47, 8, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:07:06', '2026-04-05 15:07:06'),
(48, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:07:31', '2026-04-05 15:07:31'),
(49, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:07:42', '2026-04-05 15:07:42'),
(50, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:07:58', '2026-04-05 15:07:58'),
(51, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:08:29', '2026-04-05 15:08:29'),
(52, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:08:42', '2026-04-05 15:08:42'),
(53, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:09:10', '2026-04-05 15:09:10'),
(54, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:09:31', '2026-04-05 15:09:31'),
(55, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:11:36', '2026-04-05 15:11:36'),
(56, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:12:01', '2026-04-05 15:12:01'),
(57, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:12:45', '2026-04-05 15:12:45'),
(58, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:13:07', '2026-04-05 15:13:07'),
(59, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:15:09', '2026-04-05 15:15:09'),
(60, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:15:29', '2026-04-05 15:15:29'),
(61, 8, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:15:42', '2026-04-05 15:15:42'),
(62, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:16:10', '2026-04-05 15:16:10'),
(63, 2, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:16:14', '2026-04-05 15:16:14'),
(64, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:18:01', '2026-04-05 15:18:01'),
(65, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:18:11', '2026-04-05 15:18:11'),
(66, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:19:24', '2026-04-05 15:19:24'),
(67, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:19:26', '2026-04-05 15:19:26'),
(68, 2, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:19:27', '2026-04-05 15:19:27'),
(69, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:19:48', '2026-04-05 15:19:48'),
(70, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 1, '2026-04-05 15:19:52', '2026-04-05 16:04:11'),
(71, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 1, '2026-04-05 15:20:01', '2026-04-05 16:04:08'),
(72, 8, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:20:36', '2026-04-05 15:20:36'),
(73, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:20:39', '2026-04-05 15:20:39'),
(74, 10, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-05 15:20:41', '2026-04-05 15:20:41'),
(75, 12, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 15:55:47', '2026-04-05 15:55:47'),
(76, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 1, '2026-04-05 16:06:05', '2026-04-05 16:06:13'),
(77, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:40', 'like', 1, '2026-04-05 16:07:31', '2026-04-05 16:08:52'),
(78, 1, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 16:09:05', '2026-04-05 16:09:05'),
(79, 14, '<strong>Katrina Bogisich</strong> đã bắt đầu theo dõi bạn. follow:3', 'follow', 0, '2026-04-05 16:10:55', '2026-04-05 16:10:55'),
(80, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:11:12', '2026-04-05 16:12:02'),
(81, 1, '<strong>Katrina Bogisich</strong> đã thích bình luận của bạn. likecomment:262', 'likecomment', 1, '2026-04-05 16:11:31', '2026-04-05 16:14:10'),
(82, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:51:17', '2026-04-05 16:55:01'),
(83, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:55:08', '2026-04-05 16:56:53'),
(84, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:55:50', '2026-04-05 16:56:51'),
(85, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:56:18', '2026-04-05 16:56:49'),
(86, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:56:43', '2026-04-05 16:56:47'),
(87, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:56:58', '2026-04-05 16:57:17'),
(88, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:57:08', '2026-04-05 16:57:14'),
(89, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 16:58:06', '2026-04-05 16:59:10'),
(90, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 0, '2026-04-05 16:59:19', '2026-04-05 16:59:19'),
(91, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 0, '2026-04-05 16:59:23', '2026-04-05 16:59:23'),
(92, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 17:00:18', '2026-04-05 17:26:03'),
(93, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:3', 'like', 1, '2026-04-05 17:01:04', '2026-04-05 17:25:55'),
(94, 8, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:6', 'like', 0, '2026-04-06 15:40:22', '2026-04-06 15:40:22'),
(95, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bình luận bài viết của bạn. comment:604', 'comment', 0, '2026-04-06 15:49:44', '2026-04-06 15:49:44'),
(96, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:78', 'like', 0, '2026-04-06 15:49:50', '2026-04-06 15:49:50'),
(97, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bình luận bài viết của bạn. comment:605', 'comment', 0, '2026-04-06 15:50:04', '2026-04-06 15:50:04'),
(98, 11, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:23', 'like', 0, '2026-04-06 16:13:14', '2026-04-06 16:13:14'),
(99, 11, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:22', 'like', 0, '2026-04-06 16:13:16', '2026-04-06 16:13:16'),
(100, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:78', 'like', 0, '2026-04-06 16:13:41', '2026-04-06 16:13:41'),
(101, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã bình luận bài viết của bạn. comment:606', 'comment', 0, '2026-04-06 16:13:46', '2026-04-06 16:13:46'),
(102, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-06 16:13:50', '2026-04-06 16:13:50'),
(103, 4, '<strong>Dr. Garret Baumbach Jr.</strong> đã bắt đầu theo dõi bạn. follow:1', 'follow', 0, '2026-04-06 16:13:51', '2026-04-06 16:13:51'),
(104, 19, '<strong>Dr. Garret Baumbach Jr.</strong> đã bình luận bài viết của bạn. comment:608', 'comment', 0, '2026-04-06 16:25:20', '2026-04-06 16:25:20'),
(105, 3, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:78', 'like', 0, '2026-04-06 16:33:03', '2026-04-06 16:33:03'),
(106, 2, '<strong>Dr. Garret Baumbach Jr.</strong> đã thích bài viết của bạn. post:10', 'like', 0, '2026-04-06 16:57:27', '2026-04-06 16:57:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('ducp552244@gmail.com', '$2y$12$XZb3jPxNy//frkGJDTY.MeCvNMaUmerR81SiVtkvQInHtEG2VuhlS', '2026-03-10 23:23:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `is_comment_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `shared_post_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) DEFAULT 'show'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `topic_id`, `content`, `is_comment_enabled`, `pinned`, `shared_post_id`, `created_at`, `updated_at`, `status`) VALUES
(2, 14, NULL, 'Dolore autem saepe aspernatur iste. Eos ab et et voluptatum et reiciendis optio consequatur. Et et architecto aliquam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-04-05 17:16:13', 'hidden'),
(3, 3, NULL, 'Non consequatur est rerum consequatur doloremque voluptatum ut. Pariatur aut qui nemo qui ipsum rerum voluptates. Laborum id maxime asperiores eveniet error nesciunt. Voluptatem nulla earum velit omnis.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-04-05 17:16:10', 'hidden'),
(4, 12, NULL, 'Id quia eligendi consequatur velit dolorum. Tempora aut cumque autem aut ut enim dolor. Consequatur numquam autem recusandae autem sint. Esse et sit quia alias doloremque.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(5, 7, NULL, 'Illo recusandae aut reprehenderit labore aut sed. Voluptas accusantium deleniti doloribus quia ut dolor. Maxime est aperiam eveniet aliquid omnis sed aut.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(6, 8, NULL, 'Blanditiis sed aspernatur pariatur nihil quia deleniti. Quisquam quo in et quas. Qui alias in harum.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(10, 2, NULL, 'Reiciendis laboriosam ex quo vel adipisci fugiat aliquid. Quaerat et sint et sit ab molestiae. In eveniet fugiat nostrum voluptatem assumenda ut ut.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(11, 19, NULL, 'Atque dolores et ut tenetur sed non id. Explicabo quidem qui alias delectus dolor voluptas repudiandae aliquam. Quidem commodi illum voluptas tempore voluptatem sint.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(12, 9, NULL, 'Earum porro minus rerum veniam quas alias. Sit repellat veniam ducimus ipsum asperiores nobis. Aut cumque voluptas aut laboriosam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(13, 20, NULL, 'Officiis quis quis non consequatur aut et omnis. Quas reiciendis sit ex quo aperiam quibusdam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(14, 12, NULL, 'Sapiente in ut reiciendis consequatur. Quia id suscipit repudiandae rerum perspiciatis soluta ducimus. Explicabo est voluptas itaque exercitationem dolores quia. Distinctio excepturi dicta aut assumenda molestiae. Dolorem reprehenderit quasi odit nisi est at.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(15, 14, NULL, 'Neque non ex voluptas et tempore est suscipit quos. Alias suscipit excepturi cupiditate odit nihil. Temporibus velit fugiat quia iste. Hic assumenda est possimus autem labore. Qui inventore unde qui et a.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(16, 2, NULL, 'Debitis eveniet excepturi assumenda ratione aliquid voluptatibus. Perferendis velit commodi officia est. Mollitia pariatur impedit dolores. Quisquam illum est sapiente sunt repellat reprehenderit id.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(17, 9, NULL, 'Mollitia voluptas veniam quisquam in similique rerum. Expedita similique voluptate reiciendis. Officia blanditiis quas optio dolorem rerum laboriosam quaerat.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(18, 19, NULL, 'Iste illum labore blanditiis quia voluptatem. Ullam eveniet aut quia sed magni assumenda. Eos ut aperiam perspiciatis alias.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(19, 10, NULL, 'Est sequi fugit accusantium distinctio et temporibus. Magni cupiditate sunt perferendis. Doloremque nostrum corrupti dolorem.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(20, 9, NULL, 'Et quia molestiae earum minus odit ipsa facere. Ratione nihil libero nulla. Aut distinctio rerum et consequuntur consequatur perferendis omnis. Et voluptas deleniti et. Libero dolore neque sunt recusandae quibusdam officiis tenetur.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(21, 6, NULL, 'Molestiae eum maxime earum non animi eligendi. Ut tempora et nemo quia. Deleniti possimus suscipit aliquam magni reprehenderit.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(22, 11, NULL, 'Laboriosam vitae cum tempore sunt voluptate sit. Et aspernatur modi omnis ut tempora perspiciatis quis et. Nulla voluptatum molestiae sed maxime.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(23, 11, NULL, 'Aut tenetur exercitationem optio et culpa cupiditate. Est dolores eveniet voluptatem asperiores non provident ut. Ut aut qui sint ea. Culpa nulla aut nulla architecto delectus ab quo.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(24, 9, NULL, 'Debitis error et inventore labore dolorum atque. Harum cum sit quam hic asperiores nobis. Quis nihil atque dolor illo. Architecto quibusdam officiis nisi adipisci qui in quidem.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(25, 20, NULL, 'Et id ipsum soluta. Voluptas asperiores aliquam impedit vero sequi libero ut. Modi reiciendis qui fugiat et molestiae repellendus. Doloremque cum amet est doloremque.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(26, 8, NULL, 'Facilis quae voluptatem est dicta. Iste eaque aut molestiae est est saepe. Culpa magni consequuntur quia dolor reiciendis distinctio. Est ea officia dignissimos impedit. Nemo unde corrupti eveniet magni.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(27, 7, NULL, 'Nihil odio voluptas sed nihil distinctio. Provident accusantium dolore saepe laudantium laudantium aperiam temporibus. Qui repellat quis id sed quisquam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(28, 17, NULL, 'Consequatur ut expedita architecto ipsum illum provident. Quia eum voluptatem illum ut. Sunt asperiores numquam magnam omnis et.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(29, 16, NULL, 'Dolor aut error praesentium provident. Ab nemo veritatis autem deserunt. Eum quas sint cupiditate error ducimus est labore numquam. Deleniti officiis sit fuga perferendis omnis perspiciatis exercitationem alias.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(30, 7, NULL, 'Nobis ad excepturi architecto libero nostrum qui blanditiis. Harum inventore in vel aut. Vero facilis autem non aliquam facere nihil. Hic sint dolore vitae omnis eos doloribus quas labore.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(31, 5, NULL, 'Fugiat sed est sunt dicta quasi. Corporis nulla est et ad. Sed molestiae enim non sunt iure vel.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(32, 1, NULL, 'Cupiditate doloremque aperiam nam qui illo aut laudantium fuga. Assumenda voluptates sed repellendus veniam aspernatur quis. Quas neque eveniet excepturi ea.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(33, 17, NULL, 'Neque et esse repudiandae aut deserunt quod ut. Facilis repellendus repellendus accusantium dolores labore. Corrupti rerum autem nulla. Quo illo velit consequatur eius ullam autem. Dolor dolor asperiores fugit vel delectus.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(34, 14, NULL, 'Itaque nihil occaecati velit placeat amet. Rerum numquam sint voluptatem consequatur.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(35, 11, NULL, 'Rerum et sunt unde. Voluptatum consectetur dolor dolor cum distinctio est. Quibusdam aut perferendis aut hic est rerum.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(36, 2, NULL, 'Ad voluptatem optio itaque tenetur alias harum. Amet unde temporibus earum blanditiis dolorem consequatur. Dicta blanditiis porro voluptas dolores quam quam perferendis. Eos totam explicabo veniam velit in hic animi.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(37, 17, NULL, 'Quo laborum fugiat dolorem. Eveniet culpa temporibus eum accusantium reiciendis numquam ratione temporibus. Ut quibusdam enim quod molestias ab ut.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(38, 5, NULL, 'Voluptate qui dolore illo possimus rem. Provident tenetur error iusto distinctio consequatur cum. Sit et ad atque odio minima dolorem.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(39, 15, NULL, 'Quia fugiat veniam tenetur est est ipsam itaque tempore. Quaerat eligendi voluptate corrupti quo voluptates cupiditate enim est. Quaerat repellat autem sequi rerum temporibus nihil. Nesciunt quia corrupti quaerat blanditiis culpa.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(40, 3, NULL, 'Qui beatae fugit aut qui consequatur sint voluptatibus. Temporibus velit commodi illo repudiandae. Placeat sit sint praesentium ea reprehenderit ipsam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(41, 17, NULL, 'Optio a quam quia quae odit. Amet omnis delectus et saepe maiores sequi. Necessitatibus soluta distinctio ad ut dolorem. Animi aperiam tempore aut est amet sed.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(42, 1, NULL, 'Eaque quaerat tempora iure in quisquam. Voluptas aut ipsam architecto doloribus asperiores et ea corrupti.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(43, 6, NULL, 'Distinctio iure molestiae magni sed voluptatum ut ipsam repudiandae. Esse magni vel et impedit sed autem praesentium. Deleniti amet cupiditate vero modi.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(44, 1, NULL, 'Corporis consequatur ut maiores. Dignissimos eum dolorum magni in. Velit dolores et reprehenderit ut nesciunt rerum ipsam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(45, 18, NULL, 'Repellendus assumenda quo sint et eos non odit. Possimus non quo repellendus omnis. Quis quia similique iste eum aperiam.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(46, 10, NULL, 'Laborum doloribus non et dolorem. Quidem sapiente impedit dolorem est temporibus blanditiis. Tempore aspernatur non quam exercitationem repellat. Vel laudantium sit autem vel.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(47, 3, NULL, 'Culpa autem cum illo ipsam nihil molestias. Atque quia sed et quibusdam. Id qui aspernatur velit porro fuga qui natus.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(48, 13, NULL, 'Soluta asperiores minus esse saepe ex quae. Blanditiis consequuntur ducimus fuga vel rerum placeat vero. Quasi minus facere voluptates laborum dolor. Perferendis iure beatae similique ad porro exercitationem quae.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(49, 5, NULL, 'Nemo velit sit fuga quas maiores. Non expedita deleniti velit aut distinctio atque error. Quia qui sapiente autem omnis sed repellat velit. Voluptate et aliquam iste nobis enim.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(50, 19, NULL, 'Voluptatum maxime repellendus delectus quia repellat facere. Minima natus vel similique praesentium id qui soluta. Non minus et perspiciatis voluptatum. Perspiciatis dolorem sed autem velit earum aut quasi.', 1, 0, NULL, '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'show'),
(54, 1, 1, 'sdfg ngàn để baop mênh mông ngắt ngây sưởi ấm con tin anh hao gầy về trong vệt nắng cuối trời ngắt ngaayyyyy/ tình yêu thì đôiu khioa sdahjsbd c absdbax casbdjbasd acabxjcasbc asbdashbdasc dsdfg ngàn để baop mênh mông ngắt ngây sưởi ấm con tin anh hao gầy về trong vệt nắng cuối trời ngắt ngaayyyyy/ tình yêu thì đôiu khioa sdahjsbd c absdbax casbdjbasd acabxjcasbc asbdashbdasc dsdfg ngàn để baop mênh mông ngắt ngây sưởi ấm con tin anh hao gầy về trong vệt nắng cuối trời ngắt ngaayyyyy/ tình yêu thì đôiu khioa sdahjsbd c absdbax casbdjbasd acabxjcasbc asbdashbdasc d', 1, 0, NULL, '2026-03-05 09:46:10', '2026-04-06 15:48:31', 'show'),
(56, 1, 1, 'xdasdasdasd', 1, 0, NULL, '2026-03-05 09:50:23', '2026-04-05 16:05:39', 'hidden'),
(57, 1, 1, 'hello casc cascas', 1, 0, NULL, '2026-03-11 09:22:57', '2026-04-04 16:09:37', 'hidden'),
(78, 3, NULL, 'ádasdasdad', 1, 0, NULL, '2026-04-05 17:27:14', '2026-04-05 17:27:14', 'show');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `post_topic`
--

CREATE TABLE `post_topic` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `topic_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `post_topic`
--

INSERT INTO `post_topic` (`id`, `post_id`, `topic_id`) VALUES
(7, 78, 86);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `profiles`
--

CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `profiles`
--

INSERT INTO `profiles` (`id`, `user_id`, `display_name`, `avatar`, `bio`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dr. Garret Baumbach Jr.', 'default-avatar.png', 'Unde consequatur aliquam libero aut consequatur.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(2, 2, 'Nguyễn Hoàng Long', 'default-avatar.png', 'A product builder by craft, and a two-wheel wanderer by heart', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(3, 3, 'Lê Tuấn', 'default-avatar.png', 'Hello World', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(4, 4, 'Dao Duy Hung', 'default-avatar.png', '', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(5, 5, 'Minh Nguyen', 'default-avatar.png', 'Tập tành custom', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(6, 6, 'Minh Gundam', 'default-avatar.png', 'I love Gundam', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(7, 7, 'Liễu Như Yên', 'default-avatar.png', 'Thích nấu ăn, thích mô hình', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(8, 8, 'Duy Nguyễn', 'default-avatar.png', '0,4% luck', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(9, 9, 'Miss Bianka Hodkiewicz', 'default-avatar.png', 'Rerum et eum corrupti cupiditate aut.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(10, 10, 'Dr. Rae Hamill', 'default-avatar.png', 'Voluptatem et est ut maiores in maxime in officia.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(11, 11, 'Ms. Elta Jaskolski II', 'default-avatar.png', 'Quo vel iure velit distinctio.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(12, 12, 'Dr. Amir Hauck I', 'default-avatar.png', 'Maiores dicta fugiat rerum eum.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(13, 13, 'Adell Corwin', 'default-avatar.png', 'Consectetur deleniti asperiores a.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(14, 14, 'Delia Kihn', 'default-avatar.png', 'Sit animi ea omnis magnam repellendus.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(15, 15, 'Miss Nina Raynor V', 'default-avatar.png', 'Velit qui placeat non est quas.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(16, 16, 'Prof. Allene Farrell IV', 'default-avatar.png', 'Voluptas earum autem consequatur.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(17, 17, 'Delilah Batz', 'default-avatar.png', 'Totam omnis et eveniet odit a quasi inventore.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(18, 18, 'Mallie Will', 'default-avatar.png', 'Placeat quod minus voluptas quo voluptate et.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(19, 19, 'Aditya Cummerata', 'default-avatar.png', 'Iusto cumque illum omnis nesciunt.', '2026-03-05 08:50:47', '2026-03-05 08:50:47'),
(20, 20, 'Miss Bonnie Beahan Sr.', 'default-avatar.png', 'Laborum perspiciatis suscipit et autem dolor dolores repudiandae fugit.', '2026-03-05 08:50:47', '2026-03-05 08:50:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `target_id`, `target_type`, `category`, `reason`, `status`, `admin_note`, `resolved_by`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 56, 'App\\Models\\Post', 'Spam', NULL, 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 16:05:39', '2026-04-04 17:28:46', '2026-04-05 16:05:39'),
(2, 1, 89, 'App\\Models\\Comment', 'Spam', NULL, 'resolved', 'Đã xử lý ẩn nội dung', 1, '2026-04-04 17:35:11', '2026-04-04 17:34:53', '2026-04-04 17:35:11'),
(3, 1, 3, 'App\\Models\\User', 'FakeNews', NULL, 'resolved', 'Đã xử lý ẩn nội dung', 1, '2026-04-04 17:35:57', '2026-04-04 17:35:47', '2026-04-04 17:35:57'),
(4, 1, 54, 'App\\Models\\Post', 'Spam', NULL, 'dismissed', NULL, NULL, NULL, '2026-04-04 17:37:08', '2026-04-06 15:48:31'),
(5, 1, 54, 'App\\Models\\Post', 'Violence', NULL, 'dismissed', NULL, NULL, NULL, '2026-04-04 17:38:52', '2026-04-06 15:48:31'),
(6, 1, 54, 'App\\Models\\Post', 'Harassment', 'Quấy rối hoặc bắt nạt', 'dismissed', NULL, NULL, NULL, '2026-04-04 17:42:45', '2026-04-06 15:48:31'),
(7, 1, 54, 'App\\Models\\Post', 'Other', 'lừa đâỏ', 'dismissed', NULL, NULL, NULL, '2026-04-04 17:46:16', '2026-04-06 15:48:31'),
(8, 1, 2, 'App\\Models\\Post', 'Other', 'lừa', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:13', '2026-04-04 17:46:51', '2026-04-05 17:16:13'),
(9, 1, 2, 'App\\Models\\Post', 'Violence', 'Tính bạo lực / Kích động', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:13', '2026-04-04 17:47:10', '2026-04-05 17:16:13'),
(10, 1, 2, 'App\\Models\\Post', 'Violence', 'Tính bạo lực / Kích động', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:13', '2026-04-04 17:47:15', '2026-04-05 17:16:13'),
(11, 1, 2, 'App\\Models\\Post', 'Violence', 'Tính bạo lực / Kích động', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:13', '2026-04-04 17:47:23', '2026-04-05 17:16:13'),
(12, 1, 56, 'App\\Models\\Post', 'Spam', 'Nội dung rác (Spam)', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 16:05:39', '2026-04-05 15:55:15', '2026-04-05 16:05:39'),
(13, 1, 2, 'App\\Models\\Post', 'Spam', 'Nội dung rác (Spam)', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:13', '2026-04-05 17:15:53', '2026-04-05 17:16:13'),
(14, 1, 3, 'App\\Models\\Post', 'Nudity', 'Ảnh khỏa thân hoặc khiêu dâm', 'resolved', 'Đã ẩn nội dung', 1, '2026-04-05 17:16:10', '2026-04-05 17:15:58', '2026-04-05 17:16:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `search_history`
--

CREATE TABLE `search_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `search_history`
--

INSERT INTO `search_history` (`id`, `user_id`, `keyword`, `created_at`, `updated_at`) VALUES
(13, 1, 'gundam', '2026-03-25 08:23:04', '2026-04-06 16:13:28'),
(14, 1, 'kkk', '2026-03-25 15:31:48', '2026-03-26 17:33:23'),
(15, 1, 'g', '2026-03-25 16:44:09', '2026-03-25 16:44:09'),
(16, 1, 'gu', '2026-03-25 16:44:10', '2026-03-25 17:17:56'),
(17, 1, 'gun', '2026-03-25 16:44:10', '2026-04-06 15:47:47'),
(18, 1, 'gund', '2026-03-25 16:44:13', '2026-04-06 16:13:56'),
(19, 1, 'gunda', '2026-03-25 16:44:14', '2026-03-25 16:44:14'),
(20, 1, 'model kit', '2026-03-25 16:46:18', '2026-03-25 16:47:22'),
(22, 1, 'p', '2026-03-26 17:33:12', '2026-03-26 17:33:12'),
(23, 1, 'pp', '2026-03-26 17:33:17', '2026-03-26 17:33:17'),
(24, 1, 'qui', '2026-03-26 17:34:01', '2026-03-26 17:34:17'),
(25, 1, 'beth mc', '2026-03-27 16:49:37', '2026-03-27 16:49:37'),
(26, 1, 'herta', '2026-03-28 16:53:21', '2026-04-04 06:55:58'),
(27, 1, 'her', '2026-03-28 17:26:10', '2026-04-01 17:11:39'),
(28, 1, 'chào', '2026-04-01 17:11:52', '2026-04-03 15:09:10'),
(29, 1, 'chào\'', '2026-04-01 17:12:17', '2026-04-01 17:13:51'),
(30, 1, 'hả', '2026-04-03 15:02:49', '2026-04-03 15:04:49'),
(31, 1, 'dr', '2026-04-04 05:52:01', '2026-04-06 15:46:57'),
(32, 1, 'đr', '2026-04-04 07:36:22', '2026-04-04 07:36:22'),
(33, 3, 'dr', '2026-04-05 15:15:40', '2026-04-05 15:16:04'),
(34, 1, 'search', '2026-04-05 15:20:16', '2026-04-05 15:20:16'),
(35, 1, 'mel', '2026-04-05 16:06:02', '2026-04-05 16:09:22'),
(36, 1, 'eligendi', '2026-04-06 15:40:06', '2026-04-06 15:40:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0p3G3dn2wiyvCo9pd21WekjCtezQrPLx6ta5GKKs', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWHpqSzRRVXd2RkJMNFBRNkw4bHVyWFNJaFVNRFRSbmgyZ3g3aWFWTiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjE2OiJodHRwOi8vMTI3LjAuMC4xIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=', 1775495494),
('VWgQMTwfX4wQI6EEOAMZxIJIkzXXQ2BL9CVS8l6a', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSUNuNEt1eFZSMlljeW5kN1JvaFFIM0w3SDdwZVpZRWsxb2p3Vkg1eSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjE2OiJodHRwOi8vMTI3LjAuMC4xIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1775494943);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `topics`
--

CREATE TABLE `topics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `topics`
--

INSERT INTO `topics` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'gundam', '2026-03-05 16:36:23', '2026-03-17 10:24:55'),
(53, 'ádasd', '2026-03-18 03:29:46', '2026-03-18 03:29:46'),
(56, 'ádasdsa', '2026-03-18 03:30:35', '2026-03-18 03:30:35'),
(57, 'hahhhhh', '2026-03-18 03:31:24', '2026-03-18 03:31:24'),
(58, 'ádasdasdasds', '2026-03-18 03:31:38', '2026-03-18 03:31:38'),
(60, 'sdasdadad', '2026-03-18 03:32:33', '2026-03-18 03:32:33'),
(66, 'sdasdadasdad', '2026-03-18 03:34:46', '2026-03-18 03:34:46'),
(67, 'deeeeeeeeeeee', '2026-03-18 03:36:11', '2026-03-18 03:36:11'),
(69, 'admin', '2026-03-18 08:47:02', '2026-03-18 08:47:02'),
(73, 'admin', '2026-03-18 08:47:11', '2026-03-18 08:47:11'),
(77, 'admin', '2026-03-18 09:00:01', '2026-03-18 09:00:01'),
(78, 'ducp', '2026-03-20 09:03:16', '2026-03-20 09:03:16'),
(79, 'model kit', '2026-03-22 08:10:28', '2026-03-22 08:10:28'),
(80, 'kit girl', '2026-03-22 08:26:06', '2026-03-22 08:26:06'),
(81, 'kit nữ', '2026-03-22 08:28:24', '2026-03-22 08:28:24'),
(82, 'metal build', '2026-03-22 08:28:24', '2026-03-22 08:28:24'),
(83, 'high model', '2026-03-22 08:28:24', '2026-03-22 08:28:24'),
(85, 'kkkk', '2026-03-22 08:37:57', '2026-03-22 08:37:57'),
(86, 'gund', '2026-04-05 17:27:14', '2026-04-05 17:27:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user',
  `status` varchar(255) DEFAULT 'show'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `status`) VALUES
(1, 'Ottilie Moen', 'vgrady@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', '8oCaUQYCTSlaQzBhDbxjzWORNlfsBvrkyvz1N8CqaXXIF1P474wKgG35sBYF', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'admin', 'show'),
(2, 'Herta Green', 'lang.annabelle@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'MhR9DVJlPFRuLTCJ1woh45L1ymIZTQ8umMId6pM3rageJmEcKAsqKc2eycIL', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'hidden'),
(3, 'Melvin Bednar', 'rzboncak@example.net', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'uJ7h5fxd58ydW67136VNvEcRQ0SIMaiufnkCPgbon3hzdHrOrbSkjjvQuz6X', '2026-03-05 08:50:47', '2026-04-04 17:35:57', 'user', 'hidden'),
(4, 'Dena Hessel', 'ilynch@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'mLiPLkkGus', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(5, 'Concepcion Tremblay II', 'bcummerata@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', '3U2gBpCsLe', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(6, 'Mrs. Stephanie Rosenbaum', 'amara.hickle@example.net', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'JjkipBa0i8', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(7, 'Khalil Schneider DDS', 'jodie64@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'GSYP0orBLi', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(8, 'Miss Blanca Bartoletti II', 'xschuppe@example.net', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', '7uCoeYLwa3', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(9, 'Torey Bernier', 'mustafa.gislason@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'Q8ioMrh2ER', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(10, 'Mr. Buford Lebsack', 'trodriguez@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'utk0ijDhsr', '2026-03-05 08:50:47', '2026-04-04 16:31:41', 'user', 'show'),
(11, 'Dr. Katarina Hahn Jr.', 'kelsi67@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'cWZwUr6QvS', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(12, 'Dr. Casimir Funk', 'awilkinson@example.net', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'sjjTenB5Un', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(13, 'Annabel Moen', 'aylin15@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', '4ci92c4u56', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(14, 'Dr. Pamela Keeling DVM', 'ujacobson@example.net', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'lKDhwmlvRa', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(15, 'Dr. Alyson Lang', 'goodwin.trisha@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'Laz1hLH7K8', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(16, 'Rae Tremblay', 'ray.runte@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'TKDHTBzUT4', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(17, 'Destany Dicki III', 'stark.benedict@example.org', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'MkxF7iB17i', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(18, 'Sherwood Medhurst', 'pfannerstill.velma@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'CDIVHm2Ppk', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(19, 'Itzel Emard', 'rgulgowski@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'Pr43DA5cWr', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show'),
(20, 'Dr. Jairo Larson', 'grant.edwin@example.com', '2026-03-05 08:50:47', '$2y$12$6w3EfjYejYnY2Iflb.WHs.K.Mvx8SGXBeyUYBjNeeEMslPemyg/x2', 'mM574tS63N', '2026-03-05 08:50:47', '2026-03-05 08:50:47', 'user', 'show');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `video_views`
--

CREATE TABLE `video_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `media_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_post_id_foreign` (`post_id`),
  ADD KEY `comments_parent_comment_id_foreign` (`parent_comment_id`);

--
-- Chỉ mục cho bảng `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `conversation_users`
--
ALTER TABLE `conversation_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `conversation_users_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  ADD KEY `conversation_users_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `favorites_user_id_post_id_unique` (`user_id`,`post_id`),
  ADD KEY `favorites_post_id_foreign` (`post_id`);

--
-- Chỉ mục cho bảng `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follows_follower_id_following_id_unique` (`follower_id`,`following_id`),
  ADD KEY `follows_following_id_foreign` (`following_id`);

--
-- Chỉ mục cho bảng `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Chỉ mục cho bảng `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `like_comments`
--
ALTER TABLE `like_comments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `like_comments_user_id_comment_id_unique` (`user_id`,`comment_id`),
  ADD KEY `like_comments_comment_id_foreign` (`comment_id`);

--
-- Chỉ mục cho bảng `like_posts`
--
ALTER TABLE `like_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `like_posts_user_id_post_id_unique` (`user_id`,`post_id`),
  ADD KEY `like_posts_post_id_foreign` (`post_id`);

--
-- Chỉ mục cho bảng `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_post_id_foreign` (`post_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`);

--
-- Chỉ mục cho bảng `message_media`
--
ALTER TABLE `message_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_message_media_message` (`message_id`);

--
-- Chỉ mục cho bảng `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Chỉ mục cho bảng `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posts_user_id_foreign` (`user_id`),
  ADD KEY `posts_topic_id_foreign` (`topic_id`),
  ADD KEY `posts_shared_post_id_foreign` (`shared_post_id`);

--
-- Chỉ mục cho bảng `post_topic`
--
ALTER TABLE `post_topic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post_topic_post` (`post_id`),
  ADD KEY `fk_post_topic_topic` (`topic_id`);

--
-- Chỉ mục cho bảng `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profiles_user_id_unique` (`user_id`);

--
-- Chỉ mục cho bảng `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `search_history_user_id_foreign` (`user_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Chỉ mục cho bảng `topics`
--
ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Chỉ mục cho bảng `video_views`
--
ALTER TABLE `video_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `video_views_user_id_media_id_unique` (`user_id`,`media_id`),
  ADD KEY `video_views_media_id_foreign` (`media_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=609;

--
-- AUTO_INCREMENT cho bảng `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT cho bảng `conversation_users`
--
ALTER TABLE `conversation_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `follows`
--
ALTER TABLE `follows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT cho bảng `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT cho bảng `like_comments`
--
ALTER TABLE `like_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `like_posts`
--
ALTER TABLE `like_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=268;

--
-- AUTO_INCREMENT cho bảng `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=264;

--
-- AUTO_INCREMENT cho bảng `message_media`
--
ALTER TABLE `message_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT cho bảng `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT cho bảng `post_topic`
--
ALTER TABLE `post_topic`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `topics`
--
ALTER TABLE `topics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT cho bảng `video_views`
--
ALTER TABLE `video_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_parent_comment_id_foreign` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `conversation_users`
--
ALTER TABLE `conversation_users`
  ADD CONSTRAINT `conversation_users_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_follower_id_foreign` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_following_id_foreign` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `like_comments`
--
ALTER TABLE `like_comments`
  ADD CONSTRAINT `like_comments_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `like_posts`
--
ALTER TABLE `like_posts`
  ADD CONSTRAINT `like_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `like_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `message_media`
--
ALTER TABLE `message_media`
  ADD CONSTRAINT `fk_message_media_message` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_shared_post_id_foreign` FOREIGN KEY (`shared_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_topic_id_foreign` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `post_topic`
--
ALTER TABLE `post_topic`
  ADD CONSTRAINT `fk_post_topic_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_post_topic_topic` FOREIGN KEY (`topic_id`) REFERENCES `topics` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `search_history`
--
ALTER TABLE `search_history`
  ADD CONSTRAINT `search_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `video_views`
--
ALTER TABLE `video_views`
  ADD CONSTRAINT `video_views_media_id_foreign` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_views_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
