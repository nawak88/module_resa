<?php

namespace Modules\Resa\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Actions créées par GenericSkeletorPolicy :
     * index, create, store, update, delete, deleteAny, restore, forceDelete, forceDeleteAny, reorder
     *
     * Ici on ajoute uniquement les actions supplémentaires non couvertes par les policies.
     */
    private array $extraActions = ['import', 'export'];

    private array $resources = [
        'salle',
        'reservation',
    ];

    public function run(): void
    {
        // Permissions spécifiques hors-ressource
        $specific = [
            'resa::recherche_annuaire',
            'resa::voir_tous_les_marins',
            'resa::change_configuration_module',
            'resa::admin',
            'resa::reservation.view',
        ];

        foreach ($specific as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // import/export par ressource (les autres viennent des policies)
        foreach ($this->resources as $resource) {
            foreach ($this->extraActions as $action) {
                Permission::firstOrCreate([
                    'name'       => "resa::{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
