# Gestión de Ofertas y Categorías - ADESHOP

## 📋 Descripción

Se han agregado nuevas funcionalidades al módulo de productos para permitir:
1. **Configurar productos en oferta** con precios especiales y porcentajes de descuento
2. **Crear categorías** directamente desde el formulario de productos

## 🎯 Funcionalidades Agregadas

### 1. Sistema de Ofertas

#### Campos Agregados a la Base de Datos:
- `is_on_sale` (TINYINT): Indica si el producto está en oferta (0 = No, 1 = Sí)
- `sale_price` (DECIMAL): Precio con descuento cuando está en oferta
- `sale_percentage` (INT): Porcentaje de descuento (opcional, para mostrar badges)

#### Características:
✅ **Switch de activación** - Activar/desactivar oferta con un simple toggle
✅ **Precio de oferta** - Campo para ingresar el precio rebajado
✅ **Porcentaje de descuento** - Campo opcional para mostrar el % de ahorro
✅ **Cálculo automático** - Al ingresar el porcentaje, calcula el precio automáticamente y viceversa
✅ **Validaciones**:
- El precio de oferta debe ser menor que el precio normal
- Si está en oferta, el precio de oferta es obligatorio
- El porcentaje debe estar entre 1% y 99%

#### Cómo Usar:
1. En **Crear Producto** o **Editar Producto**, ve a la sección "Configuración de Oferta"
2. Activa el switch "Producto en Oferta"
3. Ingresa el **Precio de Oferta** O el **Porcentaje de Descuento**
   - Si ingresas el porcentaje, el precio se calcula automáticamente
   - Si ingresas el precio, el porcentaje se calcula automáticamente
4. Guarda el producto

### 2. Crear Categorías Dinámicamente

#### Características:
✅ **Botón integrado** - "Crear nueva categoría" directamente en el formulario
✅ **Modal emergente** - Interfaz limpia para crear categorías sin salir del formulario
✅ **Actualización automática** - La nueva categoría aparece inmediatamente en el select
✅ **Validación** - El nombre de categoría es obligatorio

#### Cómo Usar:
1. En **Crear Producto** o **Editar Producto**, en el campo "Categoría"
2. Haz clic en el botón **"Crear nueva categoría"**
3. Ingresa el nombre de la categoría en el modal
4. Haz clic en **"Crear Categoría"**
5. La página se recarga y la nueva categoría estará disponible

## 🗄️ Actualización de Base de Datos

### Para Bases de Datos Existentes:

Ejecuta el siguiente script SQL para agregar los campos de oferta:

```sql
USE adeshop;

ALTER TABLE products 
ADD COLUMN is_on_sale TINYINT(1) DEFAULT 0 COMMENT 'Indica si el producto está en oferta',
ADD COLUMN sale_price DECIMAL(10,2) NULL COMMENT 'Precio con descuento cuando está en oferta',
ADD COLUMN sale_percentage INT NULL COMMENT 'Porcentaje de descuento (opcional)';
```

O ejecuta el archivo: `database/add_offer_fields.sql`

### Para Nuevas Instalaciones:

El archivo `database/adeshop.sql` ya incluye estos campos, por lo que no necesitas hacer nada adicional.

## 📝 Ejemplos de Uso

### Ejemplo 1: Producto con 20% de descuento

```
Producto: Camiseta Básica
Precio Normal: $25.00
Producto en Oferta: ✓ Activado
Porcentaje de Descuento: 20%
→ Precio de Oferta (automático): $20.00
```

### Ejemplo 2: Producto con precio específico de oferta

```
Producto: Zapatillas Deportivas
Precio Normal: $80.00
Producto en Oferta: ✓ Activado
Precio de Oferta: $59.99
→ Porcentaje de Descuento (automático): 25%
```

### Ejemplo 3: Crear categoría y asignar producto

```
1. Click en "Crear nueva categoría"
2. Ingresar: "Ropa de Invierno"
3. Guardar categoría
4. Seleccionar "Ropa de Invierno" en el select de categorías
5. Completar el resto del formulario del producto
```

## 🎨 Interfaz de Usuario

### Sección de Ofertas:
- Tarjeta destacada con fondo claro
- Icono de etiqueta (tag) para identificar ofertas
- Switch toggle moderno para activar/desactivar
- Campos que aparecen/desaparecen dinámicamente
- Mensajes de ayuda bajo cada campo

### Modal de Crear Categoría:
- Diseño limpio y profesional
- Botón de cerrar (X) en la esquina
- Campo de texto con placeholder descriptivo
- Botones de acción: Cancelar y Crear

## 🔧 Archivos Modificados

### Nuevos Archivos:
- `database/add_offer_fields.sql` - Script para actualizar DB existente
- `docs/OFERTAS_Y_CATEGORIAS.md` - Esta documentación

### Archivos Modificados:
- `admin/producto_crear.php` - Agregadas ofertas y crear categoría
- `admin/producto_editar.php` - Agregadas ofertas y crear categoría
- `database/adeshop.sql` - Estructura actualizada con campos de oferta

## 💡 Notas Importantes

### Ofertas:
- El precio de oferta **siempre** debe ser menor que el precio normal
- Si desactivas la oferta, los datos se conservan en la base de datos pero no se muestran
- El porcentaje es **opcional** - puedes crear ofertas sin especificar el %
- Los cálculos automáticos se actualizan en tiempo real mientras escribes

### Categorías:
- Al crear una categoría desde el formulario, la página se recarga
- Los datos del producto que estabas ingresando se mantienen (excepto la imagen en crear)
- Puedes seguir creando múltiples categorías sin problemas
- Las categorías se ordenan alfabéticamente en el select

## 🚀 Próximos Pasos Sugeridos

Para aprovechar estas funcionalidades en el frontend:

1. **Mostrar badges de oferta** en las tarjetas de productos
2. **Tachar el precio normal** y mostrar el precio de oferta en grande
3. **Mostrar porcentaje de descuento** como badge destacado
4. **Filtrar productos en oferta** en el catálogo
5. **Crear una sección "Ofertas"** en el menú principal

## 🐛 Resolución de Problemas

### Los campos de oferta no aparecen:
- Verifica que hayas ejecutado el script `add_offer_fields.sql`
- Asegúrate de que la tabla `products` tenga los nuevos campos

### El modal de crear categoría no se abre:
- Verifica que Bootstrap JavaScript esté cargado correctamente
- Revisa la consola del navegador para errores

### Los cálculos automáticos no funcionan:
- Asegúrate de que JavaScript esté habilitado en el navegador
- Verifica que los campos `price`, `sale_price` y `sale_percentage` existan en el DOM

## 📊 Estructura de Datos

### Tabla `products` (campos de oferta):

| Campo | Tipo | Nulo | Descripción |
|-------|------|------|-------------|
| is_on_sale | TINYINT(1) | NO | 0=Sin oferta, 1=En oferta |
| sale_price | DECIMAL(10,2) | SÍ | Precio con descuento |
| sale_percentage | INT | SÍ | % de descuento (1-99) |

## 📅 Versión

- **Versión**: 2.0
- **Fecha**: Noviembre 2025
- **Módulo**: Gestión de Productos
