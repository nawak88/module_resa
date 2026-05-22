<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\Formation;

class FormationSeeder extends Seeder
{
    public function run(): void
    {
        $formations = [
            ['libelle_court' => 'FPV',  'libelle_long' => 'Drones (voilure fixe et quadricoptere)', 'ordre' => 1],
            ['libelle_court' => '20mm', 'libelle_long' => 'Simulation armement 20mm',   'ordre' => 2],
            ['libelle_court' => 'MENTOR',  'libelle_long' => 'Manutention Torpille MU90 Porteur FREMM','ordre' => 3],
            
            ['libelle_court' => 'MEOVDS',  'libelle_long' => 'Mise a l\'eau SONAR remorqué 4249 porteur FREMM','ordre' => 4],
            ['libelle_court' => '3D',  'libelle_long' => 'Modelisation 3D , logiciel BLENDER','ordre' => 5],
            
            ];

        foreach ($formations as $data) {
            Formation::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
