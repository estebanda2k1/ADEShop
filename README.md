# ADESHOP - Mini Marketplace (Tienda de Ropa)

Este es un proyecto PHP + MySQL (XAMPP) para un mini-marketplace de tienda de ropa con sistema de registro de usuarios completo.

## 📋 Requisitos

- XAMPP (Apache + MySQL)
- PHP 7.4+
- Navegador web moderno

## 🚀 Instalación Rápida

1. Copia la carpeta `ADESHOP` en `C:\xampp\htdocs` (si no está ya ahí)
2. Inicia Apache y MySQL desde el panel de control de XAMPP
3. Crea la base de datos e importa el esquema:
   - Abre phpMyAdmin: `http://localhost/phpmyadmin`
   - Importa el archivo `database/adeshop.sql`
4. Edita `config.php` si es necesario (por defecto usa `root` sin contraseña)
5. Abre `http://localhost/ADESHOP` en tu navegador

## ✨ Características Implementadas

### 📝 Sistema de Registro de Usuarios
- ✅ Registro completo con validaciones
- ✅ Campos: Nombres, Apellidos, Email, Cédula, Contraseña
- ✅ Validación frontend (JavaScript) y backend (PHP)
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Verificación de emails y cédulas únicas
- ✅ Mensajes de error y éxito claros
- ✅ Diseño responsive con Bootstrap 5

### 🛍️ Tienda
- `index.php` - Catálogo de productos
- `product.php` - Detalle de producto
- `cart.php` - Carrito de compras basado en sesiones

### 👨‍💼 Área de Administración
- `admin/login.php` - Login de administrador
- `admin/index.php` - Dashboard
- `admin/products.php` - Gestión de productos (lectura)

## 📁 Estructura del Proyecto

```
ADESHOP/
├── config.php              # Configuración de base de datos
├── index.php               # Página principal (productos)
├── product.php             # Detalle de producto
├── cart.php                # Carrito de compras
├── registro.php            # 🆕 Módulo de registro de usuarios
├── admin/                  # Panel de administración
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── products.php
├── templates/              # Plantillas compartidas
│   ├── header.php
│   └── footer.php
├── database/               # Scripts SQL
│   └── adeshop.sql        # Esquema actualizado con tabla users
├── docs/                   # Documentación
│   └── REGISTRO.md        # 🆕 Doc del módulo de registro
├── assets/                 # Recursos estáticos
│   └── images/            # Imágenes de productos
└── README.md              # Este archivo
```

## 🗄️ Estructura de Base de Datos

### Tabla: `users` (Actualizada)
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nombres (VARCHAR 100, NOT NULL)
- apellidos (VARCHAR 100, NOT NULL)
- email (VARCHAR 255, NOT NULL, UNIQUE)
- cedula (VARCHAR 20, NOT NULL, UNIQUE)
- username (VARCHAR 100, NOT NULL, UNIQUE)
- password (VARCHAR 255, NOT NULL) -- hasheada con bcrypt
- is_admin (TINYINT, DEFAULT 0)
- created_at (TIMESTAMP)
```

### Otras Tablas
- `categories` - Categorías de productos
- `products` - Productos de la tienda
- `orders` - Pedidos de clientes
- `order_items` - Items de cada pedido

Ver `docs/REGISTRO.md` para más detalles sobre el módulo de registro.

## 🔐 Seguridad Implementada

- ✅ **SQL Injection**: Uso de prepared statements (PDO)
- ✅ **XSS**: Sanitización con `htmlspecialchars()`
- ✅ **Contraseñas**: Hash con `password_hash()` (bcrypt)
- ✅ **Validación**: Doble validación (frontend + backend)
- ✅ **Campos únicos**: Email y cédula verificados

## 🎯 Páginas Disponibles

| URL | Descripción |
|-----|-------------|
| `/ADESHOP/` | Página principal con productos |
| `/ADESHOP/registro.php` | 🆕 Registro de nuevos usuarios |
| `/ADESHOP/product.php?id=X` | Detalle de producto |
| `/ADESHOP/cart.php` | Carrito de compras |
| `/ADESHOP/admin` | Panel de administración |
| `/ADESHOP/admin/login.php` | Login de admin |

## 🧪 Probar el Módulo de Registro

1. Navega a: `http://localhost/ADESHOP/registro.php`
2. Completa el formulario con:
   - Nombres (solo letras)
   - Apellidos (solo letras)
   - Email válido
   - Cédula (solo números, mínimo 6 dígitos)
   - Contraseña (mínimo 6 caracteres)
   - Confirmar contraseña
3. Haz clic en "Registrarse"
4. Verás un mensaje de éxito y podrás iniciar sesión

## 📝 Datos de Prueba

El archivo SQL incluye datos de ejemplo:
- **Categorías**: T-Shirts, Hoodies, Jeans
- **Productos**: 3 productos de ejemplo
- **Admin**: (deberás crear uno con el formulario de registro y luego cambiar `is_admin=1` en la BD)

## 🚧 Próximas Funcionalidades (TODO)

- [ ] Login de usuarios (no solo admin)
- [ ] Perfil de usuario editable
- [ ] Recuperación de contraseña
- [ ] Verificación de email
- [ ] Checkout completo con persistencia de pedidos
- [ ] CRUD completo de productos en admin
- [ ] Upload de imágenes de productos
- [ ] Filtros y búsqueda de productos
- [ ] Historial de pedidos del usuario

## 🛠️ Configuración

### config.php
```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'adeshop';
$DB_USER = 'root';
$DB_PASS = ''; // Cambiar si tu MySQL tiene contraseña
```

## 📚 Documentación Adicional

- `docs/REGISTRO.md` - Documentación completa del módulo de registro

## ⚠️ Notas Importantes

1. **Base de Datos**: El módulo de registro está **listo para conectarse** a la base de datos. Solo necesitas importar el SQL actualizado.

2. **Contraseñas**: Las contraseñas se almacenan hasheadas usando bcrypt. Nunca se guardan en texto plano.

3. **Username**: Se genera automáticamente a partir del email (parte antes del @).

4. **Validaciones**: El sistema valida en el frontend (para mejor UX) y en el backend (para seguridad).

## 🐛 Solución de Problemas

### "Database connection failed"
- Verifica que MySQL esté corriendo en XAMPP
- Verifica las credenciales en `config.php`
- Verifica que la base de datos `adeshop` exista

### "Este correo electrónico ya está registrado"
- El email debe ser único. Usa otro email o elimina el registro existente.

### "Esta cédula ya está registrada"
- La cédula debe ser única. Usa otra cédula o elimina el registro existente.

## 📧 Contacto

Proyecto desarrollado para XAMPP / PHP + MySQL

---

**Versión**: 1.1  
**Última actualización**: Octubre 2025  
**Estado**: ✅ Módulo de registro completamente funcional
