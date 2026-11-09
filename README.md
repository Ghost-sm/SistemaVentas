# BRYSA: sistema web de ventas e inventario con sugerencia de reposición asistida por IA

Sistema web y API REST para la tienda de abarrotes **BRYSA**. Registra ventas de forma automatica (venta, detalle, inventario, kardex y caja en una sola transacción) y, a partir del historial de ventas, calcula cuántas unidades reponer de cada producto. Un modelo de lenguaje (opcional) redacta la explicación de la sugerencia. El sistema apoya la decisión del propietario: **no crea compras ni modifica existencias por su cuenta**.

## Caso de uso del MVP

Registrar una venta (por la interfaz web o por la API) y consultar la sugerencia de reposición de un producto con base en las ventas registradas.

## Estado de las funcionalidades

| Funcionalidad | Estado |
|---|---|
| Registro de venta atómico por la interfaz web (venta, detalle, inventario, kardex y caja) | Implementado |
| Registro de venta por API REST con validación de stock e idempotencia | Implementado |
| Autenticación por token (Sanctum) y permisos por rol (Spatie) | Implementado |
| Sugerencia de reposición con promedio móvil y regla del dominio | Implementado |
| Explicación con LLM detrás de un puerto intercambiable, con respaldo sin IA | Implementado |
| Documentación de la API (OpenAPI 3 y colección Postman) | Implementado |
| Contenedorización con Docker Compose | Implementado |
| Pronóstico Holt-Winters en servicio Python/FastAPI | Futuro |
| Productos complementarios, operaciones inusuales y asistente en lenguaje natural | Futuro |
| Fiados, cuentas por cobrar y abonos | Futuro |
| Aprobar la sugerencia y crear la orden de compra; anulaciones y devoluciones por API | Futuro |

## Cómo ejecutar el proyecto

### Opción A: Laragon (Windows)

Requisitos: **PHP 8.4 o superior** (con las extensiones `zip`, `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `gd` e `intl`), Composer, MySQL 8 y Git.

1. Clonar el repositorio y entrar a la carpeta:

   ```bash
   git clone https://github.com/Ghost-sm/SistemaVentas.git
   cd SistemaVentas
   ```

2. Instalar las dependencias y crear la configuración:

   ```bash
   composer install
   copy .env.example .env
   php artisan key:generate
   ```

3. Crear una base de datos vacía (por ejemplo `brysa`) y configurarla en `.env`:

   ```
   DB_DATABASE=brysa
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Crear las tablas y cargar los datos básicos:

   ```bash
   php artisan migrate --seed
   ```

5. Iniciar el servidor y abrir <http://localhost:8000>:

   ```bash
   php artisan serve
   ```

   Si el proyecto está en `C:\laragon\www`, Laragon también lo publica en `http://SistemaVentas.test`.

**Usuario de demostración:** `admin@gmail.com` / `12345678`. Es solo para la demo y debe cambiarse en cualquier uso real.

### Opción B: Docker Compose

Requisito: Docker Desktop.

```bash
cp .env.docker.example .env      # editar las contraseñas de la base de datos
docker compose up --build
```

Aplicación en <http://localhost:8000>. Al arrancar, el contenedor espera a MySQL, ejecuta las migraciones y carga los datos básicos. Para detener todo: `docker compose down` (con `-v` también borra los datos).

### Pruebas

```bash
php artisan test
```

## Configuración (variables de entorno)

| Variable | Para qué sirve | Valor por defecto |
|---|---|---|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexión a MySQL | Ver `.env.example` |
| `IA_LLM_DRIVER` | `none` (plantilla, sin IA) o `anthropic` (LLM) | `none` |
| `ANTHROPIC_API_KEY` | Clave del proveedor de lenguaje (solo si `IA_LLM_DRIVER=anthropic`) | vacía |
| `ANTHROPIC_MODEL`, `IA_LLM_TIMEOUT` | Modelo y tiempo máximo de espera | configurables |
| `IA_VENTANA_DIAS` | Días de historial del promedio móvil | `28` |

El archivo `.env` está en `.gitignore`: ninguna clave queda en el repositorio. Solo se versionan las plantillas sin credenciales.
