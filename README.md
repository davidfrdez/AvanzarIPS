# 🔐 API Avanzar IPS — Documentación Oficial para Frontend (React)

**Proyecto:** Desarrollo Colombia | **Stack:** Laravel 11 + React  
**Base URL:** `http://localhost:8000`

---

## 📌 Introducción
Esta API gestiona el Módulo Administrativo, de Citas y de Historias Clínicas (Terapias) siguiendo estrictamente el Modelo Relacional y la Normativa HCE.

> **Importante:** Debes enviar siempre los headers:
> `Accept: application/json`
> `Content-Type: application/json`

---

## 🔑 Módulo de Autenticación (Sanctum SPA)

### 1. Iniciar Sesión (Login)
`POST /api/auth/login`

Valida las credenciales y devuelve el Token Sanctum.

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com",
  "password": "mi_password_segura"
}
```

### 2. Cerrar Sesión (Logout)
`POST /api/auth/logout` 🔒 *Requiere Header: `Authorization: Bearer {token}`*

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

### 4. Validar Código
`POST /api/password/validate`

**Body JSON:**
```json
{
  "correo": "santiagodavid980@gmail.com",
  "code": "123456"
}
```

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

## 📋 Módulo de Catálogos (Básicos)

### 3. Obtener Roles
`GET /api/roles`

Retorna el listado de roles (1: Administrador, 2: Médico, etc).

### 4. Obtener Especialidades
`GET /api/especialidades`

Retorna el listado de especialidades.

### 4.1 Obtener Usuarios
`GET /api/usuarios` 🔒 *Requiere autenticación*

Retorna la lista de todos los usuarios registrados en el sistema, con su respectivo rol y especialidad.

### 4.2 Obtener Médicos
`GET /api/medicos` 🔒 *Requiere autenticación*

Retorna la lista de usuarios filtrados que actúan como médicos, especialistas o profesionales.

### 5. Obtener Árbol de Objetivos
`GET /api/objetivos` 🔒 *Requiere autenticación*

Devuelve la lista anidada de **Objetivos -> Actividades -> Respuestas Predeterminadas**. Es **VITAL** para armar el formulario dinámico de Terapias en React.

**Response `200 OK` (Pruébalo en Postman!):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "nombre": "Mejorar Movilidad Articular",
      "actividades": [
        {
          "id": 1,
          "nombre": "Estiramiento pasivo",
          "respuestas": [
            {
              "id": 1,
              "texto_predeterminado": "Logro completado sin dolor"
            }
          ]
        }
      ]
    }
  ]
}
```

---

## 🏥 Módulo de Pacientes y Citas

### 6. Registrar Paciente Completo
`POST /api/pacientes` 🔒 *Requiere autenticación*

**Body JSON:**
```json
{
  "tipo_documento": "CC",
  "cedula": "1020304050",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "fecha_nacimiento": "1990-05-15",
  "sexo": "M",
  "direccion": "Calle 123",
  "barrio": "Centro",
  "telefono": "3001234567",
  "correo": "juan@example.com",
  "ocupacion": "Ingeniero",
  "eps": "Sura",
  "regimen_salud": "Contributivo",
  "categoria_eps": "A",
  "nombre_responsable": "Maria Pérez",
  "telefono_responsable": "3009876543",
  "parentesco_responsable": "Madre"
}
```

### 6.1 Obtener Todos los Pacientes
`GET /api/pacientes` 🔒 *Requiere autenticación*

Devuelve la lista completa de pacientes registrados en el sistema.

### 7. Agendar Cita
`POST /api/citas` 🔒 *Requiere autenticación*

**Body JSON:**
```json
{
  "paciente_id": 1,
  "medico_id": 2,
  "especialidad_id": 2,
  "programada_para": "2026-05-20 10:00:00"
}
```

---

## ⚕️ Módulo Clínico (Terapias)

### 8. Ver Historial de Terapias
`GET /api/terapias` 🔒 *Requiere autenticación*

Devuelve todas las terapias creadas hasta el momento de forma anidada con los resultados checkeados, pacentes y médicos asociados.

### 9. Registrar Nueva Terapia
`POST /api/terapias` 🔒 *Requiere autenticación*

Registra una evolución clínica. Bloquea duplicados en el mismo día automáticamente.

**Body JSON:**
```json
{
  "paciente_id": 1,
  "objetivo_id": 1,
  "actividad_id": 1,
  "especialidad_id": 2,
  "firma_electronica": "Firma del Dr. Daniel",
  "resultados": [
    {
      "respuesta_id": 1,
      "marcado": true,
      "notas_libres": "El paciente mejoró mucho hoy"
    }
  ]
}
```

---

## ⚕️ Formularios Clínicos y Legales (Complementarios)

### 10. API Resources Clínicos (Métodos GET y POST)
Todas estas rutas heredan el soporte nativo REST y soportan consultas estandarizadas. Protegidas vía Sanctum.

**Endpoints Disponibles Base:**
* `GET y POST /api/historias-ingreso` (Reporte extenso inicial de consultas, antecedentes, etc)
* `GET y POST /api/consentimientos` (Captura de aceptaciones legales y firmas de tutores)
* `GET y POST /api/ordenes-medicas` (Remisiones médicas generales)
* `GET y POST /api/consultas-especialistas` (Historias avanzadas y diagnóstico con métricas EEAG)
* `GET y POST /api/escalas-weefim` (Métricas obligatorias infantiles de cognición y movilidad)

*Ejemplo Payload `POST /api/escalas-weefim`:*
*(El backend calculará automáticamente `puntaje_total` y `porcentaje_funcionalidad`)*
```json
{
  "paciente_id": 1,
  "fecha_evaluacion": "2026-05-20",
  "subtotal_autocuidado": 40,
  "subtotal_movilidad": 30,
  "subtotal_cognicion": 35
}
```

---

## 📊 Módulo 3: Dashboard y Reportería Administrativa

### 11. Obtener Métricas Generales
`GET /api/dashboard/metrics` 🔒 *Requiere autenticación (Módulo Admin)*

Devuelve todos los KPIs y datos formateados para pintar las gráficas del estado actual de la IPS.

**Response `200 OK`:**
```json
{
  "status": "success",
  "data": {
    "kpis": {
      "total_pacientes": 105,
      "terapias_mes_actual": 42,
      "citas_pendientes": 12,
      "medicos_activos": 5
    },
    "graficos": {
      "terapias_por_especialidad": [
        { "especialidad": "Fisioterapia", "total": 20 },
        { "especialidad": "Fonoaudiología", "total": 22 }
      ],
      "top_profesionales_mes": [
        { "nombre": "Daniel (Secundario)", "terapias_realizadas": 35 }
      ]
    }
  }
}
```

### 12. Consultar Registros de Auditoría
`GET /api/auditoria` 🔒 *Requiere autenticación (Módulo Admin)*

Devuelve el registro histórico inmutable de quién hizo qué en el sistema. Ideal para la trazabilidad legal del software.

**Response `200 OK`:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "accion": "CREAR",
      "nombre_tabla": "terapias",
      "registro_id": 4,
      "detalles": "{\"paciente_id\": 1, \"especialidad_id\": 2}",
      "usuario": {
        "id": 2,
        "nombre": "Daniel (Secundario)"
      },
      "created_at": "2026-05-20T10:05:00.000000Z"
    }
  ]
}
```

### 13. Exportar Historia Clínica PDF
`GET /api/pacientes/{id}/exportar-historia` 🔒 *Requiere autenticación*

Genera y descarga en crudo al navegador el archivo PDF con toda la información consolidada y la matriz evolutiva de terapias de un paciente, listo para entregar al Ministerio. Ojo: La petición GET debe incluir el encabezado de autenticación.

---

## 🗄️ Procedimiento Actual de Base de Datos para Producción
1. Asegúrate de ejecutar `composer install` si borras dependencias.
2. Utiliza `php artisan migrate:fresh --seed` (Esto correrá los Seeders y creará Pacientes y Objetivos de prueba base que antes no existían).