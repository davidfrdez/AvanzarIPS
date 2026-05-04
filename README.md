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
| `POST`   | `/api/especialidades`              | 🔒 | `especialidades.gestionar` |
| `GET`    | `/api/especialidades/{id}`         | 🔒 | — |
| `PUT`    | `/api/especialidades/{id}`         | 🔒 | `especialidades.gestionar` |
| `DELETE` | `/api/especialidades/{id}`         | 🔒 | `especialidades.gestionar` |
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
| `GET`    | `/api/pacientes`                            | 🔒 | — (`?estado=activos\|inactivos\|todos`) |
| `POST`   | `/api/pacientes`                            | 🔒 | `pacientes.crear` |
| `GET`    | `/api/pacientes/{paciente}`                 | 🔒 | — |
| `GET`    | `/api/pacientes/{paciente}/balance-horas`   | 🔒 | — (`?mes=YYYY-MM`) |
| `PUT`    | `/api/pacientes/{paciente}/alta`            | 🔒 | `pacientes.gestionar` — dar de alta (baja clínica) |
| `PUT`    | `/api/pacientes/{paciente}/reactivar`       | 🔒 | `pacientes.gestionar` — reactivar |
| `DELETE` | `/api/pacientes/{paciente}`                 | 🔒 | `pacientes.gestionar` — desactiva (nunca borra) |
| `GET`    | `/api/pacientes/{id}/exportar-historia`     | 🔒 | — (PDF) |
| `GET`    | `/api/pacientes/plantilla-excel`            | 🔒 | `pacientes.crear` |
| `POST`   | `/api/pacientes/importar-excel`             | 🔒 | `pacientes.crear` |

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

### Balance de horas por mes

#### `GET /api/pacientes/{id}/balance-horas`
Devuelve el cupo mensual del paciente: citas programadas vs. terapias ejecutadas.

| Parámetro | Descripción |
|-----------|-------------|
| `?mes=YYYY-MM` | Mes a consultar (default: mes actual) |

```bash
curl http://127.0.0.1:8000/api/pacientes/5/balance-horas?mes=2026-05 \
  -H "Authorization: Bearer <token>"
```

**Respuesta 200:**
```json
{
  "status": "success",
  "data": {
    "paciente_id": 5,
    "mes": "2026-05",
    "horas_programadas": 20,
    "horas_ejecutadas": 14,
    "horas_disponibles": 6,
    "puede_registrar": true
  }
}
```

> **Regla de cupo:** `POST /api/terapias` bloquea con **422** si `horas_ejecutadas >= horas_programadas` en el mes de la `fecha_hora` enviada. El mensaje incluye las cifras para que el frontend las muestre.

---

### Alta y reactivación de pacientes

> **Los pacientes nunca se eliminan.** Por integridad clínica, tanto `DELETE /pacientes/{id}` como `PUT /pacientes/{id}/alta` simplemente marcan `esta_activo = false`. El historial clínico completo queda siempre consultable y el PDF descargable.

#### `PUT /api/pacientes/{id}/alta` — Dar de alta (desactivar)
Marca el paciente como inactivo. Úsalo cuando un paciente concluye su tratamiento o deja de asistir.

```bash
curl -X PUT http://127.0.0.1:8000/api/pacientes/5/alta \
  -H "Authorization: Bearer <token>"
```

**Respuesta 200:**
```json
{
  "data": {
    "id": 5,
    "nombres": "Juan",
    "esta_activo": false,
    ...
  }
}
```

#### `PUT /api/pacientes/{id}/reactivar` — Reactivar
Vuelve a marcar el paciente como activo (p. ej., retoma tratamiento tras una pausa larga).

```bash
curl -X PUT http://127.0.0.1:8000/api/pacientes/5/reactivar \
  -H "Authorization: Bearer <token>"
```

#### `GET /api/pacientes?estado=` — Filtro de estado

| Parámetro | Resultado |
|-----------|-----------|
| `?estado=activos` | Solo pacientes en tratamiento activo **(default)** |
| `?estado=inactivos` | Solo pacientes dados de alta |
| `?estado=todos` | Todos los pacientes sin filtrar |

Ejemplo — listar dados de alta:
```bash
curl http://127.0.0.1:8000/api/pacientes?estado=inactivos \
  -H "Authorization: Bearer <token>"
```

**Permiso requerido:** `pacientes.gestionar` (alta y reactivar). El listado no requiere permiso adicional.

---

### 📥 Cargas masivas por Excel

> **Catálogo de cargas:** `GET /api/cargas-masivas` devuelve la lista de tipos disponibles (con su URL de plantilla, URL de import y flag `disponible`). El frontend debe consumir este endpoint y renderizar la pantalla dinámicamente, **sin hardcodear rutas**.

| Tipo | Plantilla | Import | Estado |
|------|-----------|--------|--------|
| **Pacientes** | `GET /api/pacientes/plantilla-excel` | `POST /api/pacientes/importar-excel` | ✅ Disponible |
| **Citas** (ejemplo) | `GET /api/cargas-masivas/citas/plantilla` | _(no implementado)_ | 🟡 Solo plantilla |
| **Usuarios** (ejemplo) | `GET /api/cargas-masivas/usuarios/plantilla` | _(no implementado)_ | 🟡 Solo plantilla |

Las plantillas de **citas** y **usuarios** existen para que el equipo y el frontend puedan diseñar contra un contrato concreto cuando se prioricen esos imports. Hoy solo descargan el xlsx con encabezados + 1 fila de ejemplo.

**Auditoría:** cada import masivo escribe un registro en `auditoria_cambios` con `accion='CARGA_MASIVA'`, además de los registros individuales que cada modelo genera vía el trait `Auditable`. El registro de batch incluye nombre de archivo, total procesado, total insertado, total con errores y la lista de cédulas creadas.

#### Pacientes (flujo completo)

Permiso requerido: `pacientes.crear`.

#### `GET /api/pacientes/plantilla-excel`
Descarga `plantilla_pacientes.xlsx`. Trae dos hojas:

- **Pacientes** — encabezados oficiales + una fila de ejemplo.
- **Catálogos** — valores válidos para `tipo_documento` (CC, TI, CE, RC, PA, PE) y `sexo` (M, F).

> En el frontend usar `responseType: 'blob'` (axios) o `fetch().blob()` y disparar la descarga con un `<a download>`.

#### `POST /api/pacientes/importar-excel`
**Content-Type:** `multipart/form-data`
**Campo:** `archivo` (xlsx/xls, máx. 5 MB)

**Reglas:**
- **Tope:** 500 filas por archivo (las adicionales se ignoran y `data.excedio_limite = true`).
- **Best-effort:** las filas válidas se insertan; las inválidas se devuelven con detalle.
- Cada paciente se crea en su propia transacción → un fallo no aborta el lote.
- Reusa exactamente las mismas reglas de `POST /api/pacientes` (cédula única, enums, fecha ≤ hoy, etc.).
- **Filas vacías** se ignoran (no cuentan como error).

**Códigos de respuesta:**
| Código | Cuándo |
|--------|--------|
| `201` | Todas las filas insertadas, cero errores |
| `207` | Éxito parcial — hubo filas válidas e inválidas |
| `403` | Falta permiso `pacientes.crear` |
| `422` | El archivo no es xlsx/xls válido o pesa más de 5 MB |

**Response 207 (ejemplo):**
```json
{
  "status": "success",
  "message": "2 pacientes importados, 1 con errores.",
  "data": {
    "total_filas_procesadas": 3,
    "total_insertadas": 2,
    "insertadas": [
      { "fila": 2, "cedula": "1000000001" },
      { "fila": 3, "cedula": "1000000002" }
    ],
    "total_errores": 1,
    "errores": [
      {
        "fila": 4,
        "cedula": "1000000001",
        "errores": {
          "cedula": ["Ya existe un paciente registrado con este número de documento."]
        }
      }
    ],
    "limite_filas": 500,
    "excedio_limite": false
  }
}
```

**Cómo probar manualmente (curl):**
```bash
# 1) Descarga la plantilla
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/pacientes/plantilla-excel \
  -o plantilla_pacientes.xlsx

# 2) Llena la hoja "Pacientes" con tus filas y súbela
curl -X POST http://127.0.0.1:8000/api/pacientes/importar-excel \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "archivo=@plantilla_pacientes.xlsx"
```

**Cómo probar desde Scalar (`/docs`):**
1. Login → copia el token en el candado superior.
2. Endpoint **GET `/api/pacientes/plantilla-excel`** → "Try It" → descarga el xlsx.
3. Llénalo localmente (respeta los enums de la hoja "Catálogos").
4. Endpoint **POST `/api/pacientes/importar-excel`** → en el body multipart, selecciona el archivo en el campo `archivo` → "Send".

**Cómo consumirlo desde el frontend (axios):**
```js
// Descargar plantilla
const blob = await axios.get('/api/pacientes/plantilla-excel', {
  responseType: 'blob',
  headers: { Authorization: `Bearer ${token}` },
});
const url = URL.createObjectURL(blob.data);
Object.assign(document.createElement('a'), { href: url, download: 'plantilla_pacientes.xlsx' }).click();

// Importar Excel
const fd = new FormData();
fd.append('archivo', file); // file = input[type=file].files[0]
const { data } = await axios.post('/api/pacientes/importar-excel', fd, {
  headers: { Authorization: `Bearer ${token}` }, // NO seteen Content-Type, axios lo arma con boundary
});
console.log(data.data.total_insertadas, data.data.errores);
```

#### Catálogo y plantillas ejemplo

##### `GET /api/cargas-masivas`
Lista los tipos de carga masiva disponibles. Diseñado para que el frontend renderice la pantalla "Cargas masivas" sin hardcodear nada.

**Response 200:**
```json
{
  "status": "success",
  "data": [
    {
      "key": "pacientes",
      "nombre": "Pacientes",
      "descripcion": "Carga masiva de pacientes en la tabla `pacientes`. Tope 500 filas.",
      "plantilla_url": "/api/pacientes/plantilla-excel",
      "import_url": "/api/pacientes/importar-excel",
      "disponible": true,
      "permiso": "pacientes.crear",
      "tope_filas": 500
    },
    {
      "key": "citas",
      "nombre": "Citas (ejemplo, no implementado)",
      "descripcion": "Plantilla ejemplo para futura carga masiva de citas. Solo descarga; el import aún no está disponible.",
      "plantilla_url": "/api/cargas-masivas/citas/plantilla",
      "import_url": null,
      "disponible": false,
      "permiso": "pacientes.crear",
      "tope_filas": null
    },
    {
      "key": "usuarios",
      "nombre": "Usuarios (ejemplo, no implementado)",
      "descripcion": "Plantilla ejemplo para futura carga masiva de personal/médicos. Solo descarga; el import aún no está disponible.",
      "plantilla_url": "/api/cargas-masivas/usuarios/plantilla",
      "import_url": null,
      "disponible": false,
      "permiso": "usuarios.crear",
      "tope_filas": null
    }
  ]
}
```

##### `GET /api/cargas-masivas/citas/plantilla`
Descarga `plantilla_citas.xlsx` con columnas: `paciente_id`, `medico_id`, `especialidad_id`, `programada_para` (formato `YYYY-MM-DD HH:MM:SS`).

##### `GET /api/cargas-masivas/usuarios/plantilla`
Descarga `plantilla_usuarios.xlsx` con columnas: `nombre`, `correo`, `rol_id`, `especialidad_id`, `esta_activo`.
> El `password` **no** va en la plantilla por seguridad. Cuando se implemente el import, se generará un temporal y se forzará reset al primer login.

#### Cómo añadir una nueva carga masiva (guía rápida)

1. Crear `app/Exports/PlantillaXxxxExport.php` (sigue el patrón de `PlantillaCitasExport`).
2. Crear `app/Imports/XxxxImport.php` con las reglas reusando el `FormRequest` existente del create individual.
3. Crear `XxxxImportController` con `plantilla()` + `import()`. Reusa el patrón de `PacienteImportController`.
4. Registrar las dos rutas en `routes/api.php` **antes** de cualquier ruta con `{xxxx}` parametrizada.
5. Añadir la entrada al array de `CargasMasivasController::index()` con `disponible: true`.
6. Añadir registro de auditoría con `accion: AccionAuditoria::CARGA_MASIVA` y `nombre_tabla` = la tabla destino.
7. Documentar en este README y agregar tests en `tests/Feature/`.

---

## 📅 Citas

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| `GET`  | `/api/citas`        | 🔒 | Listado con filtros opcionales: `paciente_id`, `medico_id`, `desde`, `hasta`. |
| `POST` | `/api/citas`        | 🔒 | Crea una cita. Valida que el profesional **no tenga** otra cita en el mismo `programada_para` (responde 422 si hay colisión). |
| `POST` | `/api/citas/batch`  | 🔒 | Agenda **varias terapias/citas** del mismo paciente en un solo request. |

**Body `POST /api/citas`:**
```json
{
  "paciente_id": 1,
  "medico_id": 2,
  "especialidad_id": 2,
  "programada_para": "2026-05-20 10:00:00"
}
```

**Body `POST /api/citas/batch` (agendar varias terapias del paciente):**
```json
{
  "paciente_id": 1,
  "citas": [
    { "medico_id": 2, "especialidad_id": 2, "programada_para": "2026-05-20 09:00:00" },
    { "medico_id": 3, "especialidad_id": 3, "programada_para": "2026-05-20 10:00:00" },
    { "medico_id": 4, "especialidad_id": 4, "programada_para": "2026-05-20 11:00:00" }
  ]
}
```
Devuelve `{creadas, errores, data}` — best-effort: las que tienen colisión se reportan en `errores`, las demás se crean.

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

`POST` registra una evolución clínica. Se permiten **varias terapias por paciente el mismo día y especialidad** (formato F-GDG-020 admite múltiples sesiones diarias). Acepta `fecha_hora` opcional para registros retroactivos. La firma se cifra con AES-256 vía cast `encrypted` (no exponer en respuestas).

```json
{
  "paciente_id": 1,
  "objetivo_id": 1,
  "actividad_id": 1,
  "especialidad_id": 2,
  "firma_electronica": "Firma del Dr. Daniel",
  "fecha_hora": "2026-04-20 14:00:00",
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

**Acciones registradas** (`accion`): `CREAR`, `EDITAR`, `ELIMINAR`, `RESTAURAR`, `CONSULTAR`, `CARGA_MASIVA`. Las cargas masivas escriben **un registro de batch** (con `registro_id=0` y un resumen JSON en `detalles`) **además** de los registros individuales por cada fila insertada — útil para auditar quién subió qué archivo, cuándo y cuántas filas resultaron en error.

---

## 📄 Exportación PDF

| Método | Ruta | Auth |
|--------|------|------|
| `GET` | `/api/pacientes/{id}/exportar-historia` | 🔒 |

Descarga el PDF consolidado siguiendo el **formato oficial F-GDG-020 EVOLUCIÓN DE PACIENTE** de Avanzar IPS:
- Encabezado con logo, código `F-GDG-020`, "Documento Controlado".
- Sección **Recepción**: Nombre, Identificación, EPS, Edad, Sexo (M/F en cajitas) y Diagnóstico.
- Tabla de evoluciones: `Fecha | Hora | Área | Atención, Actividad y/o Procedimiento`, con firma del profesional bajo cada registro (Nombre, Correo, Especialidad, AVANZAR IPS).
- El campo **Área** se abrevia automáticamente desde la especialidad: `PSICO`, `FONO`, `FISIO`, `T.O.`, `VISIO`.

---

## 🔐 RBAC (Permisos por Rol)

| Rol | Permisos |
|-----|----------|
| **Administrador** | (todos, super-rol) — incluye `usuarios.*`, `roles.gestionar`, `objetivos.gestionar`, **`especialidades.gestionar`**, `auditoria.ver`, `pacientes.carga_masiva`, `usuarios.reset_password` |
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
| `422`  | Validación fallida / error de negocio (ej. cita con colisión de horario del profesional) |
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

# Carga masiva: descargar plantilla y subirla rellena
curl -H "Authorization: Bearer $TOKEN" \
  http://127.0.0.1:8000/api/pacientes/plantilla-excel -o plantilla_pacientes.xlsx
curl -X POST http://127.0.0.1:8000/api/pacientes/importar-excel \
  -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  -F "archivo=@plantilla_pacientes.xlsx"
```

---

## 📌 Roadmap pendiente
Ver [ReadmeTareas.md](ReadmeTareas.md) para los items priorizados restantes (M3 PDFs masivos, M9 carga masiva, M10 reportes ZIP, etc.).

---

## 🆕 Changelog reciente (Preproduccion)

**Sprint actual — Especialidades, terapias múltiples y formato F-GDG-020**

- ✅ **Especialidades CRUD admin** — nuevo `EspecialidadController` + permiso `especialidades.gestionar`. Endpoints: `POST/GET/PUT/DELETE /api/especialidades/{id}`. El borrado se bloquea con 409 si la especialidad tiene profesionales o citas asociadas.
- ✅ **Múltiples terapias por paciente/día** — se removió el bloqueo `"Ya existe una terapia para este paciente hoy"` en `TerapiaController@store`. El formato F-GDG-020 admite varias sesiones diarias (psicología, fonoaudiología, fisio, T.O. en distintas horas). Se acepta `fecha_hora` opcional para registros retroactivos.
- ✅ **Citas mejoradas** — `GET /api/citas` con filtros, validación de colisión médico+hora (422), y `POST /api/citas/batch` para agendar varias terapias del paciente en un solo request.
- ✅ **PDF rediseñado** — `resources/views/pdf/historia_clinica.blade.php` ahora replica el formato oficial **F-GDG-020 EVOLUCIÓN DE PACIENTE** (Recepción + tabla Fecha/Hora/Área/Atención con firma por evolución).
- ✅ **Especialidades nuevas en seeder** — `Psicologia` y `Visioterapia` (además de Fisioterapia, Fonoaudiología y Terapia Ocupacional).
- ✅ **Documentación API** — la UI Scalar (`/docs`) refleja automáticamente los nuevos endpoints (auto-detectados por Scramble desde rutas + FormRequests + Resources).
