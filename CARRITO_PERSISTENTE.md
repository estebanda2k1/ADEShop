# Instrucciones para Activar el Carrito Persistente

## Paso 1: Ejecutar el Script SQL

Para que el carrito se guarde después de cerrar sesión, necesitas crear la tabla `cart_items` en tu base de datos.

### Opción A: Usando phpMyAdmin
1. Abre phpMyAdmin en tu navegador: `http://localhost/phpmyadmin`
2. Selecciona la base de datos `adeshop`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido del archivo `database/add_cart_table.sql`
5. Haz clic en "Continuar" para ejecutar

### Opción B: Usando línea de comandos
```bash
# Desde el directorio raíz de XAMPP
mysql -u root -p adeshop < c:/xampp/htdocs/ADEShop/database/add_cart_table.sql
```

## ¿Qué hace este cambio?

### Antes:
- El carrito se guardaba solo en `$_SESSION`
- Al cerrar sesión con `logout.php`, se ejecutaba `session_destroy()` y se perdía todo el carrito

### Ahora:
- El carrito se guarda en la tabla `cart_items` de la base de datos
- Cada vez que agregas, actualizas o eliminas productos, se sincroniza automáticamente
- Al iniciar sesión, el carrito se carga desde la base de datos
- Al cerrar sesión, el carrito permanece en la base de datos
- Al completar una compra, se limpia tanto de sesión como de base de datos

## Archivos Modificados:

1. **cart_helper.php** (nuevo): Funciones para guardar/cargar carrito
2. **cart.php**: Sincroniza el carrito después de cada acción
3. **iniciarSesion.php**: Carga el carrito al iniciar sesión
4. **checkout.php**: Limpia el carrito de BD al completar compra
5. **product.php**: Sincroniza al agregar productos
6. **database/add_cart_table.sql** (nuevo): Tabla para carritos persistentes

## Funcionalidad:

✅ El carrito se mantiene después de cerrar sesión
✅ El carrito se sincroniza en tiempo real
✅ Al iniciar sesión recuperas tu carrito anterior
✅ Al completar compra se limpia automáticamente
✅ Cada usuario tiene su propio carrito independiente
