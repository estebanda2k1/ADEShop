# Documentación del Módulo de Registro - ADESHOP

## Estructura de la Tabla Users (Actualizada)

La tabla `users` ha sido actualizada para soportar el registro completo de usuarios con los siguientes campos:

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,           -- Nombres del usuario
  apellidos VARCHAR(100) NOT NULL,         -- Apellidos del usuario
  email VARCHAR(255) NOT NULL UNIQUE,      -- Correo electrónico (único)
  cedula VARCHAR(20) NOT NULL UNIQUE,      -- Cédula de identidad (único)
  username VARCHAR(100) NOT NULL UNIQUE,   -- Nombre de usuario (generado automáticamente)
  password VARCHAR(255) NOT NULL,          -- Contraseña (hasheada con bcrypt)
  is_admin TINYINT(1) DEFAULT 0,          -- 0=usuario normal, 1=administrador
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Campos del Formulario de Registro

1. **Nombres** (obligatorio)
   - Tipo: texto
   - Validación: Solo letras y espacios
   - Máximo: 100 caracteres

2. **Apellidos** (obligatorio)
   - Tipo: texto
   - Validación: Solo letras y espacios
   - Máximo: 100 caracteres

3. **Correo Electrónico** (obligatorio)
   - Tipo: email
   - Validación: Formato de email válido
   - Único en la base de datos
   - Máximo: 255 caracteres

4. **Cédula** (obligatorio)
   - Tipo: texto numérico
   - Validación: Solo números, mínimo 6 dígitos
   - Único en la base de datos
   - Máximo: 20 caracteres

5. **Contraseña** (obligatorio)
   - Tipo: password
   - Validación: Mínimo 6 caracteres
   - Se hashea con `password_hash()` antes de guardar

6. **Confirmar Contraseña** (obligatorio)
   - Tipo: password
   - Validación: Debe coincidir con la contraseña

## Validaciones Implementadas

### Frontend (JavaScript)
- Validación en tiempo real de campos
- Solo números permitidos en cédula
- Solo letras y espacios en nombres/apellidos
- Comparación de contraseñas en tiempo real
- Validación HTML5 nativa

### Backend (PHP)
- Sanitización de todos los inputs
- Validación de formato de email
- Validación de longitud de contraseña (mínimo 6 caracteres)
- Verificación de coincidencia de contraseñas
- Verificación de unicidad de email
- Verificación de unicidad de cédula
- Hash seguro de contraseña con `password_hash()`
- Manejo de errores PDO

## Características de Seguridad

1. **Contraseñas Hasheadas**: Se usa `password_hash()` con PASSWORD_DEFAULT (bcrypt)
2. **Prepared Statements**: Todas las consultas SQL usan prepared statements para prevenir SQL injection
3. **Validación Doble**: Frontend y backend
4. **Sanitización**: Uso de `htmlspecialchars()` y `trim()`
5. **Campos Únicos**: Email y cédula verificados antes de insertar

## Generación Automática de Username

El sistema genera automáticamente un `username` basado en:
- Primera parte del email (antes del @)
- Si ya existe, se agrega un timestamp para hacerlo único
- Ejemplo: `juan@email.com` → username: `juan` (o `juan1729234567` si ya existe)

## Flujo de Registro

1. Usuario completa el formulario
2. Validación frontend en tiempo real
3. Usuario envía el formulario
4. Validación backend completa
5. Verificación de email único
6. Verificación de cédula única
7. Generación de username único
8. Hash de contraseña
9. Inserción en base de datos
10. Mensaje de éxito y opciones para continuar

## Cómo Conectar a la Base de Datos

Actualmente el código está preparado para conectarse a la base de datos usando PDO.
Cuando estés listo para activar la conexión:

1. **Importar el esquema SQL actualizado**:
   ```bash
   # Desde phpMyAdmin: Importar el archivo database/adeshop.sql
   # O desde línea de comandos:
   mysql -u root -p < C:\xampp\htdocs\ADESHOP\database\adeshop.sql
   ```

2. **Verificar la configuración en config.php**:
   ```php
   $DB_HOST = '127.0.0.1';
   $DB_NAME = 'adeshop';
   $DB_USER = 'root';
   $DB_PASS = ''; // Cambiar si tienes contraseña
   ```

3. **Asegurar que Apache y MySQL estén corriendo** en XAMPP

## Archivos Modificados/Creados

- ✅ `registro.php` - Formulario completo de registro con validaciones
- ✅ `database/adeshop.sql` - Tabla users actualizada con nuevos campos
- ✅ `templates/header.php` - Menú actualizado con enlace a registro
- ✅ `docs/REGISTRO.md` - Esta documentación

## Próximos Pasos Sugeridos

1. Crear página de login para usuarios (no solo admin)
2. Implementar recuperación de contraseña
3. Añadir verificación de email
4. Crear perfil de usuario
5. Permitir edición de datos personales
6. Implementar sesiones de usuario en el carrito

## Prueba del Módulo

Para probar el módulo:

1. Navega a: `http://localhost/ADESHOP/registro.php`
2. Completa todos los campos
3. Verifica las validaciones en tiempo real
4. Envía el formulario
5. Deberías ver un mensaje de éxito (cuando la BD esté activa)

## Mensajes de Error Posibles

- "El nombre es requerido"
- "Los apellidos son requeridos"
- "El correo electrónico no es válido"
- "La cédula es requerida"
- "La cédula debe contener solo números (mínimo 6 dígitos)"
- "La contraseña es requerida"
- "La contraseña debe tener al menos 6 caracteres"
- "Las contraseñas no coinciden"
- "Este correo electrónico ya está registrado"
- "Esta cédula ya está registrada"

## Soporte

El módulo está completamente funcional y listo para conectarse a la base de datos.
Todos los datos se validan antes de ser insertados para garantizar la integridad.
