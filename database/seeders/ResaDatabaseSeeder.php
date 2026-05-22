<?php

namespace Modules\Resa\Database\Seeders;

use Illuminate\Database\Seeder;

class ResaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([    
            SettingSeeder::class,
            TypeSalleSeeder::class,
            TypePosteSeeder::class,
            FormationSeeder::class,
            SalleSeeder::class,
            SallePosteSeeder::class,
            PosteFormationSeeder::class,

        
           

            PermissionsSeeder::class,
            RolesSeeder::class,
        ]);

    }
}
