# IDBI Invoice Recorder Challenge

API REST que permite registrar comprobantes en formato XML y consultarlos. A partir de estos comprobantes, se extrae
información relevante como los datos del emisor y receptor, los artículos o líneas incluidas y los montos totales.

La API utiliza JSON Web Token (JWT) para la autenticación.

## Componentes

El proyecto se ha desarrollado utilizando las siguientes tecnologías:

- PHP
- Nginx (servidor web)
- MySQL (base de datos)
- MailHog (gestión de envío de correos)

## Preparación del Entorno

El proyecto cuenta con una implementación de Docker Compose para facilitar la configuración del entorno de desarrollo.

> ⚠️ Si no estás familiarizado con Docker, puedes optar por otra configuración para preparar tu entorno. Si decides
> hacerlo, omite los pasos 1 y 2.

Instrucciones para iniciar el proyecto

1. Levantar los contenedores con Docker Compose:

```bash
docker compose up -d
```

2. Acceder al contenedor web:

```bash
docker exec -it idbi-invoice-recorder-challenge-web-1 bash
```

3. Configurar las variables de entorno:

```bash
cp .env.example .env
``` 

4. Configurar el secreto de JWT en las variables de entorno (genera una cadena de texto aleatoria):

```bash
JWT_SECRET=<random_string>
```

5. Instalar las dependencias del proyecto:

```bash
composer install
```

6. Generar una clave para la aplicación:

```bash
php artisan key:generate
```

7. Ejecutar las migraciones de la base de datos:

```bash
php artisan migrate
```

8. Rellenar la base de datos con datos iniciales:

```bash
php artisan db:seed
```

**¡Y listo!** Ahora puedes empezar a desarrollar.


## Uso de la API

La API estará disponible en: `http://localhost:8080/api/v1`

### Gestión de Correos

Para visualizar los correos enviados por la aplicación, puedes acceder a la interfaz de MailHog desde tu navegador en: `http://localhost:8025`.

### Configuración de Gestión de Correo sin Docker

Para visualizar los correos enviados sin Docker, sigue estos pasos:

1. Descarga el ejecutable **MailHog_windows_amd64.exe** de la página oficial [MailHog Releases](https://github.com/mailhog/MailHog/releases).

2. Configura en el archivo `.env` lo siguiente:

    ```bash
    MAIL_MAILER=smtp
    MAIL_HOST=127.0.0.1
    MAIL_PORT=1025
    ```

3. Guarda el archivo en `C:\MailHog` y navega a la carpeta:

    ```bash
    cd C:\MailHog
    ```

4. Ejecuta MailHog:

    ```bash
    .\MailHog_windows_amd64.exe
    ```

5. Ingresa a la interfaz en `http://127.0.0.1:8025/`.

## Funcionalidades Implementadas

1. **Autenticación de Usuario:**  
   Verificación de identidad mediante JWT para realizar acciones en la API.

2. **Registro de Comprobantes XML:**  
   Carga y almacenamiento de comprobantes en formato XML, extrayendo información clave.

3. **Consulta de Comprobantes:**  
   Consulta de los comprobantes registrados por el usuario autenticado.

4. **Eliminación de Comprobantes:**  
   Eliminación de comprobantes registrados por el usuario autenticado.

## Requerimientos y Funcionalidades Adicionales

1. **Almacenamiento de Información Adicional en Comprobantes**  
   Se ha agregado la capacidad de almacenar datos como serie, número, tipo de comprobante y moneda. Además, se regularizaron los comprobantes existentes extrayendo esta información desde el campo `xml_content` de la base de datos.

    - Se actualizó la tabla `vouchers` para agregar estos campos.
    - Se creó un proceso de migración para actualizar los registros existentes.

2. **Procesamiento Asíncrono de Comprobantes**  
   El procesamiento de los comprobantes se realiza en segundo plano usando **Laravel Queue**, lo que mejora la eficiencia de la API.

    - Los comprobantes procesados con éxito y los fallidos se envían en un correo de resumen al final del procesamiento.

3. **Consulta de Montos Totales Acumulados por Moneda**  
   Se implementó un endpoint para consultar los montos totales de los comprobantes registrados, desglosados por tipo de moneda (PEN y USD).

4. **Eliminación de Comprobantes por Identificador**  
   Implementación de un endpoint para eliminar comprobantes registrados por el usuario autenticado, con validaciones de existencia y propiedad.

5. **Filtros Avanzados en la Consulta de Comprobantes**  
   Se añadieron filtros por serie, número, tipo de comprobante, moneda y rango de fechas, asegurando que los usuarios solo puedan acceder a sus propios comprobantes.

## Arquitectura del Proyecto

La API está desarrollada con **Laravel**, estructada bajo el patrón **MVC** para una organización eficiente del código.

- **Controladores:** En `app/Http/Controllers/Auth` y `app/Http/Controllers/vouchers`, gestionan las solicitudes HTTP.
- **Servicios:** En `app/Services`, contienen la lógica de negocio.
- **Modelos:** En `app/Models`, representan las tablas de la base de datos.
- **Migraciones:** En `database/migrations`, para la creación de tablas y modificaciones.
- **Rutas:** En `routes/`, donde se definen los endpoints de la API.

### Procesamiento Asíncrono

- **Jobs:** Usamos la clase **Job** de Laravel para procesar los comprobantes en segundo plano.
- **Queue:** La cola de trabajos gestiona el procesamiento y los resúmenes por correo.

## Uso de la API

1. **Autenticación de Usuario**  
   La autenticación se realiza a través de **Laravel Sanctum**. Cada solicitud debe incluir un token de acceso válido en los encabezados.

2. **Endpoints Principales:**
    - `POST /api/v1/vouchers`: Cargar y registrar comprobantes en formato XML.
    - `GET api/v1/vouchers?page=1&paginate=10&serie=ABC123&number=456789&voucher_type=invoice&currency=PEN&start_date=2024-01-01&end_date=2025-12-31`: Consultar los comprobantes registrados, con filtros opcionales.
    - `DELETE /api/v1/vouchers/{id}`: Eliminar un comprobante por su identificador.
    - `GET /api/vouchers/v1/vouchers/total-amounts?page=1&paginate=10`: Consultar los montos totales acumulados de los comprobantes registrados.

## Mejoras y Propuestas

- **Manejo de Errores:** Se ha implementado una capa de manejo de errores para respuestas claras en caso de fallos.
- **Pruebas Unitarias:** Se recomienda agregar pruebas unitarias para validar las funcionalidades clave.
- **Optimización de Base de Datos:** Aplicar índices para mejorar el rendimiento de consultas complejas.

## Conclusión

Este reto ha permitido mejorar la API de gestión de comprobantes XML, implementando nuevas funcionalidades como el procesamiento asíncrono y consultas de montos, lo que facilita su uso y mejora la eficiencia. La solución es escalable y adecuada para un entorno de producción.
