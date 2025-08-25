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
  name_permission VARCHAR(60) NOT NULL UNIQUE,
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
  password_hash_user VARCHAR(255) NOT NULL,
  status_user ENUM('ACTIVO', 'INACTIVO', 'BLOQUEADO', 'FORZAR_RESET') DEFAULT 'FORZAR_RESET',
  id_employee_fk INT UNSIGNED NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  UNIQUE KEY uq_active_session (id_user_session_fk, is_active),
  FOREIGN KEY (id_user_session_fk) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- Trigger para insertar en el historial al cerrar sesión.
-- Se activa cuando se actualiza una sesión a inactiva (logout).
-- Inserta un registro en `session_history` con los datos de la sesión.
-- Esto permite mantener un registro histórico de todas las sesiones.
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

-- --------------------
-- 1.5 Auditoría genérica
-- --------------------
-- audit_log guarda cambios con diff JSON (old→new) y metadatos.
DROP TABLE IF EXISTS audit_log;
CREATE TABLE audit_log (
  id_audit_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_name VARCHAR(50),
  entity_id BIGINT UNSIGNED,
  action_type ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','VIEW_PII'),
  changes_json JSON,
  performed_by INT UNSIGNED,
  performed_at DATETIME NOT NULL,
  ip_addr_session VARBINARY(16),
  FOREIGN KEY (performed_by) REFERENCES users(id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  id_manager_employee_fk INT UNSIGNED DEFAULT NULL
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
  id_department_fk SMALLINT UNSIGNED,
  UNIQUE KEY uq_position (name_position, id_department_fk),
  FOREIGN KEY (id_level_position_fk) REFERENCES level_position(id_level_position),
  FOREIGN KEY (id_department_fk) REFERENCES department(id_department) ON DELETE SET NULL
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
  code_incident_type VARCHAR(20) NOT NULL UNIQUE,
  name_incident_type VARCHAR(100) NOT NULL UNIQUE,
  description_incident_type TEXT,
  severity_incident_type ENUM('BAJA', 'MEDIA', 'ALTA', 'CRITICA') NOT NULL DEFAULT 'BAJA',
  action_incident_type VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE incident_type
  ADD COLUMN code_incident_type VARCHAR(20) NOT NULL UNIQUE FIRST;
ALTER TABLE incident_type
  ADD COLUMN severity_incident_type ENUM('BAJA', 'MEDIA', 'ALTA', 'CRITICA') NOT NULL DEFAULT 'BAJA' AFTER description_incident_type;  
ALTER TABLE incident_type
  ADD COLUMN action_incident_type VARCHAR(100) NOT NULL AFTER severity_incident_type;

-- --------------------
-- 2.2 Tabla employees
-- --------------------
-- Registra a los empleados con sus datos básicos y referencias a otros catálogos.
-- Incluye campos para código, nombre, fecha de contratación, estado y tipo.
DROP TABLE IF EXISTS employee;
CREATE TABLE employee (
  id_employee INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code_employee VARCHAR(10) NULL UNIQUE,
  name_employee VARCHAR(100) COLLATE utf8mb4_bin NOT NULL UNIQUE,
  date_hired DATE NOT NULL,
  status_employee ENUM('ACTIVO', 'INACTIVO', 'SUSPENDIDO') DEFAULT 'ACTIVO',
  type_employee ENUM('OPERATIVO', 'ADMINISTRATIVO') NOT NULL DEFAULT 'OPERATIVO',
  seniority_employee DECIMAL(4,2) NULL,
  id_position_fk INT UNSIGNED NOT NULL,
  FOREIGN KEY (id_position_fk) REFERENCES positions(id_position) ON DELETE RESTRICT,
  CONSTRAINT chk_employee_seniority CHECK (seniority_employee >= 0 AND seniority_employee <= 99.9)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE department
  ADD CONSTRAINT id_manager_department_fk FOREIGN KEY (id_manager_employee_fk) REFERENCES employee(id_employee) ON DELETE SET NULL;


-- Triggers para manejar el código del empleado y calcular la antigüedad.
-- El código se genera automáticamente en el formato TL-XXXX, donde XXXX es un número secuencial.
-- La antigüedad se calcula en años redondeados a un decimal.
SET @last_id = NULL;
DELIMITER //
CREATE TRIGGER trg_employee_insert_new
BEFORE INSERT ON employee
FOR EACH ROW
BEGIN
  DECLARE last_id INT;
  IF last_id IS NULL THEN
    SELECT COALESCE(SUBSTRING(MAX(code_employee), 4) + 0, 0) +1 INTO last_id
    FROM employee
    WHERE code_employee LIKE 'TL-%';
  END IF;
  SET NEW.code_employee = CONCAT('TL-', LPAD(last_id, 4, '0'));
  SET NEW.seniority_employee = ROUND(TIMESTAMPDIFF(MONTH, NEW.date_hired, CURDATE()) / 12, 1);
END//
DELIMITER ;

-- Trigger para actualizar la antigüedad al modificar la fecha de contratación.
-- Se recalcula la antigüedad solo si la fecha de contratación cambia.
-- Esto asegura que la antigüedad se mantenga actualizada sin afectar otros campos.
DELIMITER //
CREATE TRIGGER trg_employee_update_seniority
BEFORE UPDATE ON employee
FOR EACH ROW
BEGIN
  IF NEW.date_hired <> OLD.date_hired THEN
    SET NEW.seniority_employee = ROUND(TIMESTAMPDIFF(MONTH, NEW.date_hired, CURDATE()) / 12, 1);
  END IF;
END//
DELIMITER ;

-- Trigger para crear un usuario pendiente al insertar un nuevo empleado.
-- Inserta un registro en la tabla `users` con el código del empleado y un estado de 'FORZAR_RESET'.
-- Esto asegura que cada nuevo empleado tenga un usuario asociado para autenticación.
DELIMITER //
CREATE TRIGGER trg_user_after_insert_employee
AFTER INSERT ON employee
FOR EACH ROW
BEGIN
  INSERT INTO users
    (name_user, password_hash_user, id_employee_fk)
  VALUES
    (NEW.code_employee, '$pending$', NEW.id_employee);
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
  image_employee_profile VARCHAR(255) DEFAULT NULL,
  birthdate_employee_profile DATE,
  blood_type_employee_profile ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') DEFAULT NULL,
  gender_employee_profile ENUM('HOMBRE', 'MUJER') NOT NULL,
  marital_status_employee_profile ENUM('SOLTERO', 'CASADO', 'DIVORCIADO', 'VIUDO') DEFAULT 'SOLTERO',
  rfc_employee_profile VARCHAR(13) UNIQUE,
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
  digital_file_employee_profile VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  id_snapshot_department SMALLINT UNSIGNED,
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
  number_payroll_contract INT UNSIGNED NOT NULL,
  code_employee_snapshot CHAR(10) DEFAULT NULL,
  id_contract_type_fk SMALLINT UNSIGNED NOT NULL,
  id_payroll_scheme_fk SMALLINT UNSIGNED NOT NULL,
  start_date_contract DATE NOT NULL,
  trial_period_contract DATE,
  end_date_contract DATE,
  salary_contract DECIMAL(10,2) NOT NULL,
  termination_reason_contract TEXT,
  is_active TINYINT(1) DEFAULT 1,
  UNIQUE KEY uq_active_contract (id_employee_fk, is_active),
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (id_contract_type_fk) REFERENCES contract_type(id_contract_type) ON DELETE RESTRICT,
  FOREIGN KEY (id_payroll_scheme_fk) REFERENCES payroll_scheme(id_payroll_scheme) ON DELETE RESTRICT,
  CONSTRAINT chk_salary_contract CHECK (salary_contract >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trigger para manejar el código del empleado en los contratos.
-- Se asegura que al insertar un nuevo contrato, el código del empleado se complete automáticamente
-- si no se proporciona. El código se obtiene del empleado asociado al contrato.
-- Esto permite mantener la consistencia del código del empleado en los contratos.
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
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (id_leave_type_fk) REFERENCES leave_type(id_leave_type) ON DELETE RESTRICT,
  FOREIGN KEY (requested_by) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (approved_by) REFERENCES employee(id_employee) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  severity_incident ENUM('BAJA', 'MEDIA', 'ALTA', 'CRITICA') DEFAULT 'MEDIA',
  reported_by INT UNSIGNED NOT NULL,
  reported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_incident_type_fk) REFERENCES incident_type(id_incident_type) ON DELETE RESTRICT,
  FOREIGN KEY (id_employee_fk) REFERENCES employee(id_employee) ON DELETE RESTRICT,
  FOREIGN KEY (reported_by) REFERENCES employee(id_employee) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar departamentos básicos del sistema
INSERT INTO `department` (`name_department`, `id_manager_employee_fk`) VALUES
('DIRECCION', NULL),
('RECURSOS HUMANOS', NULL),
('DISEÑO', NULL),
('VENTAS', NULL),
('SISTEMAS', NULL),
('CALIDAD', NULL),
('ALMACEN', NULL),
('COMPRAS', NULL);

-- Insertar niveles de puesto básicos del sistema
INSERT INTO `level_position` (`name_level_position`, `description_level_position`) VALUES
('JUNIOR', 'Nivel de entrada para nuevos empleados administrativos'),
('INTERMEDIO', 'Nivel con experiencia moderada y autonomía para empleos administrativos'),
('SENIOR', 'Nivel avanzado con mayor responsabilidad para empleos administrativos'),
('LIDER', 'Responsable de un equipo o proyecto'),
('GERENTE', 'Encargado de la gestión de un departamento'),
('DIRECTOR', 'Alta dirección con visión estratégica'),
('INGENIERO', 'Nivel técnico especializado con alta responsabilidad'),
('AUDITOR', 'Responsable de auditorías y cumplimiento normativo'),
('TECNICO', 'Especialista técnico con habilidades prácticas'),
('AUXILIAR', 'Soporte administrativo básico');

-- Insertar puestos básicos del sistema
INSERT INTO `positions` (`name_position`, `id_level_position_fk`, `id_department_fk`) VALUES
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

-- Insertar roles básicos del sistema
INSERT INTO `roles` (`name_role`) VALUES
('ADMINISTRADOR'),
('GERENTE_RH'),
('SUPERVISOR'),
('EMPLEADO');

-- Insertar permisos básicos
INSERT INTO `permissions` (`name_permission`, `description_permission`) VALUES
('user_create', 'Crear nuevos usuarios'),
('user_edit', 'Editar información de usuarios'),
('user_delete', 'Eliminar usuarios'),
('employee_create', 'Crear registros de empleados'),
('employee_edit', 'Editar información de empleados'),
('employee_view_all', 'Ver información de todos los empleados'),
('employee_view', 'Ver información de un empleado'),
('employee_delete', 'Eliminar registros de empleados'),
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
('NOMINA_EMPLEADOS', 'SEMANAL'),
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
INSERT INTO `incident_type` (`code_incident_type`, `name_incident_type`, `description_incident_type`, `severity_incident_type`, `action_incident_type`) VALUES
('INC-001', 'RETRASO', 'Incidencia por llegar tarde al trabajo', 'BAJA', 'Advertencia verbal'),
('INC-002', 'FALTA', 'Incidencia por falta sin justificación', 'MEDIA', 'Descuento de salario'),
('INC-003', 'ACCIDENTE', 'Incidencia por accidente en el trabajo', 'ALTA', 'Investigación de incidente'),
('INC-004', 'COMPORTAMIENTO', 'Incidencia por mal comportamiento', 'MEDIA', 'Plan de mejora'),
('INC-005', 'RENDIMIENTO', 'Incidencia por bajo rendimiento', 'BAJA', 'Capacitación adicional');

-- Insertar empleados básicos del sistema
INSERT INTO `employee` (`name_employee`, `date_hired`, `type_employee`, `id_position_fk`) VALUES
('TAMEZ ALCARAZ EDGAR LEONARDO', '2017-06-01', 'ADMINISTRATIVO', 1),
('FLORES RIOS CLAUDIA', '2024-01-31', 'ADMINISTRATIVO', 2),
('SANCHEZ ZEPEDA EMMANUEL', '2017-06-01', 'ADMINISTRATIVO', 4),
('PEREZ MORALES NORMA ANGELICA', '2018-01-15', 'ADMINISTRATIVO', 6),
('MEJIA NIEVES JESUS TADEO', '2025-05-08', 'ADMINISTRATIVO', 8),
('FLORES GERARDO CLAUDIA MARLENE ', '2025-03-24', 'ADMINISTRATIVO', 10),
('PEREZ GARCIA JOSE LUIS', '2025-04-01', 'ADMINISTRATIVO', 7),
('JUAREZ GARCIA TATIANA', '2025-04-01', 'ADMINISTRATIVO', 3),
('GARCIA PEREZ JUAN CARLOS', '2025-04-01', 'ADMINISTRATIVO', 5),
('HERNANDEZ LOPEZ MARIA JOSE', '2025-04-01', 'ADMINISTRATIVO', 9);

DROP EVENT IF EXISTS `employee_senority_update`;
DELIMITER //
CREATE EVENT `employee_senority_update`
ON SCHEDULE
  EVERY 1 DAY
  STARTS '2025-08-06 23:59:59'
DO
BEGIN
  IF DAY(CURDATE()) = DAY(LAST_DAY(CURDATE())) THEN
    UPDATE employee
      SET seniority_employee = ROUND(TIMESTAMPDIFF(MONTH, date_hired, CURDATE()) / 12, 1)
    WHERE status_employee = 'ACTIVE';
  END IF;
END//
DELIMITER ;

ALTER TABLE employee_profile
  ADD COLUMN image_employee_profile VARCHAR(255) DEFAULT NULL,
  ADD COLUMN rfc_employee_profile VARCHAR(13) UNIQUE,
  ADD COLUMN digital_file_employee_profile VARCHAR(255) DEFAULT NULL;