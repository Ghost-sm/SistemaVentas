# Requisitos – BRYSA: sistema web de ventas e inventario

**Proyecto integrador · Módulo 6 · Diplomado en Ingeniería de Software Asistida por IA (USC)**
Autores: Carol Dahiana González Ceballos y Santiago Mejía Londoño · Versión 1.0 · 14 de noviembre de 2026

## Descripción

BRYSA es una tienda de abarrotes de Yumbo que llevaba el inventario, las ventas y las compras en papel. Este sistema web (Laravel 12 + MySQL) reemplaza esos registros: el cajero registra cada venta en un punto de venta, el administrador registra las compras a proveedores, y el inventario, el kardex y la caja se actualizan solos con cada operación. El acceso se controla con usuarios y roles.

La función con IA del proyecto es la **sugerencia de reposición**: a partir del historial de ventas, el sistema calcula cuántas unidades conviene reponer de cada producto y un modelo de lenguaje redacta la explicación. La sugerencia solo apoya la decisión del propietario: no crea compras ni modifica existencias por su cuenta. Esta función todavía no está implementada (se construye en la sesión S4).

**Usuarios:** administrador (propietario), cajero y encargado de inventario.

**Stack:** PHP 8.2+ / Laravel 12, MySQL 8, Blade + Bootstrap, Spatie Permission (roles), DomPDF y Laravel Excel (reportes), Docker/Sail, GitHub Actions (CI).

## Historias de usuario

### HU-01: Iniciar sesión con permisos por rol
Como **usuario de la tienda (administrador o cajero)**, quiero **iniciar sesión con mi correo y contraseña**, para **ver solo los módulos que mi rol me permite usar**.
- Dado un usuario activo con rol de cajero, cuando ingresa su correo y contraseña correctos, entonces entra al panel y en el menú solo ve los módulos que tiene permitidos (por ejemplo, ventas y caja).
- Dado un usuario registrado, cuando ingresa una contraseña incorrecta, entonces ve un mensaje de credenciales inválidas y no entra al sistema.
- Dado un cajero autenticado, cuando intenta abrir por URL un módulo sin permiso (por ejemplo, usuarios o roles), entonces el sistema le niega el acceso (error 403).

Estado: **Hecha**

### HU-02: Registrar y editar productos
Como **encargado de inventario**, quiero **registrar y editar productos con su categoría, marca y presentación**, para **tener el catálogo de la tienda actualizado**.
- Dado el formulario de nuevo producto, cuando completo los campos obligatorios y presiono "Guardar", entonces el producto aparece en la lista con un mensaje de confirmación.
- Dado un producto existente, cuando cambio su precio o su categoría y guardo, entonces la lista muestra los datos nuevos.
- Dado el formulario de nuevo producto, cuando dejo vacío un campo obligatorio o uso un código repetido, entonces el sistema no guarda y muestra el error junto al campo.

Estado: **Hecha**

### HU-03: Registrar una venta en el punto de venta
Como **cajero**, quiero **registrar la venta de uno o varios productos en el punto de venta**, para **que el inventario, el kardex y la caja se actualicen sin hacerlo a mano**.
- Dado un cajero con su caja abierta y productos con existencias, cuando registra una venta de 2 unidades de un producto, entonces la venta queda guardada con su número de comprobante y el stock de ese producto baja en 2.
- Dada una venta registrada, cuando se consulta el kardex del producto, entonces aparece la salida correspondiente a esa venta.
- Dado un cajero sin caja abierta, cuando intenta registrar una venta, entonces el sistema no lo permite y le indica que primero debe abrir la caja.

Estado: **Hecha**

### HU-04: Comprobante de la venta en PDF
Como **cajero**, quiero **generar el comprobante de una venta en PDF**, para **entregarlo o enviarlo al cliente**.
- Dada una venta registrada, cuando el cajero elige "Exportar PDF" del comprobante, entonces se descarga un PDF con el número de comprobante, los productos, las cantidades y el total.
- Dada una venta con un cliente que tiene correo registrado, cuando se completa la venta, entonces el sistema encola el envío del comprobante a ese correo.
- Dado un número de venta que no existe, cuando se pide su comprobante, entonces el sistema responde que no se encontró (error 404) y no genera ningún archivo.

Estado: **Hecha** (el envío por correo depende de configurar el servidor de correo en el despliegue)

### HU-05: Registrar una compra a un proveedor
Como **administrador**, quiero **registrar la compra de productos a un proveedor**, para **que el stock aumente automáticamente al recibir la mercancía**.
- Dado un proveedor registrado, cuando el administrador registra una compra de 10 unidades de un producto, entonces el stock de ese producto aumenta en 10 y la compra aparece en el listado.
- Dada una compra registrada, cuando se consulta el kardex del producto, entonces aparece la entrada correspondiente a esa compra.
- Dado el formulario de compra, cuando se envía sin productos o con cantidades o precios no válidos, entonces el sistema no registra la compra y muestra los errores.

Estado: **Hecha**

### HU-06: Abrir y cerrar la caja del día
Como **cajero**, quiero **abrir mi caja con un saldo inicial y cerrarla al final del turno**, para **cuadrar el dinero recibido con las ventas registradas**.
- Dado un cajero sin caja abierta, cuando abre una caja con un saldo inicial, entonces queda una caja activa a su nombre y puede registrar ventas.
- Dada una caja abierta con ventas, cuando el cajero consulta sus movimientos, entonces ve el saldo inicial, las ventas y los retiros registrados.
- Dado un cajero que ya tiene una caja abierta, cuando intenta abrir otra, entonces el sistema no lo permite.

Estado: **Hecha**

### HU-07: Exportar el historial de ventas a Excel
Como **administrador**, quiero **exportar el historial de ventas a Excel**, para **revisar los ingresos de la tienda por fuera del sistema**.
- Dado que existen ventas registradas, cuando el administrador elige "Exportar a Excel", entonces recibe un archivo .xlsx con el número, la fecha, el cliente y el total de cada venta.
- Dado un usuario sin permiso de reportes, cuando intenta la exportación, entonces el sistema le niega el acceso.

Estado: **Hecha**

### HU-08: Sugerencia de reposición con IA
Como **propietario de la tienda**, quiero **consultar cuántas unidades reponer de un producto según sus ventas recientes, con una explicación en lenguaje natural**, para **decidir mis pedidos a proveedores sin quedarme sin existencias**.
- Dado un producto con ventas en los últimos 28 días, cuando el propietario pide la sugerencia, entonces el sistema muestra la cantidad recomendada calculada con el promedio de ventas diarias y una explicación redactada por el modelo de lenguaje.
- Dado que el proveedor del modelo de lenguaje no responde o no hay clave configurada, cuando se pide la sugerencia, entonces el sistema muestra igual la cantidad calculada con una explicación de plantilla (sin IA) y no falla.
- Dada una sugerencia mostrada, cuando el propietario no la aprueba, entonces el sistema no crea ninguna compra ni cambia el inventario.

Estado: **Pendiente** (sesión S4)

## Requisitos no funcionales

| ID | Atributo | Requisito medible | Cómo se verifica |
|---|---|---|---|
| RNF-01 | Seguridad | Las contraseñas se guardan con hash (bcrypt) y ninguna clave (base de datos, correo o API del modelo de lenguaje) está en el repositorio: solo en `.env`, que está en `.gitignore`. | Revisión del repositorio y de la tabla `users` |
| RNF-02 | Rendimiento | Registrar una venta de hasta 10 productos responde en menos de 2 segundos, con un catálogo de al menos 500 productos. | Prueba manual cronometrada en la URL pública |
| RNF-03 | Disponibilidad | La URL pública del despliegue responde (código 200 en la página de inicio de sesión) durante toda la sustentación. | Verificación antes y durante la demo |
| RNF-04 | Calidad | El workflow de GitHub Actions ejecuta todas las pruebas automatizadas en cada push a `main` y termina en verde. | Pestaña Actions del repositorio |


## Seguimiento del proyecto

Las historias y tareas se gestionan en GitHub Projects, en el tablero **SistemaVentas BRYSA - Módulo 6** (columnas Por hacer, En progreso, En revisión y Hecho):

- Tablero: https://github.com/users/CarolGonzalez0516/projects/1
- Issues priorizados con MoSCoW y sesión: https://github.com/Ghost-sm/SistemaVentas/issues

El tablero está en la cuenta de una integrante del equipo porque el repositorio pertenece a una cuenta personal; por eso se enlaza aquí.
