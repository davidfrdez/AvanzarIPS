<div class="container">
    <header>
        <h1>🔐 API Avanzar IPS - Autenticación y Clínica</h1>
        <p>Proyecto: <strong>Desarrollo Colombia</strong> | Stack: Laravel 11 + React</p>
    </header>
    <section>
        <h2>📌 Introducción</h2>
        <p>Esta API gestiona el ciclo completo de vida de la sesión del usuario, la recuperación de credenciales y el flujo principal del <strong>Módulo Clínico</strong> (Pacientes, Médicos, Especialidades y Agendamiento de Citas). Se utiliza una arquitectura relacional normalizada y el campo <code>correo</code> como identificador único para los usuarios.</p>
    </section>
    <section>
        <h2>🔑 Módulo de Autenticación</h2>
        <h3>1. Iniciar Sesión (Login)</h3>
        <p><span class="badge post">POST</span> <code>/api/login</code></p>
        <p>Valida las credenciales y retorna un token de acceso (Sanctum).</p>
        <pre><code>// Body JSON
{
  "correo": "santiagodavid980@gmail.com",
  "password": "mi_password_segura"
}
// Response (200 OK)
{
  "status": "success",
  "token": "1|abc123tokengenerado...",
  "usuario": { "nombre": "David", "correo": "..." }
}</code></pre>
        <h3>2. Cerrar Sesión (Logout)</h3>
        <p><span class="badge auth">POST</span> <code>/api/logout</code></p>
        <p>Revoca el token actual del usuario. Requiere autenticación previa.</p>
        <pre><code>// Headers Requeridos
Authorization: Bearer {tu_token_aqui}
Accept: application/json
// Response (200 OK)
{
  "status": "success",
  "message": "Sesión cerrada correctamente"
}</code></pre>
    </section>
    <section>
        <h2>📧 Módulo de Recuperación OTP</h2>
        <p>Flujo de seguridad con códigos de 6 dígitos con validez de <strong>5 minutos</strong>.</p>
        <h3>3. Solicitar Código</h3>
        <p><span class="badge post">POST</span> <code>/api/password/forgot</code></p>
        <p>Genera OTP y lo envía vía <strong>Hostinger SMTP</strong> (support@dalioss.com).</p>
        <pre><code>// Body JSON
{ "correo": "santiagodavid980@gmail.com" }</code></pre>   
        <h3>4. Validar Código</h3>
        <p><span class="badge post">POST</span> <code>/api/password/validate</code></p>
        <pre><code>// Body JSON
{
  "correo": "santiagodavid980@gmail.com",
  "code": "123456"
}</code></pre>
        <h3>5. Restablecer Contraseña</h3>
        <p><span class="badge post">POST</span> <code>/api/password/reset</code></p>
        <pre><code>// Body JSON
{
  "correo": "santiagodavid980@gmail.com",
  "code": "123456",
  "password": "NuevaPassword123",
  "password_confirmation": "NuevaPassword123"
}</code></pre>
    </section>
    <section>
        <h2>🏥 Módulo Clínico (Citas y Especialidades)</h2>
        <p>Rutas protegidas para la gestión de atenciones médicas. <strong>Requieren Token de Autenticación.</strong></p>
        <h3>6. Obtener Especialidades</h3>
        <p><span class="badge get">GET</span> <code>/api/especialidades</code></p>
        <p>Retorna el catálogo de especialidades disponibles (ideal para llenar los select en React).</p>
        <pre><code>// Response (200 OK)
[
  { "id": 1, "nombre": "Sin especificar" },
  { "id": 2, "nombre": "Fisioterapia" },
  { "id": 3, "nombre": "Fonoaudiología" }
]</code></pre>
        <h3>7. Programar Cita</h3>
        <p><span class="badge post">POST</span> <code>/api/citas</code></p>
        <p>Agenda una nueva cita vinculando al paciente, el médico y la especialidad requerida.</p>
        <pre><code>// Body JSON
{
  "paciente_id": 1,
  "medico_id": 2,
  "especialidad_id": 2, 
  "programada_para": "2026-05-20 10:00:00"
}
// Response (201 Created)
{
  "status": "success",
  "message": "Cita programada correctamente",
  "cita": { "id": 1, "programada_para": "2026-05-20 10:00:00", ... }
}</code></pre>
    </section>
    <section>
        <h2>🛠️ Manejo de Errores</h2>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Mensaje</th>
                    <th>Causa / Solución</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>401</code></td>
                    <td>Unauthorized</td>
                    <td>Credenciales de login incorrectas o falta de Token en cabeceras.</td>
                </tr>
                <tr>
                    <td><code>422</code></td>
                    <td>Validation Error</td>
                    <td>Campos faltantes, código OTP expirado, o envío de IDs inexistentes (ej. <code>especialidad_id</code> no válido).</td>
                </tr>
                <tr>
                    <td><code>500</code></td>
                    <td>Server Error</td>
                    <td>Fallo de autenticación SMTP con Hostinger o error de base de datos.</td>
                </tr>
            </tbody>
        </table>
    </section>
    <div class="note">
        <strong>💡 Tip para React:</strong> Para todas las rutas protegidas (Logout, Citas), recuerda incluir siempre el Header <code>Authorization: Bearer {token}</code> que recibiste en el Login. Además, asegúrate de enviar <code>especialidad_id</code> como un número entero, no como un texto.
    </div>
    <div class="footer">
        <p>Documentación técnica para uso interno - David Fernández &copy; 2026</p>
    </div>
</div>