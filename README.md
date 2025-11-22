# ADESHOP - Sistema de Tienda de Ropa

Sistema completo de gestión de tienda de ropa desarrollado en PHP, MySQL y Bootstrap 5.

## 🚀 Características

### Para Administradores:
- ✅ **Gestión de Usuarios**: CRUD completo de clientes con búsqueda, paginación y exportación CSV
- ✅ **Gestión de Productos**: Agregar, editar, eliminar productos con control de inventario/stock
- ✅ **Gestión de Órdenes**: Visualizar todas las órdenes con sus estados y detalles
- ✅ **Control de Stock**: Sistema de alertas cuando el stock está bajo (≤5 unidades)
- ✅ **Dashboard**: Estadísticas rápidas de usuarios, productos y pedidos

### Para Clientes:
- ✅ **Registro de Usuario**: Sistema completo con validación de campos (nombres, apellidos, email, cédula, contraseña)
- ✅ **Catálogo de Productos**: Visualización de productos con información de stock
- ✅ **Carrito de Compras**: Agregar productos, modificar cantidades, verificación de stock disponible
- ✅ **Sistema de Checkout**: Procesamiento de órdenes con reducción automática de stock
- ✅ **Indicadores Visuales**: Badges de stock (Agotado, Últimas unidades, Disponible)

## 📋 Requisitos

- XAMPP (PHP 7.4+ y MySQL/MariaDB)
- Navegador web moderno
- Bootstrap 5.3.2 (incluido vía CDN)
- Bootstrap Icons (incluido vía CDN)

## 🔧 Instalación

### 1. Descargar e instalar XAMPP
- Descargar desde: https://www.apachefriends.org/
- Instalar y abrir XAMPP Control Panel
- Iniciar los servicios Apache y MySQL

### 2. Configurar el proyecto

1. Copiar la carpeta `ADESHOP` a:
   ```
   C:\xampp\htdocs\ADESHOP
   ```

2. Crear la base de datos:
   - Abrir phpMyAdmin: http://localhost/phpmyadmin
   - Importar el archivo: `database/adeshop.sql`
   - O ejecutar el script SQL directamente

3. Verificar la configuración en `config.php`:
   ```php
   $host = 'localhost';
   $dbname = 'adeshop';
   $username = 'root';
   $password = ''; // En XAMPP por defecto está vacío
   ```

4. Configurar permisos de escritura en la carpeta `uploads/` para imágenes de productos

### 3. Acceder al sistema

**Página Principal:**
- URL: http://localhost/ADESHOP/

**Usuario Administrador por defecto:**
- URL: http://localhost/ADESHOP/admin/dashboard.php
- Email: `admin@admin.com`
- Usuario: `admin`
- Contraseña: `admin123`

> **Nota**: Si el usuario administrador no existe o necesitas restablecerlo, accede a: `http://localhost/ADESHOP/admin/actualizar_admin.php`

**Registro de Cliente:**
- URL: http://localhost/ADESHOP/public/auth/registro.php

## 👤 Usuarios por Defecto

### Administrador
- Email: admin@admin.com
- Usuario: admin
- Contraseña: admin123
- Cédula: 9999999999

### Cliente
- Registrarse desde: `http://localhost/ADESHOP/public/auth/registro.php`

## 📁 Estructura del Proyecto

```
ADESHOP/
│
├── admin/                      # Panel de administración
│   ├── dashboard.php          # Panel principal
│   ├── usuarios.php           # Listado de usuarios
│   ├── usuario_*.php          # CRUD de usuarios
│   ├── productos.php          # Listado de productos
│   ├── producto_*.php         # CRUD de productos
│   ├── ordenes.php            # Listado de órdenes
│   └── orden_ver.php          # Detalles de orden
│
├── assets/                     # Recursos estáticos
│   └── images/
│       └── products/          # Imágenes de productos
│
├── database/
│   └── adeshop.sql            # Esquema de base de datos
│
├── templates/
│   ├── header.php             # Encabezado común
│   └── footer.php             # Pie de página común
│
├── config.php                 # Configuración de BD y sesión
├── index.php                  # Página principal (catálogo)
├── registro.php               # Registro de usuarios
├── login.php                  # Inicio de sesión
├── cart.php                   # Carrito de compras
├── checkout.php               # Finalizar compra
└── README.md                  # Este archivo
```

## 🗄️ Base de Datos

### Tablas principales:

1. **users**: Información de usuarios y administradores
   - Campos: id, nombres, apellidos, email, cedula, username, password, is_admin

2. **products**: Catálogo de productos
   - Campos: id, name, description, price, stock, image, category_id

3. **categories**: Categorías de productos
   - Campos: id, name

4. **orders**: Órdenes de compra
   - Campos: id, user_id, total, status, created_at

5. **order_items**: Detalles de cada orden
   - Campos: id, order_id, product_id, quantity, price

## 🔒 Seguridad

- **Contraseñas**: Encriptadas con `password_hash()` usando bcrypt
- **SQL Injection**: Protección mediante PDO y prepared statements
- **XSS**: Sanitización con `htmlspecialchars()`
- **Sesiones**: Control de acceso basado en roles (admin/cliente)

## 📊 Funcionalidades Detalladas

### Gestión de Inventario

1. **Agregar Producto**:
   - Formulario con validación de campos
   - Subida de imágenes (JPG, PNG, GIF, WEBP, máx 5MB)
   - Control de stock inicial

2. **Editar Producto**:
   - Modificar toda la información
   - Actualizar imagen (opcional)
   - Ajustar stock disponible

3. **Visualización de Stock**:
   - Badge ROJO: Stock ≤ 5 unidades (Alerta)
   - Badge VERDE: Stock > 5 unidades (Normal)
   - Badge GRIS: Stock = 0 (Agotado)

### Sistema de Compras

1. **Carrito**:
   - Agregar productos desde catálogo
   - Verificación automática de stock disponible
   - Actualización en tiempo real de cantidades
   - Cálculo automático de totales

2. **Checkout**:
   - Resumen de productos
   - Confirmación de orden
   - Reducción automática de stock al confirmar
   - Transacciones seguras (rollback en caso de error)

3. **Gestión de Órdenes (Admin)**:
   - Listado con filtros y búsqueda
   - Estados: Pendiente, Completado, Cancelado
   - Vista detallada de cada orden
   - Actualización de estados

## 🎨 Diseño

- **Framework**: Bootstrap 5.3.2
- **Iconos**: Bootstrap Icons
- **Tema**: Gradientes modernos (#667eea → #764ba2)
- **Responsive**: Compatible con dispositivos móviles

## 🐛 Solución de Problemas

### Error: "Failed opening required 'config.php'"
**Solución**: El archivo usa rutas absolutas con `__DIR__`. Verificar que todos los archivos estén en la carpeta correcta.

### Error: SQLSTATE[42000] con LIMIT/OFFSET
**Solución**: Ya corregido. Los valores LIMIT/OFFSET se concatenan como integers, no como parámetros PDO.

### Imágenes no se muestran
**Solución**: Verificar que la carpeta `assets/images/products/` tenga permisos de escritura.

### No puedo iniciar sesión como admin
**Solución**: Verificar que el usuario admin esté en la BD ejecutando el SQL de instalación.

## 📝 Datos de Prueba

El archivo SQL incluye:
- 1 usuario administrador (admin/admin123)
- 3 categorías de productos
- 3 productos de ejemplo con stock

Para agregar más datos de prueba, usar el panel de administración.

## 🔄 Actualizaciones Futuras (Sugerencias)

- [ ] Sistema de cupones de descuento
- [ ] Historial de compras para clientes
- [ ] Reportes y gráficas de ventas
- [ ] Notificaciones por email
- [ ] Sistema de reviews/calificaciones
- [ ] Integración con pasarelas de pago
- [ ] Gestión de envíos y tracking

## 👥 Soporte

Para problemas o preguntas sobre el sistema, revisar:
1. Este archivo README
2. Los comentarios en el código fuente
3. La estructura de la base de datos en `database/adeshop.sql`

## 📜 Licencia

Proyecto educativo desarrollado para demostración de habilidades en PHP/MySQL.

---

**Versión**: 2.0  
**Última actualización**: Noviembre 2025  
**Desarrollado con**: PHP, MySQL, Bootstrap 5
