<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permiso;
class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            // ----------------------------------------------------
            // Módulo 1: Administración
            // ----------------------------------------------------
            ['nombre' => 'Ver Usuarios', 'vista' => 'usuarios.ver', 'descripcion' => 'Ver lista de usuarios y roles'],
            ['nombre' => 'Crear Usuarios', 'vista' => 'usuarios.crear', 'descripcion' => 'Crear nuevos usuarios en el sistema'],
            ['nombre' => 'Editar Usuarios', 'vista' => 'usuarios.editar', 'descripcion' => 'Editar y desactivar usuarios'],
            ['nombre' => 'Gestionar Roles', 'vista' => 'roles.gestionar', 'descripcion' => 'Crear y editar roles y permisos'],
            ['nombre' => 'Gestionar Objetivos', 'vista' => 'objetivos.gestionar', 'descripcion' => 'Crear y editar objetivos, actividades y respuestas'],
            ['nombre' => 'Ver Auditoría', 'vista' => 'auditoria.ver', 'descripcion' => 'Acceso a los logs de cambios del sistema'],
            ['nombre' => 'Carga Masiva', 'vista' => 'pacientes.carga_masiva', 'descripcion' => 'Importar pacientes desde CSV/Excel'],
            ['nombre' => 'Reset Password', 'vista' => 'usuarios.reset_password', 'descripcion' => 'Forzar reseteo de contraseñas de otros usuarios'],

            // ----------------------------------------------------
            // Módulo 2: Gestión de Historias Clínicas
            // ----------------------------------------------------
            ['nombre' => 'Ver Agenda', 'vista' => 'agenda.ver', 'descripcion' => 'Visualizar agenda diaria de citas'],
            ['nombre' => 'Buscar Pacientes', 'vista' => 'pacientes.buscar', 'descripcion' => 'Buscar pacientes en el CRM'],
            ['nombre' => 'Crear Pacientes', 'vista' => 'pacientes.crear', 'descripcion' => 'Registrar un nuevo paciente en caso de no existir'],
            ['nombre' => 'Registrar Terapia', 'vista' => 'terapias.registrar', 'descripcion' => 'Registrar objetivos, actividades y resultados'],
            ['nombre' => 'Firmar Terapia', 'vista' => 'terapias.firmar', 'descripcion' => 'Aplicar firma electrónica (encriptada) al registro'],
            ['nombre' => 'Notas Libres', 'vista' => 'terapias.notas_libres', 'descripcion' => 'Permiso especial para agregar textos fuera de los predeterminados'],

            // ----------------------------------------------------
            // Módulo 3: Supervisión y Reportería
            // ----------------------------------------------------
            ['nombre' => 'Ver Dashboards', 'vista' => 'dashboards.ver', 'descripcion' => 'Acceso a KPIs y estadísticas interactivas'],
            ['nombre' => 'Ver Historial Consolidado', 'vista' => 'historial.ver', 'descripcion' => 'Ver detalles y horas por paciente/especialidad'],
            ['nombre' => 'Aprobar Excepciones PDF', 'vista' => 'pdf.aprobar', 'descripcion' => 'Aprobar PDF si no cumplen horas mensuales'],
            ['nombre' => 'Generar PDF Masivo', 'vista' => 'pdf.masivo', 'descripcion' => 'Descargar lote de historias en formato ZIP'],
            ['nombre' => 'Exportar Datos', 'vista' => 'datos.exportar', 'descripcion' => 'Exportar a Excel/CSV para análisis externo'],
        ];
    
        foreach ($permisos as $p) {
            // Usamos updateOrCreate para que no se dupliquen si corres el seeder varias veces
            Permiso::updateOrCreate(
                ['vista' => $p['vista']], // Condición de búsqueda
                $p                        // Datos a insertar/actualizar
            );
        }
    }
}
