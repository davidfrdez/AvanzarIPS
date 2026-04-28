# Tareas Pendientes del Backend (AvanzarIPS)

Este documento enumera las tareas que deben ser completadas en el backend (Laravel) para garantizar que la integración con el frontend (React) funcione correctamente de acuerdo a los requerimientos del proyecto.

## 1. Módulo de Administración - Gestión de Pacientes

### Validaciones de Creación de Pacientes (`StorePacienteRequest.php`)
Actualmente, la creación de pacientes desde el frontend envía el payload completo con todos los datos necesarios para crear la historia y el perfil del paciente. Sin embargo, las reglas de validación están incompletas.

*   **Archivo a modificar:** `app/Http/Requests/StorePacienteRequest.php`
*   **Problema:** El método `rules()` actualmente solo valida `cedula`, `nombre` y `eps`. Si el frontend envía `tipo_documento`, `nombres`, `apellidos`, `fecha_nacimiento`, etc., el `StorePacienteRequest` falla o los filtra incorrectamente, causando que `PacienteService` falle al intentar insertar campos vacíos en la base de datos. Además, el Request pide `nombre` pero la base de datos usa `nombres` y `apellidos`.
*   **Tarea:** Actualizar el arreglo de validaciones en `StorePacienteRequest.php` para que exija y valide la estructura completa que espera `PacienteService`, incluyendo:
    *   `tipo_documento` (string, requerido)
    *   `cedula` (string, requerido, único)
    *   `nombres` (string, requerido)
    *   `apellidos` (string, requerido)
    *   `fecha_nacimiento` (date, requerido)
    *   `sexo` (string, requerido)
    *   `direccion` (string, requerido)
    *   `barrio` (string, requerido)
    *   `telefono` (string, requerido)
    *   `correo` (email, nullable)
    *   `ocupacion` (string, nullable)
    *   `eps` (string, requerido)
    *   `regimen_salud` (string, nullable)
    *   `categoria_eps` (string, nullable)
    *   `nombre_responsable` (string, nullable)
    *   `telefono_responsable` (string, nullable)
    *   `parentesco_responsable` (string, nullable)
