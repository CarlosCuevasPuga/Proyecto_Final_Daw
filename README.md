# 🍽️ Passport Pal Motril (PAPM)

Aplicación web de turismo gastronómico para la ciudad de **Motril (Granada)**. Permite a los usuarios descubrir restaurantes y establecimientos locales a través de rutas temáticas, acumular puntos visitándolos y canjear recompensas en forma de cupones de descuento.

---

## ✨ Funcionalidades principales

- **Rutas gastronómicas** — Recorre itinerarios temáticos que agrupan varios restaurantes (ej. *Ruta del Pescador*, *Ruta Dulce Motril*).
- **Mapa interactivo** — Visualiza restaurantes y rutas sobre OpenStreetMap con navegación paso a paso gracias a Leaflet.js.
- **Sistema de puntos y recompensas** — Los usuarios acumulan puntos al completar rutas y los canjean por cupones de descuento.
- **Suscripción Premium** — Rutas exclusivas y contenido especial para usuarios con cuenta premium.
- **Panel de administración** — Gestión de usuarios, restaurantes, rutas y cupones reservada a administradores.
- **Perfil de usuario** — Historial de rutas completadas, puntos disponibles y cupones canjeados.
- **Widget del tiempo** — Muestra el clima actual en Motril mediante la API gratuita de Open-Meteo.
- **Diseño Mobile-First** — Interfaz adaptada a dispositivos móviles con navegación inferior.

---

## 🛠️ Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (Vanilla) |
| Backend | PHP 8+ |
| Base de datos | MySQL |
| Mapas | Leaflet.js + OpenStreetMap (gratuito) |
| Routing en mapa | Leaflet Routing Machine |
| Clima | [Open-Meteo API](https://open-meteo.com/) (gratuito, sin clave) |
| Iconos | Boxicons |
| Tipografía | Google Fonts – Outfit |

---

## 📁 Estructura del proyecto

```
Proyecto_Final_Daw/
├── index.html                  # Página principal (rutas y cupones)
├── mapa.html                   # Mapa interactivo de restaurantes
├── cupones.html                # Catálogo de cupones disponibles
├── cupones_usuario.html        # Cupones canjeados por el usuario
├── perfil_usuario.html         # Perfil y estadísticas del usuario
├── premium.html                # Página de suscripción premium
├── suscripcion_futura.html     # Próximas funcionalidades premium
├── admin.html                  # Panel de administración
│
├── api/
│   ├── config/
│   │   └── db.php              # Configuración de conexión a MySQL
│   ├── auth.php                # Endpoints de login y registro
│   ├── routes.php              # Endpoints de rutas y restaurantes
│   └── user.php                # Endpoints de perfil, puntos y cupones
│
├── css/
│   └── style.css               # Estilos globales
│
├── js/
│   ├── app.js                  # Lógica principal (auth, sesión, UI)
│   ├── mapa.js                 # Mapa, marcadores y navegación
│   ├── admin.js                # Lógica del panel de administración
│   ├── perfil_usuario.js       # Lógica del perfil
│   └── weather.js              # Widget del tiempo
│
├── database/
│   └── schema.sql              # Esquema SQL con datos de prueba
│
└── img/
    └── logo.png
```

---

## 🗄️ Base de datos

La base de datos se llama `papm_db` y contiene las siguientes tablas:

- `users` — Usuarios registrados (puntos, premium, admin).
- `restaurants` — Establecimientos con nombre, dirección, coordenadas y categoría.
- `routes` — Rutas gastronómicas (puntos de recompensa, duración, acceso premium).
- `route_restaurants` — Relación entre rutas y restaurantes (con orden de visita).
- `coupons` — Cupones canjeables con su coste en puntos y código de descuento.
- `user_coupons` — Registro de cupones canjeados por cada usuario.
- `user_completed_routes` — Historial de rutas completadas.
- `user_active_routes` — Rutas en progreso del usuario.

El archivo `database/schema.sql` incluye la creación de todas las tablas y datos de prueba (10 restaurantes, 2 rutas y 2 cupones de ejemplo).

---

## 🚀 Instalación y puesta en marcha

### Requisitos previos

- PHP 8.0 o superior
- MySQL 5.7 / MariaDB 10.3 o superior
- Servidor web local: [XAMPP](https://www.apachefriends.org/), [WAMP](https://www.wampserver.com/) o similar

### Pasos

1. **Clonar o descomprimir** el proyecto en la carpeta `htdocs` (XAMPP) o `www` (WAMP):
   ```
   htdocs/Proyecto_Final_Daw/
   ```

2. **Importar la base de datos** desde phpMyAdmin o la terminal:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. **Revisar la configuración de la BD** en `api/config/db.php` y ajustar si es necesario:
   ```php
   $host     = '127.0.0.1';
   $db_name  = 'papm_db';
   $username = 'root';
   $password = '';
   ```

4. **Iniciar el servidor** (Apache + MySQL en XAMPP) y acceder en el navegador:
   ```
   http://localhost/Proyecto_Final_Daw/
   ```

---

## 🔌 API REST (PHP)

Todos los endpoints se invocan con parámetros GET (`?action=...`) y cuerpos JSON en POST.

| Archivo | Acción | Método | Descripción |
|---|---|---|---|
| `auth.php` | `login` | POST | Autenticación de usuario |
| `auth.php` | `register` | POST | Registro de nuevo usuario |
| `routes.php` | `list` | GET | Lista rutas con sus restaurantes |
| `routes.php` | `restaurants` | GET | Lista todos los restaurantes |
| `routes.php` | `save` | POST | Crear o actualizar ruta (admin) |
| `user.php` | *(varios)* | GET/POST | Perfil, puntos, cupones, rutas activas |

---

## 👤 Roles de usuario

| Rol | Acceso |
|---|---|
| **Visitante** | Puede ver rutas y restaurantes en el mapa |
| **Usuario registrado** | Inicia rutas, acumula puntos y canjea cupones |
| **Usuario Premium** | Accede a rutas exclusivas marcadas como premium |
| **Administrador** | Panel de gestión de usuarios, rutas, restaurantes y cupones |

---

## 📦 Dependencias externas (CDN, sin instalación)

- [Leaflet.js](https://leafletjs.com/) — Mapas interactivos
- [Leaflet Routing Machine](https://www.liedman.net/leaflet-routing-machine/) — Navegación en mapa
- [Boxicons](https://boxicons.com/) — Iconografía
- [Google Fonts – Outfit](https://fonts.google.com/specimen/Outfit) — Tipografía
- [Open-Meteo](https://open-meteo.com/) — API de clima (sin registro ni clave de API)

---

## 📝 Notas de desarrollo

- La sesión se gestiona mediante **localStorage** (`papm_user`), por lo que no requiere cookies ni sesiones PHP.
- Las contraseñas se almacenan con `password_hash()` de PHP (bcrypt).
- El mapa usa coordenadas reales de Motril y geolocalización del navegador para centrar la vista en el usuario.
- El panel de administración (`admin.html`) es accesible solo si el campo `is_admin = 1` en la base de datos.

---

## 📄 Licencia

Proyecto académico desarrollado como **Proyecto Final del Ciclo Superior de DAW** (Desarrollo de Aplicaciones Web).
