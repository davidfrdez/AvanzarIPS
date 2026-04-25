# Modelo Relacional y Base de Datos AvanzarIPS

Este documento describe la estructura completa de la base de datos de AvanzarIPS, incluyendo todas las tablas, columnas, tipos de datos y relaciones entre ellas.

## Módulo 1: Administración, Seguridad y RBAC

### Tabla `roles`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `nombre` | varchar | Ej: Administrador, Médico, Coordinador |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `especialidades`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `nombre` | varchar | Ej: Fisioterapia, Fonoaudiología, Terapia Ocupacional |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `usuarios`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `rol_id` | integer | FK -> `roles.id` |
| `especialidad_id` | integer | FK -> `especialidades.id` (null) Solo para médicos/terapeutas |
| `nombre` | varchar | |
| `correo` | varchar | Unique |
| `email_verified_at` | timestamp | null |
| `password` | varchar | Encriptación obligatoria |
| `esta_activo` | boolean | default: true |
| `remember_token` | varchar | null |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `permisos`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `nombre` | varchar | |
| `vista` | varchar | Unique, Ej: usuarios.ver |
| `descripcion` | varchar | null |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `permiso_rol`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `rol_id` | integer | FK -> `roles.id` |
| `permiso_id` | integer | FK -> `permisos.id` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `auditoria_cambios`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `usuario_id` | integer | FK -> `usuarios.id` |
| `accion` | varchar | Ej: CREAR, EDITAR, DESACTIVAR |
| `nombre_tabla` | varchar | |
| `registro_id` | integer | |
| `detalles` | text | |
| `created_at` | timestamp | |

---

## Árbol de Gestión (Objetivos > Actividades > Respuestas)

### Tabla `objetivos`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `nombre` | varchar | |
| `descripcion` | text | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Tabla `asignaciones_objetivos`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `objetivo_id` | integer | FK -> `objetivos.id` |
| `rol_id` | integer | FK -> `roles.id` (null) |
| `usuario_id` | integer | FK -> `usuarios.id` (null) |

### Tabla `actividades`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `objetivo_id` | integer | FK -> `objetivos.id` |
| `nombre` | varchar | |

### Tabla `respuestas`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `actividad_id` | integer | FK -> `actividades.id` |
| `texto_predeterminado` | text | Texto para autocompletar terapias |

---

## Módulos 2 y 3: Pacientes, Citas y Terapias

### Tabla `pacientes`
| Columna | Tipo | Detalles |
| :--- | :--- | :--- |
| `id` | integer | Primary Key, Auto Increment |
| `tipo_documento` | varchar | Ej: CC, TI, RC, OTRO |
| `cedula` | varchar | Unique |
| `nombres` | varchar | |
| `apellidos` | varchar | |
| `fecha_nacimiento` | date | |
| `sexo` | varchar | M o F |
| `direccion` | varchar | |
| `barrio` | varchar | |
| `telefono` | varchar | |
| `correo` | varchar | null |
| `ocupacion` | varchar | null |
| `eps` | varchar | |
| `regimen_salud` | varchar | null |
| `categoria_eps` | varchar | null |
| `nombre_responsable` | varchar | null |
| `telefono_responsable` | varchar | null |
| `parentesco_responsable` | varchar | null |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### Documentos Clínicos Adicionales

#### `historias_clinicas_ingreso`
Contiene la historia clínica de entrada realizada por un médico. Incluye motivos de consulta, enfermedad actual, antecedentes (personales, familiares, etc.), diagnóstico y plan de tratamiento.
- FK: `paciente_id` -> `pacientes.id`
- FK: `medico_id` -> `usuarios.id`

#### `consentimientos_legales`
Documentación explícita sobre consentimientos otorgados (Tratamiento de datos, Fotografías, etc.).
- FK: `paciente_id` -> `pacientes.id`

#### `ordenes_medicas`
Detalle de las órdenes de tratamiento y medicamentos emitidas por especialistas.
- FK: `paciente_id` -> `pacientes.id`
- FK: `medico_id` -> `usuarios.id`

#### `consultas_especialistas`
Visita con especialista (Psiquiatría/otros), incluye examen mental y firma electrónica.
- FK: `paciente_id` -> `pacientes.id`
- FK: `medico_id` -> `usuarios.id`
- FK: `especialidad_id` -> `especialidades.id`

#### `escalas_weefim`
Evaluación de funcionalidad pediátrica a través de las diferentes subtotales (autocuidado, movilidad, cognición) y su cálculo porcentual.
- FK: `paciente_id` -> `pacientes.id`
- FK: `profesional_id` -> `usuarios.id`

### Citas y Terapias

#### `citas`
- FK: `paciente_id` -> `pacientes.id`
- FK: `medico_id` -> `usuarios.id`
- FK: `especialidad_id` -> `especialidades.id`

#### `terapias`
Sesiones tomadas, vinculadas a un objetivo y actividad, firmada electrónicamente.
- FK: `paciente_id` -> `pacientes.id`
- FK: `profesional_id` -> `usuarios.id`
- FK: `objetivo_id` -> `objetivos.id`
- FK: `actividad_id` -> `actividades.id`
- FK: `especialidad_id` -> `especialidades.id`

#### `resultados_terapias`
- FK: `terapia_id` -> `terapias.id`
- FK: `respuesta_id` -> `respuestas.id`

---

## Resumen de Relaciones (Foreing Keys)

**Sistema y Usuarios**
* `usuarios.rol_id` ➔ `roles.id`
* `usuarios.especialidad_id` ➔ `especialidades.id`
* `permiso_rol.rol_id` ➔ `roles.id` (Cascade)
* `permiso_rol.permiso_id` ➔ `permisos.id` (Cascade)
* `auditoria_cambios.usuario_id` ➔ `usuarios.id`

**Gestión y Configuraciones**
* `asignaciones_objetivos.objetivo_id` ➔ `objetivos.id`
* `asignaciones_objetivos.rol_id` ➔ `roles.id`
* `asignaciones_objetivos.usuario_id` ➔ `usuarios.id`
* `actividades.objetivo_id` ➔ `objetivos.id`
* `respuestas.actividad_id` ➔ `actividades.id`

**Flujo Operativo (Atención a pacientes)**
* Modelos que referencian a `pacientes.id`: *Citas, Terapias, Historias Clínicas, Consentimientos, Órdenes, Consultas y Escalas WeeFIM*.
* Modelos que referencian a `usuarios.id` (como profesional): *Citas (medico_id), Terapias (profesional_id), Historias Clínicas (medico_id), Órdenes, Consultas, Escalas WeeFIM*.
* Modelos que referencian a `especialidades.id`: *Citas, Terapias, Consultas Especialistas*.
* Relaciones anidadas de Terapias:
  * `terapias.objetivo_id` ➔ `objetivos.id`
  * `terapias.actividad_id` ➔ `actividades.id`
  * `resultados_terapias.terapia_id` ➔ `terapias.id`
  * `resultados_terapias.respuesta_id` ➔ `respuestas.id`
