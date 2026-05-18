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
            'resa::admin',              // admin du module — valide toutes les réservations
            'resa::gestionnaire_salle', // gestionnaire — valide uniquement ses salles assignées
            'resa::reservation.view',   // voir toutes les réservations
        ];

        foreach ($specific as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

       
    }
}
