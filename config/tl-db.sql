-- ############################################################
--  ESQUEMA COMPLETO – INTRANET (VERSION 1.0)
--  Autor: Ing. Tadeo Mejía · Fecha: 2025-07
--  Uso: Ejecutar en entornos CI/CD; No usar en producción
--  Descripción: Este script crea la base de datos y las tablas
-- ------------------------------------------------------------
--  PREREQUISITOS:
--    • MySQL/MariaDB 10.5+
--    • Barracuda file‑format + innodb_file_per_table = 1
--    • EVENT_SCHEDULER habilitado
-- ------------------------------------------------------------
/*!40101 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS intranet_tl DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE intranet_tl;

/* ---------------------------------------------------------------------
   1. MÓDULO DE SEGURIDAD Y AUTENTICACIÓN
   --------------------------------------------------------------------- */

-- --------------------
-- 1.1 Tabla `roles`
-- --------------------
-- Define los grupos lógicos de permisos (ej. Admin, RH, Auditor).
-- Se asignan a través de la tabla intermedia `user_roles` para que un
-- usuario pueda tener múltiples roles.   
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  id_role SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------
-- 1.2 Tabla `permissions`
-- --------------------
-- Acciones atómicas que la aplicación reconoce (p.ej. employee_create,
-- contract_manage). Se unen a los roles vía tabla puente.
DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
  id_permission SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code_permission VARCHAR(60) NOT NULL UNIQUE,
  description_permission TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla puente M:N entre roles y permisos
DROP TABLE IF EXISTS role_permissions;
CREATE TABLE role_permissions (
  role_id_fk SMALLINT UNSIGNED,
  permission_id_fk SMALLINT UNSIGNED,
  PRIMARY KEY (role_id_fk, permission_id_fk),
  FOREIGN KEY (role_id_fk) REFERENCES roles(id_role),
  FOREIGN KEY (permission_id_fk) REFERENCES permissions(id_permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------
-- 1.3 Tabla `users`
-- --------------------
-- Autenticación principal de la intranet.
-- Incluye campos para nombre, email, contraseña (hash), estado y timestamps.
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_user VARCHAR(50) COLLATE utf8mb4_bin NOT NULL UNIQUE,
  password_hash_user VARCHAR(255) NOT NULL COMMENT 'Soporte para diferentes algoritmos de hash',
  status_user ENUM('ACTIVO', 'INACTIVO', 'BLOQUEADO') DEFAULT 'ACTIVO',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  -- Índices para optimización de consultas
  INDEX idx_users_status (status_user),
  INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla de usuarios del sistema';

-- Tabla puente M:N entre usuarios y roles
DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
  id_user_fk INT UNSIGNED,
  id_role_fk SMALLINT UNSIGNED,
  PRIMARY KEY (id_user_fk, id_role_fk),
  FOREIGN KEY (id_user_fk) REFERENCES users(id_user),
  FOREIGN KEY (id_role_fk) REFERENCES roles(id_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------
-- 1.4 Control de sesión única
-- --------------------
-- user_sessions garantiza una sola sesión activa por usuario.
-- Guarda el token de sesión, IP, fecha de inicio y expiración.
DROP TABLE IF EXISTS user_sessions;
CREATE TABLE user_sessions (
  id_session BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user_session_fk INT UNSIGNED NOT NULL,
  token_session CHAR(64) NOT NULL UNIQUE,
  ip_addr_session VARBINARY(16) NOT NULL,
  login_at DATETIME NOT NULL,
  logout_at DATETIME,
  is_active TINYINT(1) DEFAULT 1,
  -- Optimización: usar función antes del constraint
  UNIQUE KEY uq_active_session (id_user_session_fk, is_active),
  FOREIGN KEY (id_user_session_fk) REFERENCES users(id_user) ON DELETE CASCADE,
  -- Índices para optimización
  INDEX idx_sessions_login_at (login_at),
  INDEX idx_sessions_logout_at (logout_at),
  INDEX idx_sessions_ip (ip_addr_session)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Gestión de sesiones activas';

-- Historial de sesiones (guarda todo)
DROP TABLE IF EXISTS session_history;
CREATE TABLE session_history LIKE user_sessions;
ALTER TABLE session_history DROP KEY uq_active_session;

-- Triggers para manejar el cierre de sesión y actualizar el historial.
-- Se asegura que al cerrar sesión se actualice el campo `logout_at` y se
-- inserte un registro en el historial de sesiones.
DELIMITER //
CREATE TRIGGER trg_session_logout
BEFORE UPDATE ON user_sessions
FOR EACH ROW
BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 AND NEW.logout_at IS NULL THEN
    SET NEW.logout_at = NOW();
  END IF;
END//
DELIMITER ;

DELIMITER //
CREATE TRIGGER trg_session_to_history
AFTER UPDATE ON user_sessions
FOR EACH ROW
BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
    INSERT INTO session_history VALUES (
      NEW.id_session,
      NEW.id_user_session_fk,
      NEW.token_session,
      NEW.ip_addr_session,
      NEW.login_at,
      NEW.logout_at,
      NEW.is_active
    );
  END IF;
END//
DELIMITER ;

/* ---------------------------------------------------------------------
   2. MÓDULO DE EMPLEADOS (HR)
   --------------------------------------------------------------------- */

-- --------------------
-- 2.1 Catálogos
-- --------------------
-- Tabla de referencia para departamentos
-- Define los departamentos de la empresa y su gerente.
-- El campo `manager_department_fk` es una referencia al usuario que es gerente.
DROP TABLE IF EXISTS department;
CREATE TABLE department (
  id_department SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_department VARCHAR(100) NOT NULL UNIQUE,
  id_manager_employee_fk INT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de niveles de puesto
-- Define los niveles jerárquicos de los puestos (p.ej. Junior, Senior).
-- Incluye un campo para descripción opcional.
DROP TABLE IF EXISTS level_position;
CREATE TABLE level_position (
  id_level_position TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_level_position VARCHAR(50) NOT NULL UNIQUE,
  description_level_position VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uq_level_position (name_level_position, description_level_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de referencia para puestos
-- Incluye niveles jerárquicos para definir permisos y roles.
-- El campo `level_position` define el nivel del puesto (p.ej. 1 = Junior, 2 = Senior).
DROP TABLE IF EXISTS positions;
CREATE TABLE positions (
  id_position INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_position VARCHAR(100) NOT NULL,
  id_level_position_fk TINYINT UNSIGNED NOT NULL,
  id_departament_fk SMALLINT UNSIGNED,
  UNIQUE KEY uq_position (name_position, id_departament_fk),
  FOREIGN KEY (id_level_position_fk) REFERENCES level_position(id_level_position),
  FOREIGN KEY (id_departament_fk) REFERENCES department(id_department) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de tipos de contrato
-- Define los tipos de contrato disponibles (p.ej. indefinido, temporal).
DROP TABLE IF EXISTS contract_type;
CREATE TABLE contract_type (
  id_contract_type SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_contract_type VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de esquemas de nómina
-- Define los esquemas de pago (p.ej. nomina, honorarios).
-- Incluye frecuencia de pago (semanal, quincenal, mensual).
DROP TABLE IF EXISTS payroll_scheme;
CREATE TABLE payroll_scheme (
  id_payroll_scheme SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_payroll_scheme VARCHAR(100) NOT NULL,
  frequency_payroll_scheme ENUM('SEMANAL', 'QUINCENAL', 'MENSUAL', 'CATORCENAL') NOT NULL,
  UNIQUE KEY uq_payroll_scheme (name_payroll_scheme, frequency_payroll_scheme)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de tipos de licencia
-- Define los tipos de licencia disponibles (p.ej. vacaciones, enfermedad).
-- Incluye campos para deducción automática de días y máximo de días permitidos.
DROP TABLE IF EXISTS leave_type;
CREATE TABLE leave_type (
  id_leave_type SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code_leave_type VARCHAR(20) NOT NULL UNIQUE,
  name_leave_type VARCHAR(100) NOT NULL UNIQUE,
  description_leave_type TEXT,
  auto_deduct_days TINYINT(1) DEFAULT 0,
  max_days_leave_type SMALLINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de tipos de incidencia
-- Define los tipos de incidencia que pueden ocurrir (p.ej. accidente, retraso).
DROP TABLE IF EXISTS incident_type;
CREATE TABLE incident_type (
  id_incident_type SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_incident_type VARCHAR(100) NOT NULL UNIQUE,
  description_incident_type TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------
-- 2.2 Tabla employees
-- --------------------
-- Registra a los empleados con sus datos básicos y referencias a otros catálogos.
-- Incluye campos para código, nombre, fecha de contratación, estado y tipo.
DROP TABLE IF EXISTS employee;
CREATE TABLE employee (
  id_employee INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code_employee VARCHAR(10) NOT NULL UNIQUE,
  name_employee VARCHAR(100) COLLATE utf8mb4_bin NOT NULL,
  date_hired DATE NOT NULL,
  status_employee ENUM('ACTIVO', 'INACTIVO', 'SUSPENDIDO') DEFAULT 'ACTIVO',
  type_employee ENUM('OPERATIVO', 'ADMINISTRATIVO') NOT NULL DEFAULT 'OPERATIVO',
  seniority_employee DECIMAL(4,2) NULL,
  id_user_fk INT UNSIGNED NOT NULL,
  id_position_fk INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user_fk) REFERENCES users(id_user) ON DELETE RESTRICT,
  FOREIGN KEY (id_position_fk) REFERENCES positions(id_position) ON DELETE RESTRICT,
  -- Índices para optimización de consultas
  INDEX idx_employee_status (status_employee),
  INDEX idx_employee_type (type_employee),
  INDEX idx_employee_date_hired (date_hired),
  INDEX idx_employee_name (name_employee),
  -- CHECK constraints para integridad de datos
  CONSTRAINT chk_employee_date_hired CHECK (date_hired <= CURDATE()),
  CONSTRAINT chk_employee_seniority CHECK (seniority_employee >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro principal de empleados';

ALTER TABLE department
  ADD CONSTRAINT id_manager_department_fk FOREIGN KEY (id_manager_employee_fk) REFERENCES employee(id_employee) ON DELETE SET NULL;

DELIMITER //
CREATE TRIGGER trg_employee_set_seniority
BEFORE INSERT ON employee
FOR EACH ROW
BEGIN
  SET NEW.seniority_employee = ROUND(TIMESTAMPDIFF(MONTH, NEW.date_hired, CURDATE()) / 12, 1);
END//
DELIMITER ;

-- --------------------
-- 2.3 Tablas de perfil y historial
-- --------------------
-- Tabla de perfil del empleado
-- Guarda la información personal del empleado.
-- Incluye campos para datos personales, contacto y emergencia.
DROP TABLE IF EXISTS employee_profile;
CREATE TABLE employee_profile (
  id_employee_profile INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_employee_fk INT UNSIGNED NOT NULL UNIQUE,
  birthdate_employee_profile DATE,
  gender_employee_profile ENUM('HOMBRE', 'MUJER') NOT NULL,
  marital_status_employee_profile ENUM('SOLTERO', 'CASADO', 'DIVORCIADO', 'VIUDO') DEFAULT 'SOLTERO',
  curp_employee_profile VARCHAR(18) UNIQUE,
  ssn_employee_profile VARCHAR(11) UNIQUE,
  account_number_employee_profile VARCHAR(20) UNIQUE,
  bank_employee_profile VARCHAR(50),
  phone_employee_profile VARCHAR(10),
  mobile_employee_profile VARCHAR(10),
  email_employee_profile VARCHAR(80) UNIQUE,
  address_employee_profile TEXT,
  emergency_contact_employee_profile VARCHAR(100),
  emergency_phone_employee_profile VARCHAR(20),
  emergency_relationship_employee_profile VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE CASCADE,
  -- Índices para búsquedas comunes
  INDEX idx_profile_email (email_employee_profile),
  INDEX idx_profile_curp (curp_employee_profile),
  INDEX idx_profile_ssn (ssn_employee_profile),
  -- CHECK constraints para validación de datos
  CONSTRAINT chk_profile_birthdate CHECK (birthdate_employee_profile <= CURDATE()),
  CONSTRAINT chk_profile_curp_format CHECK (curp_employee_profile REGEXP '^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$'),
  CONSTRAINT chk_profile_phone_format CHECK (phone_employee_profile REGEXP '^[0-9]{10}$'),
  CONSTRAINT chk_profile_mobile_format CHECK (mobile_employee_profile REGEXP '^[0-9]{10}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Perfil detallado de empleados';

-- Historial de posiciones del empleado
-- Guarda el historial de cambios de puesto del empleado.
-- Permite rastrear cambios de puesto a lo largo del tiempo.
DROP TABLE IF EXISTS employee_position_history;
CREATE TABLE employee_position_history (
  id_position_history INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_employee_fk INT UNSIGNED NOT NULL,
  id_snapshot_position INT UNSIGNED NOT NULL,
  name_snapshot_position VARCHAR(100) NOT NULL,
  id_level_snapshot_position TINYINT UNSIGNED NOT NULL,
  id_snapshot_departament SMALLINT UNSIGNED,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------
-- 2.4 Tabla de contratos
-- ---------------------
-- Registra los contratos de los empleados.
-- Incluye campos para tipo de contrato, esquema de nómina, fechas y salario.
-- Se asegura que solo haya un contrato activo por empleado a través de la restricción UNIQUE.
DROP TABLE IF EXISTS contracts;
CREATE TABLE contracts (
  id_contract INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_employee_fk INT UNSIGNED NOT NULL,
  code_employee_snapshot CHAR(10),
  id_contract_type_fk SMALLINT UNSIGNED NOT NULL,
  id_payroll_scheme_fk SMALLINT UNSIGNED NOT NULL,
  start_date_contract DATE NOT NULL,
  trial_period_contract DATE,
  end_date_contract DATE,
  salary_contract DECIMAL(10,2) NOT NULL,
  termination_reason_contract TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_active_contract (id_employee_fk, is_active),
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (id_contract_type_fk) REFERENCES contract_type(id_contract_type) ON DELETE RESTRICT,
  FOREIGN KEY (id_payroll_scheme_fk) REFERENCES payroll_scheme(id_payroll_scheme) ON DELETE RESTRICT,
  -- Índices para optimización
  INDEX idx_contracts_start_date (start_date_contract),
  INDEX idx_contracts_end_date (end_date_contract),
  INDEX idx_contracts_is_active (is_active),
  INDEX idx_contracts_salary (salary_contract),
  -- CHECK constraints para integridad de datos
  CONSTRAINT chk_contracts_start_date CHECK (start_date_contract >= '1900-01-01'),
  CONSTRAINT chk_contracts_end_date CHECK (end_date_contract IS NULL OR end_date_contract >= start_date_contract),
  CONSTRAINT chk_contracts_trial_period CHECK (trial_period_contract IS NULL OR trial_period_contract >= start_date_contract),
  CONSTRAINT chk_contracts_salary CHECK (salary_contract > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Contratos de empleados';

DELIMITER //
CREATE TRIGGER trg_contract_code_snapshot
BEFORE INSERT ON contracts
FOR EACH ROW
BEGIN
  IF NEW.code_employee_snapshot IS NULL THEN
    SET NEW.code_employee_snapshot = (SELECT code_employee FROM employee WHERE id_employee = NEW.id_employee_fk);
  END IF;
END//
DELIMITER ;  

-- ---------------------
-- 2.5 Tabla de ausencias y permisos
-- ---------------------
-- Registra las solicitudes de ausencias y permisos de los empleados.
-- Incluye campos para fechas, tipo de ausencia, estado y motivo.
-- Permite registrar quién solicitó y quién aprobó la ausencia.
DROP TABLE IF EXISTS leave_request;
CREATE TABLE leave_request (
  id_leave_request INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_employee_fk INT UNSIGNED NOT NULL,
  id_leave_type_fk SMALLINT UNSIGNED NOT NULL,
  start_date_leave DATE NOT NULL,
  end_date_leave DATE NOT NULL,
  status_leave ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO') DEFAULT 'PENDIENTE',
  reason_leave TEXT,
  requested_by INT UNSIGNED NOT NULL,
  requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_by INT UNSIGNED,
  approved_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (id_leave_type_fk) REFERENCES leave_type(id_leave_type) ON DELETE RESTRICT,
  FOREIGN KEY (requested_by) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (approved_by) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  -- Índices para optimización
  INDEX idx_leave_start_date (start_date_leave),
  INDEX idx_leave_end_date (end_date_leave),
  INDEX idx_leave_status (status_leave),
  INDEX idx_leave_requested_at (requested_at),
  INDEX idx_leave_approved_at (approved_at),
  -- CHECK constraints para integridad
  CONSTRAINT chk_leave_dates CHECK (end_date_leave >= start_date_leave),
  CONSTRAINT chk_leave_start_date CHECK (start_date_leave >= CURDATE() - INTERVAL 1 YEAR),
  CONSTRAINT chk_leave_approved_logic CHECK (
    (status_leave = 'PENDIENTE' AND approved_by IS NULL AND approved_at IS NULL) OR
    (status_leave IN ('APROBADO', 'RECHAZADO') AND approved_by IS NOT NULL AND approved_at IS NOT NULL)
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Solicitudes de permisos y ausencias';

-- ---------------------
-- 2.6 Tabla de incidencias
-- ---------------------
-- Registra las incidencias relacionadas con los empleados.
-- Incluye campos para tipo de incidencia, fecha, observaciones y apelaciones.
-- Permite registrar quién reportó la incidencia y cuándo.
DROP TABLE IF EXISTS incident;
CREATE TABLE incident (
  id_incident INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_employee_fk INT UNSIGNED NOT NULL,
  id_incident_type_fk SMALLINT UNSIGNED NOT NULL,
  date_incident DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  observation_incident TEXT,
  appeal_incident TEXT,
  reported_by INT UNSIGNED NOT NULL,
  reported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_incident ENUM('ABIERTO', 'EN_REVISION', 'CERRADO') DEFAULT 'ABIERTO',
  severity_incident ENUM('BAJA', 'MEDIA', 'ALTA', 'CRITICA') DEFAULT 'MEDIA',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_incident_type_fk) REFERENCES incident_type(id_incident_type) ON DELETE RESTRICT,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (reported_by) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  -- Índices para optimización
  INDEX idx_incident_date (date_incident),
  INDEX idx_incident_status (status_incident),
  INDEX idx_incident_severity (severity_incident),
  INDEX idx_incident_reported_at (reported_at),
  -- CHECK constraints
  CONSTRAINT chk_incident_date CHECK (date_incident <= NOW()),
  CONSTRAINT chk_incident_reported_at CHECK (reported_at <= NOW())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de incidencias de empleados';


/* ---------------------------------------------------------------------
   3. DATOS INICIALES DEL SISTEMA
   --------------------------------------------------------------------- */

INSERT INTO `users` (`name_user`, `password_hash_user`, `status_user`) VALUES
('l.tamez', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('c.rios', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('e.sanchez', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('n.perez', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('t.mejia', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('m.flores', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('j.perez', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO'),
('t.juarez', '$argon2id$v=19$m=65536,t=4,p=1$MHQxVVVQaFo4S2RsNTJHbw$lOLnRxIbRfFOUPMT5fy2pNjedG2sjkLLHCMVNkW4Ink', 'ACTIVO');

INSERT INTO `department` (`name_department`, `id_manager_employee_fk`) VALUES
('DIRECCION', NULL),
('RECURSOS HUMANOS', NULL),
('DISEÑO', NULL),
('VENTAS', NULL),
('SISTEMAS', NULL),
('CALIDAD', NULL),
('ALMACEN', NULL),
('COMPRAS', NULL);

INSERT INTO `level_position` (`name_level_position`, `description_level_position`) VALUES
('JUNIOR', 'Nivel de entrada para nuevos empleados'),
('SENIOR', 'Nivel avanzado con mayor responsabilidad'),
('LIDER', 'Responsable de un equipo o proyecto'),
('GERENTE', 'Encargado de la gestión de un departamento'),
('DIRECTOR', 'Alta dirección con visión estratégica');

INSERT INTO `positions` (`name_position`, `id_level_position_fk`, `id_departament_fk`) VALUES
('DIRECTOR GENERAL',5,1),
('GERENTE',4,2),
('AUXILIAR',1,2),
('GERENTE',4,3),
('DISEÑADOR',1,3),
('GERENTE',4,4),
('EJECUTIVO',1,4),
('GERENTE',4,5),
('TECNICO',1,5),
('GERENTE',4,6),
('AUDITOR',2,6),
('AUXILIAR',2,6);

INSERT INTO `employee` (`code_employee`, `name_employee`, `date_hired`, `type_employee`, `id_user_fk`, `id_position_fk`) VALUES
('TL-0001', 'TAMEZ ALCARAZ EDGAR LEONARDO', '2017-06-01', 'ADMINISTRATIVO', 1, 1),
('TL-0002', 'FLORES RIOS CLAUDIA', '2024-01-31', 'ADMINISTRATIVO', 2, 2),
('TL-0003', 'SANCHEZ ZEPEDA EMMANUEL', '2017-06-01', 'ADMINISTRATIVO', 3, 4),
('TL-0004', 'PEREZ MORALES NORMA ANGELICA', '2018-01-15', 'ADMINISTRATIVO', 4, 6),
('TL-0005', 'MEJIA NIEVES JESUS TADEO', '2025-05-08', 'ADMINISTRATIVO', 5, 8),
('TL-0006', 'FLORES GERARDO CLAUDIA MARLENE ', '2025-03-24', 'ADMINISTRATIVO', 6, 10),
('TL-0007', 'PEREZ GARCIA JOSE LUIS', '2025-04-01', 'ADMINISTRATIVO', 7, 11),
('TL-0008', 'JUAREZ GARCIA TATIANA', '2025-04-01', 'ADMINISTRATIVO', 8, 12);

/* ---------------------------------------------------------------------
   4. DATOS DE REFERENCIA ADICIONALES
   --------------------------------------------------------------------- */

-- Insertar roles básicos del sistema
INSERT INTO `roles` (`name_role`) VALUES
('ADMINISTRADOR'),
('GERENTE_RH'),
('SUPERVISOR'),
('EMPLEADO');

-- Insertar permisos básicos
INSERT INTO `permissions` (`code_permission`, `description_permission`) VALUES
('user_create', 'Crear nuevos usuarios'),
('user_edit', 'Editar información de usuarios'),
('user_delete', 'Eliminar usuarios'),
('employee_create', 'Crear registros de empleados'),
('employee_edit', 'Editar información de empleados'),
('employee_view_all', 'Ver información de todos los empleados'),
('contract_manage', 'Gestionar contratos de empleados'),
('leave_approve', 'Aprobar solicitudes de permisos'),
('incident_manage', 'Gestionar incidencias'),
('reports_generate', 'Generar reportes del sistema');

-- Insertar tipos de contrato
INSERT INTO `contract_type` (`name_contract_type`) VALUES
('INDEFINIDO'),
('TEMPORAL'),
('POR_PROYECTO'),
('PRACTICAS');

-- Insertar esquemas de nómina
INSERT INTO `payroll_scheme` (`name_payroll_scheme`, `frequency_payroll_scheme`) VALUES
('NOMINA_EMPLEADOS', 'QUINCENAL'),
('HONORARIOS_ASIMILADOS', 'MENSUAL'),
('SALARIO_EJECUTIVOS', 'MENSUAL');

-- Insertar tipos de licencia
INSERT INTO `leave_type` (`code_leave_type`, `name_leave_type`, `description_leave_type`, `auto_deduct_days`, `max_days_leave_type`) VALUES
('VAC', 'VACACIONES', 'Días de vacaciones anuales', 1, 365),
('ENF', 'ENFERMEDAD', 'Licencia por enfermedad', 0, 30),
('MAT', 'MATERNIDAD', 'Licencia de maternidad', 0, 84),
('PAT', 'PATERNIDAD', 'Licencia de paternidad', 0, 5),
('PER', 'PERSONAL', 'Permiso personal', 1, 3);

-- Insertar tipos de incidencia
INSERT INTO `incident_type` (`name_incident_type`, `description_incident_type`) VALUES
('RETRASO', 'Llegada tardía al trabajo'),
('FALTA', 'Ausencia no justificada'),
('ACCIDENTE', 'Accidente laboral'),
('COMPORTAMIENTO', 'Problema de comportamiento'),
('RENDIMIENTO', 'Bajo rendimiento laboral');

/* ---------------------------------------------------------------------
   5. OPTIMIZACIONES ADICIONALES Y CONFIGURACIONES
   --------------------------------------------------------------------- */

-- Configuraciones para optimización de rendimiento
SET GLOBAL innodb_buffer_pool_size = 268435456; -- 256MB para desarrollo
SET GLOBAL query_cache_size = 67108864; -- 64MB
SET GLOBAL query_cache_type = ON;

-- Crear procedimientos almacenados para operaciones comunes
DELIMITER //

-- Procedimiento para obtener empleados activos con su información completa
CREATE PROCEDURE GetActiveEmployeesWithDetails()
READS SQL DATA
COMMENT 'Obtiene empleados activos con información completa'
BEGIN
    SELECT 
        e.id_employee,
        e.code_employee,
        e.name_employee,
        e.date_hired,
        e.type_employee,
        e.seniority_employee,
        p.name_position,
        d.name_department,
        lp.name_level_position,
        u.name_user,
        u.status_user
    FROM employee e
    INNER JOIN positions p ON e.id_position_fk = p.id_position
    INNER JOIN department d ON p.id_departament_fk = d.id_department
    INNER JOIN level_position lp ON p.id_level_position_fk = lp.id_level_position
    INNER JOIN users u ON e.id_user_fk = u.id_user
    WHERE e.status_employee = 'ACTIVO'
    ORDER BY e.name_employee;
END//

-- Procedimiento para registrar una nueva sesión y cerrar sesiones previas
CREATE PROCEDURE CreateUserSession(
    IN p_user_id INT UNSIGNED,
    IN p_token CHAR(64),
    IN p_ip_addr VARBINARY(16)
)
MODIFIES SQL DATA
COMMENT 'Crea una nueva sesión y cierra sesiones previas del usuario'
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Cerrar sesiones activas existentes
    UPDATE user_sessions 
    SET is_active = 0, logout_at = NOW() 
    WHERE id_user_session_fk = p_user_id AND is_active = 1;
    
    -- Crear nueva sesión
    INSERT INTO user_sessions (id_user_session_fk, token_session, ip_addr_session, login_at)
    VALUES (p_user_id, p_token, p_ip_addr, NOW());
    
    COMMIT;
END//

DELIMITER ;

-- Crear vistas para consultas comunes
CREATE VIEW v_employee_summary AS
SELECT 
    e.id_employee,
    e.code_employee,
    e.name_employee,
    e.date_hired,
    e.status_employee,
    e.type_employee,
    ROUND(DATEDIFF(CURDATE(), e.date_hired) / 365.25, 1) AS years_service,
    p.name_position,
    d.name_department,
    c.salary_contract,
    c.start_date_contract
FROM employee e
INNER JOIN positions p ON e.id_position_fk = p.id_position
INNER JOIN department d ON p.id_departament_fk = d.id_department
LEFT JOIN contracts c ON e.id_employee = c.id_employee_fk AND c.is_active = 1;

/* ---------------------------------------------------------------------
   6. CONFIGURACIONES DE SEGURIDAD
   --------------------------------------------------------------------- */

-- Crear usuario específico para la aplicación (ejecutar como administrador)
-- CREATE USER 'intranet_app'@'localhost' IDENTIFIED BY 'SecurePassword123!';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON intranet_tl.* TO 'intranet_app'@'localhost';
-- GRANT EXECUTE ON intranet_tl.* TO 'intranet_app'@'localhost';

-- Restaurar configuraciones originales
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

SELECT *
FROM employee
JOIN positions ON employee.id_position_fk = positions.id_position
JOIN department ON positions.id_departament_fk = department.id_department
JOIN users ON users.id_user = employee.id_user_fk;