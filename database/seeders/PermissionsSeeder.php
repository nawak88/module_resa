<?php

namespace Modules\Resa\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
   

    public function run(): void
    {
        // Permissions spécifiques hors-ressource
        $specific = [
            'resa::validation_salle', // resa::reservation.valider
            'resa::reservation.view', // resa::reservation.voir
           
        ];

        foreach ($specific as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

       
    }
}
