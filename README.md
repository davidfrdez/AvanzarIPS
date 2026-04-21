# 🔐 API Avanzar IPS — Autenticación y Clínica

**Proyecto:** Desarrollo Colombia | **Stack:** Laravel 11 + React  
**Base URL:** `http://localhost:8000`

---

## 📌 Introducción

Esta API gestiona el ciclo completo de vida de la sesión del usuario, la recuperación de credenciales y el flujo principal del **Módulo Clínico** (Pacientes, Médicos, Especialidades y Agendamiento de Citas). Se utiliza una arquitectura relacional normalizada y el campo `correo` como identificador único para los usuarios.

---

## 🔑 Módulo de Autenticación

### 1. Iniciar Sesión (Login)

`POST /api/login`

Valida las credenciales y retorna un token de acceso (Sanctum).

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com",
  "password": "mi_password_segura"
}
```

**Response `200 OK`:**
```json
{
  "status": "success",
  "token": "1|abc123tokengenerado...",
  "usuario": {
    "nombre": "David",
    "correo": "santiagodavid980@gmail.com"
  }
}
```

---

### 2. Cerrar Sesión (Logout)

`POST /api/logout` 🔒 *Requiere autenticación*

Revoca el token actual del usuario.

**Headers requeridos:**
```
Authorization: Bearer {tu_token_aqui}
Accept: application/json
```

**Response `200 OK`:**
```json
{
  "status": "success",
  "message": "Sesión cerrada correctamente"
}
```

---

## 📧 Módulo de Recuperación OTP

Flujo de seguridad con códigos de **6 dígitos** con validez de **5 minutos**.

### 3. Solicitar Código

`POST /api/password/forgot`

Genera un OTP y lo envía vía **Hostinger SMTP** (`support@dalioss.com`).

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com"
}
```

---

### 4. Validar Código

`POST /api/password/validate`

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com",
  "code": "123456"
}
```

---

### 5. Restablecer Contraseña

`POST /api/password/reset`

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com",
  "code": "123456",
  "password": "NuevaPassword123",
  "password_confirmation": "NuevaPassword123"
}
```

---

## 👤 Módulo de Usuarios

### 6. Obtener Roles

`GET /api/roles` 🔒 *Requiere autenticación*

Retorna el listado de roles disponibles en el sistema (ideal para formularios de registro o gestión de usuarios en React).

```php
// routes/api.php
Route::get('/roles', [UserController::class, 'Roles']);
```

**Response `200 OK`:**
```json
[
  { "id": 1, "nombre": "Administrador" },
  { "id": 2, "nombre": "Médico" },
  { "id": 3, "nombre": "Paciente" }
]
```

---

## 🏥 Módulo Clínico (Pacientes, Citas y Especialidades)

> Todas las rutas de este módulo están protegidas. **Incluir siempre el header `Authorization: Bearer {token}`.**

### 7. Registrar Paciente

`POST /api/pacientes` 🔒 *Requiere autenticación*

Crea un nuevo paciente vinculado a su cédula, nombre completo y EPS.

**Body JSON:**
```json
{
  "cedula": "1020304050",
  "nombre": "Juan Pérez",
  "eps": "Sura"
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Paciente registrado correctamente",
  "paciente": {
    "id": 1,
    "cedula": "1020304050",
    "nombre": "Juan Pérez",
    "eps": "Sura"
  }
}
```

---

### 8. Obtener Especialidades

`GET /api/especialidades` 🔒 *Requiere autenticación*

Retorna el catálogo de especialidades disponibles (ideal para llenar los `<select>` en React).

```php
// routes/api.php
Route::get('/especialidades', [UserController::class, 'Especialidades']);
```

**Response `200 OK`:**
```json
[
  { "id": 1, "nombre": "Sin especificar" },
  { "id": 2, "nombre": "Fisioterapia" },
  { "id": 3, "nombre": "Fonoaudiología" }
]
```

---

### 9. Programar Cita

`POST /api/citas` 🔒 *Requiere autenticación*

Agenda una nueva cita vinculando al paciente, el médico y la especialidad requerida.

**Body JSON:**
```json
{
  "paciente_id": 1,
  "medico_id": 2,
  "especialidad_id": 2,
  "programada_para": "2026-05-20 10:00:00"
}
```

**Response `201 Created`:**
```json
{
  "status": "success",
  "message": "Cita programada correctamente",
  "cita": {
    "id": 1,
    "programada_para": "2026-05-20 10:00:00"
  }
}
```

---

## 🛠️ Manejo de Errores

| Código | Mensaje            | Causa / Solución |
|--------|--------------------|------------------|
| `401`  | Unauthorized       | Credenciales de login incorrectas o falta de Token en cabeceras. |
| `422`  | Validation Error   | Campos faltantes, código OTP expirado, o IDs inexistentes (ej. `especialidad_id` no válido). |
| `500`  | Server Error       | Fallo de autenticación SMTP con Hostinger o error de base de datos. |

---

## 💡 Tips para React

- Para todas las rutas protegidas (Logout, Pacientes, Citas, Especialidades, Roles), incluir siempre el header `Authorization: Bearer {token}` recibido en el Login.
- Enviar `especialidad_id`, `paciente_id` y `medico_id` siempre como **número entero**, no como string.
- La `cedula` del paciente se envía como **string** para preservar ceros a la izquierda si aplica.

---

*Documentación técnica para uso interno — David Fernández © 2026*