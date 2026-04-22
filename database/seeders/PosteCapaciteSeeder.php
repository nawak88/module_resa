<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\PosteCapacite;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;

class PosteCapaciteSeeder extends Seeder
{
    public function run(): void
    {
        $fpv  = TypeCapacite::where('libelle_court', 'FPV')->firstOrFail();
        $mm20 = TypeCapacite::where('libelle_court', '20mm')->firstOrFail();
        $bob  = TypeCapacite::where('libelle_court', 'Bob')->firstOrFail();

        $poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
        $poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
        $poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
        $poste4 = SallePoste::where('libelle_court', 'Poste 4')->firstOrFail();

        /**
         * Configuration des capacites par poste :
         *
         * Poste 1 : FPV, 20mm
         * Poste 2 : FPV
         * Poste 3 : 20mm
         * Poste 4 : FPV, 20mm, Bob
         */
        $liaisons = [
            ['salle_poste_id' => $poste1->id, 'type_capacite_id' => $fpv->id],
            ['salle_poste_id' => $poste1->id, 'type_capacite_id' => $mm20->id],

            ['salle_poste_id' => $poste2->id, 'type_capacite_id' => $fpv->id],

            ['salle_poste_id' => $poste3->id, 'type_capacite_id' => $mm20->id],

            ['salle_poste_id' => $poste4->id, 'type_capacite_id' => $fpv->id],
            ['salle_poste_id' => $poste4->id, 'type_capacite_id' => $mm20->id],
            ['salle_poste_id' => $poste4->id, 'type_capacite_id' => $bob->id],
        ];

        foreach ($liaisons as $data) {
            PosteCapacite::firstOrCreate($data);
        }
    }
}
