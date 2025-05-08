<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CodigosDelitosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codigosDelitos = [
            ['codigo' => 'ROB-001', 'descripcion' => 'Robo con violencia'],
            ['codigo' => 'ROB-002', 'descripcion' => 'Robo con intimidación'],
            ['codigo' => 'ROB-003', 'descripcion' => 'Robo en lugar habitado'],
            ['codigo' => 'ROB-004', 'descripcion' => 'Robo en lugar no habitado'],
            ['codigo' => 'ROB-005', 'descripcion' => 'Robo de vehículo'],
            ['codigo' => 'ROB-006', 'descripcion' => 'Robo de objetos personales'],
            ['codigo' => 'ROB-007', 'descripcion' => 'Robo en establecimiento comercial'],
            ['codigo' => 'HUR-001', 'descripcion' => 'Hurto simple'],
            ['codigo' => 'HUR-002', 'descripcion' => 'Hurto de vehículos'],
            ['codigo' => 'HUR-003', 'descripcion' => 'Hurto en comercio'],
            ['codigo' => 'HUR-004', 'descripcion' => 'Hurto en domicilio'],
            ['codigo' => 'HUR-005', 'descripcion' => 'Hurto de objetos en lugares públicos'],
            ['codigo' => 'ASA-001', 'descripcion' => 'Asalto con arma blanca'],
            ['codigo' => 'ASA-002', 'descripcion' => 'Asalto con arma de fuego'],
            ['codigo' => 'ASA-003', 'descripcion' => 'Asalto en la vía pública'],
            ['codigo' => 'ASA-004', 'descripcion' => 'Asalto a mano armada en comercio'],
            ['codigo' => 'ASA-005', 'descripcion' => 'Asalto en transporte público'],
            ['codigo' => 'VIO-001', 'descripcion' => 'Agresión física'],
            ['codigo' => 'VIO-002', 'descripcion' => 'Agresión sexual'],
            ['codigo' => 'VIO-003', 'descripcion' => 'Maltrato familiar'],
            ['codigo' => 'VIO-004', 'descripcion' => 'Abuso infantil'],
            ['codigo' => 'VIO-005', 'descripcion' => 'Lesiones graves'],
            ['codigo' => 'VIO-006', 'descripcion' => 'Violencia psicológica'],
            ['codigo' => 'TD-001', 'descripcion' => 'Tráfico de drogas ilícitas'],
            ['codigo' => 'TD-002', 'descripcion' => 'Tráfico de drogas en pequeña escala'],
            ['codigo' => 'TD-003', 'descripcion' => 'Cultivo de drogas'],
            ['codigo' => 'TD-004', 'descripcion' => 'Distribución de drogas'],
            ['codigo' => 'TD-005', 'descripcion' => 'Tráfico de estupefacientes a través de internet'],
            ['codigo' => 'DT-001', 'descripcion' => 'Conducir bajo influencia del alcohol'],
            ['codigo' => 'DT-002', 'descripcion' => 'Conducir bajo influencia de drogas'],
            ['codigo' => 'DT-003', 'descripcion' => 'Exceso de velocidad'],
            ['codigo' => 'DT-004', 'descripcion' => 'Conducir sin licencia'],
            ['codigo' => 'DT-005', 'descripcion' => 'Conducir sin seguro'],
            ['codigo' => 'DT-006', 'descripcion' => 'Desobedecer señales de tránsito'],
            ['codigo' => 'DT-007', 'descripcion' => 'Accidente de tránsito fatal'],
            ['codigo' => 'DT-008', 'descripcion' => 'Accidente de tránsito con daños materiales'],
            ['codigo' => 'FRA-001', 'descripcion' => 'Fraude electrónico'],
            ['codigo' => 'FRA-002', 'descripcion' => 'Estafa financiera'],
            ['codigo' => 'FRA-003', 'descripcion' => 'Fraude con tarjetas de crédito'],
            ['codigo' => 'FRA-004', 'descripcion' => 'Fraude en seguros'],
            ['codigo' => 'FRA-005', 'descripcion' => 'Fraude fiscal'],
            ['codigo' => 'HOM-001', 'descripcion' => 'Homicidio doloso'],
            ['codigo' => 'HOM-002', 'descripcion' => 'Homicidio culposo'],
            ['codigo' => 'HOM-003', 'descripcion' => 'Homicidio por violencia de género'],
            ['codigo' => 'HOM-004', 'descripcion' => 'Homicidio por venganza'],
            ['codigo' => 'HOM-005', 'descripcion' => 'Homicidio múltiple'],
            ['codigo' => 'DES-001', 'descripcion' => 'Desaparición forzada'],
            ['codigo' => 'DES-002', 'descripcion' => 'Desaparición involuntaria'],
            ['codigo' => 'DES-003', 'descripcion' => 'Desaparición de menores'],
            ['codigo' => 'DES-004', 'descripcion' => 'Desaparición por causas delictivas'],
            ['codigo' => 'DES-005', 'descripcion' => 'Desaparición en el contexto de conflictos sociales'],
            ['codigo' => 'EXT-001', 'descripcion' => 'Extorsión con violencia'],
            ['codigo' => 'EXT-002', 'descripcion' => 'Extorsión sin violencia'],
            ['codigo' => 'EXT-003', 'descripcion' => 'Extorsión en el ámbito comercial'],
            ['codigo' => 'SEC-001', 'descripcion' => 'Secuestro con fines de extorsión'],
            ['codigo' => 'SEC-002', 'descripcion' => 'Secuestro por venganza'],
            ['codigo' => 'SEC-003', 'descripcion' => 'Secuestro de menores'],
            ['codigo' => 'DI-001', 'descripcion' => 'Ciberacoso'],
            ['codigo' => 'DI-002', 'descripcion' => 'Phishing'],
            ['codigo' => 'DI-003', 'descripcion' => 'Hacking'],
            ['codigo' => 'DI-004', 'descripcion' => 'Fraude cibernético'],
            ['codigo' => 'DI-005', 'descripcion' => 'Suplantación de identidad'],
            ['codigo' => 'VAN-001', 'descripcion' => 'Daños a la propiedad pública'],
            ['codigo' => 'VAN-002', 'descripcion' => 'Daños a la propiedad privada'],
            ['codigo' => 'VAN-003', 'descripcion' => 'Graffiti no autorizado'],
            ['codigo' => 'VAN-004', 'descripcion' => 'Incendio provocado'],
        ];
        foreach ($codigosDelitos as $codigoDelito) {
            DB::table('codigos_delitos')->insert([
                'codigo' => $codigoDelito['codigo'],
                'descripcion' => $codigoDelito['descripcion'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
