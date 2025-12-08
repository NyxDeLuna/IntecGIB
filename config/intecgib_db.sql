-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-12-2025 a las 19:33:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `intecgib_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado_proyecto` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `projects`
--

INSERT INTO `projects` (`id`, `nombre`, `descripcion`, `estado_proyecto`, `created_at`, `updated_at`) VALUES
(1, 'Smart Office Building', 'Complete automation system for corporate office', 1, '2025-11-28 14:18:01', '2025-11-28 14:18:01'),
(2, 'Luxury Villa Project', 'Residential automation in luxury villa', 2, '2025-11-28 14:18:01', '2025-11-28 14:18:01'),
(3, 'Future Shopping Mall', 'Planned automation for new shopping center', 3, '2025-11-28 14:18:01', '2025-12-08 15:17:08'),
(7, 'CitiHome', 'New luxury building currently on construction', 3, '2025-12-04 12:30:53', '2025-12-08 17:13:13'),
(8, '888Poker', 'Electric Board Design on 888Poker owner\'s house in Gibraltar', 1, '2025-12-04 13:47:45', '2025-12-08 17:14:23'),
(9, 'Hassans', 'Hassans Electric Boards', 2, '2025-12-04 19:16:23', '2025-12-08 17:14:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `project_images`
--

CREATE TABLE `project_images` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `imagen` longblob DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `imagen`, `created_at`) VALUES
(1, 1, 0x696d672f70726f6a656374732f736d6172745f6f66666963655f6275696c64696e672f736d6172745f6f66666963655f6275696c64696e675f696d616765312e6a7067, '2025-11-28 14:18:01'),
(2, 1, 0x696d672f70726f6a656374732f736d6172745f6f66666963655f6275696c64696e672f736d6172745f6f66666963655f6275696c64696e675f696d616765322e6a7067, '2025-11-28 14:18:01'),
(3, 2, 0x696d672f70726f6a656374732f6c75787572795f76696c6c615f70726f6a6563742f6c75787572795f76696c6c615f70726f6a6563745f696d616765312e6a7067, '2025-11-28 14:18:01'),
(4, 3, 0x696d672f70726f6a656374732f6675747572655f73686f7070696e675f6d616c6c2f6675747572655f73686f7070696e675f6d616c6c5f696d616765312e6a7067, '2025-11-28 14:18:01'),
(11, 7, 0x696d672f70726f6a656374732f70726f6a6563745f372f696d6167655f312e6a706567, '2025-12-04 12:30:53'),
(12, 7, 0x696d672f70726f6a656374732f70726f6a6563745f372f696d6167655f322e6a706567, '2025-12-04 12:30:53'),
(17, 8, 0x696d672f70726f6a656374732f70726f6a6563745f382f696d6167655f312e6a706567, '2025-12-04 13:47:45'),
(18, 9, 0x696d672f70726f6a656374732f70726f6a6563745f392f696d6167655f312e6a706567, '2025-12-04 19:16:23'),
(19, 9, 0x696d672f70726f6a656374732f70726f6a6563745f392f696d6167655f322e6a706567, '2025-12-04 19:16:23'),
(20, 9, 0x696d672f70726f6a656374732f70726f6a6563745f392f696d6167655f332e6a706567, '2025-12-04 19:16:23'),
(21, 9, 0x696d672f70726f6a656374732f70726f6a6563745f392f696d6167655f342e6a706567, '2025-12-04 19:16:23'),
(22, 9, 0x696d672f70726f6a656374732f70726f6a6563745f392f696d6167655f352e6a706567, '2025-12-04 19:16:23'),
(24, 3, 0x696d672f75706c6f6164732f70726f6a656374732f356665366635666562316665663838665f5265736964656e7469616c5f536f6c7574696f6e732e706e67, '2025-12-08 15:17:08'),
(25, 8, 0x696d672f75706c6f6164732f70726f6a656374732f383934653736343838373639633863305f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e33392e35315f5f315f2e6a706567, '2025-12-08 17:11:13'),
(26, 8, 0x696d672f75706c6f6164732f70726f6a656374732f656239653966646639656535613335345f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e33392e35312e6a706567, '2025-12-08 17:11:13'),
(27, 8, 0x696d672f75706c6f6164732f70726f6a656374732f363830663165653563623930623832635f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e34302e30305f5f385f2e6a706567, '2025-12-08 17:11:13'),
(28, 8, 0x696d672f75706c6f6164732f70726f6a656374732f656536373538323862383462656237345f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e34302e30315f5f385f2e6a706567, '2025-12-08 17:11:13'),
(29, 7, 0x696d672f75706c6f6164732f70726f6a656374732f303931656566313838313231386137645f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e33392e34342e6a706567, '2025-12-08 17:13:13'),
(30, 7, 0x696d672f75706c6f6164732f70726f6a656374732f346233616262316662303562666633615f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e33392e35362e6a706567, '2025-12-08 17:13:13'),
(31, 7, 0x696d672f75706c6f6164732f70726f6a656374732f393434643736613737616238326663625f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e33392e35385f5f335f2e6a706567, '2025-12-08 17:13:13'),
(32, 7, 0x696d672f75706c6f6164732f70726f6a656374732f356239666365386666626661383233385f57686174734170705f496d6167655f323032352d31312d31345f61745f31352e35342e32345f5f375f2e6a706567, '2025-12-08 17:13:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` longtext NOT NULL,
  `page` varchar(50) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `email`, `rating`, `comment`, `page`, `approved`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440001', 'Carlos García', 'carlos@example.com', 5, 'Excelente servicio de automatización. El equipo fue muy profesional y atentos.', 'business.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440002', 'María López', 'maria@example.com', 4, 'Muy buenos resultados. Solo mejoraría los tiempos de entrega un poco.', 'residential.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440003', 'Juan Rodríguez', 'juan@example.com', 5, 'Instalación perfecta en nuestro edificio de oficinas. Totalmente recomendado.', 'business.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440004', 'Ana Martínez', 'ana@example.com', 3, 'Buen trabajo pero el precio fue superior a lo presupuestado.', 'residential.html', 0, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440005', 'Roberto Silva', 'roberto@example.com', 5, 'Increíble transformación de nuestro hogar. La tecnología funciona sin problemas.', 'residential.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440006', 'Fernanda Pérez', 'fernanda@example.com', 4, 'Muy satisfecha con los resultados. Equipo técnico muy competente.', 'business.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440007', 'Miguel Ángel', 'miguel@example.com', 2, 'Tuvimos problemas con la instalación inicial. Se resolvió después.', 'residential.html', 0, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440008', 'Sofía González', 'sofia@example.com', 5, 'Proyecto completado a tiempo y dentro del presupuesto. Muy recomendable.', 'business.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440009', 'David Torres', 'david@example.com', 4, 'Gran calidad en el trabajo. La atención al cliente podría mejorar un poco.', 'residential.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33'),
('550e8400-e29b-41d4-a716-446655440010', 'Cristina López', 'cristina@example.com', 5, 'Impresionante cómo quedó nuestro sistema domótico. No nos arrepentimos.', 'residential.html', 1, '2025-12-08 12:51:33', '2025-12-08 12:51:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_orders`
--

CREATE TABLE `service_orders` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `service_type` enum('maintenance','installation') NOT NULL,
  `technicians` int(11) NOT NULL,
  `hours` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `service_time` time NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `service_address` text NOT NULL,
  `service_details` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paypal_transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','paid','completed','cancelled') DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `service_orders`
--

INSERT INTO `service_orders` (`id`, `reference_number`, `service_type`, `technicians`, `hours`, `service_date`, `service_time`, `customer_name`, `customer_email`, `customer_phone`, `service_address`, `service_details`, `total_amount`, `paypal_transaction_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SRV-20251206-8410', 'installation', 1, 4, '2025-12-07', '10:00:00', 'Test User', 'test@example.com', '+35054012345', 'Test Address', 'Test', 600.00, '0XM24084CM5673319', 'paid', '2025-12-06 21:40:17', '2025-12-06 21:40:17'),
(2, 'SRV-20251206-3571', 'installation', 1, 4, '2025-12-07', '10:00:00', 'Test User', 'test@example.com', '+35054012345', 'Test Address', 'Test', 600.00, '0XM24084CM5673319', 'paid', '2025-12-06 21:40:43', '2025-12-06 21:40:43'),
(3, 'SRV-20251206-5747', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '1V779406SP5273411', 'paid', '2025-12-06 22:00:16', '2025-12-06 22:00:16'),
(4, 'SRV-20251206-1222', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '53A465247B308862W', 'paid', '2025-12-06 22:20:31', '2025-12-06 22:20:31'),
(5, 'SRV-20251206-4494', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '0KS07686KD753484H', 'paid', '2025-12-06 22:21:27', '2025-12-06 22:21:27'),
(6, 'SRV-20251206-1493', 'maintenance', 1, 1, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 80.00, '8FK32077D0004735T', 'paid', '2025-12-06 22:30:45', '2025-12-06 22:30:45'),
(7, 'SRV-20251206-2692', 'installation', 1, 1, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 150.00, '72E390579G213290V', 'paid', '2025-12-06 22:35:01', '2025-12-06 22:35:01'),
(8, 'SRV-20251206-8069', 'maintenance', 2, 5, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 800.00, '49Y56355BX9830741', 'paid', '2025-12-06 22:36:41', '2025-12-06 22:36:41'),
(9, 'SRV-20251206-3375', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '4CB45634GW4312116', 'paid', '2025-12-06 22:38:22', '2025-12-06 22:38:22'),
(10, 'SRV-20251206-9189', 'maintenance', 2, 5, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 800.00, '4FC29323AS827393K', 'paid', '2025-12-06 22:40:27', '2025-12-06 22:40:27'),
(11, 'SRV-20251206-6594', 'installation', 2, 1, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 300.00, '2VA05263ND220323S', 'paid', '2025-12-06 22:54:15', '2025-12-06 22:54:15'),
(12, 'SRV-20251206-4636', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '22B3519042575253X', 'paid', '2025-12-06 22:57:43', '2025-12-06 22:57:43'),
(13, 'SRV-20251206-9255', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '8NC6959298880062C', 'paid', '2025-12-06 23:08:28', '2025-12-06 23:08:28'),
(14, 'SRV-20251207-8524', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '8CY00131LR111864G', 'paid', '2025-12-07 00:01:32', '2025-12-07 00:01:32'),
(15, 'SRV-20251207-1174', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '64K30464H8800934D', 'paid', '2025-12-07 00:30:39', '2025-12-07 00:30:39'),
(16, 'SRV-20251207-6077', 'maintenance', 2, 6, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 960.00, '61079185X8015553T', 'paid', '2025-12-07 00:40:08', '2025-12-07 00:40:08'),
(17, 'SRV-20251207-6663', 'installation', 1, 1, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 150.00, '3M304111T9601325F', 'paid', '2025-12-07 00:41:54', '2025-12-07 00:41:54'),
(18, 'SRV-20251207-4847', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '1LU181163H514772C', 'paid', '2025-12-07 00:46:57', '2025-12-07 00:46:57'),
(19, 'SRV-20251207-1698', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '3D568092411801024', 'paid', '2025-12-07 00:54:51', '2025-12-07 00:54:51'),
(20, 'SRV-20251207-1051', 'maintenance', 2, 2, '2025-12-07', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', '', 320.00, '63E05519PD4418414', 'paid', '2025-12-07 00:57:54', '2025-12-07 00:57:54'),
(21, 'SRV-20251208-9630', 'maintenance', 1, 2, '2025-12-09', '09:00:00', 'Dario Genal', 'support@intecgib.com', '+34656959333', 'Europa Bussines Centre, Unit F18', 'psdjwoIEJ', 160.00, '5TX93427CC1712023', 'paid', '2025-12-08 16:13:24', '2025-12-08 16:13:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` varchar(50) NOT NULL,
  `pwd` varchar(32) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('admin','editor','viewer') DEFAULT 'editor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `pwd`, `nombre_completo`, `email`, `rol`) VALUES
('DGenBor', '774eb822735b49f13a6ec828e125a2a4', 'Darío Genal Borrego', 'dario@intecgib.com', 'admin'),
('IParedes', 'd01bb40719398887fea806c4562a5fe7', 'Inmaculada Paredes', 'administration@intecgib.com', 'admin'),
('JCarLuq', 'a4178f6c42f6b453d1d33c8df19fd0d0', 'Juan Carlos Luque', 'carlos@intecgib.com', 'editor'),
('SRodGar', 'e86c188c03cd470038c9b2166a519b29', 'Sergio Rodríguez García', 'sergio@intecgib.com', 'editor');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estado` (`estado_proyecto`);

--
-- Indices de la tabla `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_project_id` (`project_id`);

--
-- Indices de la tabla `service_orders`
--
ALTER TABLE `service_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `customer_email` (`customer_email`),
  ADD KEY `status` (`status`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `service_orders`
--
ALTER TABLE `service_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `project_images_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_images_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
