# Setup Wizard - Configuración Inicial de ADESHOP

## 📋 Descripción

El Setup Wizard es un asistente de configuración inicial que permite crear el primer administrador del sistema ADESHOP. Este wizard se ejecuta automáticamente cuando no existe ningún administrador en la base de datos.

## 🚀 Características

- **Detección automática**: El sistema verifica automáticamente si existe un administrador al cargar cualquier página
- **Redirección inteligente**: Si no hay administradores, todas las páginas redirigen automáticamente a `setup.php`
- **Validaciones completas**:
  - Todos los campos son obligatorios
  - Validación de formato de email
  - Usuario mínimo de 3 caracteres
  - Cédula debe tener exactamente 10 dígitos numéricos
  - Contraseña mínima de 6 caracteres
  - Verificación de que las contraseñas coincidan
  - Prevención de usuarios/emails/cédulas duplicados
  - **Mensajes de error específicos para cada campo**
- **Interfaz moderna**: Diseño atractivo con gradientes y animaciones
- **Seguridad**: Las contraseñas se hashean usando `password_hash()` de PHP

## 📝 Campos del Formulario

1. **Usuario** (*requerido*)
   - Nombre de usuario único para el administrador
   - Se usa para iniciar sesión
   - Mínimo 3 caracteres

2. **Correo Electrónico** (*requerido*)
   - Email válido y único
   - Debe tener formato correcto (ejemplo@dominio.com)

3. **Cédula** (*requerido*)
   - Número de identificación único
   - Debe contener exactamente 10 dígitos numéricos
   - Solo se permiten números

4. **Contraseña** (*requerido*)
   - Mínimo 6 caracteres
   - Se encripta antes de guardarse en la base de datos

5. **Repetir Contraseña** (*requerido*)
   - Debe coincidir exactamente con la contraseña

## 🔧 Cómo Funciona

### 1. Verificación Automática

En `config.php` se agregó una función que verifica si existe un administrador:

```php
function checkAdminExists($pdo) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE is_admin = 1');
    $stmt->execute();
    $result = $stmt->fetch();
    return $result['count'] > 0;
}
```

### 2. Redirección Automática

Si no hay administradores, el sistema redirige automáticamente a `setup.php`:

```php
if (!checkAdminExists($pdo) && $currentScript !== 'setup.php') {
    header('Location: setup.php');
    exit;
}
```

### 3. Proceso de Creación

Cuando se envía el formulario:
1. Se validan todos los campos individualmente
2. Si hay errores, se muestran mensajes específicos debajo de cada campo
3. Se verifica que el usuario/email/cédula no existan
4. Se hashea la contraseña
5. Se crea el usuario con `is_admin = 1`
6. Se muestra mensaje de éxito y enlace al inicio de sesión de clientes

## 🧪 Cómo Probar el Setup Wizard

### Opción 1: Base de Datos Nueva

1. Importa el archivo `database/adeshop.sql` sin el usuario admin por defecto
2. Visita cualquier página del sitio (ej: `http://localhost/ADEShop/index.php`)
3. Serás redirigido automáticamente a `setup.php`

### Opción 2: Eliminar Administradores Existentes

Ejecuta esta consulta SQL para eliminar todos los administradores:

```sql
DELETE FROM users WHERE is_admin = 1;
```

Luego visita cualquier página del sitio.

## 📂 Archivos Modificados/Creados

### Archivos Nuevos:
- `setup.php` - Wizard de configuración inicial

### Archivos Modificados:
- `config.php` - Agregada verificación de administrador y redirección automática

## 🎨 Diseño del Interface

El setup wizard cuenta con:
- **Gradiente púrpura** de fondo (#667eea → #764ba2)
- **Tarjeta centrada** con sombras y bordes redondeados
- **Iconos de Bootstrap Icons** para cada campo
- **Animaciones suaves** al cargar y en hover
- **Validación en tiempo real** de las contraseñas
- **Pantalla de éxito** con animación cuando se crea el admin

## 🔒 Seguridad

- Las contraseñas se hashean con `PASSWORD_DEFAULT` (actualmente bcrypt)
- Validación tanto en cliente (JavaScript) como en servidor (PHP)
- Protección contra SQL injection usando prepared statements
- Verificación de duplicados antes de insertar
- Una vez creado el admin, no se puede acceder al setup nuevamente

## 📍 Flujo de Usuario

```
1. Usuario visita cualquier página
   ↓
2. Sistema verifica si existe admin
   ↓
3a. SI existe admin → Continúa normal
3b. NO existe admin → Redirige a setup.php
   ↓
4. Usuario completa formulario
   ↓
5. Sistema valida datos
   ↓
6a. Datos válidos → Crea admin → Muestra éxito
6b. Datos inválidos → Muestra error
   ↓
6. Usuario hace clic en "Ir a Iniciar Sesión"
   ↓
7. Redirige a iniciarSesion.php (módulo de clientes)
```

## 🐛 Resolución de Problemas

### El setup no aparece
- Verifica que no exista un usuario con `is_admin = 1` en la base de datos
- Revisa que el archivo `setup.php` esté en la raíz del proyecto

### Error de conexión a base de datos
- Verifica las credenciales en `setup.php` (líneas 3-6)
- Asegúrate de que MySQL esté corriendo
- Confirma que la base de datos `adeshop` exista

### No redirige después de crear admin
- Limpia las cookies/sesión del navegador
- Verifica que el usuario se creó correctamente en la base de datos
- Revisa que tenga `is_admin = 1`

## 💡 Notas Importantes

- El setup wizard se ejecuta **solo una vez** (cuando no hay administradores)
- Una vez creado un administrador, el acceso al setup se bloquea automáticamente
- Los datos predeterminados son:
  - Nombres: "Administrador"
  - Apellidos: "Principal"
- La cédula se valida para que contenga exactamente 10 dígitos
- **Cada campo muestra su propio mensaje de error** cuando hay problemas de validación
- Después de crear el admin, se redirige al módulo de inicio de sesión de clientes (`iniciarSesion.php`)
- Estos pueden editarse después desde el panel de administración

## 🔄 Versión

- **Versión**: 1.0
- **Fecha**: Noviembre 2025
- **Autor**: Sistema ADESHOP
