# APK-TV — Plataforma OTT con Laravel

Backend completo de una plataforma OTT (tipo Netflix/IPTV) construida con **PHP 8.x + Laravel 13 + Sanctum**.

---

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/           ← Endpoints públicos/autenticados
│   │   └── Admin/         ← Panel de administración
│   └── Middleware/
│       ├── AdminRole.php         ← Control de roles (superadmin/soporte/auditor)
│       ├── ParentalControl.php   ← Filtrado de contenido adulto por perfil
│       └── CheckUserStatus.php   ← Bloquea usuarios suspendidos
├── Models/                ← Eloquent ORM (10 modelos con relaciones)
└── Services/
    ├── M3uParserService.php  ← Parser de listas M3U
    ├── XtreamService.php     ← Integración Xtream Codes
    ├── EpgService.php        ← Descarga y parseo de XMLTV
    └── TokenService.php      ← Gestión de tokens por dispositivo

database/
└── migrations/            ← 15 migraciones completas

routes/
└── api.php                ← 42 rutas organizadas por módulo
```

---

## 🚀 Instalación

### Requisitos
- PHP >= 8.2
- Composer
- MySQL 8+ (o PostgreSQL cambiando `DB_CONNECTION`)
- Redis (opcional, recomendado para producción)

### Pasos

```bash
# 1. Instalar dependencias
composer install

# 2. Copiar y configurar variables de entorno
cp .env.example .env

# Editar .env con tus datos de base de datos:
# DB_DATABASE=apktv_db
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña

# Opcional: definir credenciales admin antes del seed
# ADMIN_EMAIL=admin@tudominio.com
# ADMIN_PASSWORD=TuPasswordSeguro123!

# 3. Generar clave de aplicación
php artisan key:generate

# 4. Crear base de datos y migrar tablas
php artisan migrate

# 5. Crear el primer administrador del sistema
php artisan db:seed --class=AdminSeeder

# 6. Iniciar servidor de desarrollo
php artisan serve
```

---

## 🔑 Endpoints Principales

### Autenticación
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/login` | Login (5 intentos/min) |
| POST | `/api/logout` | Cerrar sesión actual |

### Perfiles (requiere token)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/profiles` | Listar perfiles |
| POST | `/api/profiles` | Crear perfil (max 5) |
| POST | `/api/profiles/{id}/verify-pin` | Verificar PIN |

### Dispositivos (requiere token)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/devices` | Mis dispositivos conectados |
| DELETE | `/api/devices/{id}` | Cerrar sesión en dispositivo |
| DELETE | `/api/devices` | Cerrar todas las sesiones |

### IPTV — Canales (requiere token + header `Profile-Id`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/channels/groups` | Grupos de canales (filtrado parental automático) |
| GET | `/api/channels/groups/{id}` | Canales de un grupo |
| GET | `/api/channels/{id}/epg` | EPG: Ahora y Siguiente |
| GET | `/api/playlists` | Listas cargadas |
| POST | `/api/playlists/{id}/sync` | Sincronizar lista M3U o Xtream |

### Favoritos (requiere token + header `Profile-Id`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/favorites` | Mis favoritos |
| POST | `/api/favorites/toggle` | Agregar/quitar favorito |

### Admin (requiere token de admin con rol)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/admin/dashboard` | Estadísticas globales |
| GET | `/api/admin/users` | Listar usuarios (búsqueda + filtros) |
| POST | `/api/admin/users` | Crear usuario |
| PATCH | `/api/admin/users/{id}/status` | Activar/Suspender |
| DELETE | `/api/admin/users/{id}/sessions` | Cerrar sesiones de un usuario |
| GET | `/api/admin/devices` | Todos los dispositivos |
| POST | `/api/admin/devices/{id}/close` | Cerrar sesión de dispositivo |
| POST | `/api/admin/devices/{id}/block` | Bloquear dispositivo |
| GET | `/api/admin/security/alerts` | Alertas de seguridad |
| GET | `/api/admin/security/suspicious-ips` | IPs sospechosas |
| GET | `/api/admin/security/multi-country` | Usuarios multi-país |
| GET | `/api/admin/audit` | Logs de auditoría |
| GET | `/api/admin/config` | Configuración global |
| PATCH | `/api/admin/config` | Actualizar configuración |

---

## 🔒 Seguridad Implementada

- **Tokens por dispositivo** (Sanctum) — revocación granular
- **Rate limiting** en login: 5 intentos / minuto
- **Bcrypt** (12 rondas) para contraseñas y PINs
- **Middleware CheckUserStatus** — bloquea suspendidos en cada request
- **Middleware AdminRole** — protege rutas admin por rol
- **Control parental automático** — filtrado en servidor según perfil
- **GeoIP** — detección de país/ciudad en cada dispositivo
- **Validación estricta** en todos los endpoints
- **No se exponen credenciales** en el código fuente
- Credenciales admin desde variables de entorno (`.env`)

---

## 📋 Variables de Entorno Clave (.env)

```env
# Base de datos
DB_CONNECTION=mysql
DB_DATABASE=apktv_db
DB_USERNAME=root
DB_PASSWORD=

# Admin inicial (antes de correr el seeder)
ADMIN_EMAIL=admin@tudominio.com
ADMIN_PASSWORD=TuPasswordSeguro!

# Logs de seguridad
SECURITY_LOG=true
```

---

## 🔜 Próximas Fases

- [ ] Registro de usuarios con plan de pago (código ya comentado en `AuthController`)
- [ ] Panel web de administración (Blade/Vue)
- [ ] Notificaciones en tiempo real (Node.js / WebSockets)
- [ ] App Android/iOS como cliente de la API

---

## 📄 Licencia

Proyecto privado. Todos los derechos reservados.
