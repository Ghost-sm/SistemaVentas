# Arquitectura – BRYSA: sistema web de ventas e inventario

**Proyecto integrador · Módulo 6 · Sesión 2**
Autores: Carol Dahiana González Ceballos y Santiago Mejía Londoño · 14 de noviembre de 2026

Este documento describe la arquitectura **tal como está hoy en el código** del repositorio. Lo que todavía no existe se marca como *planeado* y se enlaza con su issue.

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
    LLM["Proveedor de LLM<br/>[Sistema externo · planeado]<br/>Redacta la sugerencia de reposición (HU-08)"]

    ADM -->|"Usa por navegador (HTTPS)"| SIS
    CAJ -->|"Usa por navegador (HTTPS)"| SIS
    INV -->|"Usa por navegador (HTTPS)"| SIS
    SIS -->|"Envía comprobante (SMTP)"| MAIL
    SIS -.->|"Pide explicación (HTTPS + llave) · planeado"| LLM
```

## 2. Diagrama de contenedores (C4 nivel 2)

Las piezas que se despliegan. El sistema es un **monolito MVC**: el mismo Laravel atiende las pantallas y la lógica de negocio. No hay una API REST separada (`routes/api.php` solo tiene la ruta por defecto `/user`).

```mermaid
flowchart TB
    U["Usuarios<br/>(administrador, cajero, inventario)"]

    subgraph BRYSA["Sistema BRYSA"]
        WEB["Aplicación web Laravel<br/>[PHP 8.2 · Laravel 12]<br/>Controladores, vistas Blade (SB Admin + Bootstrap 5),<br/>Spatie Permission, DomPDF, Laravel Excel"]
        WORKER["Worker de colas<br/>[php artisan queue:work]<br/>Envía comprobantes y exporta Excel en segundo plano"]
        DB[("Base de datos<br/>[MySQL 8]<br/>Ventas, compras, inventario, kardex, cajas, usuarios")]
        FILES[("Almacenamiento de archivos<br/>[disco public de Laravel]<br/>Imágenes de productos y comprobantes")]
    end

    MAIL["Servidor SMTP<br/>[externo]"]
    LLM["Proveedor de LLM<br/>[externo · planeado HU-08]"]

    U -->|"HTTPS · HTML, formularios y JS"| WEB
    WEB -->|"SQL sobre TCP 3306 (Eloquent)"| DB
    WEB -->|"Lee y escribe archivos"| FILES
    WEB -->|"Encola trabajos (tabla jobs)"| DB
    WORKER -->|"Toma trabajos (SQL)"| DB
    WORKER -->|"SMTP"| MAIL
    WEB -.->|"HTTPS + llave en .env · planeado"| LLM
```

| Contenedor | Tecnología | Responsabilidad |
|---|---|---|
| Aplicación web | PHP 8.2, Laravel 12, Blade, Bootstrap 5 | Pantallas, validación (Form Requests), reglas de negocio, roles y permisos |
| Worker de colas | `php artisan queue:work` (`run-worker.sh`) | Trabajos `EnviarComprobanteVentaJob` y `DownloadExcelVentasAllJob` |
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
    N->>MW: POST /ventas (HTTPS, formulario + token CSRF)
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

**Hoy:** el sistema corre en local con Laravel Sail (Docker Compose: `compose.yaml`).
**Meta (issue #11):** URL pública en Railway. El repositorio ya tiene `railpack.json` (PHP 8.2) y ramas `railway/fix-deploy-*`, pero el último despliegue registrado en GitHub falló.

```mermaid
flowchart LR
    subgraph LOCAL["Desarrollo local · Laravel Sail (Docker Compose)"]
        direction TB
        APPL["Contenedor laravel.test<br/>PHP 8.x · puerto 80"]
        DBL[("Contenedor mysql<br/>MySQL 8.4 · puerto 3306<br/>volumen sail-mysql")]
        APPL -->|"TCP 3306"| DBL
    end

    subgraph RAIL["Producción · Railway (planeado, issue #11)"]
        direction TB
        APPR["Servicio web<br/>Railpack · PHP 8.2"]
        WRK["Servicio worker<br/>run-worker.sh · queue:work"]
        DBR[("MySQL administrado<br/>de Railway")]
        APPR -->|"TCP privado"| DBR
        WRK -->|"TCP privado"| DBR
    end

    DEV["Equipo de desarrollo"] -->|"git push"| GH["GitHub<br/>Ghost-sm/SistemaVentas"]
    GH -->|"Despliegue automático"| RAIL
    GH -->|"GitHub Actions: pruebas"| CI["CI"]
    USR["Usuarios de la tienda"] -->|"HTTPS"| APPR
    DEV -->|"http://localhost"| APPL
```

| Ambiente | Dónde | Variables sensibles |
|---|---|---|
| Local | Docker Compose (Sail) | `.env` local, fuera del repositorio |
| Producción (planeado) | Railway | Variables del servicio en Railway (`APP_KEY`, `DB_*`, `MAIL_*`, llave del LLM) |

## 6. Correcciones frente a la IA

Los diagramas se generaron con un asistente de IA a partir de los documentos del proyecto (informe de arquitectura del Módulo 2, informe del MVP del Módulo 3, documento de despliegue y README) y luego se compararon con el código del repositorio. Lo que no coincidía se corrigió así:

1. **API REST:** los documentos anteriores y el README describen una API REST de ventas con Sanctum. En el código no existe (`routes/api.php` solo tiene `/user`). Se dibujó como monolito MVC.
2. **Servicio de analítica en Python y LLM:** venían de la propuesta del Módulo 2, pero no están en el repositorio. Se dejaron solo como *planeado* (HU-08, issue #10) con línea punteada.
3. **Servidor Node.js:** el documento de despliegue anterior hablaba de un servidor Node.js. El backend es PHP/Laravel; Node solo se usa para Vite al desarrollar.
4. **Correo y colas:** no aparecían en los diagramas anteriores, pero existen en el código (`EnviarComprobanteVentaJob`, `run-worker.sh`), así que se agregaron el worker y el servidor SMTP.
5. **Fiados y devoluciones:** estaban en la formulación del proyecto, pero no hay tablas para ellos; no aparecen en el modelo de datos.
