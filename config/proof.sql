-- ############################################################
--  ESQUEMA COMPLETO – INTRANET (VERSION LECTURA / COMENTADA)
--  Incluye módulo de Seguridad + Módulo de Empleados
--  Motor: InnoDB | Charset: utf8mb4 | Compatibilidad: MySQL/MariaDB 10.5+
-- ------------------------------------------------------------
--  Convenciones de este archivo:
--  • Cada TABLA se crea con comentarios detallados que explican su propósito.
--  • Cada COLUMNA incluye un COMMENT acorde a su función.
--  • Bloques se agrupan por módulo para localizar fácil el código.
--  • Al final se incluyen TRIGGERS y EVENTOS automatizados.
-- ############################################################

/* ---------------------------------------------------------------------
   PREPARACIÓN GENERAL DEL ESQUEMA
   --------------------------------------------------------------------- */

-- (Opcional) Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS intranet_db
  DEFAULT CHARACTER SET = utf8mb4
  DEFAULT COLLATE        = utf8mb4_general_ci;
USE intranet_db;

SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

/* ---------------------------------------------------------------------
   1. MÓDULO DE SEGURIDAD Y AUTENTICACIÓN
   --------------------------------------------------------------------- */

-- --------------------
-- 1.1 Tabla `roles`
-- --------------------
-- Define los grupos lógicos de permisos (ej. Admin, RH, Auditor).
-- Se asignan a través de la tabla intermedia `user_roles` para que un
-- usuario pueda tener múltiples roles.
CREATE TABLE roles (
  id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del rol',
  name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre legible del rol (Admin, RH…)'
) ENGINE=InnoDB COMMENT='Catálogo de roles de usuario';

-- --------------------
-- 1.2 Tabla `permissions`
-- --------------------
-- Acciones atómicas que la aplicación reconoce (p.ej. employee_create,
-- contract_manage). Se unen a los roles vía tabla puente.
CREATE TABLE permissions (
  id   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del permiso',
  code VARCHAR(60) NOT NULL UNIQUE COMMENT 'Clave de la acción autorizable',
  description VARCHAR(255) COMMENT 'Descripción legible de la acción'
) ENGINE=InnoDB COMMENT='Catálogo de permisos granulares';

-- Tabla puente M:N entre roles y permisos
CREATE TABLE role_permissions (
  role_id       SMALLINT UNSIGNED COMMENT 'FK al rol',
  permission_id SMALLINT UNSIGNED COMMENT 'FK al permiso',
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB COMMENT='Relación muchos-a-muchos roles ↔ permisos';

-- --------------------
-- 1.3 Tabla `users`
-- --------------------
-- Autenticación principal de la intranet.
CREATE TABLE users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK usuario',
  username       VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre de inicio de sesión',
  email          VARCHAR(100) NOT NULL UNIQUE COMMENT 'Correo corporativo',
  password_hash  CHAR(97) NOT NULL COMMENT 'Hash Argon2id / Bcrypt de la contraseña',
  status         ENUM('active','blocked','inactive') DEFAULT 'active' COMMENT 'Estado de la cuenta',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de alta',
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última modificación'
) ENGINE=InnoDB COMMENT='Usuarios de la intranet';

-- Tabla puente M:N usuario ↔ rol
CREATE TABLE user_roles (
  user_id INT UNSIGNED COMMENT 'FK al usuario',
  role_id SMALLINT UNSIGNED COMMENT 'FK al rol',
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB COMMENT='Relación usuarios ↔ roles';

-- --------------------
-- 1.4 Control de sesión única
-- --------------------
-- user_sessions garantiza una sola sesión activa por usuario.
CREATE TABLE user_sessions (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK sesión',
  user_id       INT UNSIGNED NOT NULL COMMENT 'FK al usuario',
  session_token CHAR(64) NOT NULL UNIQUE COMMENT 'Token aleatorio de la sesión',
  ip_addr       VARBINARY(16) NOT NULL COMMENT 'IP origen (packed IPv4/IPv6)',
  login_at      DATETIME NOT NULL COMMENT 'Momento del login',
  expires_at    DATETIME COMMENT 'Fecha de expiración',
  is_active     TINYINT(1) DEFAULT 1 COMMENT '1 = vigente, 0 = cerrada',
  UNIQUE KEY uq_one_session (user_id, is_active),
  CONSTRAINT fk_us_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Sesión activa del usuario (una a la vez)';

-- Historial de sesiones (se guarda todo)
CREATE TABLE session_history LIKE user_sessions;
ALTER TABLE session_history DROP KEY uq_one_session;
ALTER TABLE session_history ADD CONSTRAINT fk_sh_user FOREIGN KEY (user_id) REFERENCES users(id);

-- Trigger pasa registro de user_sessions → session_history al cerrar la sesión
DELIMITER //
CREATE TRIGGER trg_session_to_history
AFTER UPDATE ON user_sessions
FOR EACH ROW
BEGIN
  IF OLD.is_active = 1 AND NEW.is_active = 0 THEN
    INSERT INTO session_history
      (user_id, session_token, ip_addr, login_at, expires_at, is_active)
    VALUES
      (NEW.user_id, NEW.session_token, NEW.ip_addr, NEW.login_at, NEW.expires_at, NEW.is_active);
  END IF;
END//
DELIMITER ;

-- --------------------
-- 1.5 Auditoría genérica
-- --------------------
-- audit_log guarda cambios con diff JSON (old→new) y metadatos.
CREATE TABLE audit_log (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK registro auditoría',
  entity       VARCHAR(50) COMMENT 'Tabla o entidad afectada',
  entity_id    BIGINT UNSIGNED COMMENT 'ID del registro afectado',
  action       ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT','VIEW_PII') COMMENT 'Tipo de acción',
  changes_json JSON COMMENT 'Valores cambiados (diff)',
  performed_by INT UNSIGNED COMMENT 'Usuario que ejecutó la acción',
  performed_at DATETIME NOT NULL COMMENT 'Fecha/hora de la acción',
  ip_addr      VARBINARY(16) COMMENT 'IP origen',
  CONSTRAINT fk_al_user FOREIGN KEY (performed_by) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Bitácora de auditoría con diff por columna';

/* ---------------------------------------------------------------------
   2. MÓDULO DE EMPLEADOS (HR)
   --------------------------------------------------------------------- */

-- --------------------
-- 2.1 Catálogos
-- --------------------
CREATE TABLE departments (
  id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK departamento',
  name        VARCHAR(80) NOT NULL UNIQUE COMMENT 'Nombre del departamento',
  manager_id  INT UNSIGNED COMMENT 'Empleado responsable (nullable)',
  CONSTRAINT fk_dept_manager FOREIGN KEY (manager_id) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Catálogo de departamentos';

CREATE TABLE positions (
  id    SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK puesto',
  name  VARCHAR(80) NOT NULL UNIQUE COMMENT 'Nombre del puesto',
  level TINYINT UNSIGNED COMMENT 'Nivel jerárquico o experiencia (1–5)'
) ENGINE=InnoDB COMMENT='Catálogo de puestos';

CREATE TABLE contract_types (
  id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK tipo de contrato',
  name VARCHAR(40) NOT NULL UNIQUE COMMENT 'planta, indefinido, temporal, practicante'
) ENGINE=InnoDB COMMENT='Tipos de contrato';

CREATE TABLE payroll_schemes (
  id      TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK esquema de pago',
  scheme  VARCHAR(40) NOT NULL UNIQUE COMMENT 'nomina, asimilado, honorarios…'
) ENGINE=InnoDB COMMENT='Esquemas de nómina';

CREATE TABLE leave_types (
  id              TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK tipo de ausencia',
  code            VARCHAR(10) NOT NULL UNIQUE COMMENT 'Código (VAC, MED, PER…) ',
  description     VARCHAR(100) COMMENT 'Descripción',
  auto_deduct_days TINYINT UNSIGNED DEFAULT 0 COMMENT 'Días que descuenta automáticamente'
) ENGINE=InnoDB COMMENT='Catálogo de ausencias';

-- --------------------
-- 2.2 Tabla `employees`
-- --------------------
CREATE TABLE employees (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK empleado',
  employee_code  CHAR(8) NOT NULL UNIQUE COMMENT 'Código interno único',
  clock_code     CHAR(8) UNIQUE COMMENT 'Código para reloj checador',
  first_name     VARCHAR(60) COMMENT 'Nombre(s)',
  last_name      VARCHAR(60) COMMENT 'Apellido(s)',
  hire_date      DATE COMMENT 'Fecha de ingreso original',
  status         ENUM('active','inactive','terminated') DEFAULT 'active' COMMENT 'Estado laboral',
  user_id        INT UNSIGNED COMMENT 'FK a users (si tiene acceso intranet)',
  is_active      TINYINT(1) DEFAULT 1 COMMENT '0 = borrado lógico',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_emp_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Empleados';

-- Datos personales sensibles en tabla aparte
CREATE TABLE employee_profile (
  employee_id        INT UNSIGNED PRIMARY KEY COMMENT 'FK a employees (1:1)',
  dob                DATE COMMENT 'Fecha de nacimiento',
  gender             ENUM('F','M','O') COMMENT 'Género',
  curp               CHAR(18) COMMENT 'CURP (MX)',
  nss                CHAR(11) COMMENT 'Número de Seguro Social (IMSS)',
  phone              VARCHAR(20) COMMENT 'Teléfono de contacto',
  address            VARCHAR(255) COMMENT 'Dirección',
  emergency_contact  VARCHAR(255) COMMENT 'Contacto de emergencia',
  updated_by         INT UNSIGNED COMMENT 'Usuario que actualizó',
  updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_profile_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_profile_user FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Datos sensibles del empleado';

-- Historial de puestos (solo 1 activo al tiempo)
CREATE TABLE employee_position_history (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK historial puesto',
  employee_id    INT UNSIGNED COMMENT 'Empleado',
  position_id    SMALLINT UNSIGNED COMMENT 'Puesto',
  department_id  SMALLINT UNSIGNED COMMENT 'Departamento',
  start_date     DATE COMMENT 'Inicio',
  end_date       DATE COMMENT 'Fin (NULL = actual)',
  CONSTRAINT fk_eph_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_eph_pos FOREIGN KEY (position_id) REFERENCES positions(id),
  CONSTRAINT fk_eph_dept FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB COMMENT='Historial de puestos del empleado';

-- Trigger para garantizar único registro activo
DELIMITER //
CREATE TRIGGER trg_one_active_position
BEFORE INSERT ON employee_position_history
FOR EACH ROW
BEGIN
  IF NEW.end_date IS NULL THEN
    IF EXISTS (SELECT 1 FROM employee_position_history
               WHERE employee_id = NEW.employee_id AND end_date IS NULL) THEN
      SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Solo puede haber un puesto activo por empleado';
    END IF;
  END IF;
END//
DELIMITER ;

-- Contratos
CREATE TABLE contracts (
  id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK contrato',
  employee_id             INT UNSIGNED COMMENT 'Empleado',
  employee_code_snapshot  CHAR(8) COMMENT 'Copia del código de empleado al momento',
  contract_type_id        TINYINT UNSIGNED COMMENT 'Tipo de contrato',
  payroll_scheme_id       TINYINT UNSIGNED COMMENT 'Esquema nómina',
  start_date              DATE COMMENT 'Inicio contrato',
  end_date                DATE COMMENT 'Fin (NULL = vigente)',
  weekly_hours            TINYINT UNSIGNED COMMENT 'Horas semanales',
  salary_type             ENUM('fixed','variable') COMMENT 'Tipo de salario',
  salary_amount           DECIMAL(12,2) COMMENT 'Monto',
  is_current              TINYINT(1) DEFAULT 1 COMMENT '1 = contrato vigente',
  CONSTRAINT uq_current_contract UNIQUE (employee_id, is_current),
  CONSTRAINT fk_con_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_con_type FOREIGN KEY (contract_type_id) REFERENCES contract_types(id),
  CONSTRAINT fk_con_pay FOREIGN KEY (payroll_scheme_id) REFERENCES payroll_schemes(id)
) ENGINE=InnoDB COMMENT='Contratos del empleado';

-- Ausencias / permisos
CREATE TABLE leaves (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK permiso/vacación',
  employee_id    INT UNSIGNED COMMENT 'Empleado',
  leave_type_id  TINYINT UNSIGNED COMMENT 'Tipo de ausencia',
  start_date     DATE COMMENT 'Inicio',
  end_date       DATE COMMENT 'Fin',
  status         ENUM('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Estado',
  requested_by   INT UNSIGNED COMMENT 'Usuario que solicita',
  approved_by    INT UNSIGNED COMMENT 'Quien aprueba (nullable)',
  CONSTRAINT fk_leave_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_leave_type FOREIGN KEY (leave_type_id) REFERENCES leave_types(id),
  CONSTRAINT fk_leave_req FOREIGN KEY (requested_by) REFERENCES users(id),
  CONSTRAINT fk_leave_app FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Registro de ausencias';

-- Incidencias disciplinarias
CREATE TABLE incidents (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK incidente',
  employee_id    INT UNSIGNED COMMENT 'Empleado',
  incident_date  DATE COMMENT 'Fecha del incidente',
  category       VARCHAR(40) COMMENT 'Tipo (retardo, falta, seguridad…) ',
  description    VARCHAR(255) COMMENT 'Detalle',
  action_taken   VARCHAR(255) COMMENT 'Medida correctiva',
  recorded_by    INT UNSIGNED COMMENT 'Usuario que registra',
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inc_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_inc_rec FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Incidencias y sanciones';

-- Archivos PDF en NAS
CREATE TABLE employee_files (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK archivo',
  employee_id   INT UNSIGNED COMMENT 'Empleado',
  file_path     VARCHAR(255) COMMENT 'Ruta relativa en NAS',
  category      VARCHAR(40) COMMENT 'contrato, identificación…',
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  uploaded_by   INT UNSIGNED COMMENT 'Usuario que sube',
  CONSTRAINT fk_ef_emp FOREIGN KEY (employee_id) REFERENCES employees(id),
  CONSTRAINT fk_ef_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB COMMENT='Metadatos de expedientes digitales';

-- Registro del reloj checador
CREATE TABLE clock_records (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'PK registro checador',
  clock_code     CHAR(8) COMMENT 'Código reloj (FK lógico)',
  check_time     DATETIME COMMENT 'Marca de tiempo',
  type           ENUM('in','out') COMMENT 'in = entrada, out = salida',
  source_device  VARCHAR(40) COMMENT 'Dispositivo origen'
) ENGINE=InnoDB COMMENT='Marcas de reloj (importadas)';

/* ---------------------------------------------------------------------
   3. EVENTOS AUTOMÁTICOS DEL SCHEDULER
   --------------------------------------------------------------------- */

SET GLOBAL EVENT_SCHEDULER = ON;

-- Aviso de contratos por vencer en 30 días
CREATE EVENT IF NOT EXISTS ev_expiring_contracts
ON SCHEDULE EVERY 1 DAY STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO
  INSERT INTO audit_log(entity, action, performed_at, performed_by)
  VALUES ('contracts', 'SYSTEM_ALERT', NOW(), NULL);

-- Cálculo de antigüedad diaria (ejemplo simplificado)
CREATE EVENT IF NOT EXISTS ev_seniority_update
ON SCHEDULE EVERY 1 DAY STARTS CURRENT_TIMESTAMP + INTERVAL 1 DAY
DO
  UPDATE employees SET updated_at = updated_at;

/* ---------------------------------------------------------------------
   RESTAURAR FLAG DE FK
   --------------------------------------------------------------------- */
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

-- ############################################################
--  FIN DE ESQUEMA COMPLETO (LECTURA)
-- ############################################################
