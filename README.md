# 🍊 PAPM — Passport Pal Motril (Laravel 11)

> **Aplicación web de rutas gastronómicas con sistema de puntos y cupones**
> Proyecto de fin de ciclo · Grado Superior DAW
> Autores: **Carlos Cuevas Puga** y **Antonio Rico Guirado**

---

## 🛠️ Stack tecnológico

| Capa | Tecnología | Justificación |
|------|-----------|---------------|
| **Backend** | Laravel 11 | Framework PHP moderno con Eloquent ORM, migrations, validación, middleware y routing expresivo |
| **Autenticación** | Laravel Sanctum | Tokens API ligeros sin OAuth, perfectos para SPA + apps móviles |
| **Base de datos** | MySQL 8 / MariaDB | BD relacional con soporte completo de FK, índices y transacciones |
| **Tests** | PHPUnit 11 | Suite de tests integrada en Laravel; `RefreshDatabase` para aislamiento |
| **Frontend** | HTML5 + CSS3 + JS vanilla | Sin frameworks pesados; carga directa desde `public/` |
| **APIs externas** | Google Maps · OpenWeatherMap | Mapa interactivo y clima de Motril |

---

## 📁 Estructura del proyecto

```
papmotril-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/          # Controladores de la API REST
│   │   │   ├── AuthController.php        → registro, login, me, logout
│   │   │   ├── RestauranteController.php → index, show, valorar
│   │   │   ├── RutaController.php        → index, show, iniciar, completar, progreso
│   │   │   ├── CuponController.php       → index, misCupones, canjear
│   │   │   └── UserController.php        → perfil, actualizarPerfil, ranking, suscribirse
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php             → middleware('role:admin,restaurante')
│   │   │   └── RequirePremium.php        → middleware('premium')
│   │   └── Requests/                 # Form Requests con validación declarativa
│   │       ├── Auth/{RegistroRequest, LoginRequest}
│   │       ├── Restaurante/ValoracionRequest
│   │       └── User/{ActualizarPerfilRequest, SuscripcionRequest}
│   └── Models/
│       ├── User.php              → HasApiTokens + sumarPuntos() / restarPuntos()
│       ├── Restaurante.php       → Scopes: activo, categoria, precio
│       ├── Ruta.php              → BelongsToMany con pivot 'orden'
│       ├── ProgresoRuta.php
│       ├── Cupon.php             → Scope activo() (vigente + usos disponibles)
│       ├── CuponUsuario.php
│       ├── Valoracion.php
│       ├── HistorialPuntos.php
│       └── Suscripcion.php
├── bootstrap/
│   └── app.php                   # Kernel Laravel 11: rutas, middleware, excepciones JSON
├── config/
│   └── cors.php                  # CORS configurado para el frontend
├── database/
│   ├── factories/                # Factories para testing
│   │   ├── UserFactory.php
│   │   ├── RestauranteFactory.php
│   │   ├── RutaFactory.php
│   │   └── CuponFactory.php
│   ├── migrations/               # 5 migraciones ordenadas cronológicamente
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UsuariosSeeder.php
│       ├── RestaurantesSeeder.php
│       ├── RutasSeeder.php
│       └── CuponesSeeder.php
├── public/                       # Frontend estático (servido directamente)
│   ├── css/main.css
│   ├── js/api.js
│   ├── index.html
│   ├── login.html
│   ├── registro.html
│   ├── rutas.html
│   ├── restaurantes.html
│   ├── cupones.html
│   └── perfil.html
├── routes/
│   └── api.php                   # 18 endpoints organizados por dominio
├── tests/
│   └── Feature/
│       ├── AuthTest.php              → 9 tests de autenticación
│       ├── RutaTest.php              → 9 tests de rutas gastronómicas
│       └── RestauranteCuponTest.php  → 10 tests de restaurantes y cupones
├── .env.example
├── composer.json                 # Laravel 11 + Sanctum
└── phpunit.xml                   # SQLite :memory: para tests
```

---

## ⚙️ Instalación paso a paso

### Requisitos previos

| Herramienta | Versión mínima |
|-------------|----------------|
| PHP | 8.2 con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `bcmath` |
| Composer | 2.x |
| MySQL | 8.0 / MariaDB 10.6 |
| Node.js | No requerido (frontend vanilla) |

---

### 1 — Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/papmotril-laravel.git
cd papmotril-laravel
```

### 2 — Instalar dependencias PHP

```bash
composer install
```

### 3 — Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus datos:

```env
DB_HOST=127.0.0.1
DB_DATABASE=papmotril
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

FRONTEND_URL=http://localhost:8000

GOOGLE_MAPS_KEY=AIza...tu_clave...
OPENWEATHER_KEY=tu_clave_openweather
```

### 4 — Crear la base de datos

```bash
mysql -u root -p -e "CREATE DATABASE papmotril CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5 — Ejecutar migraciones y seeders

```bash
# Solo migraciones
php artisan migrate

# Migraciones + datos de prueba (recomendado para desarrollo)
php artisan migrate --seed

# O si quieres empezar desde cero
php artisan migrate:fresh --seed
```

### 6 — Arrancar el servidor de desarrollo

```bash
php artisan serve
# → API disponible en http://localhost:8000/api
# → Frontend disponible en http://localhost:8000
```

> **Todo en uno**: Laravel sirve tanto la API como el frontend estático desde `public/`.

---

## 🔑 Cuentas de prueba

| Rol | Email | Contraseña | Plan |
|-----|-------|-----------|------|
| Admin | `admin@papmotril.com` | `password123` | Premium |
| Turista Premium | `maria@example.com` | `password123` | Premium |
| Turista Gratis | `carlos@example.com` | `password123` | Gratis |
| Restaurante | `elpuerto@example.com` | `password123` | Premium |

---

## 🧪 Ejecutar los tests

```bash
# Todos los tests (usa SQLite :memory:, no toca tu BD)
php artisan test

# Solo los tests de autenticación
php artisan test --filter AuthTest

# Con cobertura detallada
php artisan test --coverage
```

**28 tests** en total distribuidos en 3 clases:

| Clase | Tests | Cubre |
|-------|-------|-------|
| `AuthTest` | 9 | Registro, login, credenciales, puntos bienvenida, logout, revocación de token |
| `RutaTest` | 9 | Listado, filtros, iniciar, completar, puntos, duplicados, progreso |
| `RestauranteCuponTest` | 10 | Filtros, valoraciones, cupones premium, canje, puntos, validaciones |

---

## 🌐 Referencia de la API

> Base URL: `http://localhost:8000/api`
> Autenticación: `Authorization: Bearer <token>`

### Auth

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| `POST` | `/auth/registro` | ❌ | Registro + 20 pts bienvenida |
| `POST` | `/auth/login` | ❌ | Login → Sanctum token |
| `GET` | `/auth/me` | ✅ | Usuario autenticado |
| `POST` | `/auth/logout` | ✅ | Revoca el token actual |

### Restaurantes

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| `GET` | `/restaurantes` | ❌ | Lista con filtros `?categoria=&precio=&orden=` |
| `GET` | `/restaurantes/{id}` | ❌ | Detalle + valoraciones + cupones activos |
| `POST` | `/restaurantes/{id}/valorar` | ✅ | Puntúa 1-5 y gana +10 pts |

### Rutas gastronómicas

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| `GET` | `/rutas` | ❌ | Lista con contador de paradas |
| `GET` | `/rutas/{id}` | ❌ | Detalle + paradas ordenadas |
| `POST` | `/rutas/{id}/iniciar` | ✅ | Registra inicio (idempotente) |
| `POST` | `/rutas/{id}/completar` | ✅ | Completa y suma `puntos_reward` |
| `GET` | `/rutas/progreso` | ✅ | Estado de todas las rutas del usuario |

### Cupones

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| `GET` | `/cupones` | ✅ | Cupones disponibles (filtra Premium si no aplica) |
| `POST` | `/cupones/{id}/canjear` | ✅ | Canjea y descuenta puntos |
| `GET` | `/cupones/mis-cupones` | ✅ | Historial de canjes del usuario |

### Usuarios

| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| `GET` | `/usuarios/perfil` | ✅ | Perfil + historial + rutas completadas |
| `PUT` | `/usuarios/perfil` | ✅ | Actualiza nombre, apellidos o contraseña |
| `GET` | `/usuarios/ranking` | ✅ | Top 10 por puntos |
| `POST` | `/usuarios/suscripcion` | ✅ | Activa Premium mensual o anual |

---

## 🔐 Seguridad

- **Contraseñas**: bcrypt vía el cast `'password' => 'hashed'` de Laravel
- **Tokens**: Sanctum `personal_access_tokens`, revocados en logout
- **Validación**: Form Requests con mensajes en español y respuestas JSON 422
- **SQL Injection**: Imposible gracias a Eloquent + Query Builder con bindings
- **CORS**: Configurado en `config/cors.php` con `FRONTEND_URL` desde `.env`
- **Roles**: Middleware `CheckRole` y `RequirePremium` como guardas en las rutas
- **Excepciones**: `bootstrap/app.php` convierte todas las excepciones a JSON en rutas `/api/*`

---

## 🗄️ Diagrama de relaciones (Eloquent)

```
User ──────────────────┬──< ProgresoRuta >── Ruta ──< ruta_restaurantes >── Restaurante
                       ├──< CuponUsuario >── Cupon ───────────────────────── Restaurante
                       ├──< HistorialPuntos
                       ├──< Valoracion >──── Restaurante
                       └──< Suscripcion
```

---

## 🧩 Comandos Artisan útiles

```bash
# Ver todas las rutas de la API
php artisan route:list --path=api

# Limpiar caché en desarrollo
php artisan optimize:clear

# Abrir Tinker (REPL interactivo)
php artisan tinker

# Ejemplo en Tinker: dar 100 puntos a María
$u = \App\Models\User::where('email','maria@example.com')->first();
$u->sumarPuntos(100, 'Test desde Tinker');

# Resetear BD y recargar seeders
php artisan migrate:fresh --seed
```

---

## 🚀 Despliegue en producción

```bash
# Optimizar autoload y configuración
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Variables de entorno críticas en producción
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
FRONTEND_URL=https://tu-dominio.com
JWT_SECRET=cadena-muy-larga-y-aleatoria
```

---

## 📄 Licencia

Proyecto académico — Grado Superior de Desarrollo de Aplicaciones Web
© 2025 Carlos Cuevas Puga y Antonio Rico Guirado
#   P r o y e c t o _ F i n a l _ D a w  
 