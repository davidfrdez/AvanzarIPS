# 🔐 API Avanzar IPS — Documentación Oficial para Frontend (React)

**Stack:** Laravel 13 · PHP 8.3 · MySQL · Sanctum
**Base URL local:** `http://localhost:8000`
**Cumplimiento:** Ley 2015 (HCE inmodificable y trazable) + Ley 1581 (Protección de Datos Personales — Colombia).

---

## 📚 Documentación interactiva (auto-generada)

La API genera y sirve su propia documentación OpenAPI 3.1 a partir del código tipado (FormRequests, Resources, Enums, DTOs). **Se levanta automáticamente con `php artisan serve` — no requiere comandos extra ni anotaciones manuales.**

| URL | Herramienta | Descripción |
|-----|-------------|-------------|
| **`/docs`** | **Scalar** (recomendado) | UI moderna con dark mode, "Try It" en vivo, búsqueda y client SDK preview. |
| `/docs/api` | Stoplight Elements | UI clásica de Scramble. Funcional como fallback. |
| `/docs/api.json` | OpenAPI 3.1 spec | Spec crudo. Útil para importar en Postman/Insomnia o generar SDKs. |

> **Cómo probar endpoints protegidos desde Scalar:**
> 1. Hacer `POST /api/auth/login` desde la propia UI.
> 2. Copiar el `data.token` de la respuesta.
> 3. En Scalar, click en el candado superior y pegarlo como Bearer Token.
> 4. Probar cualquier endpoint protegido.

---

## 🚀 Quickstart

```bash
git clone https://github.com/davidfrdez/AvanzarIPS.git
cd AvanzarIPS
composer install
cp .env.example .env
php artisan key:generate
# Configura DB en .env (mysql / avanzar)
php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000
# Documentación interactiva: http://127.0.0.1:8000/docs
```

**Headers obligatorios** en todas las requests:
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer <token>          # solo en endpoints protegidos
```

---

## 🔑 Autenticación (Sanctum)

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| `POST` | `/api/auth/login` | Pública | Login. Rate-limit `5/min`. Devuelve `{token, user}`. |
| `POST` | `/api/auth/logout` | 🔒 Bearer | Revoca el token actual. |

### `POST /api/auth/login`
**Body:**
```json
{ "correo": "santiagodavid980@gmail.com", "password": "admin1234" }
```
**Response 200:**
```json
{
  "status": "success",
  "message": "Ingreso exitoso",
  "data": {
    "token": "1|abc...",
    "user": {
      "id": 1, "nombre": "David", "correo": "santiagodavid980@gmail.com",
      "esta_activo": true, "rol_id": 1,
      "rol": { "id": 1, "nombre": "Administrador" },
      "permisos": ["usuarios.ver","usuarios.crear",...],
      "especialidad_id": 1,
      "especialidad": { "id": 1, "nombre": "..." }
    }
  }
}
```

---

## 📧 Recuperación de Contraseña (OTP)

Flujo de 3 pasos. Código alfanumérico de **8 caracteres**, válido por **5 minutos**. Cada endpoint tiene rate-limit propio.

| Método | Ruta | Auth | Body |
|--------|------|------|------|
| `POST` | `/api/password/forgot`   | Pública (5/min) | `{correo}` |
| `POST` | `/api/password/validate` | Pública (10/min) | `{correo, code}` |
| `POST` | `/api/password/reset`    | Pública (5/min) | `{correo, code, password, password_confirmation}` |

---

## 👥 Usuarios y Catálogos

| Método | Ruta | Auth | Permiso requerido |
|--------|------|------|-------------------|
| `GET`    | `/api/roles`                       | Pública | — |
| `GET`    | `/api/especialidades`              | Pública | — |
| `GET`    | `/api/usuarios`                    | 🔒 | `usuarios.ver` |
| `POST`   | `/api/usuarios`                    | 🔒 | `usuarios.crear` |
| `GET`    | `/api/usuarios/{user}`             | 🔒 | `usuarios.ver` |
| `PUT`    | `/api/usuarios/{user}`             | 🔒 | `usuarios.editar` |
| `DELETE` | `/api/usuarios/{user}`             | 🔒 | `usuarios.editar` (soft delete) |
| `PUT`    | `/api/usuarios/{user}/desactivar`  | 🔒 | `usuarios.editar` |
| `PUT`    | `/api/usuarios/{user}/activar`     | 🔒 | `usuarios.editar` |
| `GET`    | `/api/medicos`                     | 🔒 | — |

> **Super-rol Administrador:** los usuarios con rol `Administrador` reciben automáticamente todos los permisos sin necesidad de asignación explícita.

### `POST /api/usuarios`
**Body:**
```json
{
  "nombre": "Profe Test",
  "correo": "prof@test.com",
  "rol_id": 2,
  "especialidad_id": 1,
  "password": "Profesor1"
}
```

### `PUT /api/usuarios/{user}`
**Body (todos los campos opcionales):**
```json
{ "nombre": "Nuevo Nombre", "rol_id": 3, "password": "Nueva1234" }
```

---

## 🏥 Pacientes

| Método | Ruta | Auth | Permiso |
|--------|------|------|---------|
| `GET`    | `/api/pacientes`                   | 🔒 | — |
| `POST`   | `/api/pacientes`                   | 🔒 | `pacientes.crear` |
| `GET`    | `/api/pacientes/{paciente}`        | 🔒 | — |
| `DELETE` | `/api/pacientes/{paciente}`        | 🔒 | — (soft delete) |
| `GET`    | `/api/pacientes/{id}/exportar-historia` | 🔒 | — (PDF) |

### `POST /api/pacientes`
Crea paciente y, opcionalmente, su Historia Clínica de Ingreso en una transacción atómica.

**Body:**
```json
{
  "tipo_documento": "CC",
  "cedula": "1020304050",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "fecha_nacimiento": "1990-05-15",
  "sexo": "M",
  "direccion": "Calle 123 # 45-67",
  "barrio": "Centro",
  "telefono": "3001234567",
  "correo": "juan@example.com",
  "ocupacion": "Ingeniero",
  "eps": "Sura",
  "regimen_salud": "Contributivo",
  "categoria_eps": "A",
  "nombre_responsable": "María Pérez",
  "telefono_responsable": "3009876543",
  "parentesco_responsable": "Madre",

  "ingreso": {
    "medico_id": 2,
    "motivo_consulta": "Dolor lumbar persistente",
    "anamnesis": "...",
    "ant_personales": "...",
    "impresion_diagnostica": "Lumbalgia mecánica",
    "plan_tratamiento": "10 sesiones de fisioterapia"
  }
}
```

**Validaciones:**
- `tipo_documento` ∈ `CC|TI|CE|RC|PA|PE` (Enum).
- `sexo` ∈ `M|F` (Enum).
- `cedula` única (ignorando soft-deleted).
- `fecha_nacimiento` no puede ser posterior a hoy.
- Bloque `ingreso` es opcional.

---

## 📅 Citas

| Método | Ruta | Auth |
|--------|------|------|
| `POST` | `/api/citas` | 🔒 |

**Body:**
```json
{
  "paciente_id": 1,
  "medico_id": 2,
  "especialidad_id": 2,
  "programada_para": "2026-05-20 10:00:00"
}
```

---

## 🌳 Árbol Clínico (Objetivos → Actividades → Respuestas)

CRUD completo del árbol jerárquico para el componente `ActivitiesManager` del frontend. **Eliminar un nodo padre con hijos retorna 409**.

### Objetivos
| Método | Ruta | Auth | Permiso |
|--------|------|------|---------|
| `GET`    | `/api/objetivos`             | 🔒 | — |
| `POST`   | `/api/objetivos`             | 🔒 | `objetivos.gestionar` |
| `GET`    | `/api/objetivos/{objetivo}`  | 🔒 | — |
| `PUT`    | `/api/objetivos/{objetivo}`  | 🔒 | `objetivos.gestionar` |
| `DELETE` | `/api/objetivos/{objetivo}`  | 🔒 | `objetivos.gestionar` |

### Actividades (rama)
| Método | Ruta | Auth | Permiso |
|--------|------|------|---------|
| `POST`   | `/api/actividades`               | 🔒 | `objetivos.gestionar` |
| `PUT`    | `/api/actividades/{actividad}`   | 🔒 | `objetivos.gestionar` |
| `DELETE` | `/api/actividades/{actividad}`   | 🔒 | `objetivos.gestionar` |

### Respuestas (hoja)
| Método | Ruta | Auth | Permiso |
|--------|------|------|---------|
| `POST`   | `/api/respuestas`             | 🔒 | `objetivos.gestionar` |
| `PUT`    | `/api/respuestas/{respuesta}` | 🔒 | `objetivos.gestionar` |
| `DELETE` | `/api/respuestas/{respuesta}` | 🔒 | `objetivos.gestionar` |

### Ejemplos
```json
// POST /api/objetivos
{ "nombre": "Mejorar Movilidad", "descripcion": "Aumentar rango de movimiento" }

// POST /api/actividades
{ "objetivo_id": 1, "nombre": "Estiramiento pasivo" }

// POST /api/respuestas
{ "actividad_id": 1, "texto_predeterminado": "Logro completado sin dolor" }
```

`GET /api/objetivos` retorna el árbol completo:
```json
{
  "data": [
    {
      "id": 1, "nombre": "Mejorar Movilidad",
      "actividades": [
        { "id": 1, "nombre": "Estiramiento pasivo",
          "respuestas": [ { "id": 1, "texto_predeterminado": "..." } ] }
      ]
    }
  ]
}
```

---

## ⚕️ Terapias (Evolución diaria)

| Método | Ruta | Auth | Permiso |
|--------|------|------|---------|
| `GET`  | `/api/terapias` | 🔒 | — |
| `POST` | `/api/terapias` | 🔒 | `terapias.registrar` |

`POST` registra una evolución clínica. **Bloquea duplicados del mismo paciente en el mismo día**. La firma se cifra con AES-256 vía cast `encrypted` (no exponer en respuestas).

```json
{
  "paciente_id": 1,
  "objetivo_id": 1,
  "actividad_id": 1,
  "especialidad_id": 2,
  "firma_electronica": "Firma del Dr. Daniel",
  "resultados": [
    { "respuesta_id": 1, "marcado": true, "notas_libres": "Mejora notable" }
  ]
}
```

---

## 📋 Formularios Clínicos

Todos requieren autenticación. Los modelos correspondientes auditan create/update/delete y tienen `SoftDeletes`.

| Método | Ruta | Notas |
|--------|------|-------|
| `GET`  `POST` | `/api/historias-ingreso`        | Anamnesis y antecedentes (índice en `paciente_id`). |
| `GET`  `POST` | `/api/consentimientos`          | Estado: `Pendiente / Firmado / Rechazado`. Firmante cifrado. |
| `GET`  `POST` | `/api/ordenes-medicas`          | Remisiones. |
| `GET`  `POST` | `/api/consultas-especialistas`  | Diagnóstico, escala EEAG, firma cifrada. |
| `GET`  `POST` | `/api/escalas-weefim`           | El backend calcula `puntaje_total` y `porcentaje_funcionalidad`. |

### `POST /api/escalas-weefim`
```json
{
  "paciente_id": 1,
  "fecha_evaluacion": "2026-05-20",
  "subtotal_autocuidado": 40,
  "subtotal_movilidad": 30,
  "subtotal_cognicion": 35
}
```

### `POST /api/consentimientos`
```json
{
  "paciente_id": 1,
  "tipo_consentimiento": "Tratamiento",
  "estado": "Firmado",
  "firmado_por_representante": false,
  "nombre_firmante": "Juan Pérez",
  "documento_firmante": "1020304050",
  "fecha_firma": "2026-05-20"
}
```

### `POST /api/consultas-especialistas`
```json
{
  "paciente_id": 1,
  "especialidad_id": 3,
  "motivo_consulta": "...",
  "examen_mental": "...",
  "diagnostico": "F32.0",
  "concepto": "...",
  "escala_eeag": "75",
  "firma_electronica": "..."
}
```

---

## 📊 Dashboard y Auditoría

| Método | Ruta | Auth |
|--------|------|------|
| `GET` | `/api/dashboard/metrics` | 🔒 (Admin) |
| `GET` | `/api/auditoria`         | 🔒 (Admin) |

`GET /api/auditoria` devuelve el registro **append-only** de cambios del sistema. Cada entrada incluye `usuario_id`, `accion`, `nombre_tabla`, `registro_id`, `ip`, `user_agent` y `created_at`. **Cualquier intento de UPDATE/DELETE sobre `auditoria_cambios` lanza excepción** (Ley 2015).

---

## 📄 Exportación PDF

| Método | Ruta | Auth |
|--------|------|------|
| `GET` | `/api/pacientes/{id}/exportar-historia` | 🔒 |

Descarga el PDF consolidado de la historia clínica del paciente.

---

## 🔐 RBAC (Permisos por Rol)

| Rol | Permisos |
|-----|----------|
| **Administrador** | (todos, super-rol) |
| **Coordinador** | `dashboards.ver`, `historial.ver`, `pdf.aprobar`, `pdf.masivo`, `datos.exportar` |
| **Medico** | `agenda.ver`, `pacientes.buscar`, `pacientes.crear`, `terapias.registrar`, `terapias.firmar`, `terapias.notas_libres` |

Ver `database/seeders/PermisosSeeder.php` y `PermisoRolSeeder.php` para el catálogo completo.

---

## ⚠️ Códigos de respuesta esperables

| Código | Cuándo |
|--------|--------|
| `200`  | OK (lectura, update, delete) |
| `201`  | Recurso creado |
| `401`  | Sin token o token inválido |
| `403`  | Token válido pero sin permiso (RBAC) |
| `404`  | Recurso no encontrado |
| `409`  | Conflicto (ej. eliminar nodo del árbol con hijos) |
| `422`  | Validación fallida / error de negocio (ej. terapia duplicada el mismo día) |
| `429`  | Rate limit excedido (login y password reset) |

---

## 🗄️ Setup de BD para desarrollo

```bash
php artisan migrate:fresh --seed   # crea tablas + seeders (admin, médico, objetivos)
```

Credenciales de prueba (de `UsuariosSeeder`):
- **Admin:** `santiagodavid980@gmail.com` / `admin1234`
- **Médico:** `fepiperuiz11@gmail.com` / `admin1234`

---

## 🧪 Tests rápidos con curl

```bash
# Login
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"correo":"santiagodavid980@gmail.com","password":"admin1234"}' \
  | php -r 'echo json_decode(file_get_contents("php://stdin"))->data->token;')

# Listar pacientes
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/pacientes

# Crear objetivo
curl -X POST http://127.0.0.1:8000/api/objetivos \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"nombre":"Test","descripcion":"Probando"}'
```

---

## 📌 Roadmap pendiente
Ver [ReadmeTareas.md](ReadmeTareas.md) para los items priorizados restantes (M3 PDFs masivos, M9 carga masiva, M10 reportes ZIP, etc.).
