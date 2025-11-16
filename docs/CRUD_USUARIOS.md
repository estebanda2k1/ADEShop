# Módulo CRUD de Gestión de Usuarios - ADESHOP

## 📋 Descripción General

Sistema completo de administración de usuarios (CRUD) para el panel de administración de ADESHOP. Permite a los administradores gestionar la base de datos de clientes registrados en la plataforma.

## ✨ Características Implementadas

### 🏠 Dashboard Administrativo (`dashboard.php`)
- ✅ Estadísticas rápidas (total usuarios, productos, pedidos)
- ✅ Tarjetas de acceso a módulos principales
- ✅ Diseño moderno con gradientes y Bootstrap 5
- ✅ Iconos Bootstrap Icons
- ✅ Responsive design

### 📊 Lista de Usuarios (`admin/usuarios.php`)
- ✅ Tabla completa de clientes registrados
- ✅ Avatares generados con iniciales y colores aleatorios
- ✅ Búsqueda por nombre, email o cédula
- ✅ Paginación (10 usuarios por página)
- ✅ Acciones rápidas: Ver, Editar, Eliminar
- ✅ Exportación a CSV
- ✅ Filtrado de solo clientes (no muestra administradores)
- ✅ Modal de confirmación para eliminación
- ✅ Mensajes de éxito/error

### ➕ Crear Usuario (`admin/usuario_crear.php`)
- ✅ Formulario completo con todos los campos
- ✅ Validación frontend (JavaScript en tiempo real)
- ✅ Validación backend (PHP)
- ✅ Verificación de email único
- ✅ Verificación de cédula única
- ✅ Hash seguro de contraseña (bcrypt)
- ✅ Opción para crear administradores
- ✅ Generación automática de username

### 👁️ Ver Usuario (`admin/usuario_ver.php`)
- ✅ Vista detallada de información del usuario
- ✅ Avatar grande con iniciales
- ✅ Información personal completa
- ✅ Estadísticas (pedidos realizados)
- ✅ Botones de acción rápida
- ✅ Diseño tipo perfil

### ✏️ Editar Usuario (`admin/usuario_editar.php`)
- ✅ Formulario prellenado con datos actuales
- ✅ Validación frontend y backend
- ✅ Cambio opcional de contraseña
- ✅ Verificación de unicidad (excluyendo el usuario actual)
- ✅ Actualización de privilegios de administrador
- ✅ Mantiene contraseña si no se especifica nueva

### 📥 Exportar Usuarios (`admin/usuario_exportar.php`)
- ✅ Exportación a formato CSV
- ✅ UTF-8 con BOM (compatible con Excel)
- ✅ Nombre de archivo con timestamp
- ✅ Todos los campos relevantes

## 🔐 Seguridad Implementada

| Característica | Implementación |
|----------------|----------------|
| **Autenticación** | Verificación de sesión en todas las páginas |
| **Autorización** | Solo administradores pueden acceder |
| **SQL Injection** | PDO Prepared Statements |
| **XSS** | htmlspecialchars() en todos los outputs |
| **Contraseñas** | password_hash() con bcrypt |
| **CSRF** | Implementación recomendada (pendiente) |
| **Autoprotección** | No se puede eliminar el usuario actual |

## 📁 Estructura de Archivos

```
ADESHOP/
├── dashboard.php                    # Panel principal de administración
├── admin/
│   ├── usuarios.php                 # Lista de usuarios (READ)
│   ├── usuario_crear.php            # Crear usuario (CREATE)
│   ├── usuario_ver.php              # Ver detalles (READ)
│   ├── usuario_editar.php           # Editar usuario (UPDATE)
│   ├── usuario_exportar.php         # Exportar a CSV
│   └── login.php                    # Login actualizado
└── docs/
    └── CRUD_USUARIOS.md            # Esta documentación
```

## 🎯 Funcionalidades CRUD

### CREATE (Crear)
**Ruta:** `admin/usuario_crear.php`

**Campos:**
- Nombres (obligatorio, solo letras)
- Apellidos (obligatorio, solo letras)
- Email (obligatorio, único, formato válido)
- Cédula (obligatorio, única, solo números)
- Contraseña (obligatorio, mínimo 6 caracteres)
- Confirmar Contraseña (obligatorio, debe coincidir)
- Es Administrador (checkbox opcional)

**Validaciones:**
- ✅ Frontend: HTML5 + JavaScript en tiempo real
- ✅ Backend: Validación completa en PHP
- ✅ Unicidad de email y cédula
- ✅ Username generado automáticamente

### READ (Leer)
**Rutas:** 
- `admin/usuarios.php` (lista)
- `admin/usuario_ver.php?id=X` (detalle)

**Funciones:**
- ✅ Lista paginada (10 por página)
- ✅ Búsqueda por múltiples campos
- ✅ Vista detallada con estadísticas
- ✅ Filtrado automático (solo clientes)

### UPDATE (Actualizar)
**Ruta:** `admin/usuario_editar.php?id=X`

**Funciones:**
- ✅ Edición de todos los campos
- ✅ Cambio opcional de contraseña
- ✅ Cambio de privilegios de administrador
- ✅ Validación de unicidad (excluyendo el usuario actual)
- ✅ Mantiene datos originales si no se modifican

### DELETE (Eliminar)
**Ruta:** `admin/usuarios.php?action=delete&id=X`

**Funciones:**
- ✅ Modal de confirmación
- ✅ Protección: no se puede eliminar el usuario actual
- ✅ Solo elimina clientes, no administradores directamente
- ✅ Mensaje de confirmación

## 🚀 Cómo Usar

### 1. Acceder al Dashboard
```
URL: http://localhost/ADESHOP/dashboard.php
Requisito: Estar autenticado como administrador
```

### 2. Gestión de Usuarios
```
1. En el dashboard, clic en "Gestión de Usuarios"
2. Verás la lista completa de clientes
3. Usa los botones de acción según necesites
```

### 3. Crear Nuevo Usuario
```
1. Clic en "Nuevo Usuario"
2. Completa todos los campos
3. Marca "Es Administrador" si aplica
4. Clic en "Crear Usuario"
```

### 4. Buscar Usuarios
```
1. Usa la barra de búsqueda en la parte superior
2. Puedes buscar por: nombres, apellidos, email o cédula
3. Los resultados se filtran automáticamente
```

### 5. Ver Detalles
```
1. Clic en el botón azul (ojo) en la lista
2. Verás toda la información del usuario
3. Acceso rápido a editar o eliminar
```

### 6. Editar Usuario
```
1. Clic en el botón amarillo (lápiz) en la lista
2. Modifica los campos necesarios
3. Opcionalmente cambia la contraseña
4. Clic en "Guardar Cambios"
```

### 7. Eliminar Usuario
```
1. Clic en el botón rojo (basurero) en la lista
2. Confirma en el modal
3. El usuario será eliminado permanentemente
```

### 8. Exportar Datos
```
1. Clic en "Exportar" en la barra de herramientas
2. Se descargará un archivo CSV
3. Compatible con Excel y otras herramientas
```

## 🎨 Diseño y UX

### Colores
- **Primary:** #667eea (azul-morado)
- **Success:** #28a745 (verde)
- **Warning:** #ffc107 (amarillo)
- **Danger:** #dc3545 (rojo)
- **Info:** #17a2b8 (cian)

### Iconos (Bootstrap Icons)
- 👥 `bi-people-fill` - Usuarios
- ➕ `bi-plus-circle` - Crear
- 👁️ `bi-eye` - Ver
- ✏️ `bi-pencil` - Editar
- 🗑️ `bi-trash` - Eliminar
- 📥 `bi-download` - Exportar
- 🔍 `bi-search` - Buscar

### Características UX
- ✅ Hover effects en tarjetas
- ✅ Transiciones suaves
- ✅ Alerts con cierre automático
- ✅ Modales de confirmación
- ✅ Feedback visual en formularios
- ✅ Loading states (futuro)

## 📊 Base de Datos

### Tabla: `users`
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Índices Recomendados
```sql
CREATE INDEX idx_email ON users(email);
CREATE INDEX idx_cedula ON users(cedula);
CREATE INDEX idx_is_admin ON users(is_admin);
CREATE INDEX idx_created_at ON users(created_at);
```

## 🔄 Flujo de Autenticación

```
1. Usuario accede a dashboard.php
   ↓
2. Verificación de sesión
   ↓
3a. ✅ Es admin → Muestra dashboard
3b. ❌ No es admin → Redirige a index.php
   ↓
4. Admin puede acceder a todos los módulos
```

## 📈 Estadísticas del Dashboard

El dashboard muestra:
- **Total de Clientes**: COUNT de usuarios donde is_admin = 0
- **Total de Productos**: COUNT de la tabla products
- **Total de Pedidos**: COUNT de la tabla orders

## ⚙️ Configuración

### Paginación
Modifica en `admin/usuarios.php`:
```php
$per_page = 10; // Cambiar número de usuarios por página
```

### Permisos de Administrador
Solo usuarios con `is_admin = 1` pueden:
- Acceder al dashboard
- Ver módulo de usuarios
- Crear/editar/eliminar usuarios
- Exportar datos

## 🧪 Testing

### Casos de Prueba

**1. Crear Usuario**
- ✅ Crear con datos válidos
- ✅ Intentar email duplicado
- ✅ Intentar cédula duplicada
- ✅ Contraseñas no coinciden
- ✅ Campos vacíos

**2. Editar Usuario**
- ✅ Modificar solo nombre
- ✅ Cambiar contraseña
- ✅ Mantener contraseña
- ✅ Cambiar privilegios

**3. Eliminar Usuario**
- ✅ Eliminar usuario normal
- ✅ Intentar eliminar usuario actual (bloqueado)
- ✅ Cancelar eliminación

**4. Búsqueda**
- ✅ Buscar por nombre
- ✅ Buscar por email
- ✅ Buscar por cédula
- ✅ Sin resultados

## 🚧 Mejoras Futuras

- [ ] Filtros avanzados (por fecha, tipo)
- [ ] Ordenamiento por columnas
- [ ] Activar/desactivar usuarios (soft delete)
- [ ] Log de actividad de usuarios
- [ ] Roles y permisos personalizados
- [ ] Envío de email de bienvenida
- [ ] Reseteo de contraseña desde admin
- [ ] Importación masiva desde CSV
- [ ] Gráficos y reportes
- [ ] Historial de cambios

## 📝 Notas Importantes

1. **Solo clientes visibles**: El módulo solo muestra usuarios con `is_admin = 0` por defecto.

2. **Protección de cuenta**: No puedes eliminar tu propia cuenta mientras estás autenticado.

3. **Username automático**: Se genera a partir del email. Si existe, se añade timestamp.

4. **Contraseña opcional en edición**: Al editar, solo se actualiza si se especifica una nueva.

5. **Exportación CSV**: Incluye BOM UTF-8 para compatibilidad con Excel.

## 🐛 Solución de Problemas

### "Access Denied"
- Verifica que estés autenticado como administrador
- Revisa que `$_SESSION['is_admin']` sea `1`

### "Usuario no encontrado"
- Verifica que el ID sea válido
- Asegúrate de que el usuario no fue eliminado

### "Email ya registrado"
- El email debe ser único en toda la tabla
- Usa otro email o edita el existente

### Exportación no descarga
- Verifica permisos de PHP
- Revisa errores en el log de PHP

## 📧 Soporte

Para más información, consulta:
- `README.md` - Documentación general
- `docs/REGISTRO.md` - Módulo de registro público
- Código fuente con comentarios

---

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Estado:** ✅ Completamente funcional
