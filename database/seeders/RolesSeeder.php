<?php

namespace Modules\Resa\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // --- Rôle Administrateur : toutes les permissions du module ---
        $role = Role::where(['name' => 'Administrateur Resa', 'guard_name' => 'web'])->first();

        if (null == $role) {
            $role = Role::firstOrCreate([
                'name'       => 'resa::admin',
                'guard_name' => 'web',
            ]);

            // Toutes les permissions existantes en base pour ce module
            $allPermissions = Permission::where('name', 'like', 'resa::%')
                ->pluck('name')
                ->toArray();

            $this->attachPermissionsToRole($role, $allPermissions);
        }

        // --- Rôle Marin : consultation uniquement ---
        // $role = Role::where(['name' => 'planificationgpd::marin', 'guard_name' => 'web'])->first();

        // if (null == $role) {
        //     $role = Role::firstOrCreate([
        //         'name'       => 'planificationgpd::marin',
        //         'guard_name' => 'web',
        //     ]);

        //     $this->attachPermissionsToRole($role, [
        //         'planificationgpd::marin.index',
        //         'planificationgpd::mission.index',
        //     ]);
        // }
    }

    private function attachPermissionsToRole(Role $role, array $permissions): void
    {
        foreach ($permissions as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                if (!$role->permissions()->where('id', $permission->id)->exists()) {
                    $role->permissions()->attach($permission->id);
                }
            } else {
                Log::warning("Permission manquante : {$permName}");
            }
        }
    }
}
