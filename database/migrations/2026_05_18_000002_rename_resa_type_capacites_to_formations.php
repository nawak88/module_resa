<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renommer la table type_capacites → formations
        Schema::rename('resa_type_capacites', 'resa_formations');

        // Renommer la colonne type_capacite_id → formation_id dans poste_capacites
        Schema::table('resa_poste_capacites', function (Blueprint $table) {
            $table->renameColumn('type_capacite_id', 'formation_id');
        });

        // Renommer la table poste_capacites → poste_formations
        Schema::rename('resa_poste_capacites', 'resa_poste_formations');

        // Renommer la colonne capacites_utilisees → formations_utilisees dans reservations
        Schema::table('resa_reservations', function (Blueprint $table) {
            $table->renameColumn('capacites_utilisees', 'formations_utilisees');
        });
    }

    public function down(): void
    {
        Schema::table('resa_reservations', function (Blueprint $table) {
            $table->renameColumn('formations_utilisees', 'capacites_utilisees');
        });

        Schema::rename('resa_poste_formations', 'resa_poste_capacites');

        Schema::table('resa_poste_capacites', function (Blueprint $table) {
            $table->renameColumn('formation_id', 'type_capacite_id');
        });

        Schema::rename('resa_formations', 'resa_type_capacites');
    }
};


