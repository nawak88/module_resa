<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\PosteFormation;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\Formation;

class PosteFormationSeeder extends Seeder
{
    public function run(): void
    {
        $fpv  = Formation::where('libelle_court', 'FPV')->firstOrFail();
        $mm20 = Formation::where('libelle_court', '20mm')->firstOrFail();
        $mentor  = Formation::where('libelle_court', 'MENTOR')->firstOrFail();
        $meovds  = Formation::where('libelle_court', 'MEOVDS')->firstOrFail();
        $d3  = Formation::where('libelle_court', '3D')->firstOrFail();


        $poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
        $poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
        $poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
        $poste4 = SallePoste::where('libelle_court', 'Poste 4')->firstOrFail();

        /**
         * Configuration des formations par poste :
         *
         * Poste 1 : FPV, 20mm
         * Poste 2 : FPV
         * Poste 3 : 20mm
         * Poste 4 : FPV, 20mm, Bob
         */
        $liaisons = [
            ['salle_poste_id' => $poste1->id, 'formation_id' => $fpv->id],
            ['salle_poste_id' => $poste1->id, 'formation_id' => $mm20->id],
            ['salle_poste_id' => $poste1->id, 'formation_id' => $mentor->id],
            ['salle_poste_id' => $poste1->id, 'formation_id' => $meovds->id],
            ['salle_poste_id' => $poste1->id, 'formation_id' => $d3->id],

            ['salle_poste_id' => $poste2->id, 'formation_id' => $fpv->id],
            ['salle_poste_id' => $poste2->id, 'formation_id' => $mm20->id],
            ['salle_poste_id' => $poste2->id, 'formation_id' => $mentor->id],
            ['salle_poste_id' => $poste2->id, 'formation_id' => $meovds->id],
            ['salle_poste_id' => $poste2->id, 'formation_id' => $d3->id],

            ['salle_poste_id' => $poste3->id, 'formation_id' => $fpv->id],
            ['salle_poste_id' => $poste3->id, 'formation_id' => $mm20->id],
            ['salle_poste_id' => $poste3->id, 'formation_id' => $mentor->id],
            ['salle_poste_id' => $poste3->id, 'formation_id' => $meovds->id],
            ['salle_poste_id' => $poste3->id, 'formation_id' => $d3->id],
            

            ['salle_poste_id' => $poste4->id, 'formation_id' => $fpv->id],
            ['salle_poste_id' => $poste4->id, 'formation_id' => $mm20->id],
            ['salle_poste_id' => $poste4->id, 'formation_id' => $mentor->id],
            ['salle_poste_id' => $poste4->id, 'formation_id' => $meovds->id],
            ['salle_poste_id' => $poste4->id, 'formation_id' => $d3->id],
        ];

        foreach ($liaisons as $data) {
            PosteFormation::firstOrCreate($data);
        }
    }
}
