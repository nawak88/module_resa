<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Resa\Models\TypePoste;

class TypePosteSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['libelle_court' => 'Poste standard', 'libelle_long' => 'Poste de simulation standard', 'ordre' => 1],
        ];

        foreach ($types as $data) {
            TypePoste::firstOrCreate(
                ['libelle_court' => $data['libelle_court']],
                $data
            );
        }
    }
}
