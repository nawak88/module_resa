<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\TypeCapacite;

class TypeCapaciteSeeder extends Seeder
{
    public function run(): void
    {
        $capacites = [
            ['libelle_court' => 'FPV',  'libelle_long' => 'Pilotage First Person View', 'ordre' => 1],
            ['libelle_court' => '20mm', 'libelle_long' => 'Simulation armement 20mm',   'ordre' => 2],
            ['libelle_court' => 'Bob',  'libelle_long' => 'Simulation Bob',              'ordre' => 3],
        ];

        foreach ($capacites as $data) {
            TypeCapacite::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
