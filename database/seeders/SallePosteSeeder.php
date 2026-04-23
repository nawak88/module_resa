<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;

class SallePosteSeeder extends Seeder
{
    public function run(): void
    {
        $typeStandard = TypePoste::where('libelle_court', 'Poste standard')->firstOrFail();

        // ── Salles simulateur : postes reels ───────────────────────────────
        $salle1 = Salle::where('libelle_court', 'Salle 1')->first();

        if ($salle1) {
            $postes = [
                ['libelle_court' => 'Poste 1', 'ordre' => 1, 'system' => false],
                ['libelle_court' => 'Poste 2', 'ordre' => 2, 'system' => false],
                ['libelle_court' => 'Poste 3', 'ordre' => 3, 'system' => false],
                ['libelle_court' => 'Poste 4', 'ordre' => 4, 'system' => false],
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
                        'system'        => $data['system'],
                    ]
                );
            }
        }

        // ── Salles classiques : poste fictif "Salle entiere" ───────────────
        // system = true : jamais affiche, jamais supprimable via l\'interface
        $sallesClassiques = Salle::whereHas('typeSalle', fn ($q) =>
            $q->where('mode', TypeSalle::MODE_CLASSIQUE)
        )->get();

        foreach ($sallesClassiques as $salle) {
            SallePoste::firstOrCreate(
                [
                    'salle_id'      => $salle->id,
                    'libelle_court' => 'Salle entiere',
                ],
                [
                    'type_poste_id' => $typeStandard->id,
                    'ordre'         => 0,
                    'system'        => true,
                ]
            );
        }
    }
}
