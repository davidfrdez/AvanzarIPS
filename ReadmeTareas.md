# Roadmap de Mejora — Avanzar IPS API

> **Documento vivo de tareas pendientes del backend.** No contiene código: describe qué cambiar, dónde, por qué y en qué orden. Cada ítem se implementará sólo tras aprobación explícita.

## Contexto

Avanzar IPS es una API REST en Laravel/PHP 8.4 para Historia Clínica Electrónica (HCE) en Colombia, sujeta a **Ley 2015** (HCE inmodificable y trazable) y **Ley 1581** (Protección de Datos). Auditoría exhaustiva del repo reveló que la base está construida pero existen **inconsistencias críticas** entre Controllers y Services, brechas legales (sin SoftDeletes, auditoría modificable, datos sensibles sin encriptar) y deuda arquitectónica (Controllers gordos, Resources ausentes, RBAC roto).

---

## ⚡ Solicitudes del frontend (bloqueantes de integración)

El equipo de frontend (React) reportó que su UI ya está construida y necesita estos endpoints/comportamientos del backend para poder operar. Cada solicitud está mapeada al ítem correspondiente del roadmap:

| # | Solicitud frontend | Estado actual backend | Item roadmap |
| --- | --- | --- | --- |
| FE-1 | Validaciones completas en `StorePacienteRequest` (tipo_documento, nombres, apellidos, fecha_nacimiento, sexo, dirección, barrio, teléfono, correo, ocupación, eps, regimen_salud, categoria_eps, responsable y parentesco) | ✅ Reglas ya están en el archivo. Falta confirmar con frontend que el payload coincide y reforzar con Enums (TipoDocumento, Sexo) | C7 (RBAC), A4 (authorize), A5 (Enums) |
| FE-2 | CRUD del Árbol Clínico (`/api/objetivos`, `/api/actividades`, `/api/respuestas` con POST/PUT/DELETE) — UI ya construida (`ActivitiesManager`) | ❌ Solo `GET /api/objetivos`. Falta todo el resto | **M2** |
| FE-3 | `PUT /api/usuarios/{id}` para editar usuario/rol y `DELETE /api/usuarios/{id}` (o `PUT .../desactivar`) para baja de profesional | ❌ Solo `POST` y `GET` existen | **M1** + C3 (SoftDeletes en User) |
| FE-4 | `POST /api/pacientes/carga-masiva` para importar CSV/Excel | ❌ No existe | **M9** |
| FE-5 | `POST /api/reportes/generar-zip` recibiendo array de `paciente_ids` y devolviendo `.zip` consolidado de PDFs | ❌ Solo existe descarga PDF individual | **M10** |

**Recomendación de orden para no bloquear al frontend:** ejecutar M2 → M1 (parte usuarios) → M9 → M10 antes de los items 🟠 ALTOS de arquitectura interna, una vez resueltos los 🔴 CRÍTICOS C1, C2, C3, C7 (sin estos cuatro nada funciona ni es seguro).

---

## Inventario de hallazgos (resumen)

**Patrón positivo a replicar:** la cadena `StorePacienteRequest → PacienteDTO → PacienteServiceInterface → PacienteService → (pendiente) PacienteResource` ya cumple la arquitectura exigida (strict_types, return types, DTOs readonly, transacciones, interfaces). Es la referencia para todos los demás módulos.

| Capa | Estado | Hallazgo principal |
| --- | --- | --- |
| Controllers | 50% gordos | Validación inline y lógica de negocio en `Terapia`, `EscalaWeefim`, `HistoriaClinicaIngreso`, `OrdenMedica`, `ConsentimientoLegal`, `ConsultaEspecialista` |
| Services | Inconsistente | Solo `PacienteService` cumple arquitectura. `Auth/User/Cita` sin strict_types, sin DTOs, sin interfaces |
| Form Requests | 75% sin permisos | `authorize()` retorna `true` en `StoreUser`, `StoreCita`, `Login` |
| Resources | 11% cobertura | Solo existe `UserResource`. Faltan 14+ |
| Models | 0% SoftDeletes | Ningún modelo crítico tiene `SoftDeletes` |
| Auditoría | No inmodificable | `AuditoriaCambio` permite update/delete; falta IP/User-Agent; trait no aplicado a 6 modelos clínicos |
| RBAC | Roto | `User::can()` busca campo `vista` que no existe en tabla `permisos` (campo real: `slug`) |
| Encriptación | Inconsistente | Firmas encriptadas en controller (no en cast). Datos clínicos PII sin cifrar |
| Jobs/Queues | Inexistente | Sin `app/Jobs/`. Generación PDF síncrona |
| Policies | Inexistente | Sin `app/Policies/` |
| Tests | Vacíos | Solo `ExampleTest.php` |
| CORS / Headers | Sin configurar | `config/cors.php` no existe |

---

## Prioridad 🔴 CRÍTICO (Bloqueantes legales y de funcionamiento)

### C1. Reparar inconsistencia `PacienteController` ↔ `PacienteService`
- **Problema:** El controller llama `getAllPacientes()` y `createPaciente($array)`; el service expone `paginate()` y `create(PacienteDTO, ?HistoriaClinicaIngresoDTO)`. **Endpoint roto en runtime.**
- **Archivo:** `app/Http/Controllers/PacienteController.php`.
- **Acción:** reemplazar por `paginate()` y `create($request->toPacienteDTO(), $request->toIngresoDTO())`. Devolver `PacienteResource`. Inyectar `PacienteServiceInterface`.

### C2. Registrar binding de la interfaz en `AppServiceProvider`
- **Problema:** existe `PacienteServiceInterface` pero no hay binding.
- **Archivo:** `app/Providers/AppServiceProvider.php`.
- **Acción:** registrar `PacienteServiceInterface → PacienteService` en `register()`.

### C3. Implementar `SoftDeletes` (Ley 2015 — prohibido borrado físico)
- **Problema:** ningún modelo lo usa. `PacienteService::softDelete()` ejecuta borrado físico real.
- **Modelos:** `User`, `Paciente`, `Terapia`, `HistoriaClinicaIngreso`, `ConsentimientoLegal`, `OrdenMedica`, `ConsultaEspecialista`, `EscalaWeefim`, `ResultadoTerapia`.
- **Migraciones:** crear nuevas migraciones que añadan `softDeletes()` a cada tabla — **no editar migraciones existentes** ya aplicadas.
- **Validación de unicidad:** revisar `unique` en Form Requests para que ignoren registros con `deleted_at` (`StorePacienteRequest` ya lo hace; replicar patrón).

### C4. Hacer `AuditoriaCambio` inmodificable (Ley 2015 — append-only)
- **Problema:** tabla normal, permite `update()` y `delete()`. Viola auditoría inmodificable.
- **Archivo:** `app/Models/AuditoriaCambio.php`.
- **Acción:**
    - Hooks `updating()` y `deleting()` que lancen excepción.
    - Considerar revocar permisos `UPDATE`/`DELETE` a nivel de DB para el usuario de aplicación (defensa en profundidad).
    - Backlog: endpoint admin de export y firmado periódico (hash encadenado).

### C5. Aplicar `Auditable` a modelos clínicos faltantes
- **Problema:** trait sólo está en `Paciente`, `User`, `Cita`, `Terapia`.
- **Modelos a aplicar:** `ConsentimientoLegal`, `HistoriaClinicaIngreso`, `OrdenMedica`, `ConsultaEspecialista`, `EscalaWeefim`, `ResultadoTerapia`.
- **Mejora del trait:** capturar `request()->ip()` y `request()->userAgent()` y persistirlos en `auditoria_cambios` (requiere añadir columnas vía migración).

### C6. Auditar `CONSULTAR` (lectura) de HCE
- **Problema:** Ley 2015 exige trazar también las **lecturas** de HCE. Hoy `AccionAuditoria::CONSULTAR` existe pero no se emite nunca.
- **Acción:** registrar evento al servir endpoints `show`/`exportar-historia` de paciente y cualquier formato clínico.
- **Punto de implementación:** middleware dedicado o llamadas explícitas en services de lectura.

### C7. Reparar RBAC roto en `User::can()`
- **Problema:** `User.php` busca `permisos.vista`; la tabla tiene columna `slug`. El check **siempre falla** → `pacientes.crear` en `StorePacienteRequest` es inalcanzable.
- **Archivos:** `app/Models/User.php`, seeders de permisos.
- **Acción:** unificar en `slug`. Verificar seeder define los slugs esperados (`pacientes.crear`, `pacientes.ver`, `terapias.crear`, etc.).

### C8. Encriptación correcta de firmas electrónicas (AES-256 — Ley 1581)
- **Problema:** controllers de `Terapia` y `ConsultaEspecialista` usan `encrypt()` manual; modelo sin cast `encrypted` → la lectura no se desencripta automáticamente.
- **Archivos:** `app/Models/Terapia.php`, `app/Models/ConsultaEspecialista.php`, controllers correspondientes.
- **Acción:**
    - Mover responsabilidad al modelo con `protected $casts = ['firma_electronica' => 'encrypted']`.
    - Quitar `encrypt()` manual de controllers/services.
    - Verificar `APP_KEY` y `cipher` AES-256-CBC en `config/app.php`.

### C9. Encriptar campos clínicos PII (Ley 1581)
- **Problema:** antecedentes, anamnesis, impresión diagnóstica, plan de tratamiento, datos del firmante en consentimientos — todos en texto plano.
- **Modelos:** `HistoriaClinicaIngreso` (8+ campos), `ConsentimientoLegal` (`nombre_firmante`, `documento_firmante`), `Paciente` (debate: cédula/correo/teléfono).
- **Riesgo de cifrar todo:** se pierde búsqueda y `unique`. Recomendación: cifrar antecedentes y anamnesis libres; mantener identificadores indexables sin cifrar pero protegidos por RBAC + auditoría.
- **Pendiente:** decidir columnas exactas (ver "Decisiones pendientes").

### C10. Rate limiting en login (anti-fuerza bruta — Ley 1581)
- **Archivo:** `routes/api.php`.
- **Acción:** middleware `throttle:5,1` (5 intentos/minuto por IP+correo) en `/api/auth/login`. Lockout exponencial tras N fallos en `AuthService` con notificación al usuario.

### C11. `PasswordResetController` bypassa auditoría
- **Problema:** `User::where(...)->update(...)` evita los eventos del modelo → cambio de contraseña **no auditado**.
- **Archivo:** `app/Http/Controllers/PasswordResetController.php`.
- **Acción:**
    - Reemplazar `->update()` masivo por `$user->password = ...; $user->save()` para disparar `Auditable`.
    - Limitar intentos de validación del código (3 intentos máx).
    - Incrementar entropía del código (de 6 dígitos a 8 alfanum o token UUID).
    - Auditar también la eliminación del registro de reset.

### C12. Autorización ausente en controllers clínicos
- **Problema:** `TerapiaController`, `ConsentimientoLegalController`, `ConsultaEspecialistaController`, `OrdenMedicaController`, `EscalaWeefimController`, `HistoriaClinicaIngresoController`, `AuditoriaController` **no verifican permisos**. Cualquier usuario autenticado puede leer/escribir cualquier paciente.
- **Acción:**
    - Mover validación a Form Requests con `authorize()` que verifique `can('<modulo>.<accion>')`.
    - Crear Policies (`app/Policies/`) y registrarlas en `AuthServiceProvider`.
    - Aplicar middleware `can:` en rutas (defensa en profundidad).
    - `AuditoriaController` restringido a rol Auditor/Admin.

---

## Prioridad 🟠 ALTO (Arquitectura y consistencia)

### A1. Adelgazar Controllers gordos (Service Layer)
- **Controllers afectados:** `TerapiaController`, `EscalaWeefimController`, `HistoriaClinicaIngresoController`, `OrdenMedicaController`, `ConsentimientoLegalController`, `ConsultaEspecialistaController`.
- **Acción por cada uno:**
    1. Crear `Store<X>Request` y `Update<X>Request` (validación + `authorize()` + `to<X>DTO()`).
    2. Crear `<X>DTO` (`final readonly`, strict_types).
    3. Crear `<X>ServiceInterface` y `<X>Service` (`final`, strict_types, transacciones, auditoría delegada).
    4. Reducir el controller a inyección de servicio + Resource.
    5. Mover lógica de negocio (cálculo WEEFIM, prevención duplicados terapia, encriptación firma) al service.

### A2. Crear API Resources faltantes
- **A crear:** `PacienteResource`, `CitaResource`, `TerapiaResource`, `ResultadoTerapiaResource`, `HistoriaClinicaIngresoResource`, `ConsentimientoLegalResource`, `OrdenMedicaResource`, `ConsultaEspecialistaResource`, `EscalaWeefimResource`, `ObjetivoResource`, `ActividadResource`, `RespuestaResource`, `EspecialidadResource`, `RolResource`, `AuditoriaCambioResource`.
- **Reglas:** `whenLoaded()` para relaciones; nunca exponer firmas encriptadas; ocultar `password`, `remember_token`, campos internos.

### A3. Refactor `AuthService`, `UserService`, `CitaService` al patrón estándar
- **Acciones por cada uno:**
    - Añadir `declare(strict_types=1)`.
    - Crear DTOs (`LoginDTO`, `UserDTO`, `CitaDTO`).
    - Crear interfaces (`AuthServiceInterface`, etc.) y registrar bindings.
    - Tipos de retorno explícitos en todos los métodos.
    - Sustituir auditoría manual por trait `Auditable` ya aplicado al modelo.

### A4. Form Requests: completar `authorize()` real
- **Archivos:** `StoreUserRequest`, `StoreCitaRequest`, `LoginRequest` (login se queda público; usuarios y citas requieren permiso).
- **Acción:** replicar patrón `StorePacienteRequest` (`$this->user()->can('<slug>')`).

### A5. Enums adicionales
- **A crear en `app/Enums/`:**
    - `EstadoConsentimiento` (FIRMADO/RECHAZADO/PENDIENTE).
    - `TipoConsentimiento` (TRATAMIENTO/FOTOS/REVISION_VISUAL).
    - `EstadoCita` (PROGRAMADA/CONFIRMADA/CANCELADA/COMPLETADA/NO_ASISTIO).
    - `OrigenEnfermedad` (GENERAL/LABORAL/TRAFICO/OTRO).
    - `Pronostico` (BUENO/RESERVADO/MALO).
- **Aplicar:** casts en modelos respectivos + reglas `Enum::class` en Form Requests.

### A6. Catálogos maestros (parametrización vs strings libres)
- **Tablas a crear:** `eps`, `regimenes_salud`, `parentescos`, `tipos_documento_persona` (si se decide tabla en lugar de Enum).
- **Migrar:** `paciente.eps`, `paciente.regimen_salud`, `paciente.parentesco_responsable` a FKs con seeders iniciales (EPS colombianas, regímenes contributivo/subsidiado/especial/excepción).
- **Beneficio:** validación referencial + reportería + auditoría de cambios en catálogo.

### A7. Versionado de API (`/api/v1/...`)
- **Archivo:** `routes/api.php`.
- **Acción:** anidar todas las rutas dentro de `Route::prefix('v1')->group(...)`. Documentar deprecación.

### A8. Índices de base de datos
- **Migración nueva** que añada índices a:
    - `pacientes.cedula` (UNIQUE ya existe; añadir índice compuesto `(tipo_documento, cedula)` si búsqueda combinada).
    - `citas.programada_para`, `citas.medico_id`, `citas.paciente_id`.
    - `terapias.fecha_hora`, `terapias.paciente_id`.
    - `auditoria_cambios.(nombre_tabla, registro_id)`, `auditoria_cambios.usuario_id`, `auditoria_cambios.created_at`.
    - `historias_clinicas_ingreso.paciente_id`, `consentimientos_legales.paciente_id`, etc.

### A9. Inconsistencia de tabla `users` vs `usuarios`
- **Problema:** migración crea tabla `usuarios`, pero FKs y código de Laravel asumen `users`. Riesgo de FK rotas.
- **Acción:** auditar migraciones que referencian `usuarios` y unificar nombre (recomendado: mantener `users` que es default de Laravel y simplifica Sanctum, factories). Crear migración de renombrado si se confirma divergencia.

### A10. Configurar CORS y headers de seguridad
- **Archivos:** `config/cors.php` (crear), middleware nuevo `SecurityHeaders`.
- **Acción:**
    - CORS restringido a dominios del frontend.
    - Headers: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Strict-Transport-Security` en producción, `Content-Security-Policy` mínimo para API.

---

## Prioridad 🟡 MEDIO (Funcionalidad y completitud)

### M1. CRUD completo (show/update/destroy) en formatos extendidos
- **Faltan:** `show`, `update`, `destroy` para `pacientes`, `citas`, `terapias`, todos los formatos extendidos.
- **Reglas de inmutabilidad:** ej. `ConsentimientoLegal` firmado no se edita ni elimina (solo se anula con motivo registrado). `Terapia` evolucionada solo el autor del registro o admin con auditoría.
- **Endpoints específicos pedidos por el frontend (Personal y Profesionales):**
    - `PUT /api/usuarios/{id}` — editar usuario / rol.
    - `DELETE /api/usuarios/{id}` o `PUT /api/usuarios/{id}/desactivar` — baja de profesional. Recomendación: implementar `desactivar` (soft delete + flag `activo=false`) ya que `User` debe usar `SoftDeletes` (ver C3).

### M2. CRUD completo para Parametrización (Árbol Clínico — bloqueante frontend)
- **Solicitado por el frontend:** ya tienen el componente visual `ActivitiesManager` y necesitan los endpoints CRUD del árbol jerárquico.
- **Endpoints requeridos:**
    - `POST`, `PUT`, `DELETE` para `/api/objetivos` (raíz del árbol).
    - `POST`, `PUT`, `DELETE` para `/api/actividades` (ramas).
    - `POST`, `PUT`, `DELETE` para `/api/respuestas` (hojas).
- **Faltan internamente:** Controllers/Services/Requests/Resources para `Actividad`, `Respuesta`, `Objetivo` (solo tiene `index`), `AsignacionObjetivo`, `Especialidad`.
- **Reglas:** árbol jerárquico estricto — no se puede borrar `Objetivo` con `Actividades` referenciadas en `Terapias`/`ResultadosTerapia`. Validar en service. Cascada lógica con `SoftDeletes`.

### M3. Job de generación masiva de PDFs (Queues + Batches, 50 por lote)
- **Crear:** `app/Jobs/GenerarPdfHistoriaClinica.php`, despachado vía `Bus::batch()` con chunks de 50.
- **Configuración:** driver de cola en `.env` (database/redis), `php artisan queue:work` en producción (supervisor).
- **Endpoints nuevos:** `POST /api/pacientes/exportar-historias-masivo` (recibe IDs, devuelve `batch_id`); `GET /api/exportaciones/{batchId}` (estado).
- **Storage:** `storage/app/exports/{batchId}/...` con descarga firmada (signed URL) y expiración.

### M4. Almacenamiento real de firma digital en `ConsentimientoLegal`
- **Problema:** sin campo de firma encriptada (solo metadata).
- **Acción:** añadir columna `firma_digital` (text, encrypted cast) y `firma_hash` (sha256 para integridad).
- **Migración:** nueva. Documentar formato (base64 imagen / SVG path).

### M5. Notificaciones / Mailables clínicos
- Mail de cita agendada al paciente (con archivo `.ics`).
- Mail de recordatorio 24h antes (Job programado vía Scheduler).
- Mail de reset password (ya existe — revisar template).
- Notificación interna a médico cuando se crea ingreso.

### M6. Logging estructurado + canal de auditoría
- **`config/logging.php`:** canal `audit` separado (daily, retención 5 años por requisito legal HCE).
- **Auditar accesos a HCE:** middleware o evento que registre quién consultó qué.
- **Producción:** desactivar `APP_DEBUG=true`, bajar `LOG_LEVEL` a `warning`.

### M7. Suite de tests
- **`tests/Feature/`:**
    - Auth (login OK, login fail, throttling, logout, password reset).
    - RBAC (cada rol accede solo a lo permitido).
    - Auditoría (cada CRUD de modelo crítico genera registro en `auditoria_cambios`).
    - Auditoría inmodificable (intento de update/delete falla).
    - Encriptación (firma se cifra y descifra correctamente).
    - SoftDeletes (delete no es físico; restore funciona).
    - Flujo Paciente + Ingreso atómico.
    - Generación PDF batch (50 por lote, no más).
- **`tests/Unit/`:** DTOs, Enums, lógica WEEFIM, validación de duplicados terapia.

### M8. OpenAPI / documentación API
- Instalar `darkaonline/l5-swagger` o equivalente. Anotar endpoints. Generar `storage/api-docs/api-docs.json`. Servir Swagger UI en `/api/documentation` solo en non-prod.

### M9. Carga masiva de pacientes (CSV/Excel — solicitado por frontend)
- **Endpoint:** `POST /api/pacientes/carga-masiva`.
- **Acción:** recibir archivo CSV/Excel, validar fila por fila reusando reglas de `StorePacienteRequest`, importar en transacción, devolver reporte (insertados / fallidos con motivo por fila).
- **Componentes:**
    - Paquete sugerido: `maatwebsite/excel` (composer).
    - `app/Imports/PacientesImport.php` con `ToModel`, `WithHeadingRow`, `WithValidation`, `SkipsOnFailure`, `WithChunkReading` (procesar en lotes de 500 filas).
    - Job asíncrono `ImportarPacientesJob` para archivos grandes (>1000 filas).
    - Plantilla descargable: `GET /api/pacientes/carga-masiva/plantilla` (devuelve XLSX modelo con headers exactos).
- **Validaciones:** mismo set que `StorePacienteRequest`; cédula duplicada en archivo o en BD se reporta sin abortar el lote completo.
- **Auditoría:** registrar en `auditoria_cambios` la operación de import (usuario, archivo, total filas, total OK, total error).
- **Permisos:** requiere `pacientes.crear-masivo` (nuevo slug).

### M10. Reportes en lote — ZIP consolidado (solicitado por frontend)
- **Endpoint:** `POST /api/reportes/generar-zip`.
- **Payload:** `{ "paciente_ids": [int, ...] }` (máx 50 por requisito de negocio, ver sección de reglas).
- **Comportamiento:**
    - Generar PDF de historia clínica de cada paciente (reusar `PdfController::descargarHistoria` o `PdfService` extraído).
    - Empaquetar en `.zip` (un PDF por paciente, nombrado `historia_<cedula>_<fecha>.pdf`).
    - Si la lista supera 50: rechazar con 422 o derivar a flujo asíncrono de M3 (decisión de producto).
- **Storage:** generar en `storage/app/exports/zip/` con nombre único; devolver descarga firmada o stream directo.
- **Componentes:**
    - `ZipArchive` nativo de PHP.
    - `app/Services/ReporteZipService.php` (final, strict_types).
    - `Store<X>Request` para validar `paciente_ids` (array, exists:pacientes,id, max:50).
- **Auditoría:** cada PDF generado dispara `AccionAuditoria::CONSULTAR` sobre el paciente correspondiente (Ley 2015 — auditar lecturas de HCE).
- **Permisos:** `reportes.generar-zip`.

---

## Prioridad 🟢 BAJO (Mantenimiento y calidad)

### B1. Larastan / PHPStan nivel 8
Activar análisis estático estricto en CI. Corregir reportes.

### B2. Laravel Pint + pre-commit hook
Formato consistente, integración con git hooks o GitHub Actions.

### B3. Seeders completos
Roles, permisos (slugs `<modulo>.<accion>`), especialidades, EPS, regímenes, parentescos, usuario admin inicial.

### B4. Factories para tests
Para todos los modelos críticos. Facilita tests y demos.

### B5. Comandos artisan de mantenimiento
- `pacientes:purgar-soft-deleted-vencidos` (con retención legal — generalmente nunca; útil en dev).
- `auditoria:exportar-firmado` para entrega periódica a entes regulatorios.

### B6. Health check endpoint
`GET /api/health` para monitoreo (no autenticado, expone solo estado DB/cache/queue).

---

## Decisiones pendientes (input requerido antes de ejecutar)

1. **Encriptación de PII de paciente:** ¿qué columnas concretas cifrar? (cédula, correo, teléfono, dirección, nombres). Cifrar cédula imposibilita búsqueda; alternativa: hash determinístico para búsqueda + cifrado para presentación.
2. **Reglas de inmutabilidad:** ¿`ConsentimientoLegal` firmado se anula con motivo y queda registro, o queda totalmente bloqueado?
3. **Catálogos de EPS:** ¿lista oficial ADRES (Colombia) como seeder, o entrada libre con autocompletado?
4. **Versionado API:** ¿migrar todo a `/api/v1` ahora o mantener URLs actuales y añadir `v1` solo a endpoints nuevos?
5. **Storage PDFs masivos:** ¿local (`storage/app/`), S3, o disco compartido NFS?
6. **Driver de queue producción:** ¿database, Redis o SQS?
7. **Tabla `users` vs `usuarios`:** ¿nombre actual real en DB y elegir uno?
8. **Login/JWT:** ¿Sanctum SPA-mode (cookie) o token-mode? Hoy parece token-mode; afecta CORS y CSRF.
9. **Política de retención de auditoría:** Ley 2015 sugiere mínimo 15 años para HCE. ¿Confirmar?

---

## Archivos críticos a modificar (referencia rápida)

### Existentes
- [app/Http/Controllers/PacienteController.php](app/Http/Controllers/PacienteController.php) — C1
- [app/Http/Controllers/TerapiaController.php](app/Http/Controllers/TerapiaController.php) — A1
- [app/Http/Controllers/EscalaWeefimController.php](app/Http/Controllers/EscalaWeefimController.php) — A1
- [app/Http/Controllers/HistoriaClinicaIngresoController.php](app/Http/Controllers/HistoriaClinicaIngresoController.php) — A1
- [app/Http/Controllers/OrdenMedicaController.php](app/Http/Controllers/OrdenMedicaController.php) — A1
- [app/Http/Controllers/ConsentimientoLegalController.php](app/Http/Controllers/ConsentimientoLegalController.php) — A1
- [app/Http/Controllers/ConsultaEspecialistaController.php](app/Http/Controllers/ConsultaEspecialistaController.php) — A1
- [app/Http/Controllers/AuditoriaController.php](app/Http/Controllers/AuditoriaController.php) — C12
- [app/Http/Controllers/PasswordResetController.php](app/Http/Controllers/PasswordResetController.php) — C11
- [app/Http/Requests/StoreUserRequest.php](app/Http/Requests/StoreUserRequest.php) — A4
- [app/Http/Requests/StoreCitaRequest.php](app/Http/Requests/StoreCitaRequest.php) — A4
- [app/Models/User.php](app/Models/User.php) — C3, C7
- [app/Models/Paciente.php](app/Models/Paciente.php) — C3
- [app/Models/Terapia.php](app/Models/Terapia.php) — C3, C8
- [app/Models/ConsultaEspecialista.php](app/Models/ConsultaEspecialista.php) — C3, C8
- [app/Models/HistoriaClinicaIngreso.php](app/Models/HistoriaClinicaIngreso.php) — C3, C5, C9
- [app/Models/ConsentimientoLegal.php](app/Models/ConsentimientoLegal.php) — C3, C5, C9, M4
- [app/Models/OrdenMedica.php](app/Models/OrdenMedica.php) — C3, C5
- [app/Models/EscalaWeefim.php](app/Models/EscalaWeefim.php) — C3, C5
- [app/Models/ResultadoTerapia.php](app/Models/ResultadoTerapia.php) — C3, C5
- [app/Models/AuditoriaCambio.php](app/Models/AuditoriaCambio.php) — C4
- [app/Traits/Auditable.php](app/Traits/Auditable.php) — C5 (IP/UA)
- [app/Services/AuthService.php](app/Services/AuthService.php) — A3
- [app/Services/UserService.php](app/Services/UserService.php) — A3
- [app/Services/CitaService.php](app/Services/CitaService.php) — A3
- [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) — C2
- [routes/api.php](routes/api.php) — C10, A7

### A crear (resumen de directorios)
- `app/Enums/` — `EstadoConsentimiento`, `TipoConsentimiento`, `EstadoCita`, `OrigenEnfermedad`, `Pronostico`
- `app/DTOs/` — `LoginDTO`, `UserDTO`, `CitaDTO`, `TerapiaDTO`, `ConsentimientoLegalDTO`, `OrdenMedicaDTO`, `ConsultaEspecialistaDTO`, `EscalaWeefimDTO`, `ObjetivoDTO`, etc.
- `app/Services/` — `TerapiaService(+Interface)`, `ConsentimientoLegalService`, `OrdenMedicaService`, `ConsultaEspecialistaService`, `EscalaWeefimService`, `HistoriaClinicaIngresoService`, `ParametrizacionService`, `PdfService`
- `app/Services/Contracts/` — interfaces correspondientes
- `app/Http/Resources/` — 15 Resources listados en A2
- `app/Http/Requests/` — `Update<X>Request` para cada módulo + `Store<Formato>Request`
- `app/Policies/` — `PacientePolicy`, `TerapiaPolicy`, `ConsentimientoLegalPolicy`, `AuditoriaPolicy`, etc.
- `app/Jobs/` — `GenerarPdfHistoriaClinica`
- `app/Mail/` — `CitaAgendadaMail`, `RecordatorioCitaMail`
- `app/Http/Middleware/` — `SecurityHeaders`, `LogConsultaHCE`
- `database/migrations/` — adds `softDeletes`, índices, columnas IP/UA en auditoría, tablas catálogos, columna `firma_digital`
- `database/seeders/` — Permisos, EPS, Regímenes, Parentescos, Especialidades, Admin
- `tests/Feature/`, `tests/Unit/` — suite descrita en M7
- `config/cors.php`

---

## Plan de ejecución por sprints sugerido

| Sprint | Foco | Ítems |
| --- | --- | --- |
| 1 | Reparar funcionamiento básico + bloqueantes legales mínimos | C1, C2, C3, C4, C7, C8 |
| 2 | **Desbloquear frontend** (Árbol Clínico + CRUD usuarios + carga masiva + ZIP) | M2, M1 (usuarios), M9, M10 |
| 3 | Auditoría completa + RBAC + reset password | C5, C6, C10, C11, C12 |
| 4 | Refactor arquitectónico Controllers/Services/Resources | A1, A2, A3, A4, A5 |
| 5 | Catálogos, índices, versionado, CORS, encriptación PII | A6, A7, A8, A9, A10, C9 |
| 6 | CRUD restante, Jobs PDFs, firma digital, mailers | M1 (resto), M3, M4, M5 |
| 7 | Logging, tests, OpenAPI | M6, M7, M8 |
| 8 | Calidad, seeders, factories, comandos | B1–B6 |

---

## Verificación end-to-end (cómo se valida cada bloque)

- **C1/C2:** `php artisan route:list` y `curl POST /api/pacientes` con payload válido → 201; payload inválido → 422; sin token → 401.
- **C3 SoftDeletes:** crear paciente, `DELETE`, verificar `deleted_at` no nulo y registro persiste; `withTrashed()` lo encuentra.
- **C4 Auditoría inmodificable:** `tinker` → `AuditoriaCambio::first()->update(...)` → excepción.
- **C5 Auditoría completa:** crear consentimiento → fila en `auditoria_cambios` con IP y UA.
- **C7 RBAC:** usuario sin permiso `pacientes.crear` → 403; con permiso → 201.
- **C8 Encriptación firma:** insertar firma → en DB columna ilegible; `Terapia::find(x)->firma_electronica` retorna texto plano.
- **C10 Throttle:** 6 logins fallidos rápidos → 429.
- **M3 PDFs batch:** `POST /exportar-masivo` con 120 IDs → 3 batches de [50,50,20]; `php artisan queue:work` los procesa; archivos en `storage/app/exports/`.
- **M7 Tests:** `php artisan test --testsuite=Feature` → 100% pass.

---

## Notas finales

- **Nada de código en este documento.** Cada ítem se implementará sólo tras aprobación explícita y, donde aplique, después de resolver las "Decisiones pendientes".
- **Patrón de referencia:** todo nuevo módulo debe imitar la cadena `Store<X>Request → <X>DTO → <X>ServiceInterface → <X>Service → <X>Resource`.
- **Riesgo de migraciones masivas:** SoftDeletes y cambios de columnas en producción requieren ventana de mantenimiento o migraciones reversibles bien probadas en staging.
- **Compliance no es opcional:** los ítems 🔴 son **bloqueantes** para operar legalmente con datos reales de pacientes en Colombia.
