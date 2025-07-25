# Database Performance Optimization Recommendations
# Recomendaciones de Optimización de Rendimiento de Base de Datos

## English:
The following SQL statements create indexes that will significantly improve query performance for the intranet application. Run these on your MySQL database to optimize the most common queries.

## Español:
Las siguientes declaraciones SQL crean índices que mejorarán significativamente el rendimiento de las consultas para la aplicación de intranet. Ejecuta estos en tu base de datos MySQL para optimizar las consultas más comunes.

## Critical Indexes for Performance / Índices Críticos para Rendimiento:

```sql
-- User table optimizations / Optimizaciones de tabla de usuarios
CREATE INDEX idx_users_email ON users(email_user);
CREATE INDEX idx_users_name ON users(name_user);
CREATE INDEX idx_users_status ON users(status_user);

-- Employee table optimizations / Optimizaciones de tabla de empleados
CREATE INDEX idx_employee_status ON employee(status_employee);
CREATE INDEX idx_employee_user_fk ON employee(id_user_fk);
CREATE INDEX idx_employee_position_fk ON employee(id_position_fk);
CREATE INDEX idx_employee_name ON employee(name_employee);

-- User sessions optimizations / Optimizaciones de sesiones de usuario
CREATE INDEX idx_user_sessions_user_fk ON user_sessions(id_user_session_fk);
CREATE INDEX idx_user_sessions_token ON user_sessions(token_session);
CREATE INDEX idx_user_sessions_active ON user_sessions(is_active);
CREATE INDEX idx_user_sessions_login_at ON user_sessions(login_at);
CREATE INDEX idx_user_sessions_composite ON user_sessions(id_user_session_fk, is_active);

-- Session history optimizations / Optimizaciones de historial de sesiones
CREATE INDEX idx_session_history_user_fk ON session_history(id_user_session_fk);
CREATE INDEX idx_session_history_login_at ON session_history(login_at);

-- Position and department optimizations / Optimizaciones de posición y departamento
CREATE INDEX idx_positions_department_fk ON positions(id_departament_fk);

-- Roles and permissions optimizations / Optimizaciones de roles y permisos
CREATE INDEX idx_user_roles_user_fk ON user_roles(id_user_fk);
CREATE INDEX idx_user_roles_role_fk ON user_roles(id_role_fk);
CREATE INDEX idx_role_permissions_role_fk ON role_permissions(id_role_fk);
CREATE INDEX idx_role_permissions_permission_fk ON role_permissions(id_permission_fk);
```

## Additional Performance Tips / Consejos Adicionales de Rendimiento:

### English:
1. **Query Caching**: Enable MySQL query cache in your my.cnf:
   ```
   query_cache_type = 1
   query_cache_size = 128M
   ```

2. **InnoDB Buffer Pool**: Increase buffer pool size:
   ```
   innodb_buffer_pool_size = 1G
   ```

3. **Connection Pooling**: The application now uses persistent connections for better performance.

4. **Monitor Queries**: Use `EXPLAIN` to analyze slow queries:
   ```sql
   EXPLAIN SELECT * FROM employee WHERE status_employee = 1;
   ```

### Español:
1. **Caché de Consultas**: Habilita el caché de consultas de MySQL en tu my.cnf:
   ```
   query_cache_type = 1
   query_cache_size = 128M
   ```

2. **Pool de Buffer de InnoDB**: Aumenta el tamaño del pool de buffer:
   ```
   innodb_buffer_pool_size = 1G
   ```

3. **Agrupación de Conexiones**: La aplicación ahora usa conexiones persistentes para mejor rendimiento.

4. **Monitorear Consultas**: Usa `EXPLAIN` para analizar consultas lentas:
   ```sql
   EXPLAIN SELECT * FROM employee WHERE status_employee = 1;
   ```

## Performance Monitoring / Monitoreo de Rendimiento:

### English:
Monitor these metrics to ensure optimal performance:
- Query execution time
- Database connection count
- Cache hit ratios
- Index usage statistics

### Español:
Monitorea estas métricas para asegurar un rendimiento óptimo:
- Tiempo de ejecución de consultas
- Cantidad de conexiones a la base de datos
- Ratios de aciertos de caché
- Estadísticas de uso de índices