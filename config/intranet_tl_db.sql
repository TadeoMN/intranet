-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 16-10-2025 a las 14:15:40
-- Versión del servidor: 8.0.43-0ubuntu0.24.04.2
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `intranet_tl`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id_audit_log` bigint UNSIGNED NOT NULL,
  `entity_name` varchar(50) DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `action_type` enum('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','VIEW_PII') DEFAULT NULL,
  `changes_json` json DEFAULT NULL,
  `performed_by` int UNSIGNED DEFAULT NULL,
  `performed_at` datetime NOT NULL,
  `ip_addr_session` varbinary(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contracts`
--

CREATE TABLE `contracts` (
  `id_contract` int UNSIGNED NOT NULL,
  `id_employee_fk` int UNSIGNED NOT NULL,
  `number_payroll_contract` int UNSIGNED NOT NULL,
  `code_employee_snapshot` char(10) DEFAULT NULL,
  `id_contract_type_fk` smallint UNSIGNED NOT NULL,
  `id_payroll_scheme_fk` smallint UNSIGNED NOT NULL,
  `start_date_contract` date NOT NULL,
  `trial_period_contract` date DEFAULT NULL,
  `end_date_contract` date DEFAULT NULL,
  `salary_contract` decimal(10,2) NOT NULL,
  `termination_reason_contract` text,
  `is_active` tinyint(1) DEFAULT '1'
) ;

--
-- Disparadores `contracts`
--
DELIMITER $$
CREATE TRIGGER `trg_contract_code_snapshot` BEFORE INSERT ON `contracts` FOR EACH ROW BEGIN
  IF NEW.code_employee_snapshot IS NULL THEN
    SET NEW.code_employee_snapshot = (SELECT code_employee FROM employee WHERE id_employee = NEW.id_employee_fk)$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_type`
--

CREATE TABLE `contract_type` (
  `id_contract_type` smallint UNSIGNED NOT NULL,
  `name_contract_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `department`
--

CREATE TABLE `department` (
  `id_department` smallint UNSIGNED NOT NULL,
  `name_department` varchar(100) NOT NULL,
  `id_manager_employee_fk` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee`
--

CREATE TABLE `employee` (
  `id_employee` int UNSIGNED NOT NULL,
  `code_employee` varchar(10) DEFAULT NULL,
  `name_employee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `date_hired` date NOT NULL,
  `status_employee` enum('ACTIVO','INACTIVO','SUSPENDIDO') DEFAULT 'ACTIVO',
  `type_employee` enum('OPERATIVO','ADMINISTRATIVO') NOT NULL DEFAULT 'OPERATIVO',
  `seniority_employee` decimal(4,2) DEFAULT NULL,
  `id_position_fk` int UNSIGNED NOT NULL
) ;

--
-- Disparadores `employee`
--
DELIMITER $$
CREATE TRIGGER `trg_employee_insert_new` BEFORE INSERT ON `employee` FOR EACH ROW BEGIN
  DECLARE last_id INT$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_employee_update_seniority` BEFORE UPDATE ON `employee` FOR EACH ROW BEGIN
  IF NEW.date_hired <> OLD.date_hired THEN
    SET NEW.seniority_employee = ROUND(TIMESTAMPDIFF(MONTH, NEW.date_hired, CURDATE()) / 12, 1)$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_user_after_insert_employee` AFTER INSERT ON `employee` FOR EACH ROW BEGIN
  INSERT INTO users
    (name_user, password_hash_user, id_employee_fk)
  VALUES
    (NEW.code_employee, '$pending$', NEW.id_employee)$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee_position_history`
--

CREATE TABLE `employee_position_history` (
  `id_position_history` int UNSIGNED NOT NULL,
  `id_employee_fk` int UNSIGNED NOT NULL,
  `id_snapshot_position` int UNSIGNED NOT NULL,
  `name_snapshot_position` varchar(100) NOT NULL,
  `id_level_snapshot_position` tinyint UNSIGNED NOT NULL,
  `id_snapshot_department` smallint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee_profile`
--

CREATE TABLE `employee_profile` (
  `id_employee_profile` int UNSIGNED NOT NULL,
  `id_employee_fk` int UNSIGNED NOT NULL,
  `birthdate_employee_profile` date DEFAULT NULL,
  `gender_employee_profile` enum('HOMBRE','MUJER') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `marital_status_employee_profile` enum('SOLTERO','CASADO','DIVORCIADO','VIUDO') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'SOLTERO',
  `curp_employee_profile` varchar(18) DEFAULT NULL,
  `ssn_employee_profile` varchar(11) DEFAULT NULL,
  `account_number_employee_profile` varchar(20) DEFAULT NULL,
  `bank_employee_profile` varchar(50) DEFAULT NULL,
  `phone_employee_profile` varchar(10) DEFAULT NULL,
  `mobile_employee_profile` varchar(10) DEFAULT NULL,
  `email_employee_profile` varchar(80) DEFAULT NULL,
  `address_employee_profile` text,
  `emergency_contact_employee_profile` varchar(100) DEFAULT NULL,
  `emergency_phone_employee_profile` varchar(20) DEFAULT NULL,
  `emergency_relationship_employee_profile` varchar(50) DEFAULT NULL,
  `image_employee_profile` varchar(255) DEFAULT NULL,
  `rfc_employee_profile` varchar(13) DEFAULT NULL,
  `digital_file_employee_profile` varchar(255) DEFAULT NULL,
  `blood_type_employee_profile` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incident`
--

CREATE TABLE `incident` (
  `id_incident` int UNSIGNED NOT NULL,
  `id_employee_fk` int UNSIGNED NOT NULL,
  `id_incident_type_fk` smallint UNSIGNED NOT NULL,
  `ot_incident` varchar(20) DEFAULT NULL,
  `waste_incident` varchar(200) DEFAULT NULL,
  `observation_incident` text,
  `appeal_incident` text,
  `identification_incident` enum('INTERNA','EXTERNA') DEFAULT 'INTERNA',
  `reported_by` int UNSIGNED NOT NULL,
  `reported_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incident_type`
--

CREATE TABLE `incident_type` (
  `id_incident_type` smallint UNSIGNED NOT NULL,
  `code_incident_type` varchar(20) NOT NULL,
  `name_incident_type` varchar(100) NOT NULL,
  `description_incident_type` text,
  `severity_incident_type` enum('BAJA','MEDIA','ALTA','CRITICA') NOT NULL DEFAULT 'BAJA',
  `action_incident_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leave_request`
--

CREATE TABLE `leave_request` (
  `id_leave_request` int UNSIGNED NOT NULL,
  `id_employee_fk` int UNSIGNED NOT NULL,
  `id_leave_type_fk` smallint UNSIGNED NOT NULL,
  `start_date_leave` date NOT NULL,
  `end_date_leave` date NOT NULL,
  `status_leave` enum('PENDIENTE','APROBADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `reason_leave` text,
  `requested_by` int UNSIGNED NOT NULL,
  `requested_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_by` int UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leave_type`
--

CREATE TABLE `leave_type` (
  `id_leave_type` smallint UNSIGNED NOT NULL,
  `code_leave_type` varchar(20) NOT NULL,
  `name_leave_type` varchar(100) NOT NULL,
  `description_leave_type` text,
  `auto_deduct_days` tinyint(1) DEFAULT '0',
  `max_days_leave_type` smallint UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `level_position`
--

CREATE TABLE `level_position` (
  `id_level_position` tinyint UNSIGNED NOT NULL,
  `name_level_position` varchar(50) NOT NULL,
  `description_level_position` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payroll_scheme`
--

CREATE TABLE `payroll_scheme` (
  `id_payroll_scheme` smallint UNSIGNED NOT NULL,
  `name_payroll_scheme` varchar(100) NOT NULL,
  `frequency_payroll_scheme` enum('SEMANAL','QUINCENAL','MENSUAL','CATORCENAL') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `id_permission` smallint UNSIGNED NOT NULL,
  `name_permission` varchar(60) NOT NULL,
  `description_permission` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `positions`
--

CREATE TABLE `positions` (
  `id_position` int UNSIGNED NOT NULL,
  `name_position` varchar(100) NOT NULL,
  `id_level_position_fk` tinyint UNSIGNED NOT NULL,
  `id_department_fk` smallint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_role` smallint UNSIGNED NOT NULL,
  `name_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id_fk` smallint UNSIGNED NOT NULL,
  `permission_id_fk` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `session_history`
--

CREATE TABLE `session_history` (
  `id_session` bigint UNSIGNED NOT NULL,
  `id_user_session_fk` int UNSIGNED NOT NULL,
  `token_session` char(64) NOT NULL,
  `ip_addr_session` varbinary(16) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int UNSIGNED NOT NULL,
  `name_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `password_hash_user` varchar(255) NOT NULL,
  `status_user` enum('ACTIVO','INACTIVO','BLOQUEADO','FORZAR_RESET') DEFAULT 'FORZAR_RESET',
  `id_employee_fk` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id_user_fk` int UNSIGNED NOT NULL,
  `id_role_fk` smallint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id_session` bigint UNSIGNED NOT NULL,
  `id_user_session_fk` int UNSIGNED NOT NULL,
  `token_session` char(64) NOT NULL,
  `ip_addr_session` varbinary(16) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `user_sessions`
--
DELIMITER $$
CREATE TRIGGER `trg_session_logout` BEFORE UPDATE ON `user_sessions` FOR EACH ROW BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 AND NEW.logout_at IS NULL THEN
    SET NEW.logout_at = NOW()$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_session_to_history` AFTER UPDATE ON `user_sessions` FOR EACH ROW BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
    INSERT INTO session_history VALUES (
      NEW.id_session,
      NEW.id_user_session_fk,
      NEW.token_session,
      NEW.ip_addr_session,
      NEW.login_at,
      NEW.logout_at,
      NEW.is_active
    )$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id_audit_log`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indices de la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id_contract`),
  ADD UNIQUE KEY `uq_active_contract` (`id_employee_fk`,`is_active`),
  ADD KEY `id_contract_type_fk` (`id_contract_type_fk`),
  ADD KEY `id_payroll_scheme_fk` (`id_payroll_scheme_fk`);

--
-- Indices de la tabla `contract_type`
--
ALTER TABLE `contract_type`
  ADD PRIMARY KEY (`id_contract_type`),
  ADD UNIQUE KEY `name_contract_type` (`name_contract_type`);

--
-- Indices de la tabla `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`id_department`),
  ADD UNIQUE KEY `name_department` (`name_department`),
  ADD KEY `id_manager_department_fk` (`id_manager_employee_fk`);

--
-- Indices de la tabla `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id_employee`),
  ADD UNIQUE KEY `name_employee` (`name_employee`),
  ADD UNIQUE KEY `uq_employee` (`name_employee`,`date_hired`,`id_position_fk`),
  ADD UNIQUE KEY `code_employee` (`code_employee`),
  ADD KEY `id_position_fk` (`id_position_fk`);

--
-- Indices de la tabla `employee_position_history`
--
ALTER TABLE `employee_position_history`
  ADD PRIMARY KEY (`id_position_history`),
  ADD KEY `id_employee_fk` (`id_employee_fk`);

--
-- Indices de la tabla `employee_profile`
--
ALTER TABLE `employee_profile`
  ADD PRIMARY KEY (`id_employee_profile`),
  ADD UNIQUE KEY `id_employee_fk` (`id_employee_fk`),
  ADD UNIQUE KEY `curp_employee_profile` (`curp_employee_profile`),
  ADD UNIQUE KEY `ssn_employee_profile` (`ssn_employee_profile`),
  ADD UNIQUE KEY `account_number_employee_profile` (`account_number_employee_profile`),
  ADD UNIQUE KEY `email_employee_profile` (`email_employee_profile`),
  ADD UNIQUE KEY `rfc_employee_profile` (`rfc_employee_profile`);

--
-- Indices de la tabla `incident`
--
ALTER TABLE `incident`
  ADD PRIMARY KEY (`id_incident`),
  ADD KEY `id_incident_type_fk` (`id_incident_type_fk`),
  ADD KEY `id_employee_fk` (`id_employee_fk`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indices de la tabla `incident_type`
--
ALTER TABLE `incident_type`
  ADD PRIMARY KEY (`id_incident_type`),
  ADD UNIQUE KEY `name_incident_type` (`name_incident_type`),
  ADD UNIQUE KEY `code_incident_type` (`code_incident_type`);

--
-- Indices de la tabla `leave_request`
--
ALTER TABLE `leave_request`
  ADD PRIMARY KEY (`id_leave_request`),
  ADD KEY `id_employee_fk` (`id_employee_fk`),
  ADD KEY `id_leave_type_fk` (`id_leave_type_fk`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indices de la tabla `leave_type`
--
ALTER TABLE `leave_type`
  ADD PRIMARY KEY (`id_leave_type`),
  ADD UNIQUE KEY `code_leave_type` (`code_leave_type`),
  ADD UNIQUE KEY `name_leave_type` (`name_leave_type`);

--
-- Indices de la tabla `level_position`
--
ALTER TABLE `level_position`
  ADD PRIMARY KEY (`id_level_position`),
  ADD UNIQUE KEY `name_level_position` (`name_level_position`),
  ADD UNIQUE KEY `uq_level_position` (`name_level_position`,`description_level_position`);

--
-- Indices de la tabla `payroll_scheme`
--
ALTER TABLE `payroll_scheme`
  ADD PRIMARY KEY (`id_payroll_scheme`),
  ADD UNIQUE KEY `uq_payroll_scheme` (`name_payroll_scheme`,`frequency_payroll_scheme`);

--
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id_permission`),
  ADD UNIQUE KEY `name_permission` (`name_permission`);

--
-- Indices de la tabla `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id_position`),
  ADD UNIQUE KEY `uq_position` (`name_position`,`id_department_fk`),
  ADD KEY `id_level_position_fk` (`id_level_position_fk`),
  ADD KEY `id_department_fk` (`id_department_fk`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `name_role` (`name_role`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id_fk`,`permission_id_fk`),
  ADD KEY `permission_id_fk` (`permission_id_fk`);

--
-- Indices de la tabla `session_history`
--
ALTER TABLE `session_history`
  ADD PRIMARY KEY (`id_session`),
  ADD UNIQUE KEY `token_session` (`token_session`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `name_user` (`name_user`),
  ADD UNIQUE KEY `id_employee_fk` (`id_employee_fk`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id_user_fk`,`id_role_fk`),
  ADD KEY `id_role_fk` (`id_role_fk`);

--
-- Indices de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id_session`),
  ADD UNIQUE KEY `token_session` (`token_session`),
  ADD UNIQUE KEY `uq_active_session` (`id_user_session_fk`,`is_active`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id_audit_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id_contract` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contract_type`
--
ALTER TABLE `contract_type`
  MODIFY `id_contract_type` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `department`
--
ALTER TABLE `department`
  MODIFY `id_department` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employee`
--
ALTER TABLE `employee`
  MODIFY `id_employee` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employee_position_history`
--
ALTER TABLE `employee_position_history`
  MODIFY `id_position_history` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employee_profile`
--
ALTER TABLE `employee_profile`
  MODIFY `id_employee_profile` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incident`
--
ALTER TABLE `incident`
  MODIFY `id_incident` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incident_type`
--
ALTER TABLE `incident_type`
  MODIFY `id_incident_type` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `leave_request`
--
ALTER TABLE `leave_request`
  MODIFY `id_leave_request` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `leave_type`
--
ALTER TABLE `leave_type`
  MODIFY `id_leave_type` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `level_position`
--
ALTER TABLE `level_position`
  MODIFY `id_level_position` tinyint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payroll_scheme`
--
ALTER TABLE `payroll_scheme`
  MODIFY `id_payroll_scheme` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_permission` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `positions`
--
ALTER TABLE `positions`
  MODIFY `id_position` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` smallint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `session_history`
--
ALTER TABLE `session_history`
  MODIFY `id_session` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id_session` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id_user`);

--
-- Filtros para la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE RESTRICT,
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`id_contract_type_fk`) REFERENCES `contract_type` (`id_contract_type`) ON DELETE RESTRICT,
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`id_payroll_scheme_fk`) REFERENCES `payroll_scheme` (`id_payroll_scheme`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `id_manager_department_fk` FOREIGN KEY (`id_manager_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE SET NULL;

--
-- Filtros para la tabla `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`id_position_fk`) REFERENCES `positions` (`id_position`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `employee_position_history`
--
ALTER TABLE `employee_position_history`
  ADD CONSTRAINT `employee_position_history_ibfk_1` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`);

--
-- Filtros para la tabla `employee_profile`
--
ALTER TABLE `employee_profile`
  ADD CONSTRAINT `employee_profile_ibfk_1` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incident`
--
ALTER TABLE `incident`
  ADD CONSTRAINT `incident_ibfk_1` FOREIGN KEY (`id_incident_type_fk`) REFERENCES `incident_type` (`id_incident_type`) ON DELETE RESTRICT,
  ADD CONSTRAINT `incident_ibfk_2` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE RESTRICT,
  ADD CONSTRAINT `incident_ibfk_3` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `leave_request`
--
ALTER TABLE `leave_request`
  ADD CONSTRAINT `leave_request_ibfk_1` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE RESTRICT,
  ADD CONSTRAINT `leave_request_ibfk_2` FOREIGN KEY (`id_leave_type_fk`) REFERENCES `leave_type` (`id_leave_type`) ON DELETE RESTRICT,
  ADD CONSTRAINT `leave_request_ibfk_3` FOREIGN KEY (`requested_by`) REFERENCES `employee` (`id_employee`) ON DELETE RESTRICT,
  ADD CONSTRAINT `leave_request_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `employee` (`id_employee`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`id_level_position_fk`) REFERENCES `level_position` (`id_level_position`),
  ADD CONSTRAINT `positions_ibfk_2` FOREIGN KEY (`id_department_fk`) REFERENCES `department` (`id_department`) ON DELETE SET NULL;

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id_fk`) REFERENCES `roles` (`id_role`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id_fk`) REFERENCES `permissions` (`id_permission`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_employee_fk`) REFERENCES `employee` (`id_employee`) ON DELETE SET NULL;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`id_user_fk`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`id_role_fk`) REFERENCES `roles` (`id_role`);

--
-- Filtros para la tabla `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`id_user_session_fk`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
