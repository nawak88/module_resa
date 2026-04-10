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
            
            
            SalleSeeder::class,
            
           

            PermissionsSeeder::class,
            RolesSeeder::class,
        ]);

    }
}
