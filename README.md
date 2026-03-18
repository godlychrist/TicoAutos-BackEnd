# 🏎️ TicoAutos — Backend API

API REST para el marketplace de vehículos **TicoAutos**, construida con **Laravel 9** y **MongoDB Atlas**. Provee autenticación JWT, CRUD de vehículos con subida de imágenes, y un sistema de mensajería entre compradores y vendedores.

> 🖥️ **Frontend:** Este backend se conecta con el cliente Vue.js disponible en el repositorio [ticoautos-frontend](https://github.com/tu-usuario/ticoautos-frontend).

---

## 🛠 Tech Stack

| Tecnología | Versión | Uso |
|---|---|---|
| **PHP** | ^8.0.2 | Lenguaje del servidor |
| **Laravel** | ^9.19 | Framework backend |
| **MongoDB Atlas** | Cloud | Base de datos NoSQL |
| **jenssegers/mongodb** | ^3.9 | Driver Eloquent para MongoDB |
| **tymon/jwt-auth** | 2.1 | Autenticación con JSON Web Tokens |
| **Laravel Sanctum** | ^3.0 | Tokens API |
| **Composer** | Latest | Gestor de dependencias PHP |

---

## ✅ Requisitos Previos

- **PHP** >= 8.0.2 → [Descargar](https://www.php.net/downloads)
- **Composer** → [Descargar](https://getcomposer.org/download/)
- **Extensión PHP MongoDB** (`php_mongodb.dll`) habilitada en tu `php.ini`
- **Cuenta de MongoDB Atlas** con un cluster activo → [MongoDB Atlas](https://www.mongodb.com/atlas)

```bash
# Verificar instalaciones
php -v          # PHP 8.x
composer -V     # Composer 2.x
```

---

## 🚀 Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd ticoautos-backend

# 2. Instalar dependencias con Composer
composer install

# 3. Copiar el archivo de entorno
copy .env.example .env

# 4. Generar la clave de la aplicación
php artisan key:generate

# 5. Generar el secreto JWT
php artisan jwt:secret

# 6. Crear enlace simbólico para servir imágenes desde storage
php artisan storage:link
```

### Configurar `.env`

Editá el archivo `.env` con tus credenciales de MongoDB Atlas:

```env
DB_CONNECTION=mongodb
DB_URI=mongodb+srv://<usuario>:<contraseña>@<cluster>.mongodb.net/<database>?retryWrites=true&w=majority
DB_DATABASE=TicoCars
```

> ⚠️ Asegurate de agregar tu IP a la **whitelist** de MongoDB Atlas (Network Access).

---

## ▶️ Ejecución

```bash
php artisan serve
```

> El servidor se inicia en `http://127.0.0.1:8000`

---

## 📡 Endpoints de la API

Base URL: `http://127.0.0.1:8000/api`

### Autenticación (públicas)

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/register` | Registrar nuevo usuario |
| `POST` | `/login` | Iniciar sesión → retorna JWT |

### Vehículos — Lectura (públicas)

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/vehicles` | Listar vehículos (paginado + filtros) |
| `GET` | `/vehicles/{id}` | Detalle de un vehículo con datos del vendedor |

**Query params disponibles:** `search`, `brand`, `year_min`, `year_max`, `status`, `price_range`, `page`

### Vehículos — Escritura (🔒 requieren JWT)

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/vehicles` | Crear vehículo (FormData con imagen) |
| `PUT` | `/vehicles/{id}` | Editar vehículo |
| `DELETE` | `/vehicles/{id}` | Eliminar vehículo |

### Conversaciones y Mensajes (🔒 requieren JWT)

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/conversations` | Crear o reutilizar conversación |
| `GET` | `/conversations` | Listar conversaciones del usuario |
| `GET` | `/conversations/{id}` | Obtener mensajes de una conversación |
| `POST` | `/messages` | Enviar un mensaje |

> 🔑 Header requerido en rutas protegidas: `Authorization: Bearer <token>`

---

## 📁 Estructura del Proyecto

```
ticoautos-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Registro y login con JWT
│   │   │   ├── VehicleController.php     # CRUD de vehículos + filtros
│   │   │   └── MessageController.php     # Mensajería y conversaciones
│   │   └── Middleware/                   # CORS, autenticación, etc.
│   ├── Models/
│   │   ├── User.php                      # Usuario (JWT + MongoDB)
│   │   ├── Vehicle.php                   # Vehículo publicado
│   │   ├── Conversation.php              # Hilo de chat buyer ↔ seller
│   │   └── Message.php                   # Mensaje individual
│   └── Providers/                        # Service providers
├── config/
│   └── database.php                      # Configuración MongoDB Atlas
├── routes/
│   └── api.php                           # Definición de rutas API
├── storage/app/public/vehicles/          # Imágenes subidas
├── .env.example                          # Plantilla de variables de entorno
└── composer.json                         # Dependencias PHP
```

---

## 🔐 Variables de Entorno

| Variable | Descripción |
|---|---|
| `DB_CONNECTION` | `mongodb` |
| `DB_URI` | Connection string de MongoDB Atlas |
| `DB_DATABASE` | Nombre de la base de datos (`TicoCars`) |
| `JWT_SECRET` | Clave para firmar tokens (auto-generada) |
| `APP_KEY` | Clave de encriptación Laravel (auto-generada) |

---

## 📝 Modelos de Datos

### User
| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | ID generado por MongoDB |
| `username` | string | Nombre de usuario |
| `password` | string | Contraseña hasheada (bcrypt) |

### Vehicle
| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | ID del vehículo |
| `brand` | string | Marca (ej: Toyota, Ferrari) |
| `model` | string | Modelo (ej: Corolla, F8 Tributo) |
| `year` | integer | Año del vehículo |
| `price` | float | Precio en USD |
| `status` | string | `available` o `sold` |
| `image` | string | Ruta de la imagen en storage |
| `user_id` | string | ID del vendedor (relación con User) |

### Conversation
| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | ID de la conversación |
| `buyer_id` | string | ID del comprador |
| `seller_id` | string | ID del vendedor |
| `vehicle_id` | string | ID del vehículo relacionado |
| `last_message` | string | Último mensaje enviado |
| `last_message_at` | datetime | Timestamp del último mensaje |

### Message
| Campo | Tipo | Descripción |
|---|---|---|
| `_id` | ObjectId | ID del mensaje |
| `conversation_id` | string | Conversación a la que pertenece |
| `sender_id` | string | ID del remitente |
| `message` | string | Contenido del mensaje |
| `created_at` | string | Timestamp de creación |
