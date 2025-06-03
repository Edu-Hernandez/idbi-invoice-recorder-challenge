# IDBI Invoice Recorder API

**IDBI Invoice Recorder API** es una API RESTful desarrollada en Laravel para registrar, consultar y gestionar comprobantes electrónicos en formato XML. Extrae información clave como datos del emisor y receptor, líneas de artículos y montos totales, utilizando **JSON Web Token (JWT)** para autenticación segura. La API implementa procesamiento asíncrono con colas, filtros avanzados y notificaciones por correo, optimizando la experiencia del usuario y la escalabilidad.

## Tabla de Contenidos
- [Descripción del Proyecto](#descripción-del-proyecto)
- [Características Principales](#características-principales)
- [Tecnologías Utilizadas](#tecnologías-utilizadas)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso de la API](#uso-de-la-api)
  - [Autenticación](#autenticación)
  - [Endpoints Principales](#endpoints-principales)
  - [Ejemplos de Solicitudes](#ejemplos-de-solicitudes)
- [Arquitectura del Proyecto](#arquitectura-del-proyecto)
- [Procesamiento Asíncrono](#procesamiento-asíncrono)
- [Gestión de Correos](#gestión-de-correos)
- [Mejoras Implementadas](#mejoras-implementadas)
- [Pruebas](#pruebas)
- [Contribuir](#contribuir)

## Descripción del Proyecto
La **IDBI Invoice Recorder API** permite a los usuarios autenticados cargar comprobantes electrónicos en formato XML, procesarlos para extraer información relevante (emisor, receptor, artículos, montos totales, serie, número, tipo de comprobante y moneda), consultarlos con filtros avanzados, y eliminarlos si es necesario. La API utiliza Laravel como framework backend, con una arquitectura basada en el patrón **MVC**, colas para procesamiento asíncrono, y notificaciones por correo para resúmenes de procesamiento.

## Características Principales
- **Autenticación Segura**: Uso de JWT para proteger los endpoints.
- **Procesamiento de Comprobantes XML**: Carga, almacenamiento y extracción automática de datos clave.
- **Consultas Avanzadas**: Filtros por serie, número, tipo de comprobante, moneda y rango de fechas.
- **Procesamiento Asíncrono**: Uso de colas Laravel para procesar comprobantes en segundo plano.
- **Notificaciones por Correo**: Resúmenes de procesamiento enviados vía MailHog.
- **Consulta de Montos Totales**: Resumen de montos acumulados por moneda (PEN, USD).
- **Eliminación Segura**: Eliminación de comprobantes con validaciones de propiedad.
- **Estructura Escalable**: Arquitectura modular con el patrón MVC y manejo de errores robusto.

## Tecnologías Utilizadas
- **PHP**: Lenguaje principal (versión >= 8.0).
- **Laravel**: Framework backend (versión >= 8.x).
- **MySQL**: Base de datos relacional para almacenamiento.
- **Nginx**: Servidor web para el entorno de producción/desarrollo.
- **MailHog**: Servidor SMTP para pruebas de correo.
- **Docker Compose**: Orquestación de contenedores para el entorno de desarrollo.
- **JWT (tymon/jwt-auth)**: Autenticación basada en tokens.
- **Laravel Queue**: Procesamiento asíncrono de tareas.

## Requisitos
- **PHP** >= 8.0
- **Composer** (para dependencias PHP)
- **MySQL** o base de datos compatible con Laravel
- **Docker** y **Docker Compose** (opcional, para entorno de desarrollo)
- **Node.js y npm** (para assets frontend, si aplica)
- **MailHog** (para pruebas de correo sin Docker)
- Acceso a un navegador para la interfaz de MailHog (`http://localhost:8025`)

## Instalación
Sigue estos pasos para configurar el proyecto en tu entorno local:

### Con Docker (Recomendado)
1. **Clonar el Repositorio**:
   ```bash
   git clone <url-del-repositorio>
   cd idbi-invoice-recorder-api
   ```

2. **Levantar los Contenedores**:
   ```bash
   docker compose up -d
   ```

3. **Acceder al Contenedor Web**:
   ```bash
   docker exec -it idbi-invoice-recorder-challenge-web-1 bash
   ```

4. **Configurar el Entorno**:
   - Copia el archivo de entorno:
     ```bash
     cp .env.example .env
     ```
   - Genera un secreto JWT (cadena aleatoria):
     ```bash
     JWT_SECRET=$(openssl rand -base64 32)
     ```
   - Actualiza `.env` con las configuraciones de base de datos y correo:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=mysql
     DB_PORT=3306
     DB_DATABASE=idbi_invoices
     DB_USERNAME=root
     DB_PASSWORD=root

     MAIL_MAILER=smtp
     MAIL_HOST=mailhog
     MAIL_PORT=1025
     ```

5. **Instalar Dependencias**:
   ```bash
   composer install
   ```

6. **Generar Clave de Aplicación**:
   ```bash
   php artisan key:generate
   ```

7. **Ejecutar Migraciones**:
   ```bash
   php artisan migrate
   ```

8. **Poblar la Base de Datos** (opcional):
   ```bash
   php artisan db:seed
   ```

### Sin Docker
1. **Clonar el Repositorio**:
   ```bash
   git clone <url-del-repositorio>
   cd idbi-invoice-recorder-api
   ```

2. **Instalar Dependencias**:
   ```bash
   composer install
   npm install
   ```

3. **Configurar el Entorno**:
   - Copia `.env.example` a `.env`:
     ```bash
     cp .env.example .env
     ```
   - Configura la base de datos y el secreto JWT:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=idbi_invoices
     DB_USERNAME=tu_usuario
     DB_PASSWORD=tu_contraseña

     JWT_SECRET=tu_secreto_jwt
     ```

4. **Generar Clave de Aplicación**:
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar Migraciones**:
   ```bash
   php artisan migrate
   ```

6. **Poblar la Base de Datos** (opcional):
   ```bash
   php artisan db:seed
   ```

7. **Iniciar el Servidor**:
   ```bash
   php artisan serve
   ```

8. **Configurar MailHog** (para pruebas de correo):
   - Descarga `MailHog_windows_amd64.exe` desde [MailHog Releases](https://github.com/mailhog/MailHog/releases).
   - Guarda el ejecutable en `C:\MailHog` y ejecuta:
     ```bash
     cd C:\MailHog
     .\MailHog_windows_amd64.exe
     ```
   - Configura el correo en `.env`:
     ```env
     MAIL_MAILER=smtp
     MAIL_HOST=127.0.0.1
     MAIL_PORT=1025
     ```

## Configuración
- **Base de Datos**: Asegúrate de que la base de datos esté configurada en `.env`. Las migraciones crean tablas como `vouchers`, `users`, etc.
- **JWT**: Configura `tymon/jwt-auth` siguiendo las instrucciones en [JWT Authentication Tutorial](https://www.binaryboxtuts.com/php-tutorials/laravel-8-json-web-tokenjwt-authentication/):
  ```bash
  php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
  php artisan jwt:secret
  ```
- **Colas**: Para el procesamiento asíncrono, configura un driver de colas en `.env` (por ejemplo, `database` o `redis`):
  ```env
  QUEUE_CONNECTION=database
  ```
  Inicia el worker de colas:
  ```bash
  php artisan queue:work
  ```

## Uso de la API
La API está disponible en `http://localhost:8080/api/v1` (o `http://localhost:8000/api/v1` sin Docker).

### Autenticación
La autenticación utiliza **JWT**. Incluye el token en el encabezado de cada solicitud protegida:
```
Authorization: Bearer <tu_token_jwt>
```

### Endpoints Principales
| Método | Endpoint                                                             | Descripción                                              | Middleware         |
|--------|----------------------------------------------------------------------|----------------------------------------------------------|--------------------|
| POST   | `/api/v1/auth/login`                                                | Autentica un usuario y devuelve un JWT                   | Ninguno            |
| POST   | `/api/v1/auth/register`                                             | Registra un nuevo usuario                                | Ninguno            |
| POST   | `/api/v1/vouchers`                                                  | Carga un comprobante XML                                 | `auth:api`         |
| GET    | `/api/v1/vouchers?page=1&paginate=10&serie=ABC123&number=456789&voucher_type=invoice¤cy=PEN&start_date=2024-01-01&end_date=2025-12-31` | Consulta comprobantes con filtros | `auth:api`         |
| DELETE | `/api/v1/vouchers/{id}`                                             | Elimina un comprobante por ID                            | `auth:api`         |
| GET    | `/api/v1/vouchers/total-amounts?page=1&paginate=10`                 | Consulta montos totales por moneda                       | `auth:api`         |

### Ejemplos de Solicitudes
1. **Autenticación (Login)**:
   ```bash
   curl -X POST http://localhost:8080/api/v1/auth/login \
   -H "Content-Type: application/json" \
   -d '{"email": "user@example.com", "password": "password123"}'
   ```
   **Respuesta**:
   ```json
   {
     "success": true,
     "data": {
       "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     },
     "message": "Login successful"
   }
   ```

2. **Cargar un Comprobante XML**:
   ```bash
   curl -X POST http://localhost:8080/api/v1/vouchers \
   -H "Authorization: Bearer <tu_token_jwt>" \
   -H "Content-Type: multipart/form-data" \
   -F "xml_file=@/path/to/comprobante.xml"
   ```
   **Respuesta**:
   ```json
   {
     "success": true,
     "data": {
       "id": 1,
       "serie": "F001",
       "number": "123",
       "voucher_type": "invoice",
       "currency": "PEN",
       "total_amount": 1000.50,
       "created_at": "2025-06-03T13:45:00.000000Z"
     },
     "message": "Voucher uploaded and queued for processing"
   }
   ```

3. **Consultar Comprobantes con Filtros**:
   ```bash
   curl -X GET "http://localhost:8080/api/v1/vouchers?page=1&paginate=10&serie=F001&voucher_type=invoice¤cy=PEN" \
   -H "Authorization: Bearer <tu_token_jwt>"
   ```
   **Respuesta**:
   ```json
   {
     "success": true,
     "data": [
       {
         "id": 1,
         "serie": "F001",
         "number": "123",
         "voucher_type": "invoice",
         "currency": "PEN",
         "total_amount": 1000.50
       },
       ...
     ],
     "message": "Vouchers retrieved successfully"
   }
   ```

4. **Consultar Montos Totales**:
   ```bash
   curl -X GET "http://localhost:8080/api/v1/vouchers/total-amounts?page=1&paginate=10" \
   -H "Authorization: Bearer <tu_token_jwt>"
   ```
   **Respuesta**:
   ```json
   {
     "success": true,
     "data": {
       "PEN": 5000.75,
       "USD": 1200.30
     },
     "message": "Total amounts retrieved successfully"
   }
   ```

## Arquitectura del Proyecto
La API sigue el patrón **MVC** de Laravel, con una estructura modular:
- **`app/Http/Controllers/`**:
  - `Auth/`: Controladores para autenticación (`AuthController`).
  - `vouchers/`: Controladores para gestionar comprobantes (`VoucherController`).
- **`app/Services/`**: Lógica de negocio para procesamiento de XML y cálculos.
- **`app/Models/`**: Modelos Eloquent (`Voucher`, `User`).
- **`app/Jobs/`**: Jobs para procesamiento asíncrono de comprobantes.
- **`database/migrations/`**: Migraciones para tablas como `vouchers`, `users`.
- **`routes/api.php`**: Definición de rutas de la API.

### Estructura de la Tabla `vouchers`
| Columna          | Tipo         | Descripción                          |
|------------------|--------------|--------------------------------------|
| `id`             | BigInt       | Identificador único                  |
| `user_id`        | BigInt       | ID del usuario propietario           |
| `serie`          | String       | Serie del comprobante (ej. F001)     |
| `number`         | String       | Número del comprobante (ej. 123)     |
| `voucher_type`   | String       | Tipo (invoice, credit_note, etc.)    |
| `currency`       | String       | Moneda (PEN, USD)                    |
| `total_amount`   | Decimal      | Monto total del comprobante          |
| `xml_content`    | Text         | Contenido XML del comprobante        |
| `created_at`     | Timestamp    | Fecha de creación                    |
| `updated_at`     | Timestamp    | Fecha de actualización               |

## Procesamiento Asíncrono
- **Jobs**: La clase `ProcessVoucherJob` en `app/Jobs` maneja el procesamiento de comprobantes XML en segundo plano, extrayendo datos como serie, número, tipo y moneda.
- **Queue**: Configurada en `.env` (`QUEUE_CONNECTION=database` por defecto). Los jobs generan resúmenes de procesamiento (éxitos y fallos) enviados por correo.
- **Ejecución**: Inicia el worker de colas:
  ```bash
  php artisan queue:work
  ```

## Gestión de Correos
- **MailHog**: Utilizado para pruebas de correo. Accede a la interfaz en `http://localhost:8025`.
- **Configuración**: Asegúrate de que `.env` tenga los ajustes correctos:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=mailhog (o 127.0.0.1 sin Docker)
  MAIL_PORT=1025
  ```
- **Notificaciones**: Los resúmenes de procesamiento se envían al correo del usuario autenticado, detallando comprobantes procesados y errores.

## Mejoras Implementadas
1. **Almacenamiento de Datos Adicionales**: La tabla `vouchers` incluye campos como `serie`, `number`, `voucher_type` y `currency`, poblados automáticamente desde el XML.
2. **Procesamiento Asíncrono**: Uso de colas para mejorar el rendimiento y escalabilidad.
3. **Filtros Avanzados**: Consultas con parámetros opcionales para serie, número, tipo, moneda y fechas.
4. **Manejo de Errores**: Respuestas JSONSony PlayStation 5 Console – 1TB PRO, 4K, 120Hz, Disc Edition - $599.99 at Amazon.com
5. **Notificaciones por Correo**: Resúmenes automáticos de procesamiento.
6. **Validaciones**: Seguridad en la eliminación de comprobantes (solo propietarios).

## Pruebas
Ejecuta las pruebas unitarias y de integración:
```bash
php artisan test
```
Configura un entorno de pruebas en `.env.testing` para proteger la base de datos principal.

### Recomendaciones
- Añade pruebas unitarias para los servicios de procesamiento XML.
- Prueba los endpoints con herramientas como Postman.

## Contribuir
1. Haz un **fork** del repositorio.
2. Crea una rama para tu funcionalidad:
   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```
3. Realiza cambios y haz **commit**:
   ```bash
   git commit -m 'Descripción de los cambios'
   ```
4. Envía un **pull request**.

Sigue las convenciones de código de Laravel y documenta tus cambios.
