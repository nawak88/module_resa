<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;

class SallePosteSeeder extends Seeder
{
    public function run(): void
    {
        $salle1      = Salle::where('libelle_court', 'Salle 1')->firstOrFail();
        $typeStandard = TypePoste::where('libelle_court', 'Poste standard')->firstOrFail();

        $postes = [
            ['libelle_court' => 'Poste 1', 'ordre' => 1],
            ['libelle_court' => 'Poste 2', 'ordre' => 2],
            ['libelle_court' => 'Poste 3', 'ordre' => 3],
            ['libelle_court' => 'Poste 4', 'ordre' => 4],
        ];

        foreach ($postes as $data) {
            SallePoste::firstOrCreate(
                [
                    'salle_id'      => $salle1->id,
                    'libelle_court' => $data['libelle_court'],
                ],
                [
                    'type_poste_id' => $typeStandard->id,
                    'ordre'         => $data['ordre'],
                ]
            );
        }
    }
}
