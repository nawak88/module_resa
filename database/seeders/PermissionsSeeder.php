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
            'resa::validation_salle',
            'resa::reservation.view',
           
        ];

        foreach ($specific as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

       
    }
}
