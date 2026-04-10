<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Resa\Models\Salle;

class SalleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Salle::firstOrCreate([
            "code" => "S1",
            "libelle_court" => "Salle 1",
            "libelle_long" => "Salle Virtuel 1 FPS",
        ]);

        Salle::firstOrCreate([
            "code" => "S2",
            "libelle_court" => "Salle 2",
            "libelle_long" => "Salle Virtuel 2 FPS",
        ]);
        
        Salle::firstOrCreate([
            "code" => "S3",
            "libelle_court" => "Salle 3",
            "libelle_long" => "Salle Virtuel 3 FPS",
        ]);

        Salle::firstOrCreate([
            "code" => "S4",
            "libelle_court" => "Salle 4",
            "libelle_long" => "Salle Virtuel 4 FPS",
        ]);
    }
}