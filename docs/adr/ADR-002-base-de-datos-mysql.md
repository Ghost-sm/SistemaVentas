# ADR-002: MySQL como base de datos

- **Estado:** aceptada
- **Fecha:** 14 de noviembre de 2026
- **Autores:** Carol Dahiana González Ceballos y Santiago Mejía Londoño

## Contexto

Una venta toca varias tablas a la vez: `ventas`, `producto_venta`, `inventario`, `kardex` y `movimientos` de la caja. Si una de esas escrituras falla, el stock y la caja quedan descuadrados, que es justo el problema que BRYSA quiere resolver. Los datos son muy relacionales (productos con categoría, marca y presentación; compras y ventas con su detalle) y se necesitan reportes por fecha y por producto.

## Decisión

Usar **MySQL 8** como única base de datos, administrada con las migraciones de Laravel y Eloquent. Cada venta y cada compra se guarda dentro de una transacción (`DB::beginTransaction` / `commit` / `rollBack`). En local corre en el contenedor `mysql:8.4` de Laravel Sail y en producción se usará el MySQL administrado de la plataforma de despliegue.

## Alternativas consideradas

1. **PostgreSQL:** igual de sólido en transacciones y más estricto con los tipos, pero el sistema ya estaba construido y probado sobre MySQL y el equipo lo conoce mejor; cambiar implicaba revisar migraciones y consultas.
2. **SQLite:** no necesita servidor y sirve para pruebas, pero no es adecuado para varios usuarios escribiendo a la vez (cajero e inventario) en producción.

## Consecuencias

**Ventajas**
- Transacciones ACID: una venta se guarda completa o no se guarda.
- Claves foráneas que protegen la integridad entre ventas, productos y cajas.
- Lo soportan Laravel Sail, Railway y la mayoría de plataformas gratuitas.

**Costos**
- Hay que mantener un servidor de base de datos (contenedor en local, servicio administrado en producción) y sus respaldos.
- Las pruebas automatizadas necesitan una base de datos de pruebas configurada en el CI (issue #9).
