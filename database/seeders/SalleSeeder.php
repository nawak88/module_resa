<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\TypeSalle;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $drone = TypeSalle::where('libelle_court', 'Simulateur drone')->firstOrFail();

        $salles = [
            [
                'libelle_court' => 'Salle 1',
                'libelle_long'  => 'Salle principale de simulation drone',
                'type_salle_id' => $drone->id,
                'code'          => 'S1',
                'capacite'      => 4,
                'color'         => 'primary',
                'ordre'         => 1,
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
