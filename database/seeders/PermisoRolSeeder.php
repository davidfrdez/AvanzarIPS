<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisoRolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        // 1. Obtener los IDs de los roles que ya insertaste
        $roles = DB::table('roles')->pluck('id', 'nombre');

        // 2. Obtener los IDs de los permisos que ya insertaste (usando 'vista' en lugar de 'slug')
        $permisos = DB::table('permisos')->pluck('id', 'vista');
        // 3. Mapeo exacto de Vistas por Rol basado en el documento SRS y el PermisosSeeder
        $mapeoRolesPermisos = [
            'Administrador' => [
                'usuarios.ver', 
                'usuarios.crear', 
                'usuarios.editar', 
                'roles.gestionar',
                'objetivos.gestionar', 
                'auditoria.ver', 
                'pacientes.carga_masiva', 
                'usuarios.reset_password'
            ],
            'Coordinador' => [
                'dashboards.ver', 
                'historial.ver', 
                'pdf.aprobar', 
                'pdf.masivo', 
                'datos.exportar'
            ],
            'Medico' => [
                'agenda.ver', 
                'pacientes.buscar', 
                'pacientes.crear', 
                'terapias.registrar', 
                'terapias.firmar', 
                'terapias.notas_libres'
            ]
        ];

        $pivoteData = [];

        // 4. Relacionar y preparar el array de inserción
        foreach ($mapeoRolesPermisos as $nombreRol => $vistasPermisos) {
            
            // Validar que el rol exista en la BD
            if (!isset($roles[$nombreRol])) {
                continue; 
            }

            $rolId = $roles[$nombreRol];

            foreach ($vistasPermisos as $vista) {
                // Validar que el permiso (vista) exista en la BD
                if (isset($permisos[$vista])) {
                    $pivoteData[] = [
                        'rol_id'     => $rolId,
                        'permiso_id' => $permisos[$vista],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // 5. Limpiar la tabla antes de insertar para evitar duplicados si se corre varias veces
        // (Opcional pero recomendado en tablas pivote durante desarrollo)
        DB::table('permiso_rol')->truncate(); 

        // 6. Insertar los datos en la tabla pivote de forma masiva
        if (!empty($pivoteData)) {
            DB::table('permiso_rol')->insert($pivoteData);
        }
    }
}
