# ADR-001: Laravel 12 con PHP 8.2 como stack del sistema

- **Estado:** aceptada
- **Fecha:** 14 de noviembre de 2026
- **Autores:** Carol Dahiana González Ceballos y Santiago Mejía Londoño

## Contexto

BRYSA necesitaba un sistema web para ventas, compras, inventario, kardex y caja, con usuarios y permisos por rol, comprobantes en PDF y exportación a Excel. El equipo tenía pocas semanas y ya contaba con un sistema de ventas construido en Laravel. En el Módulo 2 se había propuesto una API modular con un servicio aparte en Python, pero reescribir todo no cabía en el tiempo del diplomado.

## Decisión

Mantener una **aplicación web monolítica MVC en Laravel 12 con PHP 8.2**, con vistas Blade (plantilla SB Admin con Bootstrap 5) y los paquetes:

- `spatie/laravel-permission` para roles y permisos.
- `barryvdh/laravel-dompdf` para los comprobantes en PDF.
- `maatwebsite/excel` para exportar ventas.
- Eventos, listeners y colas de Laravel para kardex, inventario, caja y envío de correos.

## Alternativas consideradas

1. **API REST (Laravel o FastAPI) + frontend separado (React/Vue):** separa responsabilidades y facilita una app móvil, pero obliga a construir y mantener dos proyectos, autenticación por tokens y CORS. No alcanzaba en el tiempo disponible.
2. **Node.js con Express:** el equipo lo vio en cursos anteriores, pero habría que rehacer desde cero el sistema que ya funcionaba y no trae resueltos roles, PDF ni Excel como el ecosistema de Laravel.

## Consecuencias

**Ventajas**
- Se reutiliza el código existente: las historias HU-01 a HU-07 ya funcionan.
- Un solo proyecto para desplegar y probar (PHPUnit y GitHub Actions).
- Laravel trae validación, ORM, migraciones, colas y protección CSRF.

**Costos**
- No hay API para otros clientes; si se necesita una app móvil habrá que crear `routes/api.php` con Sanctum.
- La interfaz depende de recargar páginas y de librerías por CDN (jQuery, DataTables, SweetAlert2).
- La función de IA (HU-08) se integra dentro del mismo monolito, como un servicio de Laravel que llama al proveedor de LLM.
