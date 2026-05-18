<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\TypeSalle;

class TypeSalleSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'libelle_court' => 'Simulateur drone',
                'libelle_long'  => 'Salle de simulation drone FPV et armement',
                'ordre'         => 1,
                'mode'          => TypeSalle::MODE_SIMULATEUR,
            ],
            [
                'libelle_court' => 'Salle classique',
                'libelle_long'  => 'Salle de reunion ou de cours',
                'ordre'         => 2,
                'mode'          => TypeSalle::MODE_CLASSIQUE,
            ],
        ];

        foreach ($types as $data) {
            TypeSalle::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
