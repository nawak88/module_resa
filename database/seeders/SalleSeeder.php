<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\TypeSalle;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $sim = TypeSalle::where('libelle_court', 'Simulateur drone')->firstOrFail();
        $cla = TypeSalle::where('libelle_court', 'Salle classique')->firstOrFail();

        $salles = [
            [
                'libelle_court' => 'Salle 1',
                'libelle_long'  => 'Salle principale de simulation drone',
                'type_salle_id' => $sim->id,
                'code'          => 'S1',
                'capacite'      => 4,
                'color'         => 'primary',
                'ordre'         => 1,
            ],
            [
                'libelle_court' => 'Salle 2',
                'libelle_long'  => 'Salle classique polyvalente',
                'type_salle_id' => $cla->id,
                'code'          => 'S2',
                'capacite'      => 12,
                'color'         => 'warning',
                'ordre'         => 2,
            ],
        ];

        foreach ($salles as $data) {
            Salle::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
