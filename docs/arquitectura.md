# Arquitectura – BRYSA: sistema web de ventas e inventario

**Proyecto integrador · Módulo 6 · Sesión 2**
Autores: Carol Dahiana González Ceballos y Santiago Mejía Londoño · 14 de noviembre de 2026

Este documento describe la arquitectura **tal como está hoy en el código** del repositorio: cada elemento de los diagramas corresponde a un archivo, una dependencia o una configuración que existe en el proyecto.

Decisiones de arquitectura:

- [ADR-001: Laravel 12 con PHP 8.2 como stack](adr/ADR-001-stack-laravel.md)
- [ADR-002: MySQL como base de datos](adr/ADR-002-base-de-datos-mysql.md)

## 1. Diagrama de contexto (C4 nivel 1)

Quién usa el sistema y con qué sistemas externos habla.

```mermaid
flowchart LR
    ADM["Administrador / propietario<br/>[Persona]<br/>Configura catálogo, usuarios, roles y revisa reportes"]
    CAJ["Cajero<br/>[Persona]<br/>Abre caja y registra ventas"]
    INV["Encargado de inventario<br/>[Persona]<br/>Registra compras y revisa kardex"]

    SIS["Sistema BRYSA<br/>[Sistema de software]<br/>Ventas, compras, inventario, kardex y caja"]

    MAIL["Servidor de correo SMTP<br/>[Sistema externo]<br/>Envía el comprobante al cliente"]

    ADM -->|"Usa por navegador (HTTP)"| SIS
    CAJ -->|"Usa por navegador (HTTP)"| SIS
    INV -->|"Usa por navegador (HTTP)"| SIS
    SIS -->|"Envía comprobante (SMTP)"| MAIL
```

## 2. Diagrama de contenedores (C4 nivel 2)

Las piezas que se despliegan. El sistema es un **monolito MVC**: el mismo Laravel atiende las pantallas y la lógica de negocio. No hay una API REST separada (`routes/api.php` solo tiene la ruta por defecto `/user`).

```mermaid
flowchart TB
    U["Usuarios<br/>(administrador, cajero, inventario)"]

    subgraph BRYSA["Sistema BRYSA"]
        WEB["Aplicación web Laravel<br/>[PHP 8.2+ · Laravel 12]<br/>Controladores, vistas Blade (SB Admin + Bootstrap 5),<br/>Spatie Permission, DomPDF, Laravel Excel"]
        WORKER["Worker de colas<br/>[run-worker.sh · php artisan queue:work]<br/>Envía comprobantes y exporta Excel"]
        DB[("Base de datos<br/>[MySQL 8]<br/>Ventas, compras, inventario, kardex, cajas, usuarios")]
        FILES[("Almacenamiento de archivos<br/>[disco public de Laravel]<br/>Imágenes de productos y comprobantes")]
    end

    MAIL["Servidor SMTP<br/>[externo]"]

    U -->|"HTTP · HTML, formularios y JS"| WEB
    WEB -->|"SQL sobre TCP 3306 (Eloquent)"| DB
    WEB -->|"Lee y escribe archivos"| FILES
    WEB -->|"Encola trabajos (tabla jobs)"| DB
    WORKER -->|"Toma trabajos (SQL)"| DB
    WORKER -->|"SMTP"| MAIL
```

| Contenedor | Tecnología | Responsabilidad |
|---|---|---|
| Aplicación web | PHP 8.2, Laravel 12, Blade, Bootstrap 5 | Pantallas, validación (Form Requests), reglas de negocio, roles y permisos |
| Worker de colas | `php artisan queue:work` (`run-worker.sh`) | Trabajos `EnviarComprobanteVentaJob` y `DownloadExcelVentasAllJob`. Con `QUEUE_CONNECTION=sync` (valor de `.env.example`) se ejecutan en el mismo proceso web |
| Base de datos | MySQL 8 | Persistencia transaccional de todas las operaciones |
| Archivos | Disco `public` de Laravel | Imágenes de productos y empleados, comprobantes de compra |

## 3. Modelo de datos

Tablas principales de las migraciones (`database/migrations`). Se omiten las tablas técnicas de Laravel (sesiones, tokens, jobs) y las de Spatie Permission (roles y permisos).

```mermaid
erDiagram
    PERSONAS ||--o| CLIENTES : "es"
    PERSONAS ||--o| PROVEEDORES : "es"
    DOCUMENTOS ||--o{ PERSONAS : "identifica"
    CARACTERISTICAS ||--o| CATEGORIAS : "describe"
    CARACTERISTICAS ||--o| MARCAS : "describe"
    CARACTERISTICAS ||--o| PRESENTACIONES : "describe"
    CATEGORIAS ||--o{ PRODUCTOS : "agrupa"
    MARCAS ||--o{ PRODUCTOS : "agrupa"
    PRESENTACIONES ||--o{ PRODUCTOS : "agrupa"
    PRODUCTOS ||--o{ INVENTARIO : "tiene"
    UBICACIONES ||--o{ INVENTARIO : "guarda"
    PRODUCTOS ||--o{ KARDEX : "registra"
    EMPLEADOS ||--o| USERS : "tiene"
    USERS ||--o{ CAJAS : "abre"
    CAJAS ||--o{ MOVIMIENTOS : "registra"
    CAJAS ||--o{ VENTAS : "recibe"
    USERS ||--o{ VENTAS : "registra"
    CLIENTES ||--o{ VENTAS : "compra"
    COMPROBANTES ||--o{ VENTAS : "tipo"
    VENTAS ||--|{ PRODUCTO_VENTA : "detalle"
    PRODUCTOS ||--o{ PRODUCTO_VENTA : "vendido"
    USERS ||--o{ COMPRAS : "registra"
    PROVEEDORES ||--o{ COMPRAS : "provee"
    COMPROBANTES ||--o{ COMPRAS : "tipo"
    COMPRAS ||--|{ COMPRA_PRODUCTO : "detalle"
    PRODUCTOS ||--o{ COMPRA_PRODUCTO : "comprado"

    PRODUCTOS {
        bigint id PK
        string codigo
        string nombre
        decimal precio
        tinyint estado
        bigint categoria_id FK
        bigint marca_id FK
        bigint presentacione_id FK
    }
    INVENTARIO {
        bigint id PK
        bigint producto_id FK
        bigint ubicacione_id FK
        int cantidad
        int cantidad_minima
        int cantidad_maxima
        date fecha_vencimiento
    }
    KARDEX {
        bigint id PK
        bigint producto_id FK
        enum tipo_transaccion
        int entrada
        int salida
        int saldo
        decimal costo_unitario
    }
    VENTAS {
        bigint id PK
        bigint cliente_id FK
        bigint user_id FK
        bigint caja_id FK
        bigint comprobante_id FK
        string numero_comprobante
        enum metodo_pago
        datetime fecha_hora
        decimal subtotal
        decimal impuesto
        decimal total
    }
    PRODUCTO_VENTA {
        bigint venta_id FK
        bigint producto_id FK
        int cantidad
        decimal precio_venta
    }
    COMPRAS {
        bigint id PK
        bigint proveedore_id FK
        bigint user_id FK
        bigint comprobante_id FK
        datetime fecha_hora
        decimal total
    }
    COMPRA_PRODUCTO {
        bigint compra_id FK
        bigint producto_id FK
        int cantidad
        decimal precio_compra
        date fecha_vencimiento
    }
    CAJAS {
        bigint id PK
        bigint user_id FK
        datetime fecha_hora_apertura
        datetime fecha_hora_cierre
        decimal saldo_inicial
        decimal saldo_final
        boolean estado
    }
    MOVIMIENTOS {
        bigint id PK
        bigint caja_id FK
        enum tipo
        decimal monto
        enum metodo_pago
    }
```

## 4. Flujo de una petición: registrar una venta (HU-03)

Lo que pasa en el código cuando el cajero confirma una venta (`ventaController@store`, eventos en `EventServiceProvider`).

```mermaid
sequenceDiagram
    autonumber
    actor C as Cajero
    participant N as Navegador
    participant MW as Middleware<br/>(auth, permiso crear-venta,<br/>caja abierta)
    participant VC as ventaController@store<br/>+ StoreVentaRequest
    participant EV as Eventos y listeners
    participant DB as MySQL
    participant Q as Cola (worker)

    C->>N: Agrega productos y confirma
    N->>MW: POST /admin/ventas (formulario + token CSRF)
    MW->>MW: ¿Sesión, permiso y caja abierta?
    alt No cumple
        MW-->>N: 403 o redirección
    else Cumple
        MW->>VC: Petición validada
        VC->>DB: BEGIN TRANSACTION
        VC->>DB: INSERT ventas y producto_venta
        VC->>EV: CreateVentaDetalleEvent (por producto)
        EV->>DB: INSERT kardex (salida) y UPDATE inventario
        VC->>EV: CreateVentaEvent
        EV->>DB: INSERT movimientos (ingreso a la caja)
        EV->>Q: EnviarComprobanteVentaJob (después del commit)
        VC->>DB: COMMIT
        VC-->>N: Redirección a movimientos de caja ("Venta registrada")
        Q-->>C: Correo con el comprobante (si el cliente tiene correo)
    end
    Note over VC,DB: Si algo falla se hace ROLLBACK y no queda nada guardado
```

## 5. Despliegue

El proyecto se ejecuta con **Laravel Sail** (Docker Compose, archivo `compose.yaml`): un contenedor para la aplicación y otro para MySQL. Cada push a `main` ejecuta las pruebas en **GitHub Actions** (`.github/workflows/test.yml`).

```mermaid
flowchart LR
    DEV["Equipo de desarrollo"] -->|"git push"| GH["GitHub<br/>Ghost-sm/SistemaVentas"]
    GH -->|"push a main"| CI["GitHub Actions<br/>test.yml · PHP 8.2 · phpunit"]

    subgraph LOCAL["Laravel Sail · Docker Compose (compose.yaml)"]
        direction TB
        APPL["Contenedor laravel.test<br/>imagen sail-8.5/app · puerto 80"]
        DBL[("Contenedor mysql<br/>mysql:8.4 · puerto 3306<br/>volumen sail-mysql")]
        APPL -->|"TCP 3306"| DBL
    end

    USR["Usuarios (navegador)"] -->|"HTTP · puerto 80"| APPL
    DEV -->|"sail up"| LOCAL
```

| Pieza | Archivo en el repositorio | Variables sensibles |
|---|---|---|
| Aplicación y base de datos | `compose.yaml` | `.env` local, fuera del repositorio (`APP_KEY`, `DB_*`, `MAIL_*`) |
| Pruebas automáticas | `.github/workflows/test.yml` | Ninguna; usa `.env.example` |
