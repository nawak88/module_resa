<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\TypeSalle;

class TypeSalleSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['libelle_court' => 'Simulateur drone', 'libelle_long' => 'Salle de simulation drone FPV et armement', 'ordre' => 1],
            ['libelle_court' => 'Simulateur SSM',   'libelle_long' => 'Salle de simulation systeme de surface', 'ordre' => 2],
        ];

        foreach ($types as $data) {
            TypeSalle::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
